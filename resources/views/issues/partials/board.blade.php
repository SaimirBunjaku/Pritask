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
