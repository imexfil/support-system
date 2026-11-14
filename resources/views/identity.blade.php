@extends('store.layout')
@section('content')
<form class="support-panel support-form" method="post">
    @csrf
    <h1>{{ $register ? 'Create an account' : 'Sign in' }}</h1>
    @if($register)<label>Name <input name="name" required maxlength="100" autocomplete="name" value="{{ old('name') }}"></label>@endif
    <label>Email <input name="email" type="email" required autocomplete="email" value="{{ old('email') }}"></label>
    <label>Password <input name="password" type="password" required autocomplete="{{ $register ? 'new-password' : 'current-password' }}"></label>
    @if($register)<label>Repeat password <input name="password_confirmation" type="password" required autocomplete="new-password"></label>@endif
    <button class="button primary">{{ $register ? 'Register' : 'Sign in' }}</button>
    <a href="{{ $register ? '/login' : '/register' }}">{{ $register ? 'Sign in' : 'Create an account' }}</a>
</form>
@endsection
