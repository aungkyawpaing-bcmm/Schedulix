<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentRequest;
use App\Models\Assignment;
use App\Models\Project;
use App\Models\User;
use App\Services\AssignmentService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentService $assignmentService)
    {
    }

    public function index(Project $project): View
    {
        $this->authorize('update', $project);

        return view('assignments.index', [
            'project' => $project->load($this->indexRelations()),
            'projects' => $this->availableProjects(),
        ]);
    }

    public function search(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        $project = Project::query()->findOrFail($validated['project_id']);
        $this->authorize('update', $project);

        return redirect()->route('assignments.index', $project);
    }

    public function create(Project $project): View
    {
        $this->authorize('update', $project);

        return view('assignments.form', $this->formData($project));
    }

    public function store(AssignmentRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        try {
            $this->assignmentService->create($project, $request->validated(), $request);
        } catch (DomainException $exception) {
            return back()->withErrors(['assignment' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('assignments.index', $project)->with('status', 'Assignment created.');
    }

    public function edit(Assignment $assignment): View
    {
        $this->authorize('update', $assignment->project);

        return view('assignments.form', $this->formData(
            $assignment->project->load($this->indexRelations()),
            $assignment->loadMissing(['dependencies.dependsOn.wbsItem']),
        ));
    }

    public function update(AssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment->project);

        try {
            $this->assignmentService->update($assignment, $request->validated(), $request);
        } catch (DomainException $exception) {
            return back()->withErrors(['assignment' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('assignments.index', $assignment->project)->with('status', 'Assignment updated.');
    }

    public function recalculate(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment->project);

        try {
            $this->assignmentService->recalculate($assignment, $request);
        } catch (DomainException $exception) {
            return back()->withErrors(['assignment' => $exception->getMessage()]);
        }

        return redirect()->route('assignments.index', $assignment->project)->with('status', 'Assignment schedule recalculated.');
    }

    private function formData(Project $project, ?Assignment $editingAssignment = null): array
    {
        return [
            'project' => $project->loadMissing($this->indexRelations()),
            'projects' => $this->availableProjects(),
            'editingAssignment' => $editingAssignment,
            'assignableItems' => $project->wbsItems()
                ->where('is_assignable', true)
                ->with('children')
                ->orderBy('wbs_number')
                ->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'leaderOptions' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
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

    private function indexRelations(): array
    {
        return [
            'assignments.pic',
            'assignments.wbsItem',
            'assignments.dependencies.dependsOn.wbsItem',
            'assignments.schedule',
            'members.user',
        ];
    }
}
