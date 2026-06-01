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

        $format = $this->detectFormat($contents);
        $resized = $this->upscale($image, $width, $height, $targetWidth, $format);
        imagedestroy($image);

        if ($resized === null) {
            return $contents;
        }

        if (config('manhwa.chapter_page_sharpen', true)) {
            $this->sharpen($resized);
        }

        $encoded = $this->encodeImage($resized, $format);
        imagedestroy($resized);

        return $encoded ?? $contents;
    }

    private function upscale(GdImage $image, int $width, int $height, int $targetWidth, string $format): ?GdImage
    {
        $current = $image;
        $currentWidth = $width;
        $currentHeight = $height;
        $copied = false;

        while ($currentWidth < $targetWidth) {
            $nextWidth = min($targetWidth, max($currentWidth + 1, (int) round($currentWidth * 1.5)));
            $nextHeight = (int) round($currentHeight * ($nextWidth / $currentWidth));
            $scaled = $this->scaleStep($current, $nextWidth, $nextHeight, $format);

            if ($scaled === null) {
                if ($copied && $current !== $image) {
                    imagedestroy($current);
                }

                return null;
            }

            if ($copied && $current !== $image) {
                imagedestroy($current);
            }

            $current = $scaled;
            $copied = true;
            $currentWidth = $nextWidth;
            $currentHeight = $nextHeight;
        }

        return $current;
    }

    private function scaleStep(GdImage $image, int $targetWidth, int $targetHeight, string $format): ?GdImage
    {
        if (function_exists('imagescale')) {
            $filter = defined('IMG_BICUBIC') ? IMG_BICUBIC : IMG_BILINEAR_FIXED;
            $scaled = @imagescale($image, $targetWidth, $targetHeight, $filter);

            if ($scaled instanceof GdImage) {
                return $scaled;
            }
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            return null;
        }

        $this->prepareCanvas($canvas, $format);

        if (! imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            imagesx($image),
            imagesy($image)
        )) {
            imagedestroy($canvas);

            return null;
        }

        return $canvas;
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

        $jpegQuality = (int) config('manhwa.chapter_page_jpeg_quality', 95);
        $webpQuality = (int) config('manhwa.chapter_page_webp_quality', 92);

        $encoded = match ($format) {
            'png' => imagepng($image, null, 4),
            'gif' => imagegif($image),
            'webp' => function_exists('imagewebp')
                ? imagewebp($image, null, $webpQuality)
                : imagejpeg($image, null, $jpegQuality),
            default => imagejpeg($image, null, $jpegQuality),
        };

        if ($encoded === false) {
            ob_end_clean();

            return null;
        }

        $output = ob_get_clean();

        return is_string($output) && $output !== '' ? $output : null;
    }
}
