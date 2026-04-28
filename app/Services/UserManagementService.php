<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    private const SYSTEM_OWNER_EMAIL = 'owner@wbs-generator.test';

    public function __construct(
        private readonly UserRepository $users,
        private readonly AuditLogService $auditLogs,
    )
    {
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        $user = $this->users->create($data);
        $this->auditLogs->record('created', $user, [], $user->toArray());

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $old = $user->toArray();

        if ($user->email === self::SYSTEM_OWNER_EMAIL) {
            $data['system_role'] = 'owner';
            $data['is_active'] = true;
        }

        if (blank($data['password'] ?? null)) {
            $data = Arr::except($data, ['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->users->update($user, $data);
        $this->auditLogs->record('updated', $user, $old, $user->toArray());

        return $user;
    }
}
