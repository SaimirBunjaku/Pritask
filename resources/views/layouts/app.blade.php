<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="current-user-name" content="{{ auth()->user()->name }}">
    @endauth
    <title>@yield('title', 'Pritask')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.navbar')

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @include('partials.validation-errors')

        @yield('content')
    </main>

    <div class="modal-backdrop" id="issue-modal-backdrop">
        <div class="modal" id="issue-modal">
            <div id="issue-modal-body"></div>
        </div>
    </div>

    @include('partials.confirm-modal')

    @stack('scripts')
</body>
</html>
