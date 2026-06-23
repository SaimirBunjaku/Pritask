@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="page-header">
        <h1>Projects</h1>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="btn btn-primary">New Project</a>
        @endcan
    </div>

    @forelse ($projects as $project)
        <div class="card">
            <a href="{{ route('projects.show', $project) }}" class="card-title">{{ $project->name }}</a>
            <p class="text-muted">{{ $project->description }}</p>
            <div class="card-footer">
                <span class="text-muted">{{ $project->issues_count }} issue(s) · Owner: {{ $project->owner->name }}</span>
                <div class="card-actions">
                    @can('update', $project)
                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary">Edit</a>
                    @endcan
                    @can('delete', $project)
                        <form action="{{ route('projects.destroy', $project) }}" method="POST"
                              data-confirm-delete
                              data-confirm-title="Delete project?"
                              data-confirm-message="This will permanently delete the project and all of its issues.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    @empty
        <p class="empty-state">No projects yet. Create the first one to get started.</p>
    @endforelse

    {{ $projects->links() }}
@endsection
