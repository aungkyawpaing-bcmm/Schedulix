<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Holiday;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\UserWorkingHour;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DomainException;
use Illuminate\Support\Collection;

class SchedulingService
{
    public function generateForAssignment(Assignment $assignment): Schedule
    {
        $assignment->loadMissing([
            'project',
            'pic',
            'dependencies.dependsOn.schedule',
            'schedule.dailyAllocations',
        ]);

        $project = $assignment->project;
        $hoursRemaining = (float) $assignment->planned_hours;
        $startDate = $this->resolveStartDate($assignment);
        $currentDate = $startDate->copy();
        $plannedRows = [];
        $guard = 0;

        while ($hoursRemaining > 0 && $guard < 1000) {
            $guard++;
            $availableHours = $this->availableHoursFor($assignment, $currentDate);

            if ($availableHours <= 0) {
                $currentDate->addDay();
                continue;
            }

            $allocated = min($hoursRemaining, $availableHours);
            $plannedRows[] = [
                'work_date' => $currentDate->toDateString(),
                'planned_hours' => $allocated,
                'actual_hours' => 0,
                'variance_hours' => -$allocated,
                'is_holiday' => $this->holidayTypeFor($currentDate) !== null,
            ];

            $hoursRemaining -= $allocated;
            $currentDate->addDay();
        }

        if ($hoursRemaining > 0 || $plannedRows === []) {
            throw new DomainException('Unable to generate a schedule with the current working calendar.');
        }

        $plannedEnd = Carbon::parse(last($plannedRows)['work_date'], $project->timezone);
        if ($plannedEnd->gt(Carbon::parse($project->expected_end_date, $project->timezone))) {
            throw new DomainException('Schedule generation exceeds project expected end date.');
        }

        $existingActuals = $assignment->schedule?->dailyAllocations
            ?->mapWithKeys(fn ($row) => [$row->work_date->toDateString() => (float) $row->actual_hours])
            ?? collect();

        $schedule = Schedule::query()->updateOrCreate(
            ['assignment_id' => $assignment->id],
            [
                'project_id' => $project->id,
                'planned_start_date' => Carbon::parse($plannedRows[0]['work_date']),
                'planned_end_date' => $plannedEnd,
                'planned_hours' => $assignment->planned_hours,
                'warning_notes' => null,
            ]
        );

        $schedule->dailyAllocations()->delete();

        foreach ($plannedRows as $row) {
            $actual = (float) ($existingActuals[$row['work_date']] ?? 0);
            $schedule->dailyAllocations()->create([
                'assignment_id' => $assignment->id,
                'project_id' => $project->id,
                'work_date' => $row['work_date'],
                'planned_hours' => $row['planned_hours'],
                'actual_hours' => $actual,
                'variance_hours' => $actual - $row['planned_hours'],
                'is_holiday' => $row['is_holiday'],
            ]);
        }

        return $this->recalculateSchedule($schedule->fresh('project', 'assignment', 'dailyAllocations'));
    }

    public function recalculateProject(Project $project): void
    {
        $assignments = $project->assignments()
            ->with(['project', 'pic', 'dependencies.dependsOn.schedule', 'schedule.dailyAllocations'])
            ->orderBy('id')
            ->get();

        foreach ($assignments as $assignment) {
            $this->generateForAssignment($assignment);
        }
    }

