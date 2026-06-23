<article class="issue-comment" data-comment-id="{{ $comment->id }}">
    <header class="issue-comment-header">
        <strong class="issue-comment-author">{{ $comment->author_name }}</strong>
        <time class="issue-comment-time" datetime="{{ $comment->created_at->toIso8601String() }}">
            {{ $comment->created_at->diffForHumans() }}
        </time>
    </header>
    <p class="issue-comment-body">{{ $comment->body }}</p>
</article>
