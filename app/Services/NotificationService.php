<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\Assignment;
use App\Models\Notification;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationService
{
    public function createDailyTaskNotifications(): int
    {
        $count = 0;
        $today = now()->toDateString();

        Assignment::query()
            ->with(['schedule.dailyAllocations', 'pic', 'project'])
            ->get()
            ->each(function (Assignment $assignment) use ($today, &$count) {
                $hasTodayWork = $assignment->schedule?->dailyAllocations?->contains(
                    fn ($row) => $row->work_date->toDateString() === $today && (float) $row->planned_hours > 0
                );

                if (! $hasTodayWork || ! $assignment->pic) {
                    return;
                }

                $this->createAndSendNotification([
                    'project_id' => $assignment->project_id,
                    'assignment_id' => $assignment->id,
                    'user_id' => $assignment->assigned_pic_id,
                    'type' => 'daily_task',
                    'title' => 'Daily task reminder',
                    'message' => 'You have planned work scheduled for today.',
                    'action_url' => route('schedule.show', $assignment->project_id),
                    'scheduled_for' => now(),
                ]);

                $count++;
            });

        return $count;
    }

    public function createOverdueNotifications(): int
    {
        $count = 0;

        Assignment::query()
            ->with(['schedule', 'pic'])
            ->get()
            ->filter(fn (Assignment $assignment) => (bool) $assignment->schedule?->is_overdue)
            ->each(function (Assignment $assignment) use (&$count) {
                $this->createAndSendNotification([
                    'project_id' => $assignment->project_id,
                    'assignment_id' => $assignment->id,
                    'user_id' => $assignment->assigned_pic_id,
                    'type' => 'overdue',
                    'title' => 'Overdue assignment alert',
                    'message' => 'One of your assignments is overdue and still has remaining work.',
                    'action_url' => route('schedule.show', $assignment->project_id),
                    'scheduled_for' => now(),
                ]);

                $count++;
            });

        return $count;
    }

    public function createRiskNotifications(): int
    {
        $count = 0;

        Assignment::query()
            ->with(['schedule', 'pic'])
            ->get()
            ->filter(function (Assignment $assignment) {
                $plannedEnd = $assignment->schedule?->planned_end_date;
                return $plannedEnd !== null
                    && Carbon::parse($plannedEnd)->isBetween(now(), now()->copy()->addDays(2))
                    && (float) ($assignment->schedule?->remaining_hours ?? 0) > 0;
            })
            ->each(function (Assignment $assignment) use (&$count) {
                $this->createAndSendNotification([
                    'project_id' => $assignment->project_id,
                    'assignment_id' => $assignment->id,
                    'user_id' => $assignment->project_manager_id ?: $assignment->assigned_pic_id,
                    'type' => 'risk_alert',
                    'title' => 'Risk alert',
                    'message' => 'A task is at risk of slipping within the next two days.',
                    'action_url' => route('schedule.show', $assignment->project_id),
                    'scheduled_for' => now(),
                ]);

                $count++;
            });

        return $count;
    }

    public function createPmSummaryNotifications(): int
    {
        $count = 0;

        Project::query()->with('projectManager', 'schedules')->get()->each(function (Project $project) use (&$count) {
            if (! $project->projectManager) {
                return;
            }

            $this->createAndSendNotification([
                'project_id' => $project->id,
                'assignment_id' => null,
                'user_id' => $project->project_manager_id,
                'type' => 'summary',
                'title' => 'Project summary',
                'message' => 'Daily summary generated for '.$project->name.'.',
                'action_url' => route('dashboard'),
                'scheduled_for' => now(),
            ]);

            $count++;
        });

        return $count;
    }

    private function createAndSendNotification(array $attributes): Notification
    {
        $notification = Notification::query()->create([
            ...$attributes,
            'status' => 'pending',
            'sent_at' => null,
        ]);

        $notification->loadMissing('user', 'project', 'assignment.wbsItem');

        if (! $notification->user || blank($notification->user->email)) {
            $notification->forceFill(['status' => 'failed'])->save();

            return $notification;
        }

        try {
            Mail::to($notification->user)->send(new NotificationMail($notification));

            $notification->forceFill([
                'status' => 'sent',
                'sent_at' => now(),
            ])->save();
        } catch (Throwable) {
            $notification->forceFill([
                'status' => 'failed',
                'sent_at' => null,
            ])->save();
        }

        return $notification;
    }
}
