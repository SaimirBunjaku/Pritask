<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private const PER_PAGE = 5;

    public function index(Request $request, Issue $issue): JsonResponse
    {
        $this->authorize('view', $issue);

        $comments = $issue->comments()
            ->latest()
            ->paginate(self::PER_PAGE, ['*'], 'page', $request->integer('page', 1));

        return response()->json([
            'html' => view('issues.partials.comments-items', [
                'comments' => $comments->items(),
            ])->render(),
            'hasMore' => $comments->hasMorePages(),
            'nextPage' => $comments->currentPage() + 1,
            'total' => $comments->total(),
        ]);
    }

    public function store(StoreCommentRequest $request, Issue $issue): JsonResponse
    {
        $this->authorize('view', $issue);

        $comment = $issue->comments()->create([
            'user_id' => $request->user()->id,
            'author_name' => $request->user()->name,
            'body' => $request->validated('body'),
        ]);
        $comment->refresh();

        return response()->json([
            'comment' => view('issues.partials.comment', compact('comment'))->render(),
        ], 201);
    }

    public function update(UpdateCommentRequest $request, Issue $issue, Comment $comment): JsonResponse
    {
        abort_unless($comment->issue_id === $issue->id, 404);

        $comment->update([
            'body' => $request->validated('body'),
        ]);

        return response()->json([
            'comment' => view('issues.partials.comment', compact('comment'))->render(),
        ]);
    }

    public function destroy(Issue $issue, Comment $comment): JsonResponse
    {
        abort_unless($comment->issue_id === $issue->id, 404);

        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(null, 204);
    }
}
