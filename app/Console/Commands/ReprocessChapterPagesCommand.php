<?php

namespace App\Console\Commands;

use App\Models\ChapterPage;
use App\Services\ChapterPageImageProcessor;
use App\Services\ImageStorageService;
use App\Services\PostimagesService;
use App\Support\PostimagesConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ReprocessChapterPagesCommand extends Command
{
    protected $signature = 'manhwa:reprocess-chapter-pages
                            {--manhwa= : Manhwa slug to limit reprocessing}
                            {--chapter= : Chapter number to limit reprocessing}
                            {--dry-run : Show what would change without uploading}';

    protected $description = 'Upscale existing chapter page images to the reader width and refresh stored URLs';

    public function handle(
        ChapterPageImageProcessor $processor,
        ImageStorageService $imageStorage,
        PostimagesService $postimages,
    ): int {
        if (! extension_loaded('gd')) {
            $this->error('The PHP GD extension is required. Enable extension=gd in php.ini, then retry.');

            return self::FAILURE;
        }

        if ($imageStorage->usesPostimages() && ! PostimagesConfig::isConfigured()) {
            $this->error('Postimages is enabled but POSTIMAGES_API_KEY is missing.');

            return self::FAILURE;
        }

        $query = ChapterPage::query()
            ->with(['chapter.manhwa'])
            ->when($this->option('manhwa'), function ($builder, $slug) {
                $builder->whereHas('chapter.manhwa', fn ($manhwaQuery) => $manhwaQuery->where('slug', $slug));
            })
            ->when($this->option('chapter'), function ($builder, $chapterNumber) {
                $builder->whereHas('chapter', fn ($chapterQuery) => $chapterQuery->where('chapter_number', (int) $chapterNumber));
            })
            ->orderBy('id');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->warn('No chapter pages matched the selected filters.');

            return self::SUCCESS;
        }

        $this->info("Reprocessing {$total} chapter page(s) to {$processor->targetWidth()}px width...");

        if ($this->option('dry-run')) {
            $this->warn('Dry run only. No files will be uploaded or updated.');
        }

        $updated = 0;
        $skipped = 0;
        $failed = 0;

        $query->chunkById(20, function ($pages) use (
            $processor,
            $imageStorage,
            $postimages,
            &$updated,
            &$skipped,
            &$failed,
        ) {
            foreach ($pages as $page) {
                $label = sprintf(
                    '%s — Ch. %d — page %d',
                    $page->chapter->manhwa->title,
                    $page->chapter->chapter_number,
                    $page->sort_order
                );

                try {
                    $contents = $this->downloadPageContents($page->path);
                    $processed = $processor->process($contents);

                    if ($processed === $contents) {
                        $this->line("[skip] {$label} (already at target width or unchanged)");
                        $skipped++;

                        continue;
                    }

                    if ($this->option('dry-run')) {
                        $this->line("[dry-run] {$label}");
                        $updated++;

                        continue;
                    }

                    if ($imageStorage->usesPostimages()) {
                        $extension = strtolower(pathinfo($page->path, PATHINFO_EXTENSION) ?: 'jpg');
                        $filename = basename(parse_url($page->path, PHP_URL_PATH) ?: 'page.jpg');
                        if (! str_contains($filename, '.')) {
                            $filename .= '.'.$extension;
                        }

                        $newUrl = $postimages->uploadContents($processed, $filename);
                        $page->update(['path' => $newUrl]);
                    } elseif ($imageStorage->isRemote($page->path)) {
                        throw new RuntimeException('Remote chapter page path found while Postimages is disabled.');
                    } else {
                        Storage::disk('public')->put($page->path, $processed);
                    }

                    $this->line("[updated] {$label}");
                    $updated++;
                } catch (Throwable $exception) {
                    $this->error("[failed] {$label}: {$exception->getMessage()}");
                    $failed++;
                }
            }
        });

        $this->newLine();
        $this->info("Done. Updated: {$updated}, skipped: {$skipped}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function downloadPageContents(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $response = Http::timeout(120)->get($path);

            if (! $response->successful()) {
                throw new RuntimeException('Unable to download chapter page (HTTP '.$response->status().').');
            }

            $contents = $response->body();

            if ($contents === '') {
                throw new RuntimeException('Downloaded chapter page was empty.');
            }

            return $contents;
        }

        if (! Storage::disk('public')->exists($path)) {
            throw new RuntimeException('Chapter page file not found in storage.');
        }

        return Storage::disk('public')->get($path);
    }
}
