<p class="text-muted">{{ $project->description }}</p>

@if ($project->start_date || $project->deadline)
    <p class="text-muted">
        @if ($project->start_date) Starts {{ $project->start_date->format('M j, Y') }} @endif
        @if ($project->deadline) &middot; Due {{ $project->deadline->format('M j, Y') }} @endif
    </p>
@endif

<div class="section-header">
    <h2>Issues</h2>
    <span class="text-muted">{{ $project->issues->count() }} total</span>
</div>

<div class="project-issues-list" id="project-issues-list">
    @forelse ($project->issues as $issue)
        @include('issues.partials.project-issue-row', ['issue' => $issue])
    @empty
        <p class="empty-state">No issues in this project yet.</p>
    @endforelse
</div>
