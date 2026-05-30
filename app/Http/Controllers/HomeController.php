<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Manhwa;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'new');

        if (! in_array($tab, ['new', 'popular', 'last-update'], true)) {
            $tab = 'new';
        }

        $activeGenre = null;
        $genreSlug = $request->query('genre');

        if (filled($genreSlug)) {
            $activeGenre = Genre::query()->where('slug', $genreSlug)->first();
        }

        $query = Manhwa::query()
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
            ->withAvg('ratings', 'score');

        if ($activeGenre) {
            $query->whereHas('genres', fn ($q) => $q->whereKey($activeGenre->id));
        }

        match ($tab) {
            'popular' => $query->orderByDesc('views'),
            'last-update' => $query->orderByDesc(
                Chapter::query()
                    ->select('updated_at')
                    ->whereColumn('manhwa_id', 'manhwas.id')
                    ->latest('updated_at')
                    ->limit(1)
            ),
            default => $query->latest('created_at'),
        };

        $manhwas = $query->take(12)->get();

        return view('Home-Pages.index', compact('manhwas', 'tab', 'activeGenre'));
    }
}
