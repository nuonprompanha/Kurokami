<?php

namespace App\Services;

use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PostimagesService
{
    public function __construct(
        private readonly CaCertificateService $caCertificate,
    ) {}

    public function isConfigured(): bool
    {
        return filled(config('postimages.api_key'));
    }

    public function uploadFile(UploadedFile $file, ?string $filename = null): string
    {
        $filename ??= $file->getClientOriginalName() ?: 'image.jpg';

        return $this->uploadContents(
            file_get_contents($file->getRealPath()),
            $filename,
            $file->getMimeType() ?: 'application/octet-stream'
        );
    }

    public function uploadContents(string $contents, string $filename, string $mimeType = 'application/octet-stream'): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Postimages API key is not configured.');
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: 'jpg');
        $name = pathinfo($filename, PATHINFO_FILENAME) ?: 'image';

        $payload = [
            'key' => config('postimages.api_key'),
            'o' => '2b819584285c102318568238c7d4a4c7',
            'm' => '59c2ad4b46b0c1e12d5703302bff0120',
            'numfiles' => '1',
            'optsize' => (string) config('postimages.optsize', '0'),
            'expire' => (string) config('postimages.expire', '0'),
            'adult' => '0',
            'upload_session' => Str::random(32),
            'version' => (string) config('postimages.version', '1.0.1'),
            'portable' => '1',
            'name' => $name,
            'type' => $extension,
            'image' => base64_encode($contents),
        ];

        if ($gallery = config('postimages.gallery')) {
            $payload['gallery'] = $gallery;
        }

        $response = $this->httpClient(120)
            ->asForm()
            ->post(config('postimages.upload_url'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Postimages upload failed (HTTP '.$response->status().'): '.$response->body()
            );
        }

        return $this->parseUploadResponse($response);
    }

    private function parseUploadResponse(Response $response): string
    {
        $body = trim($response->body());

        if ($body === '') {
            throw new RuntimeException('Postimages returned an empty response.');
        }

        if (str_starts_with($body, '<?xml') || str_starts_with($body, '<data')) {
            return $this->parseXmlUploadResponse($body);
        }

        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            foreach (['url', 'direct_link', 'direct', 'hotlink'] as $key) {
                if (! empty($decoded[$key]) && is_string($decoded[$key])) {
                    return $this->normalizeDirectUrl($decoded[$key]);
                }
            }
        }

        if (preg_match('#https://i\.postimg\.cc/[^\s<"\']+#', $body, $matches)) {
            return $this->normalizeDirectUrl($matches[0]);
        }

        throw new RuntimeException('Unable to parse Postimages upload response.');
    }

    private function parseXmlUploadResponse(string $body): string
    {
        if (preg_match('/success="(\d+)"/', $body, $successMatch) && $successMatch[1] !== '1') {
            $error = 'Unknown error';

            if (preg_match('#<error>(.*?)</error>#s', $body, $errorMatch)) {
                $error = trim(strip_tags(html_entity_decode($errorMatch[1])));
            }

            throw new RuntimeException('Postimages upload rejected: '.$error);
        }

        foreach (['hotlink', 'thumbnail', 'page'] as $tag) {
            if (preg_match('#<'.$tag.'>(https://[^<]+)</'.$tag.'>#', $body, $matches)) {
                $url = $this->normalizeDirectUrl($matches[1]);

                if ($tag === 'page') {
                    return $this->resolveDirectUrlFromPage($url);
                }

                return $url;
            }
        }

        throw new RuntimeException('Unable to parse Postimages upload response.');
    }

    private function resolveDirectUrlFromPage(string $pageUrl): string
    {
        $response = $this->httpClient(30)
            ->withHeaders(['User-Agent' => 'ManhwaKurokami/1.0'])
            ->get($pageUrl);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to resolve Postimages page URL.');
        }

        if (preg_match('#https://i\.postimg\.cc/[^\s"\'<>]+#', $response->body(), $matches)) {
            return $this->normalizeDirectUrl($matches[0]);
        }

        throw new RuntimeException('Unable to find direct image URL on Postimages page.');
    }

    private function normalizeDirectUrl(string $url): string
    {
        $url = html_entity_decode(trim($url));
        $url = strtok($url, '?') ?: $url;

        return rtrim($url, '/');
    }

    private function httpClient(int $timeout = 30): PendingRequest
    {
        return Http::timeout($timeout)->withOptions([
            'verify' => $this->sslVerifyOption(),
        ]);
    }

    private function sslVerifyOption(): bool|string
    {
        if (! config('postimages.verify_ssl', true)) {
            return false;
        }

        $configured = config('postimages.ca_bundle');

        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        try {
            $systemCa = CaBundle::getSystemCaRootBundlePath();

            if ($systemCa !== '' && is_file($systemCa)) {
                return $systemCa;
            }
        } catch (\Throwable) {
            // Fall back to the bundled/downloaded CA file for local Windows setups.
        }

        $caBundle = $this->caCertificate->path();

        if (is_file($caBundle)) {
            return $caBundle;
        }

        return true;
    }
}
