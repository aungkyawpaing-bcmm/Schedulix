<?php

namespace App\Services;

use App\Models\WorkingHour;
use App\Repositories\WorkingHourRepository;

class WorkingHourService
{
    public function __construct(
        private readonly WorkingHourRepository $workingHours,
        private readonly AuditLogService $auditLogs,
    )
    {
    }

    public function create(array $data): WorkingHour
    {
        $workingHour = $this->workingHours->create($data);
        $this->auditLogs->record('created', $workingHour, [], $workingHour->toArray());

        return $workingHour;
    }

    public function update(WorkingHour $workingHour, array $data): WorkingHour
    {
        $old = $workingHour->toArray();
        $workingHour = $this->workingHours->update($workingHour, $data);
        $this->auditLogs->record('updated', $workingHour, $old, $workingHour->toArray());

        return $workingHour;
    }
}
