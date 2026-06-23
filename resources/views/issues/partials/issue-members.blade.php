<div class="issue-members-section" data-issue-id="{{ $issue->id }}">
    <label class="issue-members-label">Members</label>

    <div class="issue-members-list">
        @forelse ($issue->users as $member)
            <span class="member-pill member-pill-removable">
                <span class="member-avatar" aria-hidden="true">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                <span class="member-name">{{ $member->name }}</span>
                <button type="button"
                        class="member-pill-remove"
                        data-action="detach-member"
                        data-url="{{ route('issues.users.detach', [$issue, $member]) }}"
                        aria-label="Remove {{ $member->name }}">&times;</button>
            </span>
        @empty
            <span class="text-muted issue-members-empty">No members assigned yet.</span>
        @endforelse
    </div>

    @php
        $availableUsers = $allUsers->whereNotIn('id', $issue->users->pluck('id'));
    @endphp

    @include('issues.partials.member-picker', ['availableUsers' => $availableUsers])

    @if ($availableUsers->isEmpty() && $allUsers->isNotEmpty())
        <p class="text-muted issue-members-hint">All users are already assigned.</p>
    @endif

    <p class="field-error" data-error-for="member"></p>
</div>
