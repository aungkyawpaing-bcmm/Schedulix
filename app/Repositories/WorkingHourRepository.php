<?php

namespace App\Repositories;

use App\Models\WorkingHour;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkingHourRepository
{
    public function paginate(): LengthAwarePaginator
    {
        return WorkingHour::query()
            ->with('project')
            ->orderBy('scope_type')
            ->orderBy('weekday')
            ->paginate(14);
    }

    public function create(array $data): WorkingHour
    {
        return WorkingHour::query()->create($data);
    }

    public function update(WorkingHour $workingHour, array $data): WorkingHour
    {
        $workingHour->update($data);

        return $workingHour->fresh('project');
    }
}
