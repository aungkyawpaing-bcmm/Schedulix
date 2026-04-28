<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'position',
    'system_role',
    'locale',
    'timezone',
    'is_active',
    'is_available',
    'available_from',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'available_from' => 'date',
        ];
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'assigned_pic_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(Export::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(ProgressLog::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isOwner(): bool
    {
        return $this->system_role === 'owner';
    }

    public function canManageProject(Project $project): bool
    {
        if ($this->isOwner() || $project->project_manager_id === $this->id) {
            return true;
        }

        return $this->projectMemberships()
            ->where('project_id', $project->id)
            ->where('project_role', 'project_manager')
            ->exists();
    }

    protected function displayRole(): Attribute
    {
        return Attribute::get(fn () => str($this->system_role)->replace('_', ' ')->title()->toString());
    }
}
