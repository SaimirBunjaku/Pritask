@extends('layouts.app')

@section('title', 'New Project')

@section('content')
    <div class="page-header">
        <h1>New Project</h1>
    </div>

    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
            @error('description')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="start_date">Start date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}">
            @error('start_date')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="deadline">Deadline</label>
            <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline') }}">
            @error('deadline')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Project</button>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
