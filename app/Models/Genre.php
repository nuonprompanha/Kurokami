<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function manhwas(): BelongsToMany
    {
        return $this->belongsToMany(Manhwa::class, 'manhwa_genre')->withTimestamps();
    }
}
