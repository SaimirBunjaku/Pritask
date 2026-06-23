@extends('layouts.app')

@section('title', 'Issues')

@section('content')
    @php
        $filters = $filters ?? [];
    @endphp

    <div class="page-header">
        <h1>Issues</h1>
        <div class="board-toolbar" id="board-toolbar">
            <input type="search"
                   id="filter-search"
                   class="form-control form-control-sm board-search-input"
                   placeholder="Search issues…"
                   value="{{ $filters['search'] ?? '' }}"
                   autocomplete="off">
            <select id="filter-project" class="form-control form-control-sm select-enhanced" data-board-filter>
                <option value="">All projects</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected(($filters['project'] ?? '') == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
            <select id="filter-status" class="form-control form-control-sm select-enhanced" data-board-filter>
                <option value="">All statuses</option>
                @foreach (\App\Models\Issue::STATUSES as $value => $label)
                    <option value="{{ $value }}" data-badge="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select id="filter-priority" class="form-control form-control-sm select-enhanced" data-board-filter>
                <option value="">All priorities</option>
                <option value="low" data-badge="low" @selected(($filters['priority'] ?? '') === 'low')>Low</option>
                <option value="medium" data-badge="medium" @selected(($filters['priority'] ?? '') === 'medium')>Medium</option>
                <option value="high" data-badge="high" @selected(($filters['priority'] ?? '') === 'high')>High</option>
            </select>
            <select id="filter-tag" class="form-control form-control-sm select-enhanced" data-board-filter>
                <option value="">All tags</option>
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" data-swatch="{{ $tag->color ?? '#8e8e93' }}" @selected(($filters['tag'] ?? '') == $tag->id)>{{ $tag->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-primary" id="new-issue-btn">New Issue</button>
        </div>
    </div>

    <div class="board-viewport"
         data-all-tags='@json($tags->map(fn ($tag) => ["id" => $tag->id, "name" => $tag->name, "color" => $tag->color ?? "#8e8e93"]))'
         data-all-users='@json($users->map(fn ($user) => ["id" => $user->id, "name" => $user->name]))'>
        @include('issues.partials.board', ['issues' => $issues])
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush
