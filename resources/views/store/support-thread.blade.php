@extends('store.layout')
@section('title', $ticket->subject.' — Support')
@section('seo')
<meta name="robots" content="noindex">
<script src="/assets/support.js?v={{ substr(hash_file('sha256', public_path('assets/support.js')), 0, 12) }}" defer></script>
@endsection
@section('content')
<a class="mp-back" href="{{ route('support.index', $staff ? (['queue' => 1] + ($ticket->closed_at ? ['status' => 'closed'] : [])) : []) }}">← {{ __('Back to tickets') }}</a>
@php($options = \App\Http\Controllers\SupportController::class)
<header class="support-thread-heading">
    <div><p class="mp-eyebrow">{{ __('Ticket #:id', ['id' => $ticket->id]) }}</p><h1>{{ $ticket->subject }}</h1></div>
    <span class="support-badge" data-ticket-status>{{ __($options::STATUSES[$ticket->status]) }}</span>
</header>
<div class="support-thread-layout" data-ticket-url="{{ $messages->currentPage() === 1 ? route('support.show', $ticket->id) : '' }}">
    <section class="support-panel" aria-label="{{ __('Conversation') }}">
        @include('store.support-pagination', ['pages' => $messages])
        <ol class="support-messages" data-ticket-messages aria-live="polite">
            @foreach($messages->reverse() as $message)
                <li data-message-id="{{ $message->id }}" @class(['support-agent' => $message->from_support])>
                    <div class="support-message-heading">
                        <span class="support-avatar" aria-hidden="true">
                            {{ mb_substr($message->author_name ?: $message->author_role, 0, 1) }}
                            @if($message->avatar)<img src="{{ $message->avatar }}" alt="" loading="lazy">@endif
                        </span>
                        <strong>{{ $message->author_role_label }}{{ $message->author_name ? ': '.$message->author_name : '' }}</strong>
                        <time>{{ $message->created_at }}</time>
                    </div>
                    <p>{{ $message->body }}</p>
                </li>
            @endforeach
        </ol>
        <form class="support-form" data-ticket-reply @if($ticket->closed_at) hidden @endif method="post" action="{{ route('support.reply', ['ticket' => $ticket->id] + ($staff ? ['queue' => 1] : [])) }}">
            @csrf
            <label>{{ __('Reply') }} <textarea name="body" rows="4" maxlength="5000" required>{{ old('body') }}</textarea></label>
            <button class="button primary">{{ __($staff ? 'Reply as '.$replyRole : 'Send reply') }}</button>
        </form>
    </section>
    <aside class="support-panel support-details">
        <h2>{{ __('Ticket details') }}</h2>
        <dl>
            <dt>{{ __('Customer') }}</dt><dd>{{ $owner?->name ?? __('Unknown') }}</dd>
            <dt>{{ __('Created') }}</dt><dd>{{ $ticket->created_at }}</dd>
            <dt>{{ __('Department') }}</dt><dd>{{ __($options::DEPARTMENTS[$ticket->department]) }}</dd>
            <dt>{{ __('Priority') }}</dt><dd>{{ __($options::PRIORITIES[$ticket->priority]) }}</dd>
        </dl>
        @if($staff)
            <form class="support-form" method="post" action="{{ route('support.status', $ticket->id) }}">
                @csrf
                <label>{{ __('Subject') }} <input name="subject" maxlength="150" value="{{ old('subject', $ticket->subject) }}" required></label>
                @include('store.support-select', ['field' => 'status', 'label' => 'Status', 'choices' => $options::STATUSES, 'selected' => $ticket->status])
                @include('store.support-select', ['field' => 'department', 'label' => 'Department', 'choices' => $options::DEPARTMENTS, 'selected' => $ticket->department])
                @include('store.support-select', ['field' => 'priority', 'label' => 'Priority', 'choices' => $options::PRIORITIES, 'selected' => $ticket->priority])
                <button class="button primary">{{ __('Save changes') }}</button>
            </form>
        @endif
    </aside>
</div>
@endsection
