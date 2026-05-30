<?php

namespace App\Http\Controllers;

use App\Models\Manhwa;

class ChapterController extends Controller
{
    public function show(Manhwa $manhwa, int $chapterNumber)
    {
        $chapter = $manhwa->chapters()
            ->where('chapter_number', $chapterNumber)
            ->with('pages')
            ->firstOrFail();

        $publishedChapters = fn ($query) => $query->whereHas('pages');

        $prevChapter = $manhwa->chapters()
            ->where('chapter_number', '<', $chapterNumber)
            ->tap($publishedChapters)
            ->orderByDesc('chapter_number')
            ->first();

        $nextChapter = $manhwa->chapters()
            ->where('chapter_number', '>', $chapterNumber)
            ->tap($publishedChapters)
            ->orderBy('chapter_number')
            ->first();

        $manhwa->recordVisit();

        $chapter->loadCount(['likes', 'comments']);

        $isLiked = auth()->check() && auth()->user()->hasLikedChapter($chapter);

        $comments = \App\Models\Comment::threadFor($chapter);

        return view('Home-Pages.chapter', compact('manhwa', 'chapter', 'prevChapter', 'nextChapter', 'isLiked', 'comments'));
    }
}
