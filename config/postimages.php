<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Postimages.org Image Storage
    |--------------------------------------------------------------------------
    |
    | Upload cover and chapter images to Postimages via their API.
    | Generate an API key at https://postimages.org/login/api
    |
    */

    'enabled' => filter_var(env('POSTIMAGES_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'api_key' => env('POSTIMAGES_API_KEY'),

    'gallery' => env('POSTIMAGES_GALLERY'),

    'upload_url' => env('POSTIMAGES_UPLOAD_URL', 'https://api.postimage.org/1/upload'),

    'version' => env('POSTIMAGES_VERSION', '1.0.1'),

    // 0 = no expiration
    'expire' => env('POSTIMAGES_EXPIRE', '0'),

    // 0 = do not resize (keep original resolution on paid plans; free hotlinks up to 1280px)
    'optsize' => env('POSTIMAGES_OPTSIZE', '0'),

    /*
    | Windows/local PHP often has no CA bundle in php.ini. Download from:
    | https://curl.se/ca/cacert.pem
    */
    'ca_bundle_url' => env('POSTIMAGES_CA_BUNDLE_URL', 'https://curl.se/ca/cacert.pem'),

    'ca_bundle' => env('POSTIMAGES_CA_BUNDLE', storage_path('certs/cacert.pem')),

    'verify_ssl' => filter_var(env('POSTIMAGES_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),

];
