<?php

namespace App\Console\Commands;

use App\Services\PostimagesService;
use App\Support\PostimagesConfig;
use Illuminate\Console\Command;
use Throwable;

class DiagnosePostimagesCommand extends Command
{
    protected $signature = 'postimages:diagnose {--upload : Upload a small test image to Postimages}';

    protected $description = 'Check Postimages configuration and optionally test an upload';

    public function handle(PostimagesService $postimages): int
    {
        $diagnostics = PostimagesConfig::diagnostics();

        foreach ($diagnostics as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'yes' : 'no';
            }

            $this->line(sprintf('%-18s %s', $key.':', $value ?? '(empty)'));
        }

        if (! PostimagesConfig::enabled()) {
            $this->error('Postimages is disabled. Set POSTIMAGES_ENABLED=true in Laravel Cloud environment variables.');

            return self::FAILURE;
        }

        if (! PostimagesConfig::isConfigured()) {
            $this->error('POSTIMAGES_API_KEY is missing. Add it in Laravel Cloud environment variables, then redeploy.');

            return self::FAILURE;
        }

        if (! $this->option('upload')) {
            $this->info('Configuration looks ready. Run with --upload to test the Postimages API.');

            return self::SUCCESS;
        }

        $testImage = public_path('vendor/image/Korukami.png');

        if (! is_file($testImage)) {
            $this->error('Test image not found at public/vendor/image/Korukami.png');

            return self::FAILURE;
        }

        try {
            $url = $postimages->uploadContents(
                file_get_contents($testImage),
                'postimages-diagnostic.png',
                'image/png'
            );

            $this->info('Upload succeeded.');
            $this->line('URL: '.$url);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Upload failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
