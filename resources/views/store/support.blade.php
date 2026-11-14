@extends('store.layout')
@section('title', 'Support — Support Desk')
@section('content')
@php($options = \App\Http\Controllers\SupportController::class)
<header class="mp-product-hero"><div>
    <h1>{{ __($queue ? 'Support inbox' : 'Support') }}</h1>
    <p>{{ __('Open a private ticket and follow the conversation here.') }}</p>
</div></header>
@guest
    <a class="button primary" href="{{ route('login') }}">{{ __('Log in to contact support') }}</a>
@else
    @if(\App\Http\Controllers\SupportController::isStaff(auth()->user()))
        <p><a href="{{ route('support.index', $queue ? [] : ['queue' => 1]) }}">{{ __($queue ? 'My tickets' : 'Support inbox') }}</a></p>
    @endif
    <div @class(['support-layout', 'support-queue' => $queue])>
        <section class="support-panel">
            <h2>{{ __($queue ? 'Ticket queue' : 'My tickets') }}</h2>
            @if($queue)
                <nav class="mp-library-nav" aria-label="{{ __('Ticket status') }}">
                    <a href="{{ route('support.index', ['queue' => 1]) }}" @unless($closed) aria-current="page" @endunless>{{ __('Open tickets') }}</a>
                    <a href="{{ route('support.index', ['queue' => 1, 'status' => 'closed']) }}" @if($closed) aria-current="page" @endif>{{ __('Closed tickets') }}</a>
                </nav>
            @endif
            <form class="support-form" method="get" action="{{ route('support.index') }}">
                @if($queue)<input type="hidden" name="queue" value="1">@endif
                @if($closed)<input type="hidden" name="status" value="closed">@endif
                <label>{{ __('Search tickets') }} <input type="search" name="q" maxlength="150" value="{{ request()->string('q') }}" placeholder="{{ __('Subject or ticket number') }}"></label>
                <button class="button">{{ __('Search') }}</button>
            </form>
            <ul class="support-cards">
                @forelse($tickets as $ticket)
                    <li class="support-card">
                        <div class="support-card-top"><span>#{{ $ticket->id }}</span><span class="support-badge">{{ __($options::STATUSES[$ticket->status]) }}</span></div>
                        <h3><a href="{{ route('support.show', ['ticket' => $ticket->id] + ($queue ? ['queue' => 1] : [])) }}">{{ $ticket->subject }}</a></h3>
                        <p>{{ __($options::DEPARTMENTS[$ticket->department]) }} · {{ __('Priority: :priority', ['priority' => __($options::PRIORITIES[$ticket->priority])]) }}</p>
                        <p>{{ __('Updated :date', ['date' => $ticket->updated_at]) }}</p>
                    </li>
                @empty
                    <li>{{ __('No tickets yet.') }}</li>
                @endforelse
            </ul>
            @include('store.support-pagination', ['pages' => $tickets])
        </section>
        @unless($queue)
            <form class="support-panel support-form" method="post" action="{{ route('support.store') }}">
                @csrf
                <h2>{{ __('New ticket') }}</h2>
                <label>{{ __('Subject') }} <input name="subject" maxlength="150" value="{{ old('subject') }}" required></label>
                <label>{{ __('Message') }} <textarea name="body" rows="6" maxlength="5000" required>{{ old('body') }}</textarea></label>
                <div class="support-fields">
                    @include('store.support-select', ['field' => 'department', 'label' => 'Department', 'choices' => $options::DEPARTMENTS, 'selected' => 'technical'])
                    @include('store.support-select', ['field' => 'priority', 'label' => 'Priority', 'choices' => $options::PRIORITIES, 'selected' => 'normal'])
                </div>
                <button class="button primary">{{ __('Send ticket') }}</button>
            </form>
        @endunless
    </div>
@endguest
@endsection
