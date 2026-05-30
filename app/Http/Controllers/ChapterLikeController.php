<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RecordsSubscriberRequestMeta;
use App\Models\Manhwa;
use Illuminate\Http\Request;

class ChapterLikeController extends Controller
{
    use RecordsSubscriberRequestMeta;

    public function toggle(Request $request, Manhwa $manhwa, int $chapterNumber)
    {
        $this->ensureSubscriber($request);

        $chapter = $manhwa->chapters()
            ->where('chapter_number', $chapterNumber)
            ->firstOrFail();

        $user = $request->user();
        $existing = $user->chapterLikes()->where('chapter_id', $chapter->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $user->chapterLikes()->create(array_merge(
                [
                    'chapter_id' => $chapter->id,
                    'manhwa_id' => $manhwa->id,
                ],
                $this->requestMeta($request)
            ));
            $liked = true;
        }

        $likesCount = $chapter->likes()->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
            'message' => $liked ? 'Thanks for liking this chapter.' : 'Like removed.',
        ]);
    }
}
