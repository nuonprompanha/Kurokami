<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterLike extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'manhwa_id',
        'ip_address',
        'user_agent',
        'page_url',
        'referer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function manhwa(): BelongsTo
    {
        return $this->belongsTo(Manhwa::class);
    }
}
