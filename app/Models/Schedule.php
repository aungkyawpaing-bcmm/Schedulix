<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $fillable = [
        'project_id',
        'assignment_id',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'planned_hours',
        'actual_total_hours',
        'digestion_hours',
        'remaining_hours',
        'progress_percent',
        'is_overdue',
        'warning_notes',
    ];

    protected function casts(): array
    {
        return [
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
            'planned_hours' => 'decimal:2',
            'actual_total_hours' => 'decimal:2',
            'digestion_hours' => 'decimal:2',
            'remaining_hours' => 'decimal:2',
            'progress_percent' => 'decimal:2',
            'is_overdue' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function dailyAllocations(): HasMany
    {
        return $this->hasMany(ScheduleDailyAllocation::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasManyThrough(
            ProgressLog::class,
            ScheduleDailyAllocation::class,
            'schedule_id',
            'schedule_daily_allocation_id'
        );
    }
}
