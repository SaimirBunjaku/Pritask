@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <div class="page-header">
        <h1>{{ $project->name }}</h1>
        <div class="card-actions">
            <a href="{{ route('issues.index') }}?project={{ $project->id }}" class="btn btn-secondary">View on board</a>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary">Edit</a>
            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project and all its issues?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>

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
@endsection

@push('scripts')
    <script>
        if (window.location.search.includes('project=')) {
            sessionStorage.setItem('boardProjectFilter', new URLSearchParams(window.location.search).get('project'));
        }
    </script>
@endpush
