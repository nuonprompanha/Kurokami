<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'google_id', 'password', 'role', 'avatar', 'bio', 'two_factor_secret', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMINISTRATOR = 'administrator';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_SUBSCRIBER = 'subscriber';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public static function roles(): array
    {
        return [
            self::ROLE_ADMINISTRATOR => 'Administrator',
            self::ROLE_EDITOR => 'Editor',
            self::ROLE_SUBSCRIBER => 'Subscriber',
        ];
    }

    public function roleLabel(): string
    {
        return self::roles()[$this->role] ?? 'Subscriber';
    }

    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isSubscriber(): bool
    {
        return $this->role === self::ROLE_SUBSCRIBER;
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->isAdministrator() || $this->isEditor();
    }

    public function commentAuthorName(): string
    {
        if ($this->canAccessAdminPanel()) {
            return $this->roleLabel();
        }

        return $this->name;
    }

    public function showsDepartmentOnComments(): bool
    {
        return $this->canAccessAdminPanel();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null && $this->two_factor_secret !== null;
    }

    public function hasPendingTwoFactorSetup(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at === null;
    }

    public function requiresTwoFactorOnLogin(): bool
    {
        return $this->canAccessAdminPanel() && $this->hasTwoFactorEnabled();
    }

    public function canManageManhwa(): bool
    {
        return $this->canAccessAdminPanel();
    }

    public function adminHomeRoute(): string
    {
        return $this->isAdministrator()
            ? route('admin.dashboard')
            : route('admin.manhwas.index');
    }

    public function bookmarkedManhwas(): BelongsToMany
    {
        return $this->belongsToMany(Manhwa::class, 'manhwa_bookmarks')->withTimestamps();
    }

    public function hasBookmarked(Manhwa $manhwa): bool
    {
        if ($this->relationLoaded('bookmarkedManhwas')) {
            return $this->bookmarkedManhwas->contains('id', $manhwa->id);
        }

        return $this->bookmarkedManhwas()->whereKey($manhwa->id)->exists();
    }

    public function manhwaLikes(): HasMany
    {
        return $this->hasMany(ManhwaLike::class);
    }

    public function manhwaRatings(): HasMany
    {
        return $this->hasMany(ManhwaRating::class);
    }

    public function ratingFor(Manhwa $manhwa): ?int
    {
        $score = $this->manhwaRatings()->where('manhwa_id', $manhwa->id)->value('score');

        return $score !== null ? (int) $score : null;
    }

    public function chapterLikes(): HasMany
    {
        return $this->hasMany(ChapterLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function hasLiked(Manhwa $manhwa): bool
    {
        return $this->manhwaLikes()->where('manhwa_id', $manhwa->id)->exists();
    }

    public function hasLikedChapter(Chapter $chapter): bool
    {
        return $this->chapterLikes()->where('chapter_id', $chapter->id)->exists();
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }

            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=14213d&color=fca311&size=128';
    }
}
