<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Models\Export;
use App\Models\Project;
use App\Services\ExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportHistoryController extends Controller
{
    public function __construct(private readonly ExportService $exportService)
    {
    }

    public function index(): View
    {
        return view('exports.index', [
            'exports' => Export::query()->with('project', 'user')->latest()->paginate(10),
            'projects' => Project::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ExportRequest $request, Project $project): BinaryFileResponse
    {
        $this->authorize('create', Export::class);

        return $this->exportService->export($project, $request->validated(), $request);
    }

    public function download(Export $export): BinaryFileResponse
    {
        $this->authorize('view', $export);

        return $this->exportService->download($export);
    }
}
