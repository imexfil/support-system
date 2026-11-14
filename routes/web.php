<?php

use App\Http\Controllers\SupportController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

Route::redirect('/', '/support');
Route::get('/support', [SupportController::class, 'index'])->name('support.index');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'identity', ['register' => false])->name('login');
    Route::view('/register', 'identity', ['register' => true]);
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }
        $request->session()->regenerate();

        return redirect()->intended('/support');
    })->middleware('throttle:5,1');
    Route::post('/register', function (Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);
        $data['password'] = Hash::make($data['password']);
        Auth::login(User::create($data));
        $request->session()->regenerate();

        return redirect('/support');
    })->middleware('throttle:5,1');
});

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/support');
    });
    Route::prefix('support')->name('support.')->group(function () {
        Route::post('/', [SupportController::class, 'store'])->middleware('throttle:5,1')->name('store');
        Route::get('/{ticket}', [SupportController::class, 'show'])->whereNumber('ticket')->name('show');
        Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->whereNumber('ticket')->middleware('throttle:20,1')->name('reply');
        Route::post('/{ticket}/status', [SupportController::class, 'status'])->whereNumber('ticket')->name('status');
    });
    Route::get('/team', function (Request $request) {
        abort_unless($request->user()->isAdministrator(), 403);
        $search = $request->string('q')->limit(150)->toString();
        $users = User::whereIn('role', ['customer', 'support'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('name', '%'.$search.'%')->orWhereLike('email', '%'.$search.'%')))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('team', compact('users'));
    });
    Route::post('/team/{user}', function (Request $request, User $user) {
        abort_unless($request->user()->isAdministrator(), 403);
        abort_unless(in_array($user->role, ['customer', 'support'], true), 404);
        User::whereKey($user->id)->where('role', $user->role)
            ->update(['role' => $user->role === 'support' ? 'customer' : 'support']);

        return redirect('/team');
    });
});
