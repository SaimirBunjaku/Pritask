@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <div id="project-show" data-project-show data-current-url="{{ route('projects.show', $project) }}">
        <div class="page-header">
            <div class="page-header-title project-switcher">
                <label for="project-switcher" class="visually-hidden">Switch project</label>
                <select id="project-switcher" class="project-switcher-select select-enhanced">
                    @foreach ($projects as $item)
                        <option value="{{ route('projects.show', $item) }}" @selected($item->id === $project->id)>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="card-actions" id="project-show-actions">
                <a href="{{ route('issues.index') }}?project={{ $project->id }}" class="btn btn-secondary" data-project-board-link>View on board</a>
                <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary" data-project-edit-link>Edit</a>
                <form action="{{ route('projects.destroy', $project) }}" method="POST"
                      data-confirm-delete
                      data-confirm-title="Delete project?"
                      data-confirm-message="This will permanently delete the project and all of its issues."
                      data-project-delete-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>

        <div id="project-show-body">
            @include('projects.partials.show-body', ['project' => $project])
        </div>
    </div>
@endsection
