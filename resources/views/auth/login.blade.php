@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <h1 class="auth-title">Log in</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="remember">
                Remember me
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Log in</button>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-secondary">Create account</a>
            @endif
        </div>
    </form>

    <div class="auth-demo text-muted">
        <p><strong>Demo accounts</strong> (password for all: <code>password</code>)</p>
        <ul class="auth-demo-list">
            <li><code>pritech@example.com</code> — project owner</li>
            <li><code>jordan@example.com</code> — project owner</li>
            <li><code>sam@example.com</code> — assignable member</li>
        </ul>
    </div>
@endsection
