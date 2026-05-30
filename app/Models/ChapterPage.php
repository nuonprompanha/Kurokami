<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChapterPage extends Model
{
    protected $fillable = [
        'chapter_id',
        'path',
        'sort_order',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function url(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $this->path), '/');
    }
}
