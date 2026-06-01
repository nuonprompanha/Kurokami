<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chapter page display width
    |--------------------------------------------------------------------------
    |
    | Chapter images narrower than this are upscaled on upload so the reader
    | can use width: 100% without the browser stretching low-res files.
    |
    */

    'chapter_page_target_width' => (int) env('CHAPTER_PAGE_TARGET_WIDTH', 900),

    'chapter_page_jpeg_quality' => (int) env('CHAPTER_PAGE_JPEG_QUALITY', 92),

    'chapter_page_webp_quality' => (int) env('CHAPTER_PAGE_WEBP_QUALITY', 90),

    'chapter_page_sharpen' => filter_var(env('CHAPTER_PAGE_SHARPEN', true), FILTER_VALIDATE_BOOLEAN),

];
