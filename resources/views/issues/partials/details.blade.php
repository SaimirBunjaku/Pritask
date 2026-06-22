<div class="modal-header">
    <h2>{{ $issue->title }}</h2>
    <button type="button" class="modal-close" data-action="close-modal">&times;</button>
</div>

<div class="modal-meta">
    <span class="badge badge-{{ $issue->status }}">{{ $issue->statusLabel() }}</span>
    <span class="badge badge-{{ $issue->priority }}">{{ $issue->priority }}</span>
    <span class="text-muted">{{ $issue->project->name }}</span>
    @if ($issue->due_date)
        <span class="text-muted">Due {{ $issue->due_date->format('M j, Y') }}</span>
    @endif
</div>

@if ($issue->tags->isNotEmpty())
    <div class="modal-tags">
        @foreach ($issue->tags as $tag)
            <span class="tag-pill" style="--tag-color: {{ $tag->color ?? '#8e8e93' }}">{{ $tag->name }}</span>
        @endforeach
    </div>
@endif

<p>{{ $issue->description ?? 'No description provided.' }}</p>

<div class="modal-actions">
    <button type="button" class="btn btn-secondary" data-action="edit-issue" data-url="{{ route('issues.edit', $issue) }}">Edit</button>
    <button type="button" class="btn btn-danger" data-action="delete-issue" data-id="{{ $issue->id }}" data-url="{{ route('issues.destroy', $issue) }}">Delete</button>
</div>
