<?php

namespace App\Services;

use App\Support\ManhwaStorageConfig;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorageService
{
    public function __construct(
        private readonly PostimagesService $postimages,
        private readonly ChapterPageImageProcessor $chapterPageImages,
    ) {}

    public function usesPostimages(): bool
    {
        return ManhwaStorageConfig::usesPostimages();
    }

    public function usesS3(): bool
    {
        return ManhwaStorageConfig::usesS3();
    }

    public function storeUploadedFile(UploadedFile $file, string $relativePath): string
    {
        ManhwaStorageConfig::ensureReady();

        if ($this->usesPostimages()) {
            return $this->postimages->uploadFile($file, basename($relativePath));
        }

        $directory = dirname($relativePath);
        $filename = basename($relativePath);

        $this->disk()->putFileAs(
            $directory === '.' ? '' : $directory,
            $file,
            $filename,
            $this->storeOptions($file->getRealPath() ?: null)
        );

        return $relativePath;
    }

    public function storeContents(string $contents, string $relativePath): string
    {
        ManhwaStorageConfig::ensureReady();

        if ($this->isChapterPagePath($relativePath) && $this->shouldProcessChapterPages()) {
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

        $this->disk()->put($relativePath, $contents, $this->storeOptions());

        return $relativePath;
    }

    public function preservesOriginalChapterPages(): bool
    {
        return ! $this->shouldProcessChapterPages();
    }

    /**
     * Copy a file to storage without re-encoding (byte-identical to the source).
     */
    public function storeFileFromPath(string $absolutePath, string $relativePath): string
    {
        ManhwaStorageConfig::ensureReady();

        if ($this->usesPostimages()) {
            $uploadedFile = new UploadedFile(
                $absolutePath,
                basename($absolutePath),
                mime_content_type($absolutePath) ?: null,
                null,
                true,
            );

            return $this->postimages->uploadFile($uploadedFile, basename($relativePath));
        }

        $directory = dirname($relativePath);
        $filename = basename($relativePath);

        $this->disk()->putFileAs(
            $directory === '.' ? '' : $directory,
            new \Illuminate\Http\File($absolutePath),
            $filename,
            $this->storeOptions($absolutePath)
        );

        return $relativePath;
    }

    public function delete(string $path): void
    {
        if ($this->isRemote($path)) {
            return;
        }

        $this->disk()->delete($path);
    }

    public function deleteDirectory(string $path): void
    {
        if ($this->usesPostimages()) {
            return;
        }

        $this->disk()->deleteDirectory($path);
    }

    public function moveDirectory(string $from, string $to): void
    {
        if ($this->usesPostimages()) {
            return;
        }

        $disk = $this->disk();

        if ($this->usesS3()) {
            $from = trim(str_replace('\\', '/', $from), '/');
            $to = trim(str_replace('\\', '/', $to), '/');

            foreach ($disk->allFiles($from) as $file) {
                $relative = Str::after($file, $from.'/');
                $disk->move($file, $to.'/'.$relative);
            }

            return;
        }

        if ($disk->exists($from)) {
            $disk->move($from, $to);
        }
    }

    public function makeDirectory(string $path): void
    {
        if ($this->usesPostimages()) {
            return;
        }

        if (! $this->usesS3()) {
            $this->disk()->makeDirectory($path);
        }
    }

    public function isRemote(string $path): bool
    {
        return Str::startsWith($path, ['http://', 'https://']);
    }

    public function publicUrl(string $path): string
    {
        return ManhwaStorageConfig::publicUrl($path);
    }

    private function disk(): Filesystem
    {
        return Storage::disk(ManhwaStorageConfig::disk());
    }

    private function isChapterPagePath(string $relativePath): bool
    {
        return str_contains(str_replace('\\', '/', $relativePath), '/chapters/');
    }

    private function shouldProcessChapterPages(): bool
    {
        return (bool) config('manhwa.chapter_page_upscale', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function storeOptions(?string $absolutePath = null): array
    {
        $options = [];

        if ($absolutePath && is_file($absolutePath)) {
            $mime = mime_content_type($absolutePath);

            if (is_string($mime) && $mime !== '') {
                $options['ContentType'] = $mime;
            }
        }

        if ($this->usesS3()) {
            return $options;
        }

        return array_merge($options, ['visibility' => 'public']);
    }
}
