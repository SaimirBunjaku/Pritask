<div class="issue-comments-section"
     data-issue-id="{{ $issue->id }}"
     data-comments-url="{{ route('issues.comments.index', $issue) }}"
     data-comments-store-url="{{ route('issues.comments.store', $issue) }}">
    <label class="issue-comments-label">Comments</label>

    @include('issues.partials.comment-form', ['issue' => $issue])

    <div class="issue-comments-list" data-comments-list role="list"></div>

    <p class="issue-comments-status text-muted" data-comments-loading>Loading comments&hellip;</p>
    <p class="issue-comments-status text-muted" data-comments-empty hidden>No comments yet.</p>
    <button type="button" class="btn btn-secondary issue-comments-load-more" data-action="load-more-comments" hidden>
        Load more comments
    </button>
</div>
