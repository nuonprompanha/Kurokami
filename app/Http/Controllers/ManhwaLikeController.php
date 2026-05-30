<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RecordsSubscriberRequestMeta;
use App\Models\Manhwa;
use Illuminate\Http\Request;

class ManhwaLikeController extends Controller
{
    use RecordsSubscriberRequestMeta;

    public function toggle(Request $request, Manhwa $manhwa)
    {
        $this->ensureSubscriber($request);

        $user = $request->user();
        $existing = $user->manhwaLikes()->where('manhwa_id', $manhwa->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $user->manhwaLikes()->create(array_merge(
                ['manhwa_id' => $manhwa->id],
                $this->requestMeta($request)
            ));
            $liked = true;
        }

        $likesCount = $manhwa->likes()->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
            'message' => $liked ? 'Thanks for liking this series.' : 'Like removed.',
        ]);
    }
}
