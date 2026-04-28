<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly AuditLogService $auditLogs,
    )
    {
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = $this->projects->create($data);

            $project->members()->updateOrCreate(
                ['user_id' => $project->project_manager_id],
                ['project_role' => 'project_manager', 'joined_at' => now()->toDateString()]
            );

            $this->auditLogs->record('created', $project, [], $project->toArray());

            return $project->fresh(['projectManager', 'members.user']);
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $old = $project->toArray();
            $project = $this->projects->update($project, $data);

            $project->members()->updateOrCreate(
                ['user_id' => $project->project_manager_id],
                ['project_role' => 'project_manager', 'joined_at' => now()->toDateString()]
            );

            $this->auditLogs->record('updated', $project, $old, $project->toArray());

            return $project->fresh(['projectManager', 'members.user']);
        });
    }
}
