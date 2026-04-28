<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly ProjectService $projectService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        return view('projects.index', [
            'projects' => $this->projects->paginateFor($request->user()),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.form', [
            'project' => new Project(),
            'managers' => $this->projects->managers(),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->projectService->create($request->validated());

        return redirect()->route('projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.form', [
            'project' => $project,
            'managers' => $this->projects->managers(),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->projectService->update($project, $request->validated());

        return redirect()->route('projects.index')->with('status', 'Project updated.');
    }
}
