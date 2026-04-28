<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->canManageProject($project)
            || $project->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->system_role, ['owner', 'project_manager'], true);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->canManageProject($project);
    }
}
