<article class="issue-comment" data-comment-id="{{ $comment->id }}">
    <header class="issue-comment-header">
        <div class="issue-comment-meta">
            <strong class="issue-comment-author">{{ $comment->author_name }}</strong>
            <time class="issue-comment-time" datetime="{{ $comment->created_at->toIso8601String() }}">
                {{ $comment->created_at->diffForHumans() }}
            </time>
        </div>
        @can('update', $comment)
            <div class="issue-comment-actions">
                <button type="button"
                        class="issue-comment-action"
                        data-action="edit-comment"
                        aria-label="Edit comment">Edit</button>
                <button type="button"
                        class="issue-comment-action issue-comment-action-danger"
                        data-action="delete-comment"
                        data-url="{{ route('issues.comments.destroy', [$comment->issue_id, $comment]) }}"
                        aria-label="Delete comment">Delete</button>
            </div>
        @endcan
    </header>

    <div class="issue-comment-view" data-comment-view>
        <p class="issue-comment-body">{{ $comment->body }}</p>
    </div>

    @can('update', $comment)
        <form class="issue-comment-edit-form"
              data-comment-edit-form
              action="{{ route('issues.comments.update', [$comment->issue_id, $comment]) }}"
              method="POST"
              hidden>
            @csrf
            @method('PATCH')
            <textarea name="body"
                      class="form-control issue-comment-textarea"
                      rows="3"
                      required>{{ $comment->body }}</textarea>
            <p class="field-error" data-error-for="body"></p>
            <div class="issue-comment-edit-actions">
                <button type="submit" class="btn btn-primary btn-sm" data-action="save-comment">Save</button>
                <button type="button" class="btn btn-secondary btn-sm" data-action="cancel-edit-comment">Cancel</button>
            </div>
        </form>
    @endcan
</article>
