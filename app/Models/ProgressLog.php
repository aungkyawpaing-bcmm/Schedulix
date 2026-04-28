<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressLog extends Model
{
    protected $fillable = [
        'assignment_id',
        'schedule_daily_allocation_id',
        'user_id',
        'work_date',
        'actual_hours',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'actual_hours' => 'decimal:2',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(ScheduleDailyAllocation::class, 'schedule_daily_allocation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
