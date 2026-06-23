<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pritask')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="container auth-container">
        <a href="{{ route('login') }}" class="navbar-brand auth-brand">
            <img src="{{ asset('img/logo.svg') }}" alt="" class="navbar-logo" width="28" height="28">
            <span>Pritask</span>
        </a>

        <div class="card auth-card">
            @yield('content')
        </div>
    </main>
</body>
</html>
