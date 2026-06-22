<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IssueController extends Controller
{
    public function index()
    {
        $issues = Issue::with(['project', 'tags'])->latest()->get();
        $tags = Tag::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('issues.index', compact('issues', 'tags', 'projects'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();

        return view('issues.partials.form', ['issue' => null, 'projects' => $projects]);
    }

    public function store(StoreIssueRequest $request)
    {
        $issue = Issue::create($request->validated());
        $issue->load(['project', 'tags']);

        return view('issues.partials.card', compact('issue'));
    }

    public function show(Issue $issue)
    {
        $issue->load(['project', 'tags']);
        $allTags = Tag::orderBy('name')->get();

        return view('issues.partials.details', compact('issue', 'allTags'));
    }

    public function edit(Issue $issue)
    {
        $projects = Project::orderBy('name')->get();

        return view('issues.partials.form', compact('issue', 'projects'));
    }

    public function update(UpdateIssueRequest $request, Issue $issue)
    {
        $issue->update($request->validated());
        $issue->load(['project', 'tags']);

        return view('issues.partials.card', compact('issue'));
    }

    public function updateStatus(Request $request, Issue $issue)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Issue::STATUSES))],
        ]);

        $issue->update($validated);
        $issue->load(['project', 'tags']);

        return response()->json([
            'status' => $issue->status,
            'statusLabel' => $issue->statusLabel(),
            'card' => view('issues.partials.card', compact('issue'))->render(),
            'projectRow' => view('issues.partials.project-issue-row', compact('issue'))->render(),
            'modalData' => $issue->modalData(),
        ]);
    }

    public function destroy(Issue $issue)
    {
        $issue->delete();

        return response()->noContent();
    }

    public function attachTag(Issue $issue, Tag $tag)
    {
        if (! $issue->tags()->whereKey($tag->id)->exists()) {
            $issue->tags()->attach($tag);
        }

        return $this->tagSyncResponse($issue);
    }

    public function detachTag(Issue $issue, Tag $tag)
    {
        $issue->tags()->detach($tag);

        return $this->tagSyncResponse($issue);
    }

    private function tagSyncResponse(Issue $issue)
    {
        $issue->load(['project', 'tags']);
        $allTags = Tag::orderBy('name')->get();

        return response()->json([
            'issueTags' => view('issues.partials.issue-tags', compact('issue', 'allTags'))->render(),
            'card' => view('issues.partials.card', compact('issue'))->render(),
            'modalData' => $issue->modalData(),
        ]);
    }
}
