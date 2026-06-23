<div class="issue-comments-section"
     data-issue-id="{{ $issue->id }}"
     data-comments-url="{{ route('issues.comments.index', $issue) }}"
     data-comments-store-url="{{ route('issues.comments.store', $issue) }}">
    <label class="issue-comments-label">Comments</label>

    <form class="issue-comment-form" data-comment-form action="{{ route('issues.comments.store', $issue) }}" method="POST">
        <div class="form-group">
            <label for="comment-author-{{ $issue->id }}">Your name</label>
            <input type="text"
                   name="author_name"
                   id="comment-author-{{ $issue->id }}"
                   class="form-control"
                   autocomplete="name"
                   placeholder="Jane Doe">
            <p class="field-error" data-error-for="author_name"></p>
        </div>

        <div class="form-group">
            <label for="comment-body-{{ $issue->id }}">Comment</label>
            <textarea name="body"
                      id="comment-body-{{ $issue->id }}"
                      class="form-control issue-comment-textarea"
                      rows="4"
                      placeholder="Write a comment…"></textarea>
            <p class="field-error" data-error-for="body"></p>
        </div>

        <button type="submit" class="btn btn-primary issue-comment-submit" data-comment-submit>Add comment</button>
    </form>

    <div class="issue-comments-list" data-comments-list role="list"></div>

    <p class="issue-comments-status text-muted" data-comments-loading>Loading comments&hellip;</p>
    <p class="issue-comments-status text-muted" data-comments-empty hidden>No comments yet.</p>
    <button type="button" class="btn btn-secondary issue-comments-load-more" data-action="load-more-comments" hidden>
        Load more comments
    </button>
</div>
