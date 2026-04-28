<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleGridService
{
    public function __construct(private readonly SchedulingService $scheduling)
    {
    }

    public function build(Project $project): array
    {
        $project->loadMissing([
            'assignments.pic',
            'assignments.wbsItem',
            'assignments.schedule.dailyAllocations',
        ]);

        $dates = $this->scheduling->projectDates($project);
        $dateKeys = $dates->map(fn (Carbon $date) => $date->toDateString());
        $holidayMap = Holiday::query()
            ->where('is_active', true)
            ->whereBetween('holiday_date', [$project->expected_start_date, $project->expected_end_date])
            ->get()
            ->mapWithKeys(fn (Holiday $holiday) => [
                Carbon::parse($holiday->holiday_date, $project->timezone)->toDateString() => $holiday->holiday_type,
            ]);
        $assignments = $project->assignments->sortBy(fn ($assignment) => $assignment->wbsItem?->wbs_number)->values();
        $today = now($project->timezone)->toDateString();

        $detailRows = $assignments->map(function ($assignment) use ($dateKeys, $holidayMap, $today) {
            $schedule = $assignment->schedule;
            $allocations = $schedule?->dailyAllocations
                ?->keyBy(fn ($row) => $row->work_date->toDateString())
                ?? collect();
            $plannedMap = $dateKeys->mapWithKeys(
                fn ($date) => [$date => round((float) ($allocations[$date]->planned_hours ?? 0), 2)]
            );
            $actualMap = $dateKeys->mapWithKeys(
                fn ($date) => [$date => round((float) ($allocations[$date]->actual_hours ?? 0), 2)]
            );
            $plannedHours = round((float) $plannedMap->sum(), 2);
            $digestionHours = round((float) $actualMap->sum(), 2);
            $actualTotalHours = $digestionHours;
            $planRestHours = $assignment->plan_rest_hours !== null ? round((float) $assignment->plan_rest_hours, 2) : null;
            $varianceHours = round($actualTotalHours - (float) ($planRestHours ?? 0), 2);
            $remainingHours = $plannedHours > 0 ? round($plannedHours - $digestionHours, 2) : null;
            $progressPercent = $remainingHours === null
                ? null
                : ($remainingHours == 0.0 ? 100.0 : ($digestionHours <= 0 ? null : round(($digestionHours / ($digestionHours + $remainingHours)) * 100, 2)));
            $enteredActualDates = $actualMap->filter(fn ($hours) => (float) $hours > 0)->keys()->values();
            $enteredActualCount = $enteredActualDates->count();
            $firstActualDateKey = $enteredActualDates->first();
            $lastActualDateKey = $enteredActualDates->last();

            return [
                'assignment' => $assignment,
                'schedule' => $schedule,
                'planned_map' => $plannedMap,
                'actual_map' => $actualMap,
                'planned_hours' => $plannedHours,
                'digestion_hours' => $digestionHours,
                'actual_total_hours' => $actualTotalHours,
                'plan_rest_hours' => $planRestHours,
                'variance_hours' => $varianceHours,
                'remaining_hours' => $remainingHours,
                'progress_percent' => $progressPercent,
                'has_missing_actuals' => $enteredActualCount === 0,
                'has_short_actuals' => $enteredActualCount > 0 && $actualTotalHours < $plannedHours,
                'is_complete' => $progressPercent !== null && round($progressPercent, 2) >= 100.0,
                'first_actual_date' => $firstActualDateKey,
                'last_actual_date' => $lastActualDateKey,
                'date_meta' => $dateKeys->mapWithKeys(fn ($date) => [
                    $date => [
                        'is_today' => $date === $today,
                        'holiday_type' => $holidayMap[$date] ?? null,
                        'is_holiday' => isset($holidayMap[$date]),
                        'planned_has_value' => (float) ($plannedMap[$date] ?? 0) > 0,
                        'actual_has_value' => (float) ($actualMap[$date] ?? 0) > 0,
                    ],
                ]),
            ];
        });

        $dailySummary = $assignments
            ->groupBy(fn ($assignment) => $assignment->pic?->name ?? 'Unassigned')
            ->map(function (Collection $group, string $picName) use ($dateKeys, $holidayMap, $today) {
                $planned = [];
                $actual = [];
                foreach ($dateKeys as $date) {
                    $plannedTotal = 0;
                    $actualTotal = 0;

                    foreach ($group as $assignment) {
                        $allocations = $assignment->schedule?->dailyAllocations
                            ?->keyBy(fn ($row) => $row->work_date->toDateString())
                            ?? collect();

                        $plannedTotal += (float) ($allocations[$date]->planned_hours ?? 0);
                        $actualTotal += (float) ($allocations[$date]->actual_hours ?? 0);
                    }

                    $planned[$date] = round($plannedTotal, 2);
                    $actual[$date] = round($actualTotal, 2);
                }

                return [
                    'pic' => $picName,
                    'planned' => $planned,
                    'actual' => $actual,
                    'date_meta' => collect($dateKeys)->mapWithKeys(fn ($date) => [
                        $date => [
                            'is_today' => $date === $today,
                            'is_holiday' => isset($holidayMap[$date]),
                            'holiday_type' => $holidayMap[$date] ?? null,
                        ],
                    ]),
                ];
            })
            ->values();

        $monthlySummary = $assignments
            ->groupBy(fn ($assignment) => $assignment->pic?->name ?? 'Unassigned')
            ->map(function (Collection $group, string $picName) use ($dateKeys) {
                $planned = $dateKeys->sum(function ($date) use ($group) {
                    return $group->sum(function ($assignment) use ($date) {
                        return (float) ($assignment->schedule?->dailyAllocations?->first(
                            fn ($allocation) => $allocation->work_date?->toDateString() === $date
                        )?->planned_hours ?? 0);
                    });
                });
                $actual = $dateKeys->sum(function ($date) use ($group) {
                    return $group->sum(function ($assignment) use ($date) {
                        return (float) ($assignment->schedule?->dailyAllocations?->first(
                            fn ($allocation) => $allocation->work_date?->toDateString() === $date
                        )?->actual_hours ?? 0);
                    });
                });

                return [
                    'pic' => $picName,
                    'planned' => round((float) $planned, 2),
                    'actual' => round((float) $actual, 2),
                ];
            })
            ->values();

        return [
            'dates' => $dates,
            'detailRows' => $detailRows,
            'dailySummary' => $dailySummary,
            'monthlySummary' => $monthlySummary,
            'holidayDates' => $holidayMap->keys()->values(),
            'todayPanel' => [
                'today' => $today,
                'today_task_count' => $assignments->filter(function ($assignment) use ($today) {
                    return $assignment->schedule?->dailyAllocations?->contains(fn ($row) => $row->work_date->toDateString() === $today && (float) $row->planned_hours > 0);
                })->count(),
                'overdue_count' => $assignments->filter(fn ($assignment) => (bool) $assignment->schedule?->is_overdue)->count(),
                'critical_tasks_today' => $assignments->filter(function ($assignment) use ($today) {
                    return $assignment->is_critical
                        && $assignment->schedule?->dailyAllocations?->contains(fn ($row) => $row->work_date->toDateString() === $today);
                })->count(),
            ],
        ];
    }
}
