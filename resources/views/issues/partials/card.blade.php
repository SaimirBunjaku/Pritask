<div class="issue-card"
     data-id="{{ $issue->id }}"
     data-project-id="{{ $issue->project_id }}"
     data-status="{{ $issue->status }}"
     data-priority="{{ $issue->priority }}"
     data-tags="{{ $issue->tags->pluck('id')->implode(',') }}"
     data-action="open-issue"
     data-url="{{ route('issues.show', $issue) }}"
     data-issue='@json($issue->modalData())'>
    <div class="issue-card-title">{{ $issue->title }}</div>

    <div class="issue-card-meta">
        <span class="badge badge-{{ $issue->priority }}">{{ $issue->priority }}</span>
        @if ($issue->due_date)
            <span class="text-muted">{{ $issue->due_date->format('M j') }}</span>
        @endif
    </div>

    @if ($issue->tags->isNotEmpty())
        <div class="issue-card-tags">
            @foreach ($issue->tags as $tag)
                <span class="tag-pill" style="--tag-color: {{ $tag->color ?? '#8e8e93' }}">{{ $tag->name }}</span>
            @endforeach
        </div>
    @endif

    <div class="issue-card-project">{{ $issue->project->name }}</div>
</div>
