<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportController extends Controller
{
    public const STATUSES = ['open' => 'Open', 'review' => 'In review', 'hold' => 'On hold', 'waiting' => 'Waiting for customer', 'resolved' => 'Resolved', 'closed' => 'Closed'];

    public const DEPARTMENTS = ['technical' => 'Technical', 'billing' => 'Billing', 'general' => 'General'];

    public const PRIORITIES = ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'];

    public static function isStaff(?User $user): bool
    {
        return $user && in_array($user->role, ['admin', 'support'], true);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $queue = $request->boolean('queue');
        $closed = $request->query('status') === 'closed';
        $search = trim($request->string('q')->limit(150)->toString());

        abort_if($queue && ! self::isStaff($user), 403);

        $tickets = $user ? DB::table('support_tickets')
            ->when(! $queue, fn ($query) => $query->where('user_id', $user->id))
            ->when($queue || $request->has('status'), fn ($query) => $closed ? $query->whereNotNull('closed_at') : $query->whereNull('closed_at'))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereLike('subject', '%'.$search.'%');
                if (ctype_digit($search) && strlen($search) < 19) {
                    $query->orWhere('id', (int) $search);
                }
            }))
            ->orderByDesc('updated_at')->orderByDesc('id')->paginate(20)->withQueryString() : null;

        return response()->view('store.support', compact('tickets', 'queue', 'closed'))->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['subject' => 'required|string|max:150', 'body' => 'required|string|max:5000', ...$this->classificationRules()]);
        $id = DB::transaction(function () use ($request, $data) {
            $id = DB::table('support_tickets')->insertGetId([
                'user_id' => $request->user()->id,
                'subject' => $data['subject'],
                'department' => $data['department'] ?? 'technical',
                'priority' => $data['priority'] ?? 'normal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->insertMessage($id, $data['body'], $request->user(), false);

            return $id;
        });

        return redirect()->route('support.show', $id);
    }

    public function show(Request $request, int $ticket)
    {
        $record = $this->ticket($request, $ticket);
        $owner = User::find($record->user_id);
        $query = DB::table('support_messages')->where('support_ticket_id', $ticket);
        if ($request->expectsJson()) {
            return response()->json([
                'closed' => $record->closed_at !== null,
                'status' => __(self::STATUSES[$record->status]),
                'messages' => $this->presentMessages((clone $query)
                    ->where('id', '>', max(0, $request->integer('after')))
                    ->orderBy('id')->limit(100)->get(), $owner),
            ])->header('Cache-Control', 'private, no-store');
        }

        $messages = $query->orderByDesc('id')->paginate(50)->withQueryString();
        $messages->setCollection($this->presentMessages($messages->getCollection(), $owner));

        return response()->view('store.support-thread', [
            'ticket' => $record,
            'owner' => $owner,
            'replyRole' => $request->user()->isAdministrator() ? 'Administrator' : 'Support',
            'messages' => $messages,
            'staff' => $this->staffView($request, $record),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function reply(Request $request, int $ticket)
    {
        $record = $this->ticket($request, $ticket);
        $data = $request->validate(['body' => 'required|string|max:5000']);
        $staff = $this->staffView($request, $record);
        DB::transaction(function () use ($request, $record, $data, $staff) {
            $updated = DB::table('support_tickets')->where('id', $record->id)->whereNull('closed_at')
                ->update(['updated_at' => now()]);
            abort_unless($updated, 409, __('This ticket is closed.'));
            $this->insertMessage($record->id, $data['body'], $request->user(), $staff);
        });

        return redirect()->route('support.show', ['ticket' => $ticket] + ($staff ? ['queue' => 1] : []));
    }

    public function status(Request $request, int $ticket)
    {
        $record = $this->ticket($request, $ticket);
        abort_unless(self::isStaff($request->user()), 403);
        if (! $request->has('status')) {
            $request->validate(['closed' => 'required|boolean']);
            $request->merge(['status' => $request->boolean('closed') ? 'closed' : 'open']);
        }
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'subject' => 'sometimes|required|string|max:150',
            ...$this->classificationRules(),
        ]);
        DB::table('support_tickets')->where('id', $record->id)->update([
            ...$data,
            'closed_at' => $data['status'] === 'closed' ? ($record->closed_at ?? now()) : null,
            'updated_at' => now(),
        ]);

        return redirect()->route('support.show', ['ticket' => $ticket, 'queue' => 1]);
    }

    private function staffView(Request $request, object $ticket): bool
    {
        return self::isStaff($request->user())
            && ($request->boolean('queue') || (int) $ticket->user_id !== (int) $request->user()->id);
    }

    private function ticket(Request $request, int $id): object
    {
        $user = $request->user();
        $ticket = DB::table('support_tickets')->where('id', $id)
            ->when(! self::isStaff($user), fn ($query) => $query->where('user_id', $user->id))
            ->first();
        abort_unless($ticket, 404);

        return $ticket;
    }

    private function insertMessage(int $ticket, string $body, User $author, bool $staff): void
    {
        DB::table('support_messages')->insert([
            'support_ticket_id' => $ticket,
            'body' => $body,
            'from_support' => $staff,
            'author_id' => $author->id,
            'author_name' => $author->name,
            'author_role' => $staff ? ($author->isAdministrator() ? 'Administrator' : 'Support') : 'Customer',
            'created_at' => now(),
        ]);
    }
    private function presentMessages($messages, ?User $owner)
    {
        $authors = User::whereIn('id', $messages->pluck('author_id')->filter()->unique())->get()->keyBy('id');

        return $messages->map(function ($message) use ($authors, $owner) {
            $author = $authors->get($message->author_id) ?? ($message->author_role === 'Customer' && ! $message->author_name ? $owner : null);
            $message->author_role_label = __($message->author_role);
            $message->author_name = $author?->name ?? $message->author_name;
            $message->avatar = null;
            unset($message->author_id, $message->support_ticket_id);

            return $message;
        });
    }

    private function classificationRules(): array
    {
        return [
            'department' => ['sometimes', 'required', Rule::in(array_keys(self::DEPARTMENTS))],
            'priority' => ['sometimes', 'required', Rule::in(array_keys(self::PRIORITIES))],
        ];
    }

}
