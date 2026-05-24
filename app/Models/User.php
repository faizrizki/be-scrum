<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function canManageProjects(): bool
    {
        return in_array($this->role, ['ADMIN', 'PROJECT_MANAGER'], true);
    }

    public function canManageTasks(): bool
    {
        return in_array($this->role, ['ADMIN', 'PROJECT_MANAGER'], true);
    }

    public function canUpdateTaskStatus(): bool
    {
        return in_array($this->role, ['ADMIN', 'PROJECT_MANAGER', 'TEAM_MEMBER'], true);
    }

    public function canComment(): bool
    {
        return in_array($this->role, ['ADMIN', 'PROJECT_MANAGER', 'TEAM_MEMBER'], true);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
