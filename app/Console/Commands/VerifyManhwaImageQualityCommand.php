<?php

namespace App\Console\Commands;

use App\Models\ChapterPage;
use App\Models\Manhwa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class VerifyManhwaImageQualityCommand extends Command
{
    protected $signature = 'manhwa:verify-image-quality
                            {slug : Manhwa slug}
                            {--sample=10 : Number of pages to sample}';

    protected $description = 'Check S3/local chapter page dimensions and detect upscaled (blurry) uploads';

    public function handle(): int
    {
        $manhwa = Manhwa::query()->where('slug', $this->argument('slug'))->first();

        if (! $manhwa) {
            $this->error('Manhwa not found.');

            return self::FAILURE;
        }

        $disk = config('manhwa.storage') === 's3' ? 's3' : 'public';
        $sample = max(1, (int) $this->option('sample'));

        $this->info("Manhwa: {$manhwa->title}");
        $this->line('Storage: '.config('manhwa.storage'));
        $this->line('Upscale on upload: '.(config('manhwa.chapter_page_upscale') ? 'ON (reduces quality)' : 'OFF (originals)'));
        $this->line('Pixel-perfect reader: '.(config('manhwa.chapter_reader_pixel_perfect') ? 'ON' : 'OFF'));
        $this->newLine();

        $pages = ChapterPage::query()
            ->whereHas('chapter', fn ($q) => $q->where('manhwa_id', $manhwa->id))
            ->with('chapter')
            ->orderBy('chapter_id')
            ->orderBy('sort_order')
            ->limit($sample)
            ->get();

        if ($pages->isEmpty()) {
            $this->warn('No chapter pages found.');

            return self::SUCCESS;
        }

        $rows = [];
        $upscaled = 0;

        foreach ($pages as $page) {
            if (str_starts_with($page->path, 'http')) {
                $rows[] = ["Ch {$page->chapter->chapter_number}", "p{$page->sort_order}", 'remote', '-', '-'];
                continue;
            }

            if (! Storage::disk($disk)->exists($page->path)) {
                $rows[] = ["Ch {$page->chapter->chapter_number}", "p{$page->sort_order}", 'missing', '-', '-'];
                continue;
            }

            $bytes = Storage::disk($disk)->get($page->path);
            $tmp = tempnam(sys_get_temp_dir(), 'mq');
            file_put_contents($tmp, $bytes);
            [$w, $h] = getimagesize($tmp) ?: [0, 0];
            unlink($tmp);

            $flag = $w === 900 ? 'UPSCALED' : 'OK';
            if ($w === 900) {
                $upscaled++;
            }

            $rows[] = [
                "Ch {$page->chapter->chapter_number}",
                "p{$page->sort_order}",
                "{$w}x{$h}",
                number_format(strlen($bytes)),
                $flag,
            ];
        }

        $this->table(['Chapter', 'Page', 'Dimensions', 'Bytes', 'Status'], $rows);

        if ($upscaled > 0) {
            $this->newLine();
            $this->warn("{$upscaled} sampled page(s) are 900px wide (old upscaled upload). Re-upload the chapters ZIP.");

            return self::FAILURE;
        }

        $first = $rows[0][2] ?? '';
        if (preg_match('/^(\d+)x/', $first, $m) && (int) $m[1] < 1000) {
            $this->newLine();
            $this->comment('Source width is ~'.(int) $m[1].'px. S3 stores originals correctly.');
            $this->comment('For full-width sharp reading on HD/Retina screens, use source scans 1200px+ wide.');
        }

        $this->info('No upscaled pages detected in sample.');

        return self::SUCCESS;
    }
}
