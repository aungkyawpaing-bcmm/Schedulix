<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'project_wbs_item_id',
        'assigned_pic_id',
        'project_manager_id',
        'assigned_role',
        'priority',
        'status',
        'is_critical',
        'planned_hours',
        'plan_rest_hours',
        'auto_create_schedule',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'planned_hours' => 'decimal:2',
            'plan_rest_hours' => 'decimal:2',
            'auto_create_schedule' => 'boolean',
            'is_critical' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function wbsItem(): BelongsTo
    {
        return $this->belongsTo(ProjectWbsItem::class, 'project_wbs_item_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_pic_id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(AssignmentDependency::class);
    }

    public function dependencyLinks(): HasMany
    {
        return $this->hasMany(AssignmentDependency::class, 'depends_on_assignment_id');
    }

    public function dailyAllocations(): HasMany
    {
        return $this->hasMany(ScheduleDailyAllocation::class);
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(ProgressLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
