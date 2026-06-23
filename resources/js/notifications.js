export function initNotifications() {
    const root = document.querySelector('[data-notifications-root]');
    if (!root) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const toggleBtn = root.querySelector('[data-action="toggle-notifications"]');
    const panel = root.querySelector('[data-notifications-panel]');
    const list = root.querySelector('[data-notifications-list]');
    const emptyEl = root.querySelector('[data-notifications-empty]');
    const loadingEl = root.querySelector('[data-notifications-loading]');
    const countBadge = root.querySelector('[data-notifications-count]');
    const markAllBtn = root.querySelector('[data-action="mark-all-notifications-read"]');
    const indexUrl = root.dataset.notificationsUrl;
    const readAllUrl = root.dataset.notificationsReadAllUrl;
    const readUrlPrefix = root.dataset.notificationsReadUrl;

    let isOpen = false;
    let isLoading = false;

    function request(url, options = {}) {
        return fetch(url, {
            ...options,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                ...(options.headers || {}),
            },
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function updateBadge(count) {
        if (!countBadge) {
            return;
        }

        if (count > 0) {
            countBadge.hidden = false;
            countBadge.textContent = count > 9 ? '9+' : String(count);
        } else {
            countBadge.hidden = true;
            countBadge.textContent = '';
        }

        if (markAllBtn) {
            markAllBtn.hidden = count === 0;
        }
    }

    function renderNotifications(notifications) {
        if (!list) {
            return;
        }

        list.innerHTML = notifications.map((notification) => `
            <button type="button"
                    class="navbar-notification-item${notification.read ? '' : ' is-unread'}"
                    data-action="open-notification"
                    data-notification-id="${escapeHtml(notification.id)}"
                    data-issue-url="${escapeHtml(notification.issue_url)}"
                    role="listitem">
                <span class="navbar-notification-message">${escapeHtml(notification.message)}</span>
                <span class="navbar-notification-time">${escapeHtml(notification.created_at)}</span>
            </button>
        `).join('');

        const hasItems = notifications.length > 0;
        emptyEl.hidden = hasItems;
        list.hidden = !hasItems;
    }

    async function fetchNotifications() {
        if (isLoading) {
            return;
        }

        isLoading = true;
        loadingEl.hidden = false;

        try {
            const response = await request(indexUrl);

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            updateBadge(data.unreadCount);
            renderNotifications(data.notifications || []);
        } finally {
            isLoading = false;
            loadingEl.hidden = true;
        }
    }

    function closePanel() {
        isOpen = false;
        panel.hidden = true;
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    function openPanel() {
        isOpen = true;
        panel.hidden = false;
        toggleBtn.setAttribute('aria-expanded', 'true');
        fetchNotifications();
    }

    async function markAsRead(id) {
        const response = await request(`${readUrlPrefix}${id}/read`, { method: 'POST' });

        if (!response.ok) {
            return null;
        }

        const data = await response.json();
        updateBadge(data.unreadCount);

        return data;
    }

    async function markAllAsRead() {
        const response = await request(readAllUrl, { method: 'POST' });

        if (!response.ok) {
            return;
        }

        updateBadge(0);
        list.querySelectorAll('.navbar-notification-item.is-unread').forEach((item) => {
            item.classList.remove('is-unread');
        });
    }

    function openIssueFromNotification(url) {
        closePanel();

        const normalizedUrl = (() => {
            try {
                return new URL(url, window.location.origin).pathname;
            } catch {
                return url;
            }
        })();

        if (window.Pritask?.openIssue) {
            window.Pritask.openIssue(normalizedUrl, null, { fresh: true });
            return;
        }

        window.location.href = normalizedUrl;
    }

    toggleBtn.addEventListener('click', (event) => {
        event.stopPropagation();

        if (isOpen) {
            closePanel();
        } else {
            openPanel();
        }
    });

    root.addEventListener('click', async (event) => {
        const notificationBtn = event.target.closest('[data-action="open-notification"]');
        if (notificationBtn) {
            event.preventDefault();

            const id = notificationBtn.dataset.notificationId;
            const issueUrl = notificationBtn.dataset.issueUrl;

            if (id && !notificationBtn.classList.contains('is-unread')) {
                openIssueFromNotification(issueUrl);
                return;
            }

            if (id) {
                await markAsRead(id);
                notificationBtn.classList.remove('is-unread');
            }

            if (issueUrl) {
                openIssueFromNotification(issueUrl);
            }

            return;
        }

        if (event.target.closest('[data-action="mark-all-notifications-read"]')) {
            event.preventDefault();
            await markAllAsRead();
        }
    });

    document.addEventListener('click', (event) => {
        if (!isOpen) {
            return;
        }

        if (!root.contains(event.target)) {
            closePanel();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
            closePanel();
        }
    });
}
