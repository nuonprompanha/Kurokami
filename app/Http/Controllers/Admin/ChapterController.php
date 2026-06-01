<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manhwa;
use App\Services\ImageStorageService;

class ChapterController extends Controller
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function destroy(Manhwa $manhwa, int $chapterNumber)
    {
        $chapter = $manhwa->chapters()
            ->where('chapter_number', $chapterNumber)
            ->firstOrFail();

        $title = $chapter->displayTitle();
        $this->deleteChapter($chapter);

        return redirect()
            ->route('admin.manhwas.edit', $manhwa)
            ->with('success', $title.' deleted successfully.');
    }

    private function deleteChapter(Chapter $chapter): void
    {
        $chapter->loadMissing('pages');

        foreach ($chapter->pages as $page) {
            $this->imageStorage->delete($page->path);
        }

        $chapter->comments()->whereNull('parent_id')->each(fn ($comment) => $comment->delete());

        $chapter->delete();
    }
}
