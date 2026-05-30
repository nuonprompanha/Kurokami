<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManhwaRating extends Model
{
    protected $fillable = [
        'user_id',
        'manhwa_id',
        'score',
        'ip_address',
        'user_agent',
        'page_url',
        'referer',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manhwa(): BelongsTo
    {
        return $this->belongsTo(Manhwa::class);
    }
}
