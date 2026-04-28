<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkingHourRequest;
use App\Models\Project;
use App\Models\WorkingHour;
use App\Repositories\ProjectRepository;
use App\Repositories\WorkingHourRepository;
use App\Services\WorkingHourService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WorkingHourController extends Controller
{
    public function __construct(
        private readonly WorkingHourRepository $workingHours,
        private readonly ProjectRepository $projects,
        private readonly WorkingHourService $workingHourService,
    ) {
    }

    public function index(): View
    {
        return view('working-hours.index', [
            'workingHours' => $this->workingHours->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('working-hours.form', [
            'workingHour' => new WorkingHour(),
            'projects' => $this->projects->managers()->pluck('name', 'id'),
            'projectOptions' => Project::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(WorkingHourRequest $request): RedirectResponse
    {
        $this->workingHourService->create($request->validated());

        return redirect()->route('working-hours.index')->with('status', 'Working hour rule saved.');
    }

    public function edit(WorkingHour $working_hour): View
    {
        return view('working-hours.form', [
            'workingHour' => $working_hour,
            'projectOptions' => Project::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(WorkingHourRequest $request, WorkingHour $working_hour): RedirectResponse
    {
        $this->workingHourService->update($working_hour, $request->validated());

        return redirect()->route('working-hours.index')->with('status', 'Working hour rule updated.');
    }
}
