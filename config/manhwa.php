<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Manhwa image storage
    |--------------------------------------------------------------------------
    |
    | Where cover and chapter page uploads are stored.
    |
    | Supported: "local", "s3", "postimages"
    | - local: storage/app/public (run php artisan storage:link)
    | - s3: Amazon S3 (set AWS_* env variables)
    | - postimages: Postimages.org API (set POSTIMAGES_* env variables)
    |
    | When set to "local", POSTIMAGES_ENABLED=true still switches to Postimages
    | for backward compatibility.
    |
    */

    'storage' => env('MANHWA_STORAGE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Chapter page display width
    |--------------------------------------------------------------------------
    |
    | When chapter_page_upscale is true, images narrower than target width are
    | upscaled on upload. When false (recommended), originals are kept as-is for
    | maximum sharpness — the reader never stretches beyond native resolution.
    |
    */

    'chapter_page_upscale' => filter_var(env('CHAPTER_PAGE_UPSCALE', false), FILTER_VALIDATE_BOOLEAN),

    'chapter_page_target_width' => (int) env('CHAPTER_PAGE_TARGET_WIDTH', 1400),

    'chapter_page_jpeg_quality' => (int) env('CHAPTER_PAGE_JPEG_QUALITY', 98),

    'chapter_page_webp_quality' => (int) env('CHAPTER_PAGE_WEBP_QUALITY', 95),

    'chapter_page_png_compression' => (int) env('CHAPTER_PAGE_PNG_COMPRESSION', 0),

    'chapter_page_sharpen' => filter_var(env('CHAPTER_PAGE_SHARPEN', false), FILTER_VALIDATE_BOOLEAN),

    'chapter_reader_max_width' => (int) env('CHAPTER_READER_MAX_WIDTH', 0),

    /*
    |--------------------------------------------------------------------------
    | Retina / HiDPI display
    |--------------------------------------------------------------------------
    |
    | When true, chapter pages are sized so the browser does not upscale them
    | on Retina/HiDPI screens (1 image pixel = 1 screen pixel). Prevents soft/
    | blurry pages when source scans are ~690–800px wide.
    |
    */

    'chapter_reader_pixel_perfect' => filter_var(env('CHAPTER_READER_PIXEL_PERFECT', true), FILTER_VALIDATE_BOOLEAN),

];
