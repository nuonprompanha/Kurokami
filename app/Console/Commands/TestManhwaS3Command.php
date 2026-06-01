<?php

namespace App\Console\Commands;

use App\Support\ManhwaStorageConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TestManhwaS3Command extends Command
{
    protected $signature = 'manhwa:test-s3';

    protected $description = 'Verify Amazon S3 credentials and bucket access for manhwa uploads';

    public function handle(): int
    {
        $this->info('Manhwa storage diagnostics');
        $this->table(
            ['Setting', 'Value'],
            [
                ['MANHWA_STORAGE driver', ManhwaStorageConfig::driver()],
                ['S3 credentials set', ManhwaStorageConfig::isConfigured() ? 'yes' : 'no'],
                ['Bucket', config('filesystems.disks.s3.bucket') ?: '(empty)'],
                ['Region', config('filesystems.disks.s3.region') ?: '(empty)'],
            ]
        );

        if (! ManhwaStorageConfig::isConfigured()) {
            $this->error('AWS credentials or bucket are missing in .env');

            return self::FAILURE;
        }

        $testPath = 'manhwa/_s3-test/'.now()->format('YmdHis').'.txt';
        $contents = 'Kurokami S3 test at '.now()->toIso8601String();
        $passed = true;

        $this->newLine();
        $this->info("Testing S3 on: {$testPath}");

        try {
            Storage::disk('s3')->put($testPath, $contents);
            $this->line('[ok] PutObject');
        } catch (Throwable $exception) {
            $this->error('[fail] PutObject: '.$exception->getMessage());
            $passed = false;
        }

        if ($passed) {
            try {
                $read = Storage::disk('s3')->get($testPath);

                if ($read !== $contents) {
                    $this->error('[fail] GetObject: uploaded file could not be read back (check s3:GetObject permission)');
                    $passed = false;
                } else {
                    $this->line('[ok] GetObject');
                }
            } catch (Throwable $exception) {
                $this->error('[fail] GetObject: '.$exception->getMessage());
                $passed = false;
            }
        }

        if ($passed) {
            try {
                Storage::disk('s3')->exists($testPath);
                $this->line('[ok] ListBucket / HeadObject');
            } catch (Throwable $exception) {
                $this->warn('[warn] ListBucket / HeadObject: '.$exception->getMessage());
                $this->warn('       Add s3:ListBucket on the bucket ARN. Uploads may still work.');
            }
        }

        if ($passed) {
            $url = Storage::disk('s3')->url($testPath);
            $this->line("[info] Public URL: {$url}");

            try {
                $response = Http::timeout(15)->get($url);

                if ($response->successful() && $response->body() === $contents) {
                    $this->line('[ok] Public HTTP access (images will load in the browser)');
                } else {
                    $this->warn('[warn] Public HTTP access failed (HTTP '.$response->status().')');
                    $this->warn('       Make the bucket or objects publicly readable, or set AWS_URL to CloudFront.');
                }
            } catch (Throwable $exception) {
                $this->warn('[warn] Public HTTP check: '.$exception->getMessage());
            }
        }

        try {
            Storage::disk('s3')->delete($testPath);
            $this->line('[ok] DeleteObject');
        } catch (Throwable $exception) {
            $this->error('[fail] DeleteObject: '.$exception->getMessage());
            $passed = false;
        }

        $this->newLine();

        if (! $passed) {
            $this->error('S3 test failed. Fix IAM permissions for your IAM user on this bucket:');
            $this->line('  - s3:PutObject');
            $this->line('  - s3:GetObject');
            $this->line('  - s3:DeleteObject');
            $this->line('  - s3:ListBucket (on the bucket ARN, not just objects)');

            return self::FAILURE;
        }

        if (ManhwaStorageConfig::driver() !== 's3') {
            $this->warn('S3 works, but manhwa uploads are not using S3 yet.');
            $this->warn('Set MANHWA_STORAGE=s3 in .env and run: php artisan config:clear');
        } else {
            $this->info('S3 test passed. Manhwa uploads will use S3.');
        }

        return self::SUCCESS;
    }
}
