import { syncCustomSelect } from './custom-select';

export function initProjectShow() {
    const root = document.querySelector('[data-project-show]');
    const select = document.getElementById('project-switcher');
    const body = document.getElementById('project-show-body');

    if (!root || !select || !body) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let inflight = null;

    function syncActionLinks(data) {
        root.querySelector('[data-project-board-link]')?.setAttribute('href', data.boardUrl);
        root.querySelector('[data-project-edit-link]')?.setAttribute('href', data.editUrl);
        root.querySelector('[data-project-delete-form]')?.setAttribute('action', data.deleteUrl);
    }

    function syncSelectValue(url) {
        if (select.value === url) {
            return;
        }

        select.value = url;
        const wrapper = select.closest('.custom-select');
        if (wrapper) {
            syncCustomSelect(wrapper);
        }
    }

    function applyProjectData(data, { pushState = true } = {}) {
        body.innerHTML = data.bodyHtml;
        syncActionLinks(data);
        document.title = data.title;
        root.dataset.currentUrl = data.url;

        if (pushState) {
            history.pushState({ projectShowUrl: data.url }, data.title, data.url);
        }
    }

    async function loadProject(url, { pushState = true, syncSelect = false } = {}) {
        if (url === root.dataset.currentUrl && !syncSelect) {
            return;
        }

        if (inflight) {
            inflight.abort();
        }

        const controller = new AbortController();
        inflight = controller;

        root.classList.add('is-switching');

        try {
            const response = await fetch(url, {
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load project');
            }

            const data = await response.json();
            applyProjectData(data, { pushState });

            if (syncSelect) {
                syncSelectValue(data.url);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                syncSelectValue(root.dataset.currentUrl);
            }
        } finally {
            if (inflight === controller) {
                inflight = null;
            }
            root.classList.remove('is-switching');
        }
    }

    select.addEventListener('change', (event) => {
        loadProject(event.target.value);
    });

    window.addEventListener('popstate', (event) => {
        const url = event.state?.projectShowUrl || window.location.pathname;
        if (!url.startsWith('/projects/')) {
            return;
        }

        loadProject(url, { pushState: false, syncSelect: true });
    });
}
