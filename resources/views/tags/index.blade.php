@extends('layouts.app')

@section('title', 'Tags')

@section('content')
    <div class="page-header">
        <h1>Tags</h1>
    </div>

    <div class="card">
        <h2 class="card-section-title">New tag</h2>
        <form action="{{ route('tags.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Bug">
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="color" name="color" id="color" class="form-control form-control-color" value="{{ old('color', '#0071e3') }}">
                    @error('color')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create tag</button>
            </div>
        </form>
    </div>

    <h2 class="section-heading">All tags</h2>

    @forelse ($tags as $tag)
        <div class="card tag-list-item">
            <span class="tag-pill" style="--tag-color: {{ $tag->color ?? '#8e8e93' }}">{{ $tag->name }}</span>
            <span class="text-muted">{{ $tag->issues_count }} issue(s)</span>
        </div>
    @empty
        <p class="empty-state">No tags yet. Create one above to start labeling issues.</p>
    @endforelse
@endsection
