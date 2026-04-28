<?php

namespace App\Repositories;

use App\Models\Holiday;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HolidayRepository
{
    public function paginate(): LengthAwarePaginator
    {
        return Holiday::query()->orderBy('holiday_date')->paginate(10);
    }

    public function create(array $data): Holiday
    {
        return Holiday::query()->create($data);
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);

        return $holiday->fresh();
    }
}
