<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Chapter extends Model
{
    protected $fillable = [
        'manhwa_id',
        'chapter_number',
        'title',
    ];

    public function manhwa(): BelongsTo
    {
        return $this->belongsTo(Manhwa::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ChapterPage::class)->orderBy('sort_order');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ChapterLike::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function displayTitle(): string
    {
        $title = trim((string) $this->title);

        if ($title !== '' && ! $this->titleIsRedundant($title)) {
            return $title;
        }

        return 'Chapter '.$this->chapter_number;
    }

    public function hasDistinctTitle(): bool
    {
        $title = trim((string) $this->title);

        return $title !== '' && ! $this->titleIsRedundant($title);
    }

    public static function normalizeStoredTitle(?string $title, int $chapterNumber): string
    {
        $title = trim((string) $title);

        if ($title === '') {
            return '';
        }

        $chapter = new static([
            'chapter_number' => $chapterNumber,
            'title' => $title,
        ]);

        if ($chapter->titleIsRedundant($title)) {
            return '';
        }

        $stripped = preg_replace(
            '/^(?:chapter|ch\.?)\s*'.$chapterNumber.'[\s:\-_]+/i',
            '',
            $title
        );

        $stripped = trim((string) $stripped);

        if ($stripped === '' || $chapter->titleIsRedundant($stripped)) {
            return '';
        }

        return $stripped;
    }

    private function titleIsRedundant(?string $title = null): bool
    {
        $title = trim((string) ($title ?? $this->title));

        if ($title === '') {
            return true;
        }

        $number = $this->chapter_number;

        if ($title === (string) $number) {
            return true;
        }

        return (bool) preg_match('/^(?:chapter|ch\.?)\s*'.$number.'$/i', $title);
    }
}
