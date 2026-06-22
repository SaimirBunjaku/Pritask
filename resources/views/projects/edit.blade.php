@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    <div class="page-header">
        <h1>Edit Project</h1>
    </div>

    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $project->name) }}">
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ old('description', $project->description) }}</textarea>
            @error('description')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="start_date">Start date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
            @error('start_date')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="deadline">Deadline</label>
            <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}">
            @error('deadline')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
