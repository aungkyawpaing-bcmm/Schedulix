<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleProgressRequest;
use App\Models\Project;
use App\Services\ProgressService;
use App\Services\ScheduleGridService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleGridService $scheduleGrid,
        private readonly ProgressService $progressService,
    ) {
    }

    public function search(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        $project = Project::query()->findOrFail($validated['project_id']);
        $this->authorize('view', $project);

        return redirect()->route('schedule.show', $project);
    }

    public function show(Project $project, Request $request): View
    {
        $this->authorize('view', $project);

        return view('schedule.show', [
            'project' => $project,
            'projects' => $this->availableProjects(),
            'grid' => $this->scheduleGrid->build($project),
        ]);
    }

    public function storeProgress(ScheduleProgressRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $this->progressService->save($project, $request->validated(), $request);

        return redirect()->route('schedule.show', $project)->with('status', 'Schedule progress updated.');
    }

    private function availableProjects()
    {
        return Project::query()
            ->when(! auth()->user()->isOwner(), function ($query) {
                $query->where(function ($scopedQuery) {
                    $scopedQuery
                        ->where('project_manager_id', auth()->id())
                        ->orWhereHas('members', fn ($members) => $members->where('user_id', auth()->id()));
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }
}
