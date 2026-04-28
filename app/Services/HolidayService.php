<?php

namespace App\Services;

use App\Models\Holiday;
use App\Repositories\HolidayRepository;

class HolidayService
{
    public function __construct(
        private readonly HolidayRepository $holidays,
        private readonly AuditLogService $auditLogs,
    )
    {
    }

    public function create(array $data): Holiday
    {
        $holiday = $this->holidays->create($data);
        $this->auditLogs->record('created', $holiday, [], $holiday->toArray());

        return $holiday;
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        $old = $holiday->toArray();
        $holiday = $this->holidays->update($holiday, $data);
        $this->auditLogs->record('updated', $holiday, $old, $holiday->toArray());

        return $holiday;
    }
}
