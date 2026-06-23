<nav class="navbar">
    <div class="navbar-inner">
        <a href="{{ route('projects.index') }}" class="navbar-brand">
            <img src="{{ asset('img/logo.svg') }}" alt="" class="navbar-logo" width="28" height="28">
            <span>Pritask</span>
        </a>
        <div class="navbar-links">
            <a href="{{ route('projects.index') }}" @class(['is-active' => request()->routeIs('projects.*')])>Projects</a>
            <a href="{{ route('issues.index') }}" @class(['is-active' => request()->routeIs('issues.*')])>Issues</a>
            <a href="{{ route('tags.index') }}" @class(['is-active' => request()->routeIs('tags.*')])>Tags</a>
        </div>
    </div>
</nav>
