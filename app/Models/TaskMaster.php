<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskMaster extends Model
{
    use SoftDeletes;

    protected $table = 'task_master';

    protected $fillable = [
        'task_code',
        'name',
        'content_item_type',
        'platform',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function wbsItems(): HasMany
    {
        return $this->hasMany(ProjectWbsItem::class);
    }
}
