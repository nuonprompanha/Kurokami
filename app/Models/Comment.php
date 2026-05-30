<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'parent_id',
        'body',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function wasEdited(): bool
    {
        return $this->updated_at->gt($this->created_at);
    }

    public function editableBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    /**
     * @return array<string, int>
     */
    public function reactionCountsByType(): array
    {
        if (! $this->relationLoaded('reactions')) {
            return [];
        }

        $counts = [];

        foreach (CommentReaction::types() as $type => $meta) {
            $counts[$type] = 0;
        }

        foreach ($this->reactions as $reaction) {
            if (isset($counts[$reaction->type])) {
                $counts[$reaction->type]++;
            }
        }

        return $counts;
    }

    public function userReactionType(?User $user): ?string
    {
        if ($user === null || ! $this->relationLoaded('reactions')) {
            return null;
        }

        $reaction = $this->reactions->firstWhere('user_id', $user->id);

        return $reaction?->type;
    }

    public function scopeTopLevelThread(Builder $query): Builder
    {
        return $query
            ->whereNull('parent_id')
            ->with([
                'user',
                'reactions',
                'replies' => fn ($replyQuery) => $replyQuery
                    ->with(['user', 'reactions'])
                    ->oldest(),
            ])
            ->latest();
    }

    public static function threadFor(Model $commentable)
    {
        return $commentable->comments()->topLevelThread()->limit(100)->get();
    }

    public function belongsToCommentable(Model $commentable): bool
    {
        return $this->commentable_type === $commentable->getMorphClass()
            && (int) $this->commentable_id === (int) $commentable->id;
    }
}
