<?php

namespace App\Http\Controllers;

use App\Models\Manhwa;

class ManhwaController extends Controller
{
    public function show(Manhwa $manhwa)
    {
        $manhwa->load([
            'genres',
            'chapters' => fn ($query) => $query
                ->whereHas('pages')
                ->withCount('pages')
                ->orderByDesc('chapter_number'),
        ])->loadCount([
            'chapters as published_chapters_count' => fn ($query) => $query->whereHas('pages'),
        ]);

        $manhwa->recordVisit();

        $manhwa->loadCount(['likes', 'comments', 'ratings']);
        $manhwa->loadAvg('ratings', 'score');

        $isBookmarked = auth()->check() && auth()->user()->hasBookmarked($manhwa);
        $isLiked = auth()->check() && auth()->user()->hasLiked($manhwa);
        $userRating = auth()->check() ? auth()->user()->ratingFor($manhwa) : null;

        $comments = \App\Models\Comment::threadFor($manhwa);

        return view('Home-Pages.manhwa', compact('manhwa', 'isBookmarked', 'isLiked', 'userRating', 'comments'));
    }
}
