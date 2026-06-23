function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

const CUSTOM_SELECT_Z = 300;

export function clearFloatingMenuPosition(menu) {
    delete menu.dataset.lockedWidth;
    menu.style.position = '';
    menu.style.top = '';
    menu.style.left = '';
    menu.style.width = '';
    menu.style.minWidth = '';
    menu.style.maxWidth = '';
    menu.style.overflowX = '';
    menu.style.zIndex = '';
    menu.style.visibility = '';
    menu.style.pointerEvents = '';
}

function measureMenuWidth(trigger, menu) {
    const rect = trigger.getBoundingClientRect();
    const wasHidden = menu.hidden;

    menu.hidden = false;
    menu.style.width = 'auto';
    menu.style.minWidth = '0';
    menu.style.maxWidth = 'none';
    menu.style.visibility = 'hidden';
    menu.style.pointerEvents = 'none';
    menu.style.position = 'fixed';
    menu.style.left = '-9999px';
    menu.style.top = '0';

    const measuredWidth = Math.max(rect.width, menu.scrollWidth + 2);

    menu.hidden = wasHidden;
    menu.style.visibility = '';
    menu.style.pointerEvents = '';
    menu.style.left = '';
    menu.style.top = '';

    return measuredWidth;
}

export function positionFloatingMenu(trigger, menu, { repositionOnly = false, zIndex = CUSTOM_SELECT_Z } = {}) {
    const rect = trigger.getBoundingClientRect();

    menu.style.position = 'fixed';
    menu.style.zIndex = String(zIndex);
    menu.style.overflowX = 'hidden';

    if (!repositionOnly || !menu.dataset.lockedWidth) {
        menu.dataset.lockedWidth = String(measureMenuWidth(trigger, menu));
    }

    menu.style.left = `${rect.left}px`;

    const menuWidth = parseFloat(menu.dataset.lockedWidth);
    menu.style.width = `${menuWidth}px`;
    menu.style.minWidth = `${rect.width}px`;
    menu.style.maxWidth = `${Math.max(rect.width, window.innerWidth - rect.left - 12)}px`;

    menu.hidden = false;
    const menuHeight = menu.offsetHeight || Math.min(menu.scrollHeight, 240);

    if (rect.bottom + menuHeight + 8 > window.innerHeight && rect.top - menuHeight - 8 > 0) {
        menu.style.top = `${rect.top - menuHeight - 6}px`;
    } else {
        menu.style.top = `${rect.bottom + 6}px`;
    }
}

function ensureSelectId(wrapper) {
    if (!wrapper.dataset.selectId) {
        wrapper.dataset.selectId = `custom-select-${Math.random().toString(36).slice(2, 9)}`;
    }

    return wrapper.dataset.selectId;
}

function getCustomSelectMenu(wrapper) {
    const selectId = wrapper.dataset.selectId;
    if (selectId) {
        const floated = document.querySelector(`.custom-select-menu[data-owner="${selectId}"]`);
        if (floated) {
            return floated;
        }
    }

    return wrapper.querySelector('.custom-select-menu');
}

function getCustomSelectWrapperFromMenu(menu) {
    const ownerId = menu?.dataset.owner;
    if (ownerId) {
        return document.querySelector(`.custom-select[data-select-id="${ownerId}"]`);
    }

    return menu?.closest('.custom-select');
}

function openCustomSelectMenu(wrapper) {
    const trigger = wrapper.querySelector('.custom-select-trigger');
    const menu = getCustomSelectMenu(wrapper);
    const selectId = ensureSelectId(wrapper);

    menu.dataset.owner = selectId;
    document.body.appendChild(menu);
    menu.classList.add('custom-select-menu-floating');
    positionFloatingMenu(trigger, menu);
}

function closeCustomSelect(wrapper) {
    wrapper.classList.remove('is-open');
    const menu = getCustomSelectMenu(wrapper);
    if (!menu) {
        return;
    }

    menu.hidden = true;
    menu.classList.remove('custom-select-menu-floating');
    clearFloatingMenuPosition(menu);
    delete menu.dataset.owner;

    if (menu.parentElement !== wrapper) {
        wrapper.appendChild(menu);
    }

    wrapper.querySelector('.custom-select-trigger')?.setAttribute('aria-expanded', 'false');
}

export function closeAllCustomSelects(except = null) {
    document.querySelectorAll('.custom-select.is-open').forEach((wrapper) => {
        if (wrapper !== except) {
            closeCustomSelect(wrapper);
        }
    });
}

function renderOptionContent(option) {
    if (!option || option.value === '') {
        return `<span class="custom-select-placeholder">${escapeHtml(option?.textContent.trim() || 'Select…')}</span>`;
    }

    const swatch = option.dataset.swatch;
    const badge = option.dataset.badge;
    const label = escapeHtml(option.textContent.trim());

    if (swatch) {
        return `<span class="custom-select-option-inner"><span class="custom-select-swatch" style="--tag-color: ${escapeHtml(swatch)}"></span><span>${label}</span></span>`;
    }

    if (badge) {
        return `<span class="custom-select-option-inner"><span class="badge badge-${escapeHtml(badge)}">${label}</span></span>`;
    }

    return `<span class="custom-select-option-inner"><span>${label}</span></span>`;
}

