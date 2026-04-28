<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleDailyAllocation extends Model
{
    protected $fillable = [
        'schedule_id',
        'assignment_id',
        'project_id',
        'work_date',
        'planned_hours',
        'actual_hours',
        'variance_hours',
        'is_holiday',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'planned_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'variance_hours' => 'decimal:2',
            'is_holiday' => 'boolean',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(ProgressLog::class, 'schedule_daily_allocation_id');
    }
}
