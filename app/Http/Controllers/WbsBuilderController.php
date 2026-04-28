<?php

namespace App\Http\Controllers;

use App\Http\Requests\WbsItemRequest;
use App\Models\Project;
use App\Models\ProjectWbsItem;
use App\Models\TaskMaster;
use App\Services\WbsBuilderService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WbsBuilderController extends Controller
{
    public function __construct(private readonly WbsBuilderService $wbsBuilder)
    {
    }

    public function index(Project $project): View
    {
        $this->authorize('update', $project);

        return view('wbs-builder.index', [
            'project' => $project->load(['wbsItems.taskMaster', 'wbsItems.children']),
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

        return redirect()->route('wbs-builder.index', $project);
    }

    public function create(Project $project): View
    {
        $this->authorize('update', $project);

        return view('wbs-builder.form', $this->formData($project));
    }

    public function store(WbsItemRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        try {
            $this->wbsBuilder->create($project, $request->validated(), $request);
        } catch (DomainException $exception) {
            return back()->withErrors(['wbs' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('wbs-builder.index', $project)->with('status', 'WBS item created.');
    }

    public function edit(ProjectWbsItem $wbs_item): View
    {
        $this->authorize('update', $wbs_item->project);

        return view('wbs-builder.form', $this->formData(
            $wbs_item->project->load(['wbsItems.taskMaster', 'wbsItems.children']),
            $wbs_item,
        ));
    }

    public function update(WbsItemRequest $request, ProjectWbsItem $wbs_item): RedirectResponse
    {
        $this->authorize('update', $wbs_item->project);

        try {
            $this->wbsBuilder->update($wbs_item, $request->validated(), $request);
        } catch (DomainException $exception) {
            return back()->withErrors(['wbs' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('wbs-builder.index', $wbs_item->project)->with('status', 'WBS item updated.');
    }

    public function destroy(Request $request, ProjectWbsItem $wbs_item): RedirectResponse
    {
        $this->authorize('update', $wbs_item->project);

        try {
            $this->wbsBuilder->delete($wbs_item, $request);
        } catch (DomainException $exception) {
            return back()->withErrors(['wbs' => $exception->getMessage()]);
        }

        return redirect()->route('wbs-builder.index', $wbs_item->project)->with('status', 'WBS item deleted.');
    }

    private function formData(Project $project, ?ProjectWbsItem $editingItem = null): array
    {
        return [
            'project' => $project->loadMissing(['wbsItems.taskMaster', 'wbsItems.children']),
            'projects' => $this->availableProjects(),
            'taskMasters' => TaskMaster::query()->orderBy('name')->get(),
            'editingItem' => $editingItem,
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
}
