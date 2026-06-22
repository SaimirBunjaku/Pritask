<form data-issue-form
      @if ($issue) data-issue-id="{{ $issue->id }}" @endif
      action="{{ $issue ? route('issues.update', $issue) : route('issues.store') }}">
    @if ($issue)
        <input type="hidden" name="_method" value="PUT">
    @endif

    <div class="modal-header">
        <h2>{{ $issue ? 'Edit Issue' : 'New Issue' }}</h2>
        <button type="button" class="modal-close" data-action="close-modal">&times;</button>
    </div>

    <div class="modal-form-body">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ $issue->title ?? '' }}">
            <p class="field-error" data-error-for="title"></p>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ $issue->description ?? '' }}</textarea>
            <p class="field-error" data-error-for="description"></p>
        </div>

        <div class="form-group">
            <label for="project_id">Project</label>
            <select name="project_id" id="project_id" class="form-control select-enhanced">
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected(($issue->project_id ?? null) == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
            <p class="field-error" data-error-for="project_id"></p>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control select-enhanced">
                @foreach (\App\Models\Issue::STATUSES as $value => $label)
                    <option value="{{ $value }}" data-badge="{{ $value }}" @selected(($issue->status ?? 'todo') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="field-error" data-error-for="status"></p>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select name="priority" id="priority" class="form-control select-enhanced">
                @foreach (['low', 'medium', 'high'] as $level)
                    <option value="{{ $level }}" data-badge="{{ $level }}" @selected(($issue->priority ?? 'medium') === $level)>{{ ucfirst($level) }}</option>
                @endforeach
            </select>
            <p class="field-error" data-error-for="priority"></p>
        </div>

        <div class="form-group">
            <label for="due_date">Due date</label>
            <input type="date" name="due_date" id="due_date" class="form-control" value="{{ $issue?->due_date?->format('Y-m-d') ?? '' }}">
            <p class="field-error" data-error-for="due_date"></p>
        </div>
    </div>

    <div class="modal-actions modal-actions-sticky">
        <button type="submit" class="btn btn-primary">{{ $issue ? 'Save' : 'Create Issue' }}</button>
        <button type="button" class="btn btn-secondary" data-action="close-modal">Cancel</button>
    </div>
</form>
