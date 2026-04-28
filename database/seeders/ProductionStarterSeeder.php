<?php

namespace Database\Seeders;

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

class ProductionStarterSeeder extends Seeder
{
    private const TEMP_PASSWORD = 'Schedulix2026!';

    public function run(): void
    {
        [$owner, $leader, $engineerOne, $engineerTwo] = $this->seedUsers();

        $this->seedWorkingHours();
        $taskMasters = $this->seedTaskMasters();

        $projects = [
            [
                'code' => 'SCH-PORTAL',
                'name' => 'Customer Portal Revamp',
                'overview' => 'Modernize the customer-facing portal with role-based access, dashboard widgets, and document workflows.',
                'objective' => 'Deliver a cleaner user experience, simpler support workflows, and better reporting visibility.',
                'start_offset' => -10,
                'end_offset' => 55,
                'wbs' => [
                    [
                        'phase' => 'Discovery & Design',
                        'description' => 'Confirm scope, journeys, and interface direction.',
                        'tasks' => [
                            [
                                'name' => 'Requirements Workshop',
                                'content_item_type' => 'documentation',
                                'task_code' => 'DOC-REQ-001',
                                'planned_hours' => 16,
                                'assigned_pic_id' => $leader->id,
                                'assigned_role' => 'project_leader',
                                'priority' => 'high',
                                'remark' => 'Finalize stakeholders, scope boundaries, and acceptance notes.',
                            ],
                            [
                                'name' => 'Portal UI Wireframes',
                                'content_item_type' => 'design',
                                'task_code' => 'DSN-UI-001',
                                'planned_hours' => 24,
                                'assigned_pic_id' => $engineerOne->id,
                                'assigned_role' => 'member',
                                'priority' => 'high',
                                'remark' => 'Prepare responsive wireframes for dashboard and request flows.',
                            ],
                        ],
                    ],
                    [
                        'phase' => 'Build & Release',
                        'description' => 'Implement the portal and prepare release support.',
                        'tasks' => [
                            [
                                'name' => 'Portal Frontend Implementation',
                                'content_item_type' => 'development',
                                'task_code' => 'DEV-FE-001',
                                'planned_hours' => 40,
                                'assigned_pic_id' => $engineerOne->id,
                                'assigned_role' => 'member',
                                'priority' => 'critical',
                                'remark' => 'Build authenticated screens, forms, and approval timeline.',
                            ],
                            [
                                'name' => 'Release Verification',
                                'content_item_type' => 'qa',
                                'task_code' => 'QAT-UAT-001',
                                'planned_hours' => 18,
                                'assigned_pic_id' => $engineerTwo->id,
                                'assigned_role' => 'member',
                                'priority' => 'medium',
                                'remark' => 'Validate regression scenarios and launch checklist.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'SCH-MOBILE',
                'name' => 'Employee Mobile Support App',
                'overview' => 'Build a lightweight mobile-first support application for internal requests and leave submissions.',
                'objective' => 'Reduce manual coordination and provide self-service access to staff operations.',
                'start_offset' => -5,
                'end_offset' => 70,
                'wbs' => [
                    [
                        'phase' => 'Planning',
                        'description' => 'Define mobile scope, APIs, and user journeys.',
                        'tasks' => [
                            [
                                'name' => 'Mobile Scope Definition',
                                'content_item_type' => 'documentation',
                                'task_code' => 'DOC-REQ-001',
                                'planned_hours' => 14,
                                'assigned_pic_id' => $leader->id,
                                'assigned_role' => 'project_leader',
                                'priority' => 'high',
                                'remark' => 'Capture support categories, SLA rules, and approval scenarios.',
                            ],
                            [
                                'name' => 'API Contract Design',
                                'content_item_type' => 'design',
                                'task_code' => 'DSN-API-001',
                                'planned_hours' => 18,
                                'assigned_pic_id' => $engineerTwo->id,
                                'assigned_role' => 'member',
                                'priority' => 'high',
                                'remark' => 'Draft payloads and integration expectations for mobile requests.',
                            ],
                        ],
                    ],
                    [
                        'phase' => 'Implementation',
                        'description' => 'Develop backend services and mobile-ready UI.',
                        'tasks' => [
                            [
                                'name' => 'Backend Request Workflow',
                                'content_item_type' => 'development',
                                'task_code' => 'DEV-BE-001',
                                'planned_hours' => 36,
                                'assigned_pic_id' => $engineerTwo->id,
                                'assigned_role' => 'member',
                                'priority' => 'critical',
                                'remark' => 'Implement API endpoints, approval rules, and notifications.',
                            ],
                            [
                                'name' => 'Mobile UI Build',
                                'content_item_type' => 'development',
                                'task_code' => 'DEV-FE-001',
                                'planned_hours' => 32,
                                'assigned_pic_id' => $engineerOne->id,
                                'assigned_role' => 'member',
                                'priority' => 'critical',
                                'remark' => 'Create mobile-friendly request creation and tracking screens.',
                            ],
                            [
                                'name' => 'Deployment Preparation',
                                'content_item_type' => 'deployment',
                                'task_code' => 'DEP-REL-001',
                                'planned_hours' => 12,
                                'assigned_pic_id' => $leader->id,
                                'assigned_role' => 'project_leader',
                                'priority' => 'medium',
                                'remark' => 'Prepare rollout notes and production readiness checklist.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'SCH-OPS',
                'name' => 'Operations Monitoring Dashboard',
                'overview' => 'Create a central dashboard for service health, alert tracking, and weekly operational reporting.',
                'objective' => 'Improve operational visibility and shorten reaction time for support incidents.',
                'start_offset' => 0,
                'end_offset' => 45,
                'wbs' => [
                    [
                        'phase' => 'Analysis',
                        'description' => 'Clarify metrics, data sources, and alert ownership.',
                        'tasks' => [
                            [
                                'name' => 'Metrics Inventory',
                                'content_item_type' => 'documentation',
                                'task_code' => 'DOC-REQ-001',
                                'planned_hours' => 10,
                                'assigned_pic_id' => $leader->id,
                                'assigned_role' => 'project_leader',
                                'priority' => 'medium',
                                'remark' => 'Confirm dashboard KPIs, thresholds, and weekly summary expectations.',
                            ],
                            [
                                'name' => 'Dashboard Layout Design',
                                'content_item_type' => 'design',
                                'task_code' => 'DSN-UI-001',
                                'planned_hours' => 16,
                                'assigned_pic_id' => $engineerOne->id,
                                'assigned_role' => 'member',
                                'priority' => 'medium',
                                'remark' => 'Design key panels for incidents, uptime, and escalation ownership.',
                            ],
                        ],
                    ],
                    [
                        'phase' => 'Delivery',
                        'description' => 'Build the dashboard and verify release quality.',
                        'tasks' => [
                            [
                                'name' => 'Dashboard Data Integration',
                                'content_item_type' => 'development',
                                'task_code' => 'DEV-BE-001',
                                'planned_hours' => 28,
                                'assigned_pic_id' => $engineerTwo->id,
                                'assigned_role' => 'member',
                                'priority' => 'high',
                                'remark' => 'Integrate metrics feeds and service status aggregation.',
                            ],
                            [
                                'name' => 'Dashboard QA & Handover',
                                'content_item_type' => 'qa',
                                'task_code' => 'QAT-UAT-001',
                                'planned_hours' => 14,
                                'assigned_pic_id' => $leader->id,
                                'assigned_role' => 'project_leader',
                                'priority' => 'medium',
                                'remark' => 'Validate alert flows and prepare handover to operations users.',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($projects as $definition) {
            $this->seedProject(
                definition: $definition,
                owner: $owner,
                leader: $leader,
                members: [$owner, $leader, $engineerOne, $engineerTwo],
                taskMasters: $taskMasters,
            );
        }
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: User}
     */
    private function seedUsers(): array
    {
        $owner = User::query()->updateOrCreate(
            ['email' => 'htet.myet.aung@schedulix.app'],
            [
                'name' => 'Htet Myet Aung',
                'password' => Hash::make(self::TEMP_PASSWORD),
                'position' => 'Assistant Manager',
                'system_role' => 'owner',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        $leader = User::query()->updateOrCreate(
            ['email' => 'khnin.hnin.myo@schedulix.app'],
            [
                'name' => 'Khnin Hnin Myo',
                'password' => Hash::make(self::TEMP_PASSWORD),
                'position' => 'Senior Engineer',
                'system_role' => 'project_leader',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        $engineerOne = User::query()->updateOrCreate(
            ['email' => 'su.pyae.yee@schedulix.app'],
            [
                'name' => 'Su Pyae Yee',
                'password' => Hash::make(self::TEMP_PASSWORD),
                'position' => 'Engineer',
                'system_role' => 'member',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        $engineerTwo = User::query()->updateOrCreate(
            ['email' => 'aung.kyaw.paing@schedulix.app'],
            [
                'name' => 'Aung Kyaw Paing',
                'password' => Hash::make(self::TEMP_PASSWORD),
                'position' => 'Engineer',
                'system_role' => 'member',
                'locale' => 'en',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        return [$owner, $leader, $engineerOne, $engineerTwo];
    }

    private function seedWorkingHours(): void
    {
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
    }

    /**
     * @return array<string, TaskMaster>
     */
    private function seedTaskMasters(): array
    {
        $definitions = [
            [
                'task_code' => 'DOC-REQ-001',
                'name' => 'Requirements & Scope Definition',
                'content_item_type' => 'documentation',
                'platform' => 'cross_platform',
                'description' => 'Gather scope, acceptance criteria, and stakeholder decisions.',
            ],
            [
                'task_code' => 'DSN-UI-001',
                'name' => 'UI / UX Design',
                'content_item_type' => 'design',
                'platform' => 'web',
                'description' => 'Prepare screens, wireframes, and interaction patterns.',
            ],
            [
                'task_code' => 'DSN-API-001',
                'name' => 'API Contract Design',
                'content_item_type' => 'design',
                'platform' => 'backend',
                'description' => 'Define endpoint contracts and integration expectations.',
            ],
            [
                'task_code' => 'DEV-FE-001',
                'name' => 'Frontend Development',
                'content_item_type' => 'development',
                'platform' => 'web',
                'description' => 'Build screens, forms, and user flows.',
            ],
            [
                'task_code' => 'DEV-BE-001',
                'name' => 'Backend Development',
                'content_item_type' => 'development',
                'platform' => 'backend',
                'description' => 'Build APIs, workflow logic, and integrations.',
            ],
            [
                'task_code' => 'QAT-UAT-001',
                'name' => 'QA & User Acceptance Testing',
                'content_item_type' => 'qa',
                'platform' => 'cross_platform',
                'description' => 'Validate quality, regression, and acceptance readiness.',
            ],
            [
                'task_code' => 'DEP-REL-001',
                'name' => 'Release Preparation',
                'content_item_type' => 'deployment',
                'platform' => 'infra',
                'description' => 'Prepare release notes, deployment steps, and rollback plan.',
            ],
        ];

        $taskMasters = [];

        foreach ($definitions as $definition) {
            $task = TaskMaster::query()->updateOrCreate(
                ['task_code' => $definition['task_code']],
                [...$definition, 'is_active' => true]
            );

            $taskMasters[$task->task_code] = $task;
        }

        return $taskMasters;
    }

    /**
     * @param array<int, User> $members
     * @param array<string, TaskMaster> $taskMasters
     * @param array<string, mixed> $definition
     */
    private function seedProject(array $definition, User $owner, User $leader, array $members, array $taskMasters): void
    {
        $project = Project::query()->updateOrCreate(
            ['code' => $definition['code']],
            [
                'name' => $definition['name'],
                'project_manager_id' => $owner->id,
                'expected_start_date' => now()->addDays($definition['start_offset'])->toDateString(),
                'expected_end_date' => now()->addDays($definition['end_offset'])->toDateString(),
                'overview' => $definition['overview'],
                'objective' => $definition['objective'],
                'team_size' => count($members),
                'timezone' => 'Asia/Yangon',
                'status' => 'ongoing',
                'locale_default' => 'en',
            ]
        );

        foreach ($members as $member) {
            ProjectMember::query()->updateOrCreate(
                ['project_id' => $project->id, 'user_id' => $member->id],
                [
                    'project_role' => $member->id === $owner->id
                        ? 'project_manager'
                        : ($member->id === $leader->id ? 'project_leader' : 'member'),
                    'joined_at' => now()->toDateString(),
                ]
            );
        }

        $project->assignments()->delete();
        $project->wbsItems()->delete();

        /** @var WbsBuilderService $wbsBuilder */
        $wbsBuilder = app(WbsBuilderService::class);
        /** @var AssignmentService $assignmentService */
        $assignmentService = app(AssignmentService::class);
        /** @var SchedulingService $scheduling */
        $scheduling = app(SchedulingService::class);

        $previousAssignmentId = null;

        foreach ($definition['wbs'] as $phaseIndex => $phaseDefinition) {
            $phase = $wbsBuilder->create($project, [
                'parent_id' => null,
                'task_master_id' => null,
                'item_name' => $phaseDefinition['phase'],
                'item_type' => 'phase',
                'content_item_type' => null,
                'platform' => 'cross_platform',
                'description' => $phaseDefinition['description'],
                'is_assignable' => false,
                'sort_order' => $phaseIndex + 1,
            ]);

            foreach ($phaseDefinition['tasks'] as $taskIndex => $taskDefinition) {
                $taskMaster = $taskMasters[$taskDefinition['task_code']];

                $wbsItem = $wbsBuilder->create($project, [
                    'parent_id' => $phase->id,
                    'task_master_id' => $taskMaster->id,
                    'item_name' => $taskDefinition['name'],
                    'item_type' => 'task',
                    'content_item_type' => $taskDefinition['content_item_type'],
                    'platform' => $taskMaster->platform,
                    'description' => $taskDefinition['remark'],
                    'is_assignable' => true,
                    'sort_order' => $taskIndex + 1,
                ]);

                $assignment = $assignmentService->create($project, [
                    'project_manager_id' => $owner->id,
                    'project_leader_ids' => [$leader->id],
                    'project_wbs_item_id' => $wbsItem->id,
                    'depends_on_assignment_id' => $previousAssignmentId,
                    'priority' => $taskDefinition['priority'],
                    'planned_hours' => $taskDefinition['planned_hours'],
                    'assigned_pic_id' => $taskDefinition['assigned_pic_id'],
                    'leave_dates' => [],
                    'assigned_role' => $taskDefinition['assigned_role'],
                    'remark' => $taskDefinition['remark'],
                    'auto_create_schedule' => true,
                    'status' => 'scheduled',
                    'is_critical' => in_array($taskDefinition['priority'], ['high', 'critical'], true),
                ]);

                $previousAssignmentId = $assignment->id;
            }
        }

        $scheduling->recalculateProject($project->fresh());
    }
}
