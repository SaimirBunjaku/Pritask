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
        <div class="navbar-user">
            @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
            <div class="navbar-notifications-wrap"
                 data-notifications-root
                 data-notifications-url="{{ route('notifications.index') }}"
                 data-notifications-read-all-url="{{ route('notifications.readAll') }}"
                 data-notifications-read-url="{{ url('notifications') }}/">
                <button type="button"
                        class="navbar-notifications-btn"
                        data-action="toggle-notifications"
                        aria-label="Notifications"
                        aria-expanded="false"
                        aria-haspopup="true">
                    <svg class="navbar-notifications-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path fill="currentColor" d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Z"/>
                    </svg>
                    <span class="navbar-notifications-badge"
                          data-notifications-count
                          @if ($unreadNotifications === 0) hidden @endif>{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                </button>
                <div class="navbar-notifications-panel" data-notifications-panel hidden>
                    <div class="navbar-notifications-header">
                        <span class="navbar-notifications-title">Notifications</span>
                        <button type="button"
                                class="navbar-notifications-mark-all"
                                data-action="mark-all-notifications-read"
                                hidden>Mark all read</button>
                    </div>
                    <div class="navbar-notifications-list" data-notifications-list role="list"></div>
                    <p class="navbar-notifications-empty text-muted" data-notifications-empty hidden>No notifications yet.</p>
                    <p class="navbar-notifications-loading text-muted" data-notifications-loading hidden>Loading&hellip;</p>
                </div>
            </div>
            <span class="navbar-user-name">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Log out</button>
            </form>
        </div>
    </div>
</nav>
