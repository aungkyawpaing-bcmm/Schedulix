<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectWbsItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'task_master_id',
        'wbs_number',
        'level',
        'item_name',
        'item_type',
        'content_item_type',
        'platform',
        'description',
        'is_assignable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_assignable' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function taskMaster(): BelongsTo
    {
        return $this->belongsTo(TaskMaster::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class, 'project_wbs_item_id');
    }
}
