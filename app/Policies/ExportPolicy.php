<?php

namespace App\Policies;

use App\Models\Export;
use App\Models\User;

class ExportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return in_array($user->system_role, ['owner', 'project_manager', 'project_leader'], true);
    }

    public function view(User $user, Export $export): bool
    {
        return $user->isOwner() || $export->user_id === $user->id;
    }
}
