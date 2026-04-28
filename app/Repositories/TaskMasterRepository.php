<?php

namespace App\Repositories;

use App\Models\TaskMaster;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskMasterRepository
{
    public function paginate(): LengthAwarePaginator
    {
        return TaskMaster::query()->latest()->paginate(10);
    }

    public function create(array $data): TaskMaster
    {
        return TaskMaster::query()->create($data);
    }

    public function update(TaskMaster $taskMaster, array $data): TaskMaster
    {
        $taskMaster->update($data);

        return $taskMaster->fresh();
    }
}
