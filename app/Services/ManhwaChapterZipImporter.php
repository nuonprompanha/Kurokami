<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\ChapterPage;
use App\Models\Manhwa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class ManhwaChapterZipImporter
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    /**
     * @return array{chapters: int, pages: int}
     */
    public function import(Manhwa $manhwa, UploadedFile $zipFile): array
    {
        $tempDir = storage_path('app/temp/manhwa-import-'.Str::uuid());

        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        try {
            $this->extractZip($zipFile, $tempDir);

            $chapterDirs = $this->discoverChapterDirectories($tempDir);

            if ($chapterDirs === []) {
                throw new RuntimeException(
                    'No chapter folders found. Use folders named like 1, 2, chapter-1, or ch01 with images inside.'
                );
            }

            $chapterCount = 0;
            $pageCount = 0;

            foreach ($chapterDirs as $chapterNumber => $directory) {
                $images = $this->collectImages($directory);

                if ($images === []) {
                    continue;
                }

                $chapter = Chapter::query()->updateOrCreate(
                    [
                        'manhwa_id' => $manhwa->id,
                        'chapter_number' => $chapterNumber,
                    ],
                    [
                        'title' => Chapter::normalizeStoredTitle(
                            $this->chapterTitleFromFolder(basename($directory), $chapterNumber),
                            $chapterNumber
                        ),
                    ]
                );

                $this->clearChapterPages($chapter);

                $chapterPath = $manhwa->storageDirectory().'/chapters/'.$chapterNumber;
                $this->imageStorage->makeDirectory($chapterPath);

                foreach ($images as $index => $sourcePath) {
                    $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
                    $filename = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT).'.'.$extension;
                    $destination = $chapterPath.'/'.$filename;
                    $storedPath = $this->imageStorage->storeContents(
                        file_get_contents($sourcePath),
                        $destination
                    );

                    ChapterPage::query()->create([
                        'chapter_id' => $chapter->id,
                        'path' => $storedPath,
                        'sort_order' => $index + 1,
                    ]);

                    $pageCount++;
                }

                $chapterCount++;
            }

            if ($chapterCount === 0) {
                throw new RuntimeException('No valid chapter images were found in the zip file.');
            }

            return [
                'chapters' => $chapterCount,
                'pages' => $pageCount,
            ];
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    private function extractZip(UploadedFile $zipFile, string $destination): void
    {
        if (class_exists(ZipArchive::class)) {
            $this->extractZipWithZipArchive($zipFile, $destination);

            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            if ($tar = $this->windowsExecutable('tar.exe')) {
                $this->extractZipWithTar($tar, $zipFile, $destination);

                return;
            }

            if ($powershell = $this->windowsExecutable('WindowsPowerShell/v1.0/powershell.exe')) {
                $this->extractZipWithPowerShell($powershell, $zipFile, $destination);

                return;
            }
        }

        if ($this->commandExists('tar')) {
            $this->extractZipWithTar('tar', $zipFile, $destination);

            return;
        }

        if ($this->commandExists('unzip')) {
            $this->extractZipWithUnzip($zipFile, $destination);

            return;
        }

        throw new RuntimeException(
            'PHP zip extension is not enabled. Uncomment extension=zip in php.ini, then restart php artisan serve.'
        );
    }

    private function extractZipWithZipArchive(UploadedFile $zipFile, string $destination): void
    {
        $zip = new ZipArchive;
        $opened = $zip->open($zipFile->getRealPath());

        if ($opened !== true) {
            throw new RuntimeException('Unable to open the zip file.');
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entryName = $zip->getNameIndex($index);

            if ($entryName === false || str_contains($entryName, '..')) {
                $zip->close();
                throw new RuntimeException('Invalid file path inside zip archive.');
            }
        }

        $zip->extractTo($destination);
        $zip->close();
    }

    private function extractZipWithTar(string $tarExecutable, UploadedFile $zipFile, string $destination): void
    {
        $result = Process::run([
            $tarExecutable,
            '-xf',
            $zipFile->getRealPath(),
            '-C',
            $destination,
        ]);

        if (! $result->successful()) {
            throw new RuntimeException('Unable to extract the zip file: '.$result->errorOutput());
        }

        $this->guardAgainstPathTraversal($destination);
    }

    private function extractZipWithPowerShell(string $powershellExecutable, UploadedFile $zipFile, string $destination): void
    {
        $zipPath = $zipFile->getRealPath();
        $escapedZip = str_replace("'", "''", $zipPath);
        $escapedDestination = str_replace("'", "''", $destination);

        $result = Process::run([
            $powershellExecutable,
            '-NoProfile',
            '-ExecutionPolicy',
            'Bypass',
            '-Command',
            "Expand-Archive -LiteralPath '{$escapedZip}' -DestinationPath '{$escapedDestination}' -Force",
        ]);

        if (! $result->successful()) {
            throw new RuntimeException('Unable to extract the zip file: '.$result->errorOutput());
        }

        $this->guardAgainstPathTraversal($destination);
    }

    private function windowsExecutable(string $relativePath): ?string
    {
        $systemRoot = getenv('SystemRoot') ?: 'C:\\Windows';

        foreach (['System32', 'Sysnative'] as $folder) {
            $path = $systemRoot.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function extractZipWithUnzip(UploadedFile $zipFile, string $destination): void
    {
        $result = Process::run([
            'unzip',
            '-o',
            $zipFile->getRealPath(),
            '-d',
            $destination,
        ]);

        if (! $result->successful()) {
            throw new RuntimeException('Unable to extract the zip file: '.$result->errorOutput());
        }

        $this->guardAgainstPathTraversal($destination);
    }

    private function guardAgainstPathTraversal(string $root): void
    {
        $root = realpath($root) ?: $root;
        $normalizedRoot = strtolower(str_replace('\\', '/', $root));

        foreach (File::allFiles($root) as $file) {
            $realPath = realpath($file->getPathname());
            $normalizedPath = $realPath !== false
                ? strtolower(str_replace('\\', '/', $realPath))
                : '';

            if ($realPath === false || ! str_starts_with($normalizedPath, $normalizedRoot)) {
                File::deleteDirectory($root);
                throw new RuntimeException('Invalid file path inside zip archive.');
            }
        }
    }

    private function commandExists(string $command): bool
    {
        $finder = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';

        return Process::run([$finder, $command])->successful();
    }

    /**
     * @return array<int, string>
     */
    private function discoverChapterDirectories(string $root): array
    {
        $chapters = [];
        $entries = $this->immediateDirectories($root);

        if ($entries === []) {
            return [];
        }

        $hasChapterFolders = false;

        foreach ($entries as $directory) {
            $chapterNumber = $this->parseChapterNumber(basename($directory));

            if ($chapterNumber !== null) {
                $hasChapterFolders = true;
                $chapters[$chapterNumber] = $directory;
            }
        }

        if ($hasChapterFolders) {
            ksort($chapters, SORT_NUMERIC);

            return $chapters;
        }

        $nested = [];

        foreach ($entries as $directory) {
            foreach ($this->immediateDirectories($directory) as $nestedDirectory) {
                $chapterNumber = $this->parseChapterNumber(basename($nestedDirectory));

                if ($chapterNumber !== null) {
                    $nested[$chapterNumber] = $nestedDirectory;
                }
            }
        }

        ksort($nested, SORT_NUMERIC);

        return $nested;
    }

    /**
     * @return list<string>
     */
    private function immediateDirectories(string $path): array
    {
        if (! File::isDirectory($path)) {
            return [];
        }

        $directories = [];

        foreach (File::directories($path) as $directory) {
            $directories[] = $directory;
        }

        return $directories;
    }

    private function parseChapterNumber(string $name): ?int
    {
        $normalized = strtolower(trim($name));

        if (preg_match('/^(?:chapter|ch)[\s_-]*(\d+)$/i', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/^(\d+)$/', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/^(?:chapter|ch)[\s_-]*(\d+)/i', $normalized, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/^(\d+)[\s._-]/', $normalized, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function chapterTitleFromFolder(string $folderName, int $chapterNumber): string
    {
        $trimmed = trim($folderName);

        if (preg_match('/^(?:chapter|ch)[\s_-]*\d+$/i', $trimmed) || preg_match('/^\d+$/', $trimmed)) {
            return '';
        }

        $title = preg_replace('/^(?:chapter|ch)?[\s_-]*\d+[\s._-]*/i', '', $trimmed);
        $title = trim(str_replace(['_', '-'], ' ', $title ?? ''));

        return $title;
    }

    /**
     * @return list<string>
     */
    private function collectImages(string $directory): array
    {
        $images = [];

        foreach (File::allFiles($directory) as $file) {
            $extension = strtolower($file->getExtension());

            if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                $images[] = $file->getPathname();
            }
        }

        usort($images, fn (string $a, string $b) => strnatcasecmp(basename($a), basename($b)));

        return $images;
    }

    private function clearChapterPages(Chapter $chapter): void
    {
        foreach ($chapter->pages as $page) {
            $this->imageStorage->delete($page->path);
        }

        $chapter->pages()->delete();
    }
}
