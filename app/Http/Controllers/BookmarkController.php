<?php

namespace App\Http\Controllers;

use App\Models\Manhwa;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->isSubscriber()) {
            return redirect()->route('home');
        }

        $manhwas = $user->bookmarkedManhwas()
            ->with([
                'genres',
                'chapters' => fn ($q) => $q
                    ->whereHas('pages')
                    ->orderByDesc('chapter_number')
                    ->limit(3),
            ])
            ->withCount([
                'chapters as published_chapters_count' => fn ($q) => $q->whereHas('pages'),
                'ratings',
            ])
            ->withAvg('ratings', 'score')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('Home-Pages.bookmarks', compact('manhwas'));
    }

    public function toggle(Request $request, Manhwa $manhwa)
    {
        $user = $request->user();

        if (! $user->isSubscriber()) {
            abort(403);
        }

        if ($user->bookmarkedManhwas()->whereKey($manhwa->id)->exists()) {
            $user->bookmarkedManhwas()->detach($manhwa->id);
            $bookmarked = false;
        } else {
            $user->bookmarkedManhwas()->attach($manhwa->id);
            $bookmarked = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'bookmarked' => $bookmarked,
                'message' => $bookmarked ? 'Added to bookmarks.' : 'Removed from bookmarks.',
            ]);
        }

        return back()->with('bookmark_status', $bookmarked ? 'added' : 'removed');
    }
}
