<?php

namespace Tests\Feature;

use App\Mail\NotificationMail;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_task_notification_sends_email_to_assigned_pic(): void
    {
        Mail::fake();

        $this->seed();

        $project = Project::query()->where('code', 'WBS-DEMO')->firstOrFail();
        $assignment = $project->assignments()->with(['schedule.dailyAllocations', 'pic'])->firstOrFail();
        $today = now()->startOfDay();

        $assignment->schedule->dailyAllocations()->updateOrCreate(
            ['work_date' => $today],
            [
                'assignment_id' => $assignment->id,
                'project_id' => $project->id,
                'planned_hours' => 2,
                'actual_hours' => 0,
                'variance_hours' => 0,
                'is_holiday' => false,
            ]
        );

        app(NotificationService::class)->createDailyTaskNotifications();

        Mail::assertSent(NotificationMail::class, function (NotificationMail $mail) use ($assignment) {
            return $mail->hasTo($assignment->pic->email)
                && $mail->notification->user_id === $assignment->assigned_pic_id;
        });

        $this->assertDatabaseHas('notifications', [
            'assignment_id' => $assignment->id,
            'user_id' => $assignment->assigned_pic_id,
            'type' => 'daily_task',
            'status' => 'sent',
        ]);
    }
}
