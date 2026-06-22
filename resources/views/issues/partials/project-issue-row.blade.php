<div class="project-issue-row card"
     data-project-id="{{ $issue->project_id }}"
     data-issue-id="{{ $issue->id }}"
     data-status="{{ $issue->status }}">
    <div class="project-issue-main"
         data-action="open-issue"
         data-url="{{ route('issues.show', $issue) }}"
         data-issue='@json($issue->modalData())'>
        <div class="project-issue-title">{{ $issue->title }}</div>
        <div class="project-issue-meta">
            <span class="badge badge-{{ $issue->status }}">{{ $issue->statusLabel() }}</span>
            <span class="badge badge-{{ $issue->priority }}">{{ $issue->priority }}</span>
            @if ($issue->due_date)
                <span class="text-muted">Due {{ $issue->due_date->format('M j') }}</span>
            @endif
        </div>
        @if ($issue->tags->isNotEmpty())
            <div class="project-issue-tags">
                @foreach ($issue->tags as $tag)
                    <span class="tag-pill" style="--tag-color: {{ $tag->color ?? '#8e8e93' }}">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="project-issue-actions">
        @if ($issue->status === 'todo')
            <button type="button" class="btn btn-secondary btn-sm" data-action="quick-status" data-status="in_progress" title="Start working">Start</button>
        @endif
        @if ($issue->status === 'in_progress')
            <button type="button" class="btn btn-secondary btn-sm" data-action="quick-status" data-status="qa_staging" title="Send to QA">QA</button>
        @endif
        @if (in_array($issue->status, ['todo', 'in_progress', 'qa_staging', 'qa_done']))
            <button type="button" class="btn btn-secondary btn-sm" data-action="quick-status" data-status="prod" title="Mark as shipped">Ship</button>
        @endif
        @if (! in_array($issue->status, ['blocked', 'prod']))
            <button type="button" class="btn btn-secondary btn-sm btn-ghost-danger" data-action="quick-status" data-status="blocked" title="Mark as blocked">Block</button>
        @endif
        @if ($issue->status === 'blocked')
            <button type="button" class="btn btn-secondary btn-sm" data-action="quick-status" data-status="todo" title="Unblock">Unblock</button>
        @endif
    </div>
</div>
