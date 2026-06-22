@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <div class="page-header">
        <h1>{{ $project->name }}</h1>
        <div class="card-actions">
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

    <h2>Issues</h2>

    @forelse ($project->issues as $issue)
        <div class="card card-clickable"
             data-action="open-issue"
             data-url="{{ route('issues.show', $issue) }}"
             data-issue='@json($issue->modalData())'>
            <span>{{ $issue->title }}</span>
            <span class="badge badge-{{ $issue->status }}">{{ $issue->statusLabel() }}</span>
            <span class="badge badge-{{ $issue->priority }}">{{ $issue->priority }}</span>
        </div>
    @empty
        <p class="empty-state">No issues in this project yet.</p>
    @endforelse
@endsection
