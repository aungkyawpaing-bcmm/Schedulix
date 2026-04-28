<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'project_manager_id',
        'expected_start_date',
        'expected_end_date',
        'overview',
        'objective',
        'team_size',
        'timezone',
        'status',
        'locale_default',
    ];

    protected function casts(): array
    {
        return [
            'expected_start_date' => 'date',
            'expected_end_date' => 'date',
        ];
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function wbsItems(): HasMany
    {
        return $this->hasMany(ProjectWbsItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(Export::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
