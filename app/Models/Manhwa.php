<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Manhwa extends Model
{
    public const BADGES = ['New', 'Hot'];

    public const STATUSES = ['Ongoing', 'Completed', 'Hiatus', 'Cancelled', 'Upcoming'];

    public const SERIES_TYPES = ['Manhwa', 'Manhua', 'Webtoon', 'Comic'];

    public const CATEGORIES = [
        'Action',
        'Adventure',
        'Comedy',
        'Drama',
        'Fantasy',
        'Horror',
        'Romance',
        'Sci-Fi',
        'Slice of Life',
    ];

    protected $fillable = [
        'title',
        'slug',
        'cover_image',
        'category',
        'badge',
        'views',
        'description',
        'alternative_titles',
        'author',
        'artist',
        'status',
        'series_type',
        'tags',
        'is_adult_content',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_adult_content' => 'boolean',
        ];
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'manhwa_genre')->orderBy('genres.name')->withTimestamps();
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderByDesc('chapter_number');
    }

    public function bookmarkedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manhwa_bookmarks')->withTimestamps();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ManhwaLike::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ManhwaRating::class);
    }

    public function formattedAverageRating(): string
    {
        $average = $this->ratings_avg_score;

        if ($average === null || $this->ratings_count === 0) {
            return '—';
        }

        return number_format((float) $average, 1);
    }

    public function recordVisit(): void
    {
        $sessionKey = 'manhwa_visited_'.$this->id;

        if (session()->has($sessionKey)) {
            return;
        }

        $this->increment('views');
        session()->put($sessionKey, true);
    }

    public function adultLinkAttributes(): string
    {
        if (! $this->is_adult_content) {
            return '';
        }

        return 'data-adult-content="1" data-adult-slug="'.e($this->slug).'"';
    }

    public function coverUrl(): string
    {
        if (empty($this->cover_image)) {
            return asset('vendor/image/Korukami.png');
        }

        if (Str::startsWith($this->cover_image, ['http://', 'https://'])) {
            return $this->cover_image;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $this->cover_image), '/');
    }

    public function storageDirectory(): string
    {
        return 'manhwa/'.$this->slug;
    }

    /**
     * @param  list<string>|string|null  $value
     * @return list<string>
     */
    public static function parseListInput(array|string|null $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (blank($value)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n|,/', (string) $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
