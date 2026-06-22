@extends('layouts.app')

@section('title', 'Issues')

@section('content')
    <div class="page-header">
        <h1>Issues</h1>
        <div class="board-toolbar">
            <select id="filter-priority" class="form-control form-control-sm">
                <option value="">All priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <select id="filter-tag" class="form-control form-control-sm">
                <option value="">All tags</option>
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-primary" id="new-issue-btn">New Issue</button>
        </div>
    </div>

    <div class="board-viewport">
        <div class="board" id="board">
            @foreach (\App\Models\Issue::STATUSES as $value => $label)
                <div class="board-column">
                    <div class="board-column-body" data-status="{{ $value }}">
                        <div class="board-column-header">
                            <span>{{ $label }}</span>
                            <span class="board-column-count">{{ $issues->where('status', $value)->count() }}</span>
                        </div>
                        @foreach ($issues->where('status', $value) as $issue)
                            @include('issues.partials.card', ['issue' => $issue])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush
