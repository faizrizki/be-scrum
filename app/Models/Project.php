<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'progress',
        'owner_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getMembersAttribute(): int
    {
        return $this->tasks()
            ->whereNotNull('assignee_id')
            ->distinct('assignee_id')
            ->count('assignee_id') ?: 1;
    }

    /**
     * Progress proyek = (sum story_points tasks DONE) / (sum story_points all tasks) * 100
     * Override column-based 'progress' supaya auto-compute dari story points.
     */
    public function getProgressAttribute(): int
    {
        $total = (int) $this->tasks()->sum('story_points');
        if ($total === 0) {
            return 0;
        }
        $done = (int) $this->tasks()->where('status', 'DONE')->sum('story_points');
        return (int) round(($done / $total) * 100);
    }

    protected $appends = ['members', 'progress'];
}
