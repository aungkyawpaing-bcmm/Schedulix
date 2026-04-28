<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    public function paginateFor(User $user): LengthAwarePaginator
    {
        return Project::query()
            ->with('projectManager')
            ->when(! $user->isOwner(), function ($query) use ($user) {
                $query->where(function ($scopedQuery) use ($user) {
                    $scopedQuery
                        ->where('project_manager_id', $user->id)
                        ->orWhereHas('members', fn ($members) => $members->where('user_id', $user->id));
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function managers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereIn('system_role', ['owner', 'project_manager', 'project_leader'])
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Project
    {
        return Project::query()->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(['projectManager']);
    }
}
