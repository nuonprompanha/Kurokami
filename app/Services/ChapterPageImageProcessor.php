<?php

namespace App\Services;

use GdImage;

class ChapterPageImageProcessor
{
    public function targetWidth(): int
    {
        return max(1, (int) config('manhwa.chapter_page_target_width', 900));
    }

    public function process(string $contents): string
    {
        if (! extension_loaded('gd')) {
            return $contents;
        }

        $image = @imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            return $contents;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);

            return $contents;
        }

        $targetWidth = $this->targetWidth();

        if ($width >= $targetWidth) {
            imagedestroy($image);

            return $contents;
        }

        $targetHeight = (int) round($height * ($targetWidth / $width));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($resized === false) {
            imagedestroy($image);

            return $contents;
        }

        $format = $this->detectFormat($contents);
        $this->prepareCanvas($resized, $format);

        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        imagedestroy($image);

        if (config('manhwa.chapter_page_sharpen', true)) {
            $this->sharpen($resized);
        }

        $encoded = $this->encodeImage($resized, $format);
        imagedestroy($resized);

        return $encoded ?? $contents;
    }

    private function prepareCanvas(GdImage $destination, string $format): void
    {
        if (in_array($format, ['png', 'webp', 'gif'], true)) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
            imagefill($destination, 0, 0, $transparent);
            imagealphablending($destination, true);

            return;
        }

        $background = imagecolorallocate($destination, 255, 255, 255);
        imagefill($destination, 0, 0, $background);
        imagealphablending($destination, true);
    }

    private function detectFormat(string $contents): string
    {
        if (str_starts_with($contents, "\x89PNG\r\n\x1a\n")) {
            return 'png';
        }

        if (str_starts_with($contents, 'GIF87a') || str_starts_with($contents, 'GIF89a')) {
            return 'gif';
        }

        if (str_starts_with($contents, 'RIFF') && str_contains(substr($contents, 0, 16), 'WEBP')) {
            return 'webp';
        }

        return 'jpeg';
    }

    private function sharpen(GdImage $image): void
    {
        $matrix = [
            [0, -1, 0],
            [-1, 5, -1],
            [0, -1, 0],
        ];

        imageconvolution($image, $matrix, 1, 0);
    }

    private function encodeImage(GdImage $image, string $format): ?string
    {
        ob_start();

        $encoded = match ($format) {
            'png' => imagepng($image, null, 6),
            'gif' => imagegif($image),
            'webp' => function_exists('imagewebp')
                ? imagewebp($image, null, (int) config('manhwa.chapter_page_webp_quality', 90))
                : imagejpeg($image, null, (int) config('manhwa.chapter_page_jpeg_quality', 92)),
            default => imagejpeg($image, null, (int) config('manhwa.chapter_page_jpeg_quality', 92)),
        };

        if ($encoded === false) {
            ob_end_clean();

            return null;
        }

        $output = ob_get_clean();

        return is_string($output) && $output !== '' ? $output : null;
    }
}
