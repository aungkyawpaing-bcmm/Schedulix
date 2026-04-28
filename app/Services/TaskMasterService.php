<?php

namespace App\Services;

use App\Models\TaskMaster;
use App\Repositories\TaskMasterRepository;
use DomainException;

class TaskMasterService
{
    public function __construct(
        private readonly TaskMasterRepository $tasks,
        private readonly AuditLogService $auditLogs,
    )
    {
    }

    public function create(array $data): TaskMaster
    {
        $task = $this->tasks->create($data);
        $this->auditLogs->record('created', $task, [], $task->toArray());

        return $task;
    }

    public function update(TaskMaster $taskMaster, array $data): TaskMaster
    {
        if ($taskMaster->wbsItems()->exists()) {
            throw new DomainException('This task is already referenced by a project WBS item and is read-only.');
        }

        $old = $taskMaster->toArray();
        $taskMaster = $this->tasks->update($taskMaster, $data);
        $this->auditLogs->record('updated', $taskMaster, $old, $taskMaster->toArray());

        return $taskMaster;
    }
}
