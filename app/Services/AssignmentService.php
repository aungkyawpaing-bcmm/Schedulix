<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Project;
use App\Models\ProjectWbsItem;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    public function __construct(
        private readonly SchedulingService $scheduling,
        private readonly AuditLogService $auditLogs,
    ) {
    }

    public function create(Project $project, array $data, ?Request $request = null): Assignment
    {
        return DB::transaction(function () use ($project, $data, $request) {
            $this->validatePayload($project, $data);

            $leaders = Arr::pull($data, 'project_leader_ids', []);
            $leaveDates = Arr::pull($data, 'leave_dates', []);
            $dependencyId = Arr::pull($data, 'depends_on_assignment_id');

            $assignment = $project->assignments()->create([
                ...$data,
                'project_id' => $project->id,
                'plan_rest_hours' => $data['planned_hours'],
                'status' => $data['status'] ?? 'draft',
                'is_critical' => $data['is_critical'] ?? false,
            ]);

            $this->syncProjectRoles($project, $data['project_manager_id'], $leaders);
            $this->syncDependency($assignment, $dependencyId);
            $this->syncLeaves($assignment, $leaveDates);

            if ($assignment->auto_create_schedule) {
                $this->scheduling->generateForAssignment($assignment->fresh());
                $assignment->update(['status' => 'scheduled']);
            }

            $assignment = $assignment->fresh(['project', 'pic', 'wbsItem', 'dependencies.dependsOn', 'schedule']);

            $this->auditLogs->record('created', $assignment, [], $assignment->toArray(), $request);

            return $assignment;
        });
    }

    public function update(Assignment $assignment, array $data, ?Request $request = null): Assignment
    {
        return DB::transaction(function () use ($assignment, $data, $request) {
            $old = $assignment->toArray();
            $project = $assignment->project;

            $this->validatePayload($project, $data, $assignment);

            $leaders = Arr::pull($data, 'project_leader_ids', []);
            $leaveDates = Arr::pull($data, 'leave_dates', []);
            $dependencyId = Arr::pull($data, 'depends_on_assignment_id');

            $assignment->update([
                ...$data,
                'status' => $data['status'] ?? $assignment->status,
                'is_critical' => $data['is_critical'] ?? false,
            ]);

            $this->syncProjectRoles($project, $data['project_manager_id'], $leaders);
            $this->syncDependency($assignment, $dependencyId);
            $this->syncLeaves($assignment, $leaveDates);

            if ($assignment->auto_create_schedule) {
                $this->scheduling->generateForAssignment($assignment->fresh());
                $assignment->update(['status' => 'scheduled']);
            }

            $assignment = $assignment->fresh(['project', 'pic', 'wbsItem', 'dependencies.dependsOn', 'schedule']);
            $this->auditLogs->record('updated', $assignment, $old, $assignment->toArray(), $request);

            return $assignment;
        });
    }

    public function recalculate(Assignment $assignment, ?Request $request = null): Assignment
    {
        $schedule = $this->scheduling->generateForAssignment($assignment->fresh());
        $assignment->update(['status' => 'scheduled']);
        $freshAssignment = $assignment->fresh(['schedule', 'dependencies.dependsOn', 'pic', 'wbsItem']);
        $this->auditLogs->record('recalculated', $schedule, [], $schedule->toArray(), $request);

        return $freshAssignment;
    }

    private function validatePayload(Project $project, array $data, ?Assignment $current = null): void
    {
        $wbsItem = ProjectWbsItem::query()
            ->where('project_id', $project->id)
            ->findOrFail($data['project_wbs_item_id']);

        if (! $wbsItem->is_assignable || $wbsItem->children()->exists()) {
            throw new DomainException('Only assignable leaf WBS items can be assigned.');
        }

        if ($current === null && $wbsItem->assignment()->exists()) {
            throw new DomainException('This WBS item already has an assignment.');
        }

        if (
            $current !== null
            && $current->project_wbs_item_id !== $wbsItem->id
            && $wbsItem->assignment()->exists()
        ) {
            throw new DomainException('This WBS item already has an assignment.');
        }

        if (! empty($data['depends_on_assignment_id'])) {
            $dependency = Assignment::query()->findOrFail($data['depends_on_assignment_id']);
            if ($dependency->project_id !== $project->id) {
                throw new DomainException('Dependencies must belong to the same project.');
            }

            if ($current && $dependency->id === $current->id) {
                throw new DomainException('An assignment cannot depend on itself.');
            }
        }

        if (($data['assigned_role'] ?? null) === 'project_manager') {
            $existing = Assignment::query()
                ->where('project_id', $project->id)
                ->where('assigned_role', 'project_manager')
                ->when($current, fn ($query) => $query->whereKeyNot($current->id))
                ->exists();

            if ($existing) {
                throw new DomainException('Project manager must be unique per project.');
            }
        }
    }

    private function syncDependency(Assignment $assignment, ?int $dependencyId): void
    {
        $assignment->dependencies()->delete();

        if ($dependencyId) {
            $assignment->dependencies()->create([
                'depends_on_assignment_id' => $dependencyId,
                'dependency_type' => 'FS',
            ]);
        }
    }

    private function syncLeaves(Assignment $assignment, array $leaveDates): void
    {
        \App\Models\AssignmentLeave::query()
            ->where('assignment_id', $assignment->id)
            ->delete();

        foreach (collect($leaveDates)->filter()->unique()->values() as $leaveDate) {
            \App\Models\AssignmentLeave::query()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $assignment->assigned_pic_id,
                'leave_date' => $leaveDate,
                'leave_hours' => null,
                'reason' => 'Assignment leave date',
            ]);
        }
    }

    private function syncProjectRoles(Project $project, int $projectManagerId, array $leaderIds): void
    {
        $leaderIds = collect($leaderIds)
            ->filter()
            ->map(fn ($leaderId) => (int) $leaderId)
            ->reject(fn ($leaderId) => $leaderId === $projectManagerId)
            ->unique()
            ->values();

        $project->updateQuietly(['project_manager_id' => $projectManagerId]);

        $project->members()
            ->whereIn('project_role', ['project_manager', 'project_leader'])
            ->whereNotIn('user_id', [$projectManagerId, ...$leaderIds->all()])
            ->delete();

        $project->members()->updateOrCreate(
            ['user_id' => $projectManagerId],
            ['project_role' => 'project_manager', 'joined_at' => now()->toDateString()]
        );

        foreach ($leaderIds as $leaderId) {
            $project->members()->updateOrCreate(
                ['user_id' => $leaderId],
                ['project_role' => 'project_leader', 'joined_at' => now()->toDateString()]
            );
        }
    }
}
