<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    public function __construct(
        private readonly SchedulingService $scheduling,
        private readonly AuditLogService $auditLogs,
    ) {
    }

    public function save(Project $project, array $data, ?Request $request = null): void
    {
        DB::transaction(function () use ($project, $data, $request) {
            $actualHours = $data['actual_hours'] ?? [];
            $restHours = $data['plan_rest_hours'] ?? [];
            $actualStarts = $data['actual_start_dates'] ?? [];
            $actualEnds = $data['actual_end_dates'] ?? [];

            foreach ($project->assignments()->with('schedule.dailyAllocations')->get() as $assignment) {
                $schedule = $assignment->schedule;
                if (! $schedule) {
                    continue;
                }

                $user = auth()->user();
                $canManageProject = $user?->canManageProject($project) ?? false;

                if ($user && ! $canManageProject && $assignment->assigned_pic_id !== $user->id) {
                    continue;
                }

                if ($canManageProject && array_key_exists($assignment->id, $restHours) && $restHours[$assignment->id] !== null) {
                    $assignment->update(['plan_rest_hours' => $restHours[$assignment->id]]);
                }

                if ($canManageProject && ! empty($actualStarts[$assignment->id])) {
                    $schedule->actual_start_date = $actualStarts[$assignment->id];
                }

                if ($canManageProject && ! empty($actualEnds[$assignment->id])) {
                    $schedule->actual_end_date = $actualEnds[$assignment->id];
                }

                foreach ($actualHours[$assignment->id] ?? [] as $date => $hours) {
                    $workDate = Carbon::parse($date)->toDateString();
                    $actualHoursValue = (float) ($hours ?? 0);

                    $allocation = $schedule->dailyAllocations
                        ->first(fn ($row) => $row->work_date?->toDateString() === $workDate);

                    if (! $allocation && $actualHoursValue <= 0) {
                        continue;
                    }

                    if (! $allocation) {
                        $allocation = $schedule->dailyAllocations()->create([
                            'assignment_id' => $assignment->id,
                            'project_id' => $project->id,
                            'work_date' => $workDate,
                            'planned_hours' => 0,
                            'actual_hours' => 0,
                            'variance_hours' => 0,
                            'is_holiday' => false,
                        ]);

                        $schedule->setRelation(
                            'dailyAllocations',
                            $schedule->dailyAllocations->push($allocation)
                        );
                    }

                    $allocation->update([
                        'actual_hours' => $actualHoursValue,
                        'variance_hours' => $actualHoursValue - (float) $allocation->planned_hours,
                    ]);

                    if ($actualHoursValue > 0) {
                        $assignment->progressLogs()->create([
                            'schedule_daily_allocation_id' => $allocation->id,
                            'user_id' => auth()->id(),
                            'work_date' => $workDate,
                            'actual_hours' => $actualHoursValue,
                            'note' => 'Saved from schedule grid',
                        ]);
                    }
                }

                $schedule->save();
                $schedule = $this->scheduling->recalculateSchedule($schedule->fresh('project', 'assignment', 'dailyAllocations'));
                $this->auditLogs->record('progress-updated', $schedule, [], $schedule->toArray(), $request);
            }
        });
    }
}
