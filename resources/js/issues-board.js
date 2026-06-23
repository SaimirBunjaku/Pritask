import { confirmAction } from './confirm-dialog';
import { clearFloatingMenuPosition, closeAllCustomSelects, initCustomSelects, positionFloatingMenu, syncCustomSelect } from './custom-select';

const TAG_PICKER_Z = 400;

export function initIssuesBoard() {
    const modalBackdrop = document.getElementById('issue-modal-backdrop');
    if (!modalBackdrop) {
        return;
    }

    const modalBody = document.getElementById('issue-modal-body');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const board = document.getElementById('board');
    const projectFilter = document.getElementById('filter-project');
    const statusFilter = document.getElementById('filter-status');
    const priorityFilter = document.getElementById('filter-priority');
    const tagFilter = document.getElementById('filter-tag');
    let blockIssueClick = false;

    function request(url, options = {}) {
        return fetch(url, {
            ...options,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers || {}),
            },
        });
    }

    const issueCache = new Map();
    const issueInflight = new Map();
    let modalRevision = 0;

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function readIssueData(trigger) {
        const raw = trigger?.getAttribute('data-issue');
        if (!raw) {
            return null;
        }

        try {
            return JSON.parse(raw);
        } catch {
            return null;
        }
    }

    function syncBoardProjectFilter(projectId) {
        if (!projectFilter) {
            return;
        }

        if (projectFilter.value !== projectId) {
            projectFilter.value = projectId;
            const wrapper = projectFilter.closest('.custom-select');
            if (wrapper) {
                syncCustomSelect(wrapper);
            }
        }

        applyFilters();
    }

    function getAllTags() {
        const viewport = document.querySelector('.board-viewport');
        if (!viewport?.dataset.allTags) {
            return [];
        }

        try {
            return JSON.parse(viewport.dataset.allTags);
        } catch {
            return [];
        }
    }

    function renderTagPickerHtml(available) {
        if (!available.length) {
            return '';
        }

        return `
            <div class="tag-picker" data-action="tag-picker">
                <button type="button" class="tag-picker-trigger" data-action="toggle-tag-select" aria-expanded="false">
                    <span class="tag-picker-placeholder">Add a tag&hellip;</span>
                    <svg class="tag-picker-chevron" width="12" height="12" viewBox="0 0 12 12" aria-hidden="true">
                        <path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="tag-picker-menu" hidden>
                    ${available.map((tag) => (
                        `<button type="button" class="tag-picker-option" data-action="pick-tag" data-tag-id="${escapeHtml(tag.id)}">
                            <span class="tag-picker-swatch" style="--tag-color: ${escapeHtml(tag.color)}"></span>
                            <span class="tag-picker-name">${escapeHtml(tag.name)}</span>
                        </button>`
                    )).join('')}
                </div>
            </div>
        `;
    }

    function ensureTagPickerId(picker) {
        if (!picker.dataset.pickerId) {
            picker.dataset.pickerId = `tag-picker-${Math.random().toString(36).slice(2, 9)}`;
        }

        return picker.dataset.pickerId;
    }

    function getTagPickerMenu(picker) {
        const pickerId = picker.dataset.pickerId;
        if (pickerId) {
            const floated = document.querySelector(`.tag-picker-menu[data-owner="${pickerId}"]`);
            if (floated) {
                return floated;
            }
        }

        return picker.querySelector('.tag-picker-menu');
    }

    function openTagPickerMenu(picker) {
        const trigger = picker.querySelector('.tag-picker-trigger');
        const menu = getTagPickerMenu(picker);
        const section = picker.closest('.issue-tags-section');
        const pickerId = ensureTagPickerId(picker);

        menu.dataset.owner = pickerId;
        if (section) {
            menu.dataset.issueId = section.dataset.issueId;
        }

        document.body.appendChild(menu);
        menu.hidden = false;
        menu.classList.add('tag-picker-menu-floating');
        positionFloatingMenu(trigger, menu, { zIndex: TAG_PICKER_Z });
    }

    function closeTagPicker(picker) {
        picker.classList.remove('is-open');
        const menu = getTagPickerMenu(picker);
        if (!menu) {
            return;
        }

        menu.hidden = true;
        menu.classList.remove('tag-picker-menu-floating');
        clearFloatingMenuPosition(menu);
        delete menu.dataset.owner;
        delete menu.dataset.issueId;

        if (menu.parentElement !== picker) {
            picker.appendChild(menu);
        }

        picker.querySelector('.tag-picker-trigger')?.setAttribute('aria-expanded', 'false');
    }

    function closeAllTagPickers() {
        document.querySelectorAll('.tag-picker.is-open').forEach((picker) => {
            closeTagPicker(picker);
        });
    }

    function repositionOpenTagPickers() {
        document.querySelectorAll('.tag-picker.is-open').forEach((picker) => {
            const trigger = picker.querySelector('.tag-picker-trigger');
            const menu = getTagPickerMenu(picker);
            if (trigger && menu) {
                positionFloatingMenu(trigger, menu, { repositionOnly: true, zIndex: TAG_PICKER_Z });
            }
        });
    }

    async function attachTagById(section, tagId) {
        const issueId = section?.dataset.issueId;
        const errorEl = section?.querySelector('[data-error-for="tag"]');

        if (errorEl) {
            errorEl.textContent = '';
        }

        if (!issueId || !tagId) {
            return;
        }

        const response = await request(`/issues/${issueId}/tags/${tagId}`, { method: 'POST' });

        if (response.ok) {
            syncTagResponse(await response.json());
            return;
        }

        if (response.status === 422) {
            const data = await response.json();
            if (errorEl) {
                errorEl.textContent = data.errors?.tag?.[0] ?? 'Could not add tag.';
            }
            return;
        }

        if (errorEl) {
            errorEl.textContent = 'Could not add tag.';
        }
    }

    function renderIssueTagsSection(issueId, attachedTags, allTags) {
        const attachedIds = new Set(attachedTags.map((tag) => String(tag.id)));
        const available = allTags.filter((tag) => !attachedIds.has(String(tag.id)));

        const listHtml = attachedTags.length
            ? attachedTags.map((tag) => (
                `<span class="tag-pill tag-pill-removable" style="--tag-color: ${escapeHtml(tag.color)}">
                    ${escapeHtml(tag.name)}
                    <button type="button" class="tag-pill-remove" data-action="detach-tag"
                        data-url="/issues/${escapeHtml(issueId)}/tags/${escapeHtml(tag.id)}"
                        aria-label="Remove ${escapeHtml(tag.name)}">&times;</button>
                </span>`
            )).join('')
            : '<span class="text-muted issue-tags-empty">No tags yet.</span>';

        const pickerHtml = renderTagPickerHtml(available);
        const hintHtml = !pickerHtml && allTags.length
            ? '<p class="text-muted issue-tags-hint">All tags are already attached.</p>'
            : '';

        return `
            <div class="issue-tags-section" data-issue-id="${escapeHtml(issueId)}">
                <label class="issue-tags-label">Tags</label>
                <div class="issue-tags-list">${listHtml}</div>
                ${pickerHtml}
                ${hintHtml}
                <p class="field-error" data-error-for="tag"></p>
            </div>
        `;
    }

    function renderIssueCommentsSection(issueId) {
        const id = escapeHtml(issueId);

        return `
            <div class="issue-comments-section"
                 data-issue-id="${id}"
                 data-comments-url="/issues/${id}/comments"
                 data-comments-store-url="/issues/${id}/comments">
                <label class="issue-comments-label">Comments</label>
                <form class="issue-comment-form" data-comment-form action="/issues/${id}/comments" method="POST">
                    <div class="form-group">
                        <label for="comment-author-${id}">Your name</label>
                        <input type="text" name="author_name" id="comment-author-${id}"
                            class="form-control" autocomplete="name" placeholder="Jane Doe">
                        <p class="field-error" data-error-for="author_name"></p>
                    </div>
                    <div class="form-group">
                        <label for="comment-body-${id}">Comment</label>
                        <textarea name="body" id="comment-body-${id}"
                            class="form-control issue-comment-textarea"
                            rows="4" placeholder="Write a comment…"></textarea>
                        <p class="field-error" data-error-for="body"></p>
                    </div>
                    <button type="submit" class="btn btn-primary issue-comment-submit" data-comment-submit>Add comment</button>
                </form>
                <div class="issue-comments-list" data-comments-list role="list"></div>
                <p class="issue-comments-status text-muted" data-comments-loading>Loading comments&hellip;</p>
                <p class="issue-comments-status text-muted" data-comments-empty hidden>No comments yet.</p>
                <button type="button" class="btn btn-secondary issue-comments-load-more"
                    data-action="load-more-comments" hidden>Load more comments</button>
            </div>
        `;
    }

    function renderIssueDetails(issue) {
        const tagsSectionHtml = renderIssueTagsSection(issue.id, issue.tags || [], getAllTags());
        const commentsSectionHtml = renderIssueCommentsSection(issue.id);

        const dueHtml = issue.dueDate
            ? `<span class="text-muted">Due ${escapeHtml(issue.dueDate)}</span>`
            : '';

        return `
            <div class="modal-header">
                <h2>${escapeHtml(issue.title)}</h2>
                <button type="button" class="modal-close" data-action="close-modal">&times;</button>
            </div>
            <div class="modal-meta">
                <span class="badge badge-${escapeHtml(issue.status)}">${escapeHtml(issue.statusLabel)}</span>
                <span class="badge badge-${escapeHtml(issue.priority)}">${escapeHtml(issue.priority)}</span>
                <span class="text-muted">${escapeHtml(issue.project)}</span>
                ${dueHtml}
            </div>
            ${tagsSectionHtml}
            <p>${escapeHtml(issue.description || 'No description provided.')}</p>
            ${commentsSectionHtml}
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-action="edit-issue" data-url="${escapeHtml(issue.editUrl)}">Edit</button>
                <button type="button" class="btn btn-danger" data-action="delete-issue" data-id="${escapeHtml(issue.id)}" data-url="${escapeHtml(issue.deleteUrl)}">Delete</button>
            </div>
        `;
    }

    function renderModalSkeleton() {
        return `
            <div class="modal-skeleton">
                <div class="modal-skeleton-line modal-skeleton-line-lg"></div>
                <div class="modal-skeleton-line modal-skeleton-line-sm"></div>
                <div class="modal-skeleton-line"></div>
                <div class="modal-skeleton-line"></div>
                <div class="modal-skeleton-line modal-skeleton-line-short"></div>
            </div>
        `;
    }

    function syncTagResponse(data) {
        closeAllTagPickers();

        const tagsSection = modalBody.querySelector('.issue-tags-section');
        if (tagsSection) {
            tagsSection.outerHTML = data.issueTags;
        }

        if (board && data.card) {
            const existing = document.querySelector(`.issue-card[data-id="${data.modalData.id}"]`);
            if (existing) {
                const template = document.createElement('template');
                template.innerHTML = data.card.trim();
                existing.replaceWith(template.content.firstElementChild);
                applyFilters();
            }
        }

        const card = document.querySelector(`.issue-card[data-id="${data.modalData.id}"]`);
        if (card) {
            card.setAttribute('data-issue', JSON.stringify(data.modalData));
        }

        const url = modalBody.dataset.issueUrl;
        if (url) {
            issueCache.set(url, modalBody.innerHTML);
        } else {
            invalidateIssueCache(data.modalData.id);
        }

        bumpModalRevision();
    }

    function loadIssueHtml(url) {
        if (issueCache.has(url)) {
            return Promise.resolve(issueCache.get(url));
        }

        if (issueInflight.has(url)) {
            return issueInflight.get(url);
        }

        const pending = request(url)
            .then((response) => response.text())
            .then((html) => {
                issueCache.set(url, html);
                issueInflight.delete(url);
                return html;
            })
            .catch((error) => {
                issueInflight.delete(url);
                throw error;
            });

        issueInflight.set(url, pending);
        return pending;
    }

    function bumpModalRevision() {
        modalRevision += 1;
        modalBody.dataset.modalRevision = String(modalRevision);

        return modalRevision;
    }

    function applyLoadedIssueHtml(url, html, revisionAtLoad) {
        if (modalBody.dataset.issueUrl !== url) {
            return;
        }

        if (Number(modalBody.dataset.modalRevision) !== revisionAtLoad) {
            return;
        }

        modalBody.innerHTML = html;
        initCustomSelects(modalBody);
        initIssueComments(modalBody);
        issueCache.set(url, html);
    }

    function invalidateIssueCache(issueId) {
        for (const url of issueCache.keys()) {
            if (url.includes(`/issues/${issueId}`)) {
                issueCache.delete(url);
            }
        }
    }

    function showModalContent(html, url = '') {
        modalBody.innerHTML = html;
        modalBody.dataset.issueUrl = url;
        bumpModalRevision();
        modalBackdrop.classList.add('is-open');
        initCustomSelects(modalBody);
        initIssueComments(modalBody);
    }

    function setCommentsLoading(section, isLoading) {
        section.querySelector('[data-comments-loading]').hidden = !isLoading;
    }

    function updateCommentsEmptyState(section) {
        const list = section.querySelector('[data-comments-list]');
        const empty = section.querySelector('[data-comments-empty]');
        empty.hidden = list.children.length > 0;
    }

    function updateLoadMoreButton(section) {
        const loadMoreBtn = section.querySelector('[data-action="load-more-comments"]');
        loadMoreBtn.hidden = section.dataset.commentsHasMore !== 'true';
    }

    async function loadCommentsPage(section, page, { replace = false } = {}) {
        const list = section.querySelector('[data-comments-list]');
        const loadMoreBtn = section.querySelector('[data-action="load-more-comments"]');
        const url = `${section.dataset.commentsUrl}?page=${page}`;

        if (replace) {
            setCommentsLoading(section, true);
        } else {
            loadMoreBtn.disabled = true;
        }

        try {
            const response = await request(url);

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (replace) {
                list.innerHTML = data.html;
            } else {
                list.insertAdjacentHTML('beforeend', data.html);
            }

            section.dataset.commentsHasMore = data.hasMore ? 'true' : 'false';
            section.dataset.commentsNextPage = String(data.nextPage ?? page + 1);

            updateCommentsEmptyState(section);
            updateLoadMoreButton(section);
        } finally {
            setCommentsLoading(section, false);
            loadMoreBtn.disabled = false;
        }
    }

    function initIssueComments(root) {
        root.querySelectorAll('.issue-comments-section:not([data-comments-init])').forEach((section) => {
            section.dataset.commentsInit = 'true';
            section.dataset.commentsHasMore = 'false';
            loadCommentsPage(section, 1, { replace: true });
        });
    }

    async function submitCommentForm(form) {
        const section = form.closest('.issue-comments-section');
        const list = section?.querySelector('[data-comments-list]');
        const submitBtn = form.querySelector('[data-comment-submit]');

        if (!section || !list) {
            return;
        }

        clearErrors(form);
        submitBtn.disabled = true;

        try {
            const response = await request(section.dataset.commentsStoreUrl, {
                method: 'POST',
                body: new FormData(form),
            });

            if (response.status === 422) {
                showErrors(form, (await response.json()).errors);
                return;
            }

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            list.insertAdjacentHTML('afterbegin', data.comment);
            form.reset();
            section.querySelector('[data-comments-empty]').hidden = true;

            const url = modalBody.dataset.issueUrl;
            if (url) {
                issueCache.set(url, modalBody.innerHTML);
            }
            bumpModalRevision();
        } finally {
            submitBtn.disabled = false;
        }
    }

    function closeModal() {
        closeAllTagPickers();
        closeAllCustomSelects();
        modalBackdrop.classList.remove('is-open');
        window.setTimeout(() => {
            modalBody.innerHTML = '';
            delete modalBody.dataset.issueUrl;
        }, 200);
    }

    function openIssue(url, trigger = null) {
        const issueData = readIssueData(trigger);
        const cachedHtml = issueCache.get(url);

        if (issueData) {
            showModalContent(renderIssueDetails(issueData), url);
            if (issueData.projectId && board) {
                syncBoardProjectFilter(String(issueData.projectId));
            }
            const loadRevision = Number(modalBody.dataset.modalRevision);
            loadIssueHtml(url)
                .then((html) => {
                    applyLoadedIssueHtml(url, html, loadRevision);
                })
                .catch(() => {});
            return;
        }

        if (cachedHtml) {
            showModalContent(cachedHtml, url);
            return;
        }

        showModalContent(renderModalSkeleton(), url);
        const loadRevision = Number(modalBody.dataset.modalRevision);
        loadIssueHtml(url)
            .then((html) => {
                applyLoadedIssueHtml(url, html, loadRevision);
            })
            .catch(() => {
                if (modalBody.dataset.issueUrl === url) {
                    modalBody.innerHTML = '<p class="modal-error">Could not load issue.</p>';
                }
            });
    }

    function prefetchIssue(trigger) {
        const url = trigger?.dataset.url;
        if (!url || readIssueData(trigger)) {
            return;
        }

        loadIssueHtml(url).catch(() => {});
    }

    function updateColumnCounts() {
        document.querySelectorAll('.board-column').forEach((column) => {
            const count = column.querySelectorAll('.issue-card:not(.is-hidden)').length;
            column.querySelector('.board-column-count').textContent = count;
        });
    }

    function getActiveBoardFilters() {
        return {
            project: projectFilter?.value || '',
            status: statusFilter?.value || '',
            priority: priorityFilter?.value || '',
            tag: tagFilter?.value || '',
        };
    }

    function cardMatchesFilters(card, filters = getActiveBoardFilters()) {
        if (filters.project && card.dataset.projectId !== String(filters.project)) {
            return false;
        }

        if (filters.status && card.dataset.status !== filters.status) {
            return false;
        }

        if (filters.priority && card.dataset.priority !== filters.priority) {
            return false;
        }

        if (filters.tag && !card.dataset.tags.split(',').filter(Boolean).includes(String(filters.tag))) {
            return false;
        }

        return true;
    }

    function navigateWithBoardFilters() {
        const params = new URLSearchParams();
        const filters = getActiveBoardFilters();

        if (filters.project) {
            params.set('project', filters.project);
        }

        if (filters.status) {
            params.set('status', filters.status);
        }

        if (filters.priority) {
            params.set('priority', filters.priority);
        }

        if (filters.tag) {
            params.set('tag', filters.tag);
        }

        const query = params.toString();
        const target = query ? `${window.location.pathname}?${query}` : window.location.pathname;
        const current = `${window.location.pathname}${window.location.search}`;

        if (current === target) {
            return;
        }

        window.location.assign(target);
    }

    function applyFilters() {
        const filters = getActiveBoardFilters();

        document.querySelectorAll('.issue-card').forEach((card) => {
            card.classList.toggle('is-hidden', !cardMatchesFilters(card, filters));
        });

        updateColumnCounts();
    }

    function upsertCard(html) {
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const newCard = template.content.firstElementChild;

        document.querySelector(`.issue-card[data-id="${newCard.dataset.id}"]`)?.remove();

        invalidateIssueCache(newCard.dataset.id);

        const columnBody = document.querySelector(`.board-column-body[data-status="${newCard.dataset.status}"]`);
        if (!columnBody) {
            invalidateIssueCache(newCard.dataset.id);
            return;
        }

        if (!cardMatchesFilters(newCard)) {
            invalidateIssueCache(newCard.dataset.id);
            closeModal();
            return;
        }

        const firstCard = columnBody.querySelector('.issue-card');
        if (firstCard) {
            columnBody.insertBefore(newCard, firstCard);
        } else {
            columnBody.querySelector('.board-column-header')?.after(newCard) ?? columnBody.append(newCard);
        }
        applyFilters();
        updateColumnCounts();
    }

    function clearErrors(form) {
        form.querySelectorAll('[data-error-for]').forEach((el) => {
            el.textContent = '';
        });
    }

    function showErrors(form, errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const el = form.querySelector(`[data-error-for="${field}"]`);
            if (el) {
                el.textContent = messages[0];
            }
        });
    }

    // a mousedown that starts inside the modal (e.g. selecting text) can end
    // with the mouseup/click landing on the backdrop once the selection drag
    // leaves the panel - only close if the whole gesture happened on the backdrop
    let mouseDownOnBackdrop = false;

    modalBackdrop.addEventListener('mousedown', (event) => {
        mouseDownOnBackdrop = event.target === modalBackdrop;
    });

    modalBackdrop.addEventListener('click', (event) => {
        if (event.target === modalBackdrop && mouseDownOnBackdrop) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.getElementById('confirm-modal-backdrop')?.hidden === false) {
            return;
        }

        if (event.key === 'Escape' && modalBackdrop.classList.contains('is-open')) {
            closeModal();
        }
    });

    // delegated globally so any page with an issue card/row can open the modal,
    // not just the board
    document.addEventListener('click', (event) => {
        if (blockIssueClick) {
            return;
        }

        const trigger = event.target.closest('[data-action="open-issue"]');
        if (trigger) {
            openIssue(trigger.dataset.url, trigger);
        }
    });

    document.addEventListener('mouseover', (event) => {
        prefetchIssue(event.target.closest('[data-action="open-issue"]'));
    });

    document.addEventListener('mousedown', (event) => {
        prefetchIssue(event.target.closest('[data-action="open-issue"]'));
    });

    document.getElementById('new-issue-btn')?.addEventListener('click', () => {
        openIssue('/issues/create');
    });

    document.querySelectorAll('[data-board-filter]').forEach((select) => {
        select.addEventListener('change', () => {
            navigateWithBoardFilters();
        });
    });

    document.addEventListener('click', async (event) => {
        const quickBtn = event.target.closest('[data-action="quick-status"]');
        if (quickBtn) {
            event.preventDefault();
            event.stopPropagation();

            const row = quickBtn.closest('.project-issue-row');
            const issueId = row?.dataset.issueId;
            if (!issueId) {
                return;
            }

            quickBtn.disabled = true;

            const response = await request(`/issues/${issueId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: quickBtn.dataset.status }),
            });

            if (response.ok) {
                const data = await response.json();
                if (row && data.projectRow) {
                    row.outerHTML = data.projectRow;
                }
                if (board && data.card) {
                    upsertCard(data.card);
                }
                invalidateIssueCache(issueId);
            }

            quickBtn.disabled = false;
            return;
        }
    });

    document.addEventListener('click', async (event) => {
        if (event.target.closest('[data-action="detach-tag"]')) {
            event.preventDefault();
            event.stopPropagation();
            const button = event.target.closest('[data-action="detach-tag"]');
            const section = button.closest('.issue-tags-section');
            if (!section || !modalBackdrop.classList.contains('is-open')) {
                return;
            }

            const errorEl = section.querySelector('[data-error-for="tag"]');
            if (errorEl) {
                errorEl.textContent = '';
            }

            const response = await request(button.dataset.url, { method: 'DELETE' });

            if (response.ok) {
                syncTagResponse(await response.json());
            } else if (errorEl) {
                errorEl.textContent = 'Could not remove tag.';
            }
            return;
        }

        if (event.target.closest('[data-action="pick-tag"]')) {
            event.preventDefault();
            event.stopPropagation();
            const option = event.target.closest('[data-action="pick-tag"]');
            const menu = option.closest('.tag-picker-menu');
            const section = menu?.dataset.issueId
                ? modalBody.querySelector(`.issue-tags-section[data-issue-id="${menu.dataset.issueId}"]`)
                : option.closest('.issue-tags-section');
            if (!section || !modalBackdrop.classList.contains('is-open')) {
                return;
            }
            closeAllTagPickers();
            await attachTagById(section, option.dataset.tagId);
            return;
        }

        if (event.target.closest('[data-action="toggle-tag-select"]')) {
            event.preventDefault();
            event.stopPropagation();
            const picker = event.target.closest('.tag-picker');
            const menu = getTagPickerMenu(picker);
            const willOpen = menu.hidden;

            closeAllTagPickers();

            if (willOpen) {
                picker.classList.add('is-open');
                picker.querySelector('.tag-picker-trigger')?.setAttribute('aria-expanded', 'true');
                openTagPickerMenu(picker);
            }
            return;
        }

        if (!event.target.closest('.tag-picker') && !event.target.closest('.tag-picker-menu')) {
            closeAllTagPickers();
        }
    });

    window.addEventListener('resize', repositionOpenTagPickers);

    document.addEventListener('scroll', (event) => {
        if (event.target.closest('.tag-picker-menu') || event.target.closest('.custom-select-menu')) {
            return;
        }

        repositionOpenTagPickers();
    }, true);

    modalBody.addEventListener('click', async (event) => {
        if (event.target.closest('[data-action="load-more-comments"]')) {
            const section = event.target.closest('.issue-comments-section');
            const nextPage = Number.parseInt(section?.dataset.commentsNextPage || '2', 10);
            if (section && section.dataset.commentsHasMore === 'true') {
                await loadCommentsPage(section, nextPage);
            }
            return;
        }

        if (event.target.matches('[data-action="close-modal"]')) {
            closeModal();
        }

        if (event.target.matches('[data-action="edit-issue"]')) {
            openIssue(event.target.dataset.url);
        }

        if (event.target.matches('[data-action="delete-issue"]')) {
            const button = event.target;
            const confirmed = await confirmAction({
                title: 'Delete issue?',
                message: 'This issue will be permanently deleted.',
                confirmLabel: 'Delete issue',
            });

            if (!confirmed) {
                return;
            }

            const response = await request(button.dataset.url, { method: 'DELETE' });

            if (response.ok) {
                invalidateIssueCache(button.dataset.id);
                if (board) {
                    document.querySelector(`.issue-card[data-id="${button.dataset.id}"]`)?.remove();
                    updateColumnCounts();
                    closeModal();
                } else {
                    window.location.reload();
                }
            }
        }
    });

    modalBody.addEventListener('submit', async (event) => {
        const commentForm = event.target.closest('[data-comment-form]');
        if (commentForm) {
            event.preventDefault();
            await submitCommentForm(commentForm);
            return;
        }

        const form = event.target.closest('[data-issue-form]');
        if (!form) {
            return;
        }

        event.preventDefault();
        clearErrors(form);

        const response = await request(form.action, {
            method: 'POST',
            body: new FormData(form),
        });

        if (response.status === 422) {
            const data = await response.json();
            showErrors(form, data.errors);
            return;
        }

        if (!response.ok) {
            return;
        }

        if (board) {
            const html = await response.text();
            upsertCard(html);
            closeModal();
        } else {
            window.location.reload();
        }
    });

    if (!board) {
        return;
    }

    const boardViewport = board.closest('.board-viewport');
    const scrollContainer = boardViewport ?? board;

    const scrollEdge = 72;
    const maxScrollSpeed = 5;
    let pointerX = 0;
    let pointerY = 0;
    let scrollRaf = null;

    function trackPointer(event) {
        pointerX = event.clientX ?? event.touches?.[0]?.clientX ?? pointerX;
        pointerY = event.clientY ?? event.touches?.[0]?.clientY ?? pointerY;
    }

    function scrollSpeedForPointer(pointer, edgeStart, edgeEnd) {
        if (pointer < edgeStart + scrollEdge) {
            const depth = 1 - Math.max(0, pointer - edgeStart) / scrollEdge;
            return -maxScrollSpeed * depth;
        }

        if (pointer > edgeEnd - scrollEdge) {
            const depth = 1 - Math.max(0, edgeEnd - pointer) / scrollEdge;
            return maxScrollSpeed * depth;
        }

        return 0;
    }

    function autoScrollStep() {
        const boardRect = scrollContainer.getBoundingClientRect();
        const horizontalDelta = scrollSpeedForPointer(pointerX, boardRect.left, boardRect.right);

        if (horizontalDelta !== 0) {
            scrollContainer.scrollLeft += horizontalDelta;
        }

        document.querySelectorAll('.board-column-body').forEach((columnBody) => {
            if (columnBody.scrollHeight <= columnBody.clientHeight + 1) {
                return;
            }

            const rect = columnBody.getBoundingClientRect();

            if (pointerX < rect.left || pointerX > rect.right) {
                return;
            }

            const verticalDelta = scrollSpeedForPointer(pointerY, rect.top, rect.bottom);

            if (verticalDelta !== 0) {
                columnBody.scrollTop += verticalDelta;
            }
        });

        scrollRaf = requestAnimationFrame(autoScrollStep);
    }

    function startBoardAutoScroll() {
        if (!scrollRaf) {
            scrollRaf = requestAnimationFrame(autoScrollStep);
        }
    }

    function stopBoardAutoScroll() {
        if (scrollRaf) {
            cancelAnimationFrame(scrollRaf);
            scrollRaf = null;
        }
    }

    document.querySelectorAll('.board-column-body').forEach((columnBody) => {
        // eslint-disable-next-line no-undef
        Sortable.create(columnBody, {
            group: 'board',
            draggable: '.issue-card',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            chosenClass: 'sortable-chosen',
            delay: 0,
            delayOnTouchOnly: true,
            emptyInsertThreshold: 24,
            scroll: scrollContainer,
            forceAutoScrollFallback: true,
            bubbleScroll: true,
            scrollSensitivity: 60,
            scrollSpeed: 5,
            onStart(event) {
                board.classList.add('is-dragging');
                pointerX = event.originalEvent?.clientX ?? 0;
                pointerY = event.originalEvent?.clientY ?? 0;
                document.addEventListener('mousemove', trackPointer);
                document.addEventListener('touchmove', trackPointer, { passive: true });
                document.addEventListener('dragover', trackPointer);
                startBoardAutoScroll();
            },
            onMove(event) {
                pointerX = event.originalEvent?.clientX ?? pointerX;
                pointerY = event.originalEvent?.clientY ?? pointerY;
            },
            onEnd(event) {
                board.classList.remove('is-dragging');
                document.removeEventListener('mousemove', trackPointer);
                document.removeEventListener('touchmove', trackPointer);
                document.removeEventListener('dragover', trackPointer);
                stopBoardAutoScroll();

                const moved = event.from !== event.to || event.oldIndex !== event.newIndex;
                if (moved) {
                    blockIssueClick = true;
                    window.setTimeout(() => {
                        blockIssueClick = false;
                    }, 0);
                }

                const card = event.item;
                const newStatus = event.to.dataset.status;

                if (event.from === event.to && event.oldIndex === event.newIndex) {
                    return;
                }

                card.dataset.status = newStatus;
                applyFilters();

                request(`/issues/${card.dataset.id}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: newStatus }),
                }).then((response) => {
                    if (!response.ok) {
                        window.alert('Could not update status, please try again.');
                    }
                });
            },
        });
    });
}
