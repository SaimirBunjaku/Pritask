<div class="issue-tags-section" data-issue-id="{{ $issue->id }}">
    <label class="issue-tags-label">Tags</label>

    <div class="issue-tags-list">
        @forelse ($issue->tags as $tag)
            <span class="tag-pill tag-pill-removable" style="--tag-color: {{ $tag->color ?? '#8e8e93' }}">
                {{ $tag->name }}
                <button type="button"
                        class="tag-pill-remove"
                        data-action="detach-tag"
                        data-url="{{ route('issues.tags.detach', [$issue, $tag]) }}"
                        aria-label="Remove {{ $tag->name }}">&times;</button>
            </span>
        @empty
            <span class="text-muted issue-tags-empty">No tags yet.</span>
        @endforelse
    </div>

    @php
        $availableTags = $allTags->whereNotIn('id', $issue->tags->pluck('id'));
    @endphp

    @include('issues.partials.tag-picker', ['availableTags' => $availableTags])

    @if ($availableTags->isEmpty() && $allTags->isNotEmpty())
        <p class="text-muted issue-tags-hint">All tags are already attached.</p>
    @endif

    <p class="field-error" data-error-for="tag"></p>
</div>
