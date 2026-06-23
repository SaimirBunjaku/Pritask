<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount('issues')->latest()->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Request $request, Project $project)
    {
        $project->load(['issues' => fn ($query) => $query->with(['project', 'tags'])->latest()]);

        if ($request->wantsJson()) {
            return response()->json([
                'title' => $project->name,
                'url' => route('projects.show', $project),
                'projectId' => $project->id,
                'bodyHtml' => view('projects.partials.show-body', compact('project'))->render(),
                'boardUrl' => route('issues.index', ['project' => $project->id]),
                'editUrl' => route('projects.edit', $project),
                'deleteUrl' => route('projects.destroy', $project),
            ]);
        }

        $projects = Project::orderBy('name')->get(['id', 'name']);

        return view('projects.show', compact('project', 'projects'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted.');
    }
}
