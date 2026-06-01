<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ManhwaStorageConfig
{
    public const DRIVERS = ['local', 's3', 'postimages'];

    public static function driver(): string
    {
        $configured = strtolower((string) config('manhwa.storage', 'local'));

        if ($configured === 'postimages') {
            return 'postimages';
        }

        if ($configured === 's3') {
            return 's3';
        }

        if (PostimagesConfig::enabled() && PostimagesConfig::isConfigured()) {
            return 'postimages';
        }

        return 'local';
    }

    public static function usesPostimages(): bool
    {
        return self::driver() === 'postimages';
    }

    public static function usesS3(): bool
    {
        return self::driver() === 's3';
    }

    public static function usesLocal(): bool
    {
        return self::driver() === 'local';
    }

    public static function disk(): string
    {
        return self::usesS3() ? 's3' : 'public';
    }

    public static function isConfigured(): bool
    {
        if (self::usesPostimages()) {
            return PostimagesConfig::isConfigured();
        }

        if (self::usesS3()) {
            return filled(config('filesystems.disks.s3.bucket'))
                && filled(config('filesystems.disks.s3.key'))
                && filled(config('filesystems.disks.s3.secret'))
                && filled(config('filesystems.disks.s3.region'));
        }

        return true;
    }

    public static function ensureReady(): void
    {
        if (self::usesPostimages() && ! PostimagesConfig::isConfigured()) {
            throw new RuntimeException(
                'Postimages is enabled but POSTIMAGES_API_KEY is missing. Add it to your Laravel Cloud environment variables, then redeploy.'
            );
        }

        if (self::usesS3() && ! self::isConfigured()) {
            throw new RuntimeException(
                'Manhwa storage is set to S3 but AWS credentials or bucket are missing. Set AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, and AWS_BUCKET in your environment.'
            );
        }
    }

    public static function publicUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (self::usesS3()) {
            return Storage::disk('s3')->url($normalized);
        }

        return '/storage/'.$normalized;
    }

    /**
     * @return array<string, mixed>
     */
    public static function diagnostics(): array
    {
        return [
            'driver' => self::driver(),
            'configured' => self::isConfigured(),
            'disk' => self::usesPostimages() ? null : self::disk(),
            's3_bucket' => config('filesystems.disks.s3.bucket'),
            'postimages' => PostimagesConfig::diagnostics(),
        ];
    }
}
