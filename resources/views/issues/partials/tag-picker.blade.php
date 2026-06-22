@if ($availableTags->isNotEmpty())
    <div class="tag-picker" data-action="tag-picker">
        <button type="button" class="tag-picker-trigger" data-action="toggle-tag-select" aria-expanded="false">
            <span class="tag-picker-placeholder">Add a tag&hellip;</span>
            <svg class="tag-picker-chevron" width="12" height="12" viewBox="0 0 12 12" aria-hidden="true">
                <path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <div class="tag-picker-menu" hidden>
            @foreach ($availableTags as $tag)
                <button type="button" class="tag-picker-option" data-action="pick-tag" data-tag-id="{{ $tag->id }}">
                    <span class="tag-picker-swatch" style="--tag-color: {{ $tag->color ?? '#8e8e93' }}"></span>
                    <span class="tag-picker-name">{{ $tag->name }}</span>
                </button>
            @endforeach
        </div>
    </div>
@endif
