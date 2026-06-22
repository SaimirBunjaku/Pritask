export function initIssuesBoard() {
    const modalBackdrop = document.getElementById('issue-modal-backdrop');
    if (!modalBackdrop) {
        return;
    }

    const modalBody = document.getElementById('issue-modal-body');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const board = document.getElementById('board');
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

    function renderIssueDetails(issue) {
        const tagsHtml = issue.tags?.length
            ? `<div class="modal-tags">${issue.tags.map((tag) => (
                `<span class="tag-pill" style="--tag-color: ${escapeHtml(tag.color)}">${escapeHtml(tag.name)}</span>`
            )).join('')}</div>`
            : '';

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
            ${tagsHtml}
            <p>${escapeHtml(issue.description || 'No description provided.')}</p>
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
        modalBackdrop.classList.add('is-open');
    }

    function closeModal() {
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
            loadIssueHtml(url).catch(() => {});
            return;
        }

        if (cachedHtml) {
            showModalContent(cachedHtml, url);
            return;
        }

        showModalContent(renderModalSkeleton(), url);
        loadIssueHtml(url)
            .then((html) => {
                if (modalBody.dataset.issueUrl === url) {
                    modalBody.innerHTML = html;
                }
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
            const count = column.querySelectorAll('.issue-card').length;
            column.querySelector('.board-column-count').textContent = count;
        });
    }

    function applyFilters() {
        if (!priorityFilter || !tagFilter) {
            return;
        }

        const priority = priorityFilter.value;
        const tag = tagFilter.value;

        document.querySelectorAll('.issue-card').forEach((card) => {
            const matchesPriority = !priority || card.dataset.priority === priority;
            const matchesTag = !tag || card.dataset.tags.split(',').includes(tag);
            card.classList.toggle('is-hidden', !(matchesPriority && matchesTag));
        });
    }

    function upsertCard(html) {
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const newCard = template.content.firstElementChild;

        document.querySelector(`.issue-card[data-id="${newCard.dataset.id}"]`)?.remove();

        invalidateIssueCache(newCard.dataset.id);

        const columnBody = document.querySelector(`.board-column-body[data-status="${newCard.dataset.status}"]`);
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

    priorityFilter?.addEventListener('change', applyFilters);
    tagFilter?.addEventListener('change', applyFilters);

    modalBody.addEventListener('click', async (event) => {
        if (event.target.matches('[data-action="close-modal"]')) {
            closeModal();
        }

        if (event.target.matches('[data-action="edit-issue"]')) {
            openIssue(event.target.dataset.url);
        }

        if (event.target.matches('[data-action="delete-issue"]')) {
            if (!window.confirm('Delete this issue?')) {
                return;
            }

            const response = await request(event.target.dataset.url, { method: 'DELETE' });

            if (response.ok) {
                invalidateIssueCache(event.target.dataset.id);
                if (board) {
                    document.querySelector(`.issue-card[data-id="${event.target.dataset.id}"]`)?.remove();
                    updateColumnCounts();
                    closeModal();
                } else {
                    window.location.reload();
                }
            }
        }
    });

    modalBody.addEventListener('submit', async (event) => {
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
                updateColumnCounts();

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
