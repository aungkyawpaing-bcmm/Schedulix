<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Services\ScheduleGridService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleProgressSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_schedule_progress_does_not_create_duplicate_daily_allocations(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();
        $project = Project::query()->where('code', 'WBS-DEMO')->firstOrFail();
        $grid = app(ScheduleGridService::class)->build($project);

        $assignment = $project->assignments()->orderByDesc('id')->firstOrFail();
        $schedule = $assignment->schedule()->with('dailyAllocations')->firstOrFail();
        $beforeCount = $schedule->dailyAllocations->count();

        $postedHours = [];
        foreach ($grid['dates'] as $date) {
            $postedHours[$date->toDateString()] = 0;
        }

        $response = $this->actingAs($owner)->post(route('schedule.progress.store', $project), [
            'actual_hours' => [
                $assignment->id => $postedHours,
            ],
            'plan_rest_hours' => [
                $assignment->id => $assignment->plan_rest_hours,
            ],
            'actual_start_dates' => [],
            'actual_end_dates' => [],
        ]);

        $response
            ->assertRedirect(route('schedule.show', $project))
            ->assertSessionHas('status', 'Schedule progress updated.');

        $afterSchedule = $assignment->schedule()->with('dailyAllocations')->firstOrFail();

        $this->assertSame($beforeCount, $afterSchedule->dailyAllocations->count());
        $this->assertSame(
            $afterSchedule->dailyAllocations->count(),
            $afterSchedule->dailyAllocations
                ->map(fn ($allocation) => $allocation->work_date->toDateString())
                ->unique()
                ->count()
        );
    }
}
