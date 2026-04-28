<?php

namespace App\Policies;

use App\Models\TaskMaster;
use App\Models\User;

class TaskMasterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return in_array($user->system_role, ['owner', 'project_manager', 'project_leader'], true);
    }

    public function update(User $user, TaskMaster $taskMaster): bool
    {
        return $this->create($user) && ! $taskMaster->wbsItems()->exists();
    }
}
