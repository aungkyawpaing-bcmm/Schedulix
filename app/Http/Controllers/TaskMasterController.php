<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskMasterRequest;
use App\Models\TaskMaster;
use App\Repositories\TaskMasterRepository;
use App\Services\TaskMasterService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TaskMasterController extends Controller
{
    public function __construct(
        private readonly TaskMasterRepository $tasks,
        private readonly TaskMasterService $taskMasterService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', TaskMaster::class);

        return view('task-master.index', [
            'tasks' => $this->tasks->paginate(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TaskMaster::class);

        return view('task-master.form', [
            'task' => new TaskMaster(),
        ]);
    }

    public function store(TaskMasterRequest $request): RedirectResponse
    {
        $this->taskMasterService->create($request->validated());

        return redirect()->route('task-master.index')->with('status', 'Task master item created.');
    }

    public function edit(TaskMaster $task_master): View
    {
        return view('task-master.form', [
            'task' => $task_master,
            'isLocked' => $task_master->wbsItems()->exists(),
        ]);
    }

    public function update(TaskMasterRequest $request, TaskMaster $task_master): RedirectResponse
    {
        try {
            $this->taskMasterService->update($task_master, $request->validated());
        } catch (DomainException $exception) {
            return back()->withErrors(['task_code' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('task-master.index')->with('status', 'Task master item updated.');
    }
}
