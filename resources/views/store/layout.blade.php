<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Support Desk')</title>
    <link rel="stylesheet" href="/assets/app.css">
    <link rel="stylesheet" href="/assets/marketplace.css">
    @yield('seo')
</head>
<body>
    <header class="container mp-header">
        <a class="brand" href="/support">Support Desk</a>
        @auth
            <nav aria-label="Account">
                @if(auth()->user()->isAdministrator())<a href="/team">Team</a>@endif
                <span>{{ auth()->user()->name }}</span>
                <form method="post" action="/logout">@csrf<button class="button">Sign out</button></form>
            </nav>
        @else
            <a class="button" href="/login">Sign in</a>
        @endauth
    </header>
    <main class="container mp-main">
        @if($errors->any())<ul class="mp-notice" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        @yield('content')
    </main>
</body>
</html>
