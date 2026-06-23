<div class="card tag-list-item" data-tag-id="{{ $tag->id }}">
    <span class="tag-pill" style="--tag-color: {{ $tag->color ?? '#8e8e93' }}">{{ $tag->name }}</span>
    <span class="text-muted">{{ $tag->issues_count }} issue(s)</span>
</div>
