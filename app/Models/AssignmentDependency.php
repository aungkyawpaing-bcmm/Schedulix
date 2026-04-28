<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentDependency extends Model
{
    protected $fillable = [
        'assignment_id',
        'depends_on_assignment_id',
        'dependency_type',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'depends_on_assignment_id');
    }
}
