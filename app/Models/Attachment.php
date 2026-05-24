<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'comment_id',
        'name',
        'size',
        'path',
    ];

    protected $appends = ['url'];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
