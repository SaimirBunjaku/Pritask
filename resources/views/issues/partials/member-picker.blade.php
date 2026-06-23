@if ($availableUsers->isNotEmpty())
    <div class="tag-picker member-picker" data-action="member-picker">
        <button type="button" class="tag-picker-trigger" data-action="toggle-member-select" aria-expanded="false">
            <span class="tag-picker-placeholder">Assign a member&hellip;</span>
            <svg class="tag-picker-chevron" width="12" height="12" viewBox="0 0 12 12" aria-hidden="true">
                <path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <div class="tag-picker-menu" hidden>
            @foreach ($availableUsers as $user)
                <button type="button" class="tag-picker-option" data-action="pick-member" data-user-id="{{ $user->id }}">
                    <span class="member-avatar member-avatar-sm" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="tag-picker-name">{{ $user->name }}</span>
                </button>
            @endforeach
        </div>
    </div>
@endif
