<?php

namespace App\Services;

use App\Exports\WbsExport;
use App\Models\Export;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportService
{
    public function __construct(
        private readonly ScheduleGridService $scheduleGrid,
        private readonly AuditLogService $auditLogs,
    ) {
    }

    public function export(Project $project, array $data, ?Request $request = null): BinaryFileResponse
    {
        $grid = $this->scheduleGrid->build($project->fresh(['assignments.schedule.dailyAllocations', 'assignments.pic', 'assignments.wbsItem']));
        $fileName = $this->normalizeFileName(
            $data['file_name'] ?? $project->code.'-wbs-export-'.now()->format('Ymd-His')
        ).'.xlsx';
        $path = 'exports/'.$fileName;

        $exportWorkbook = new WbsExport($project, $grid, [
            'include_formula' => (bool) $data['include_formula'],
            'include_critical_path' => (bool) ($data['include_critical_path'] ?? false),
            'export_locale' => $data['export_locale'] ?? app()->getLocale(),
        ]);

        $spreadsheet = $exportWorkbook->toSpreadsheet();
        Storage::disk('local')->makeDirectory('exports');
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/private/'.$path));
        $spreadsheet->disconnectWorksheets();
        unset($writer, $spreadsheet);

        $export = Export::query()->create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'status' => 'completed',
            'file_name' => $fileName,
            'file_path' => $path,
            'filters' => $data,
            'exported_at' => now(),
        ]);

        $this->auditLogs->record('exported', $export, [], $export->toArray(), $request);

        return response()->download(storage_path('app/private/'.$path), $fileName);
    }

    public function download(Export $export): BinaryFileResponse
    {
        return response()->download(storage_path('app/private/'.$export->file_path), $export->file_name);
    }

    private function normalizeFileName(string $fileName): string
    {
        return Str::of($fileName)
            ->replaceMatches('/[^A-Za-z0-9\-_]+/', '-')
            ->trim('-')
            ->value() ?: 'wbs-export';
    }
}
