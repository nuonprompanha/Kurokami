<?php

namespace App\Services;

use App\Support\PostimagesConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageStorageService
{
    public function __construct(
        private readonly PostimagesService $postimages,
        private readonly ChapterPageImageProcessor $chapterPageImages,
    ) {}

    public function usesPostimages(): bool
    {
        return PostimagesConfig::enabled() && $this->postimages->isConfigured();
    }

    public function storeUploadedFile(UploadedFile $file, string $relativePath): string
    {
        $this->ensurePostimagesIsReady();

        if ($this->usesPostimages()) {
            return $this->postimages->uploadFile($file, basename($relativePath));
        }

        $directory = dirname($relativePath);
        $filename = basename($relativePath);

        Storage::disk('public')->putFileAs(
            $directory === '.' ? '' : $directory,
            $file,
            $filename
        );

        return $relativePath;
    }

    public function storeContents(string $contents, string $relativePath): string
    {
        $this->ensurePostimagesIsReady();

        if ($this->isChapterPagePath($relativePath)) {
            $contents = $this->chapterPageImages->process($contents);
        }

        if ($this->usesPostimages()) {
            $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION) ?: 'jpg');
            $mimeType = match ($extension) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };

            return $this->postimages->uploadContents($contents, basename($relativePath), $mimeType);
        }

        Storage::disk('public')->put($relativePath, $contents);

        return $relativePath;
    }

    public function delete(string $path): void
    {
        if ($this->isRemote($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function deleteDirectory(string $path): void
    {
        if ($this->usesPostimages()) {
            return;
        }

        Storage::disk('public')->deleteDirectory($path);
    }

    public function moveDirectory(string $from, string $to): void
    {
        if ($this->usesPostimages()) {
            return;
        }

        if (Storage::disk('public')->exists($from)) {
            Storage::disk('public')->move($from, $to);
        }
    }

    public function makeDirectory(string $path): void
    {
        if ($this->usesPostimages()) {
            return;
        }

        Storage::disk('public')->makeDirectory($path);
    }

    public function isRemote(string $path): bool
    {
        return Str::startsWith($path, ['http://', 'https://']);
    }

    private function isChapterPagePath(string $relativePath): bool
    {
        return str_contains(str_replace('\\', '/', $relativePath), '/chapters/');
    }

    private function ensurePostimagesIsReady(): void
    {
        if (! PostimagesConfig::enabled()) {
            return;
        }

        if ($this->postimages->isConfigured()) {
            return;
        }

        throw new RuntimeException(
            'Postimages is enabled but POSTIMAGES_API_KEY is missing. Add it to your Laravel Cloud environment variables, then redeploy.'
        );
    }
}
