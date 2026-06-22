@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="page-header">
        <h1>Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">New Project</a>
    </div>

    @forelse ($projects as $project)
        <div class="card">
            <a href="{{ route('projects.show', $project) }}" class="card-title">{{ $project->name }}</a>
            <p class="text-muted">{{ $project->description }}</p>
            <div class="card-footer">
                <span class="text-muted">{{ $project->issues_count }} issue(s)</span>
                <div class="card-actions">
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary">Edit</a>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project and all its issues?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="empty-state">No projects yet. Create the first one to get started.</p>
    @endforelse

    {{ $projects->links() }}
@endsection
