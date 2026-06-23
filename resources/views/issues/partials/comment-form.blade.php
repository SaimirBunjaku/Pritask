<form class="issue-comment-form" data-comment-form action="{{ route('issues.comments.store', $issue) }}" method="POST">
    <p class="issue-comment-as text-muted">Commenting as {{ auth()->user()->name }}</p>

    <div class="form-group">
        <label for="comment-body-{{ $issue->id }}">Comment</label>
        <textarea name="body"
                  id="comment-body-{{ $issue->id }}"
                  class="form-control issue-comment-textarea"
                  rows="4"
                  placeholder="Write a comment…"
                  required></textarea>
        <p class="field-error" data-error-for="body"></p>
    </div>

    <button type="submit" class="btn btn-primary issue-comment-submit" data-comment-submit>Add comment</button>
</form>
