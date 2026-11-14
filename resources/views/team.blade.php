@extends('store.layout')
@section('content')
<section class="support-panel">
    <h1>Team</h1>
    <form class="support-form" method="get">
        <label>Search by name or email <input name="q" type="search" value="{{ request('q') }}"></label>
        <button class="button">Search</button>
    </form>
    <ul class="support-list">
        @forelse($users as $user)
            <li>
                <span>{{ $user->name }} · {{ $user->email }} · {{ ucfirst($user->role) }}</span>
                <form method="post" action="/team/{{ $user->id }}">
                    @csrf
                    <button class="button">{{ $user->role === 'support' ? 'Remove Support role' : 'Assign Support role' }}</button>
                </form>
            </li>
        @empty
            <li>No matching accounts.</li>
        @endforelse
    </ul>
    @include('store.support-pagination', ['pages' => $users])
</section>
@endsection
