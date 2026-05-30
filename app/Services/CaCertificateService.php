<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CaCertificateService
{
    public function path(): string
    {
        $path = (string) config('postimages.ca_bundle', storage_path('certs/cacert.pem'));

        if (! is_file($path)) {
            $this->download($path);
        }

        return $path;
    }

    public function download(?string $path = null): string
    {
        $path ??= (string) config('postimages.ca_bundle', storage_path('certs/cacert.pem'));
        $url = (string) config('postimages.ca_bundle_url', 'https://curl.se/ca/cacert.pem');

        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create directory: {$directory}");
        }

        $response = Http::timeout(60)
            ->withOptions(['verify' => false])
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Unable to download CA bundle from {$url} (HTTP {$response->status()}).");
        }

        $contents = trim($response->body());

        if ($contents === '' || ! str_contains($contents, '-----BEGIN CERTIFICATE-----')) {
            throw new RuntimeException("Downloaded CA bundle from {$url} is invalid.");
        }

        file_put_contents($path, $contents.PHP_EOL);

        return $path;
    }
}
