<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->canManageProject($assignment->project)
            || $assignment->assigned_pic_id === $user->id;
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->view($user, $assignment);
    }
}