function renderTriggerLabel(option) {
    return renderOptionContent(option);
}

export function syncCustomSelect(wrapper) {
    const select = wrapper.querySelector('select');
    const trigger = wrapper.querySelector('.custom-select-trigger');
    const label = wrapper.querySelector('.custom-select-label');
    const selected = select.options[select.selectedIndex];

    label.innerHTML = renderTriggerLabel(selected);
    trigger.setAttribute('aria-expanded', 'false');

    wrapper.querySelectorAll('.custom-select-option').forEach((button) => {
        const isSelected = button.dataset.value === select.value;
        button.classList.toggle('is-selected', isSelected);
        button.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });
}

function buildWidthSizer(wrapper, select) {
    if (!wrapper.classList.contains('custom-select-sm')) {
        return;
    }

    const sizer = document.createElement('div');
    sizer.className = 'custom-select-sizer';
    sizer.setAttribute('aria-hidden', 'true');

    Array.from(select.options).forEach((option) => {
        const item = document.createElement('div');
        item.className = 'custom-select-sizer-item';
        item.innerHTML = renderTriggerLabel(option);
        sizer.appendChild(item);
    });

    wrapper.insertBefore(sizer, wrapper.firstChild);
}

function enhanceSelect(select) {
    if (select.dataset.enhanced === 'true') {
        return;
    }

    select.dataset.enhanced = 'true';
    select.classList.add('custom-select-native');
    select.tabIndex = -1;

    const wrapper = document.createElement('div');
    wrapper.className = `custom-select${select.classList.contains('form-control-sm') ? ' custom-select-sm' : ''}`;
    if (select.id) {
        wrapper.dataset.selectId = select.id;
    }

    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    buildWidthSizer(wrapper, select);

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'custom-select-trigger';
    trigger.dataset.action = 'toggle-custom-select';
    trigger.setAttribute('aria-expanded', 'false');
    trigger.innerHTML = `
        <span class="custom-select-label"></span>
        <svg class="custom-select-chevron" width="12" height="12" viewBox="0 0 12 12" aria-hidden="true">
            <path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    `;

    const menu = document.createElement('div');
    menu.className = 'custom-select-menu';
    menu.hidden = true;
    menu.innerHTML = Array.from(select.options).map((option) => (
        `<button type="button" class="custom-select-option" data-action="pick-custom-select" data-value="${escapeHtml(option.value)}" role="option">${renderOptionContent(option)}</button>`
    )).join('');

    wrapper.appendChild(trigger);
    wrapper.appendChild(menu);
    syncCustomSelect(wrapper);
}

export function initCustomSelects(root = document) {
    root.querySelectorAll('select.select-enhanced:not([data-enhanced])').forEach(enhanceSelect);
}

function repositionOpenCustomSelects() {
    document.querySelectorAll('.custom-select.is-open').forEach((wrapper) => {
        const trigger = wrapper.querySelector('.custom-select-trigger');
        const menu = getCustomSelectMenu(wrapper);
        if (trigger && menu) {
            positionFloatingMenu(trigger, menu, { repositionOnly: true });
        }
    });
}

export function bindCustomSelectHandlers() {
    if (document.body.dataset.customSelectBound === 'true') {
        return;
    }

    document.body.dataset.customSelectBound = 'true';

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.custom-select') && !event.target.closest('.custom-select-menu')) {
            closeAllCustomSelects();
        }

        if (event.target.closest('[data-action="toggle-custom-select"]')) {
            event.preventDefault();
            event.stopPropagation();
            const wrapper = event.target.closest('.custom-select');
            const menu = getCustomSelectMenu(wrapper);
            const trigger = wrapper.querySelector('.custom-select-trigger');
            const willOpen = menu.hidden;

            closeAllCustomSelects();

            if (willOpen) {
                wrapper.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                openCustomSelectMenu(wrapper);
            }
            return;
        }

        if (event.target.closest('[data-action="pick-custom-select"]')) {
            event.preventDefault();
            event.stopPropagation();
            const option = event.target.closest('[data-action="pick-custom-select"]');
            const menu = option.closest('.custom-select-menu');
            const wrapper = getCustomSelectWrapperFromMenu(menu);
            const select = wrapper?.querySelector('select');
            if (!select) {
                return;
            }

            const previous = select.value;

            select.value = option.dataset.value;
            syncCustomSelect(wrapper);
            closeCustomSelect(wrapper);

            if (previous !== select.value) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });

    window.addEventListener('resize', repositionOpenCustomSelects);

    document.addEventListener('scroll', (event) => {
        if (event.target.closest('.custom-select-menu') || event.target.closest('.tag-picker-menu')) {
            return;
        }

        repositionOpenCustomSelects();
    }, true);
}
