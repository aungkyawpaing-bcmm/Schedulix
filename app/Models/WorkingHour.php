<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkingHour extends Model
{
    protected $fillable = [
        'scope_type',
        'project_id',
        'weekday',
        'start_time',
        'end_time',
        'lunch_start_time',
        'lunch_end_time',
        'net_hours',
        'is_working_day',
    ];

    protected function casts(): array
    {
        return [
            'net_hours' => 'decimal:2',
            'is_working_day' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
