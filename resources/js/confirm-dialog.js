let pendingResolve = null;

function getElements() {
    return {
        backdrop: document.getElementById('confirm-modal-backdrop'),
        title: document.getElementById('confirm-dialog-title'),
        message: document.getElementById('confirm-dialog-message'),
        confirmBtn: document.querySelector('[data-action="confirm-ok"]'),
        cancelBtn: document.querySelector('[data-action="confirm-cancel"]'),
    };
}

function closeConfirmDialog(result) {
    const { backdrop } = getElements();
    if (!backdrop) {
        return;
    }

    backdrop.hidden = true;
    document.body.classList.remove('confirm-open');

    if (pendingResolve) {
        pendingResolve(result);
        pendingResolve = null;
    }
}

export function confirmAction({
    title = 'Are you sure?',
    message = '',
    confirmLabel = 'Delete',
    cancelLabel = 'Cancel',
} = {}) {
    const elements = getElements();
    if (!elements.backdrop) {
        return Promise.resolve(false);
    }

    elements.title.textContent = title;
    elements.message.textContent = message;
    elements.confirmBtn.textContent = confirmLabel;
    elements.cancelBtn.textContent = cancelLabel;

    elements.backdrop.hidden = false;
    document.body.classList.add('confirm-open');
    elements.cancelBtn.focus();

    return new Promise((resolve) => {
        pendingResolve = resolve;
    });
}

export function bindConfirmDialogHandlers() {
    const { backdrop, confirmBtn, cancelBtn } = getElements();
    if (!backdrop || backdrop.dataset.confirmBound === 'true') {
        return;
    }

    backdrop.dataset.confirmBound = 'true';

    confirmBtn.addEventListener('click', () => {
        closeConfirmDialog(true);
    });

    cancelBtn.addEventListener('click', () => {
        closeConfirmDialog(false);
    });

    backdrop.addEventListener('click', (event) => {
        if (event.target === backdrop) {
            closeConfirmDialog(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !backdrop.hidden) {
            closeConfirmDialog(false);
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-confirm-delete]');
        if (!form) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();

        const confirmed = await confirmAction({
            title: form.dataset.confirmTitle || 'Are you sure?',
            message: form.dataset.confirmMessage || '',
            confirmLabel: form.dataset.confirmLabel || 'Delete',
            cancelLabel: form.dataset.cancelLabel || 'Cancel',
        });

        if (confirmed) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    });
}
