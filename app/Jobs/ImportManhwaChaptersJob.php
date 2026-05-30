<?php

namespace App\Jobs;

use App\Models\Manhwa;
use App\Services\ManhwaChapterZipImporter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportManhwaChaptersJob
{
    use Dispatchable;

    public function __construct(
        public int $manhwaId,
        public string $zipPath,
    ) {}

    public function handle(ManhwaChapterZipImporter $importer): void
    {
        @set_time_limit(0);

        $manhwa = Manhwa::query()->find($this->manhwaId);

        if (! $manhwa) {
            $this->deleteZip();

            return;
        }

        try {
            $summary = $importer->importFromStoredPath($manhwa, $this->zipPath);

            Log::info('Manhwa chapter import completed.', [
                'manhwa_id' => $manhwa->id,
                'chapters' => $summary['chapters'],
                'pages' => $summary['pages'],
            ]);
        } catch (Throwable $exception) {
            Log::error('Manhwa chapter import failed.', [
                'manhwa_id' => $manhwa->id,
                'zip_path' => $this->zipPath,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $this->deleteZip();
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Manhwa chapter import job failed permanently.', [
            'manhwa_id' => $this->manhwaId,
            'zip_path' => $this->zipPath,
            'message' => $exception->getMessage(),
        ]);

        $this->deleteZip();
    }

    private function deleteZip(): void
    {
        if (Storage::disk('local')->exists($this->zipPath)) {
            Storage::disk('local')->delete($this->zipPath);
        }
    }
}