    public function recalculateSchedule(Schedule $schedule): Schedule
    {
        $schedule->loadMissing('project', 'assignment', 'dailyAllocations');

        $actualTotal = (float) $schedule->dailyAllocations->sum('actual_hours');
        $plannedTotal = (float) $schedule->dailyAllocations->sum('planned_hours');
        $remaining = max($plannedTotal - $actualTotal, 0);
        $startedAt = $schedule->dailyAllocations
            ->first(fn ($row) => (float) $row->actual_hours > 0)?->work_date;
        $endedAt = $schedule->dailyAllocations
            ->filter(fn ($row) => (float) $row->actual_hours > 0)
            ->sortByDesc('work_date')
            ->first()?->work_date;

        $progress = ($actualTotal + $remaining) > 0
            ? round(($actualTotal / ($actualTotal + $remaining)) * 100, 2)
            : 0;

        foreach ($schedule->dailyAllocations as $allocation) {
            $allocation->updateQuietly([
                'variance_hours' => (float) $allocation->actual_hours - (float) $allocation->planned_hours,
            ]);
        }

        $schedule->update([
            'planned_hours' => $plannedTotal,
            'actual_total_hours' => $actualTotal,
            'digestion_hours' => $actualTotal,
            'remaining_hours' => $remaining,
            'progress_percent' => $progress,
            'actual_start_date' => $startedAt,
            'actual_end_date' => $endedAt,
            'is_overdue' => $remaining > 0
                && $schedule->planned_end_date !== null
                && Carbon::parse($schedule->planned_end_date)->lt(now($schedule->project->timezone)),
        ]);

        return $schedule->fresh('assignment', 'dailyAllocations', 'project');
    }

    public function projectDates(Project $project): Collection
    {
        return collect(iterator_to_array(
            CarbonPeriod::create($project->expected_start_date, $project->expected_end_date)
        ))->map(fn ($date) => $date->copy())->values();
    }

    private function resolveStartDate(Assignment $assignment): Carbon
    {
        $project = $assignment->project;
        $dates = collect([
            Carbon::parse($project->expected_start_date, $project->timezone),
        ]);

        if ($assignment->pic?->available_from) {
            $dates->push(Carbon::parse($assignment->pic->available_from, $project->timezone));
        }

        $dependencyEnd = $assignment->dependencies
            ->filter(fn ($dependency) => $dependency->dependsOn?->schedule?->planned_end_date !== null)
            ->map(fn ($dependency) => Carbon::parse($dependency->dependsOn->schedule->planned_end_date, $project->timezone)->addDay())
            ->max();

        if ($dependencyEnd) {
            $dates->push($dependencyEnd);
        }

        $samePicEnd = Schedule::query()
            ->whereHas('assignment', function ($query) use ($assignment) {
                $query->where('assigned_pic_id', $assignment->assigned_pic_id)
                    ->whereKeyNot($assignment->id);
            })
            ->whereNotNull('planned_end_date')
            ->max('planned_end_date');

        if ($samePicEnd) {
            $dates->push(Carbon::parse($samePicEnd, $project->timezone)->addDay());
        }

        return $dates->sort()->last();
    }

    private function availableHoursFor(Assignment $assignment, Carbon $date): float
    {
        $rule = UserWorkingHour::query()
            ->where('user_id', $assignment->assigned_pic_id)
            ->where('weekday', (int) $date->dayOfWeek)
            ->first();

        if (! $rule) {
            $rule = WorkingHour::query()
                ->where('scope_type', 'project')
                ->where('project_id', $assignment->project_id)
                ->where('weekday', (int) $date->dayOfWeek)
                ->first();
        }

        if (! $rule) {
            $rule = WorkingHour::query()
                ->where('scope_type', 'global')
                ->whereNull('project_id')
                ->where('weekday', (int) $date->dayOfWeek)
                ->first();
        }

        if (! $rule || ! $rule->is_working_day) {
            return 0;
        }

        $holidayType = $this->holidayTypeFor($date);
        if ($holidayType === 'gazetted' || $holidayType === 'weekly_off') {
            return 0;
        }

        $leaveHours = (float) \App\Models\AssignmentLeave::query()
            ->where('user_id', $assignment->assigned_pic_id)
            ->whereDate('leave_date', $date->toDateString())
            ->sum('leave_hours');

        $netHours = (float) $rule->net_hours;
        if ($holidayType === 'half_day') {
            $netHours /= 2;
        }

        return max($netHours - $leaveHours, 0);
    }

    private function holidayTypeFor(Carbon $date): ?string
    {
        return Holiday::query()
            ->where('is_active', true)
            ->whereDate('holiday_date', $date->toDateString())
            ->value('holiday_type');
    }
}
