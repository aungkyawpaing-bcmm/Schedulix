<?php

namespace Database\Seeders;

use App\Models\Export;
use App\Models\Holiday;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\TaskMaster;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\AssignmentService;
use App\Services\SchedulingService;
use App\Services\WbsBuilderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@wbs-generator.test'],
            [
                'name' => 'System Owner',
                'password' => Hash::make('password'),
                'position' => 'Owner',
                'system_role' => 'owner',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        $pm = User::query()->updateOrCreate(
            ['email' => 'pm@wbs-generator.test'],
            [
                'name' => 'Project Manager',
                'password' => Hash::make('password'),
                'position' => 'Project Manager',
                'system_role' => 'project_manager',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        $leader = User::query()->updateOrCreate(
            ['email' => 'leader@wbs-generator.test'],
            [
                'name' => 'Project Leader',
                'password' => Hash::make('password'),
                'position' => 'Project Leader',
                'system_role' => 'project_leader',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        $member = User::query()->updateOrCreate(
            ['email' => 'member@wbs-generator.test'],
            [
                'name' => 'Team Member',
                'password' => Hash::make('password'),
                'position' => 'Developer',
                'system_role' => 'member',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
                'available_from' => now()->addDay()->toDateString(),
            ]
        );

        $project = Project::query()->updateOrCreate(
            ['code' => 'WBS-DEMO'],
            [
                'name' => 'Demo WBS Project',
                'project_manager_id' => $pm->id,
                'expected_start_date' => now()->startOfMonth()->toDateString(),
                'expected_end_date' => now()->addMonth()->endOfMonth()->toDateString(),
                'overview' => 'Seeded demo project for initial testing.',
                'objective' => 'Validate CRUD, schedule generation, export, and notification workflows.',
                'team_size' => 4,
                'timezone' => 'Asia/Yangon',
                'status' => 'ongoing',
                'locale_default' => 'en',
            ]
        );

        foreach ([
            [$pm->id, 'project_manager'],
            [$leader->id, 'project_leader'],
            [$member->id, 'member'],
        ] as [$userId, $role]) {
            ProjectMember::query()->updateOrCreate(
                ['project_id' => $project->id, 'user_id' => $userId],
                ['project_role' => $role, 'joined_at' => now()->toDateString()]
            );
        }

        foreach (range(0, 6) as $weekday) {
            WorkingHour::query()->updateOrCreate(
                ['scope_type' => 'global', 'project_id' => null, 'weekday' => $weekday],
                [
                    'start_time' => in_array($weekday, [0, 6], true) ? null : '09:00',
                    'end_time' => in_array($weekday, [0, 6], true) ? null : '18:00',
                    'lunch_start_time' => in_array($weekday, [0, 6], true) ? null : '12:00',
                    'lunch_end_time' => in_array($weekday, [0, 6], true) ? null : '13:00',
                    'net_hours' => in_array($weekday, [0, 6], true) ? 0 : 8,
                    'is_working_day' => ! in_array($weekday, [0, 6], true),
                ]
            );
        }

        foreach (config('wbs.content_item_types') as $type) {
            TaskMaster::query()->updateOrCreate(
                ['task_code' => strtoupper(substr($type, 0, 3)).'-001'],
                [
                    'name' => ucfirst($type).' task',
                    'content_item_type' => $type,
                    'platform' => 'web',
                    'description' => 'Seeded sample task for '.$type.'.',
                    'is_active' => true,
                ]
            );
        }

        Holiday::query()->updateOrCreate(
            ['holiday_date' => now()->addWeek()->toDateString()],
            [
                'name' => 'Sample Holiday',
                'holiday_type' => 'gazetted',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'notes' => 'Seeded holiday for schedule testing.',
            ]
        );

        $project->wbsItems()->delete();
        $project->assignments()->delete();

        $wbsBuilder = app(WbsBuilderService::class);
        $assignmentService = app(AssignmentService::class);
        $scheduling = app(SchedulingService::class);

        $analysisRoot = $wbsBuilder->create($project, [
            'parent_id' => null,
            'task_master_id' => null,
            'item_name' => 'Analysis & Planning',
            'item_type' => 'phase',
            'content_item_type' => null,
            'platform' => 'web',
            'description' => 'Discovery and planning activities.',
            'is_assignable' => false,
            'sort_order' => 1,
        ]);

        $developmentRoot = $wbsBuilder->create($project, [
            'parent_id' => null,
            'task_master_id' => null,
            'item_name' => 'Development',
            'item_type' => 'phase',
            'content_item_type' => null,
            'platform' => 'web',
            'description' => 'Core build activities.',
            'is_assignable' => false,
            'sort_order' => 2,
        ]);

        $requirements = $wbsBuilder->create($project, [
            'parent_id' => $analysisRoot->id,
            'task_master_id' => TaskMaster::query()->where('content_item_type', 'documentation')->value('id'),
            'item_name' => 'Requirements Validation',
            'item_type' => 'task',
            'content_item_type' => 'documentation',
            'platform' => 'web',
            'description' => 'Confirm final scope and requirements.',
            'is_assignable' => true,
            'sort_order' => 1,
        ]);

        $buildGrid = $wbsBuilder->create($project, [
            'parent_id' => $developmentRoot->id,
            'task_master_id' => TaskMaster::query()->where('content_item_type', 'development')->value('id'),
            'item_name' => 'Schedule Grid Build',
            'item_type' => 'task',
            'content_item_type' => 'development',
            'platform' => 'web',
            'description' => 'Build the Excel-style detail table.',
            'is_assignable' => true,
            'sort_order' => 1,
        ]);

        $exportTask = $wbsBuilder->create($project, [
            'parent_id' => $developmentRoot->id,
            'task_master_id' => TaskMaster::query()->where('content_item_type', 'deployment')->value('id'),
            'item_name' => 'Excel Export Mapping',
            'item_type' => 'task',
            'content_item_type' => 'deployment',
            'platform' => 'web',
            'description' => 'Map UI data to export output.',
            'is_assignable' => true,
            'sort_order' => 2,
        ]);

        $requirementsAssignment = $assignmentService->create($project, [
            'project_manager_id' => $pm->id,
            'project_leader_ids' => [$leader->id],
            'project_wbs_item_id' => $requirements->id,
            'priority' => 'high',
            'planned_hours' => 12,
            'assigned_pic_id' => $leader->id,
            'leave_dates' => [],
            'assigned_role' => 'project_leader',
            'remark' => 'Validate UI and technical handoff.',
            'auto_create_schedule' => true,
            'status' => 'scheduled',
            'is_critical' => true,
        ]);

        $buildAssignment = $assignmentService->create($project, [
            'project_manager_id' => $pm->id,
            'project_leader_ids' => [$leader->id],
            'project_wbs_item_id' => $buildGrid->id,
            'depends_on_assignment_id' => $requirementsAssignment->id,
            'priority' => 'critical',
            'planned_hours' => 28,
            'assigned_pic_id' => $member->id,
            'leave_dates' => [now()->addDays(10)->toDateString()],
            'assigned_role' => 'member',
            'remark' => 'Build the main WBS schedule grid.',
            'auto_create_schedule' => true,
            'status' => 'scheduled',
            'is_critical' => true,
        ]);

        $exportAssignment = $assignmentService->create($project, [
            'project_manager_id' => $pm->id,
            'project_leader_ids' => [$leader->id],
            'project_wbs_item_id' => $exportTask->id,
            'depends_on_assignment_id' => $buildAssignment->id,
            'priority' => 'medium',
            'planned_hours' => 10,
            'assigned_pic_id' => $pm->id,
            'leave_dates' => [],
            'assigned_role' => 'project_manager',
            'remark' => 'Finalize xlsx output format.',
            'auto_create_schedule' => true,
            'status' => 'scheduled',
            'is_critical' => false,
        ]);

        $scheduling->recalculateProject($project);

        $buildSchedule = $buildAssignment->fresh('schedule.dailyAllocations')->schedule;
        if ($buildSchedule) {
            foreach ($buildSchedule->dailyAllocations->take(2) as $allocation) {
                $allocation->update([
                    'actual_hours' => min((float) $allocation->planned_hours, 6),
                    'variance_hours' => min((float) $allocation->planned_hours, 6) - (float) $allocation->planned_hours,
                ]);
            }

            $scheduling->recalculateSchedule($buildSchedule->fresh('project', 'assignment', 'dailyAllocations'));
        }

        Notification::query()->updateOrCreate(
            ['project_id' => $project->id, 'assignment_id' => $buildAssignment->id, 'type' => 'risk_alert'],
            [
                'user_id' => $pm->id,
                'status' => 'sent',
                'title' => 'Risk alert',
                'message' => 'Schedule Grid Build is close to its planned end date.',
                'action_url' => route('schedule.show', $project),
                'scheduled_for' => now(),
                'sent_at' => now(),
            ]
        );

        Storage::disk('local')->put('exports/WBS-DEMO-seeded-export.xlsx', 'Seeded export placeholder');

        Export::query()->updateOrCreate(
            ['project_id' => $project->id, 'file_name' => 'WBS-DEMO-seeded-export.xlsx'],
            [
                'user_id' => $owner->id,
                'status' => 'completed',
                'file_path' => 'exports/WBS-DEMO-seeded-export.xlsx',
                'filters' => ['seeded' => true],
                'exported_at' => now(),
            ]
        );

        $exportAssignment->refresh();
    }
}
