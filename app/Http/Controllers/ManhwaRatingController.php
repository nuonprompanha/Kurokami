<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RecordsSubscriberRequestMeta;
use App\Models\Manhwa;
use Illuminate\Http\Request;

class ManhwaRatingController extends Controller
{
    use RecordsSubscriberRequestMeta;

    public function store(Request $request, Manhwa $manhwa)
    {
        $this->ensureSubscriber($request);

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $user = $request->user();

        $user->manhwaRatings()->updateOrCreate(
            ['manhwa_id' => $manhwa->id],
            array_merge(
                ['score' => $validated['score']],
                $this->requestMeta($request)
            )
        );

        $manhwa->loadAvg('ratings', 'score');
        $manhwa->loadCount('ratings');

        return response()->json([
            'score' => (int) $validated['score'],
            'average' => round((float) ($manhwa->ratings_avg_score ?? 0), 1),
            'ratings_count' => (int) $manhwa->ratings_count,
            'message' => 'Thanks for rating this series.',
        ]);

        return back()->with('rating_status', 'saved');
    }
}
