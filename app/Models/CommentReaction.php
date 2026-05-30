<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReaction extends Model
{
    public const TYPE_LIKE = 'like';

    public const TYPE_LOVE = 'love';

    public const TYPE_HAHA = 'haha';

    public const TYPE_WOW = 'wow';

    protected $fillable = [
        'user_id',
        'comment_id',
        'type',
    ];

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public static function types(): array
    {
        return [
            self::TYPE_LIKE => ['label' => 'Like', 'icon' => 'fa-thumbs-up'],
            self::TYPE_LOVE => ['label' => 'Love', 'icon' => 'fa-heart'],
            self::TYPE_HAHA => ['label' => 'Haha', 'icon' => 'fa-face-laugh'],
            self::TYPE_WOW => ['label' => 'Wow', 'icon' => 'fa-face-surprise'],
        ];
    }

    public static function isValidType(string $type): bool
    {
        return array_key_exists($type, self::types());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
