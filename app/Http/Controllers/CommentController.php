<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private const PER_PAGE = 5;

    public function index(Request $request, Issue $issue): JsonResponse
    {
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
        $comment = $issue->comments()->create($request->validated());
        $comment->refresh();

        return response()->json([
            'comment' => view('issues.partials.comment', compact('comment'))->render(),
        ], 201);
    }
}
