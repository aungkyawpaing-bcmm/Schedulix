<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Export;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\ScheduleDailyAllocation;
use App\Models\User;

class DashboardService
{
    public function summaryFor(User $user): array
    {
        $projectQuery = Project::query();

        if (! $user->isOwner()) {
            $projectQuery->where(function ($query) use ($user) {
                $query
                    ->where('project_manager_id', $user->id)
                    ->orWhereHas('members', fn ($members) => $members->where('user_id', $user->id));
            });
        }

        return [
            'total_projects' => (clone $projectQuery)->count(),
            'ongoing_projects' => (clone $projectQuery)->where('status', 'ongoing')->count(),
            'completed_projects' => (clone $projectQuery)->where('status', 'completed')->count(),
            'overdue_tasks' => Schedule::query()->where('is_overdue', true)->count(),
            'today_tasks' => ScheduleDailyAllocation::query()->whereDate('work_date', now()->toDateString())->where('planned_hours', '>', 0)->count(),
            'export_count' => Export::query()->count(),
            'alerts' => Notification::query()->latest()->take(5)->get(),
            'recent_projects' => (clone $projectQuery)->latest()->take(5)->get(),
            'recent_activities' => AuditLog::query()->with('user')->latest()->take(6)->get(),
        ];
    }
}
