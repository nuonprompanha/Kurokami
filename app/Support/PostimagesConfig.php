<?php

namespace App\Support;

class PostimagesConfig
{
    public static function enabled(): bool
    {
        $runtime = self::runtimeBoolean('POSTIMAGES_ENABLED');

        if ($runtime !== null) {
            return $runtime;
        }

        return (bool) config('postimages.enabled', false);
    }

    public static function apiKey(): ?string
    {
        return self::resolvedString('POSTIMAGES_API_KEY', 'postimages.api_key');
    }

    public static function gallery(): ?string
    {
        return self::resolvedString('POSTIMAGES_GALLERY', 'postimages.gallery');
    }

    public static function uploadUrl(): string
    {
        return self::resolvedString('POSTIMAGES_UPLOAD_URL', 'postimages.upload_url')
            ?? 'https://api.postimage.org/1/upload';
    }

    public static function version(): string
    {
        return self::resolvedString('POSTIMAGES_VERSION', 'postimages.version')
            ?? '1.0.1';
    }

    public static function expire(): string
    {
        return self::resolvedString('POSTIMAGES_EXPIRE', 'postimages.expire')
            ?? '0';
    }

    public static function optsize(): string
    {
        return self::resolvedString('POSTIMAGES_OPTSIZE', 'postimages.optsize')
            ?? '0';
    }

    public static function verifySsl(): bool
    {
        $runtime = self::runtimeBoolean('POSTIMAGES_VERIFY_SSL');

        if ($runtime !== null) {
            return $runtime;
        }

        return (bool) config('postimages.verify_ssl', true);
    }

    public static function isConfigured(): bool
    {
        return filled(self::apiKey());
    }

    /**
     * @return array<string, mixed>
     */
    public static function diagnostics(): array
    {
        return [
            'enabled' => self::enabled(),
            'configured' => self::isConfigured(),
            'api_key_set' => filled(self::apiKey()),
            'api_key_source' => self::runtimeString('POSTIMAGES_API_KEY') ? 'runtime_env' : (filled(config('postimages.api_key')) ? 'config' : 'missing'),
            'gallery' => self::gallery(),
            'upload_url' => self::uploadUrl(),
            'config_cached' => app()->configurationIsCached(),
        ];
    }

    private static function resolvedString(string $envKey, string $configKey): ?string
    {
        $runtime = self::runtimeString($envKey);

        if (filled($runtime)) {
            return $runtime;
        }

        $configured = config($configKey);

        return filled($configured) ? (string) $configured : null;
    }

    private static function runtimeString(string $key): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            return filled($_ENV[$key]) ? (string) $_ENV[$key] : null;
        }

        $value = getenv($key);

        if ($value === false) {
            return null;
        }

        return filled($value) ? (string) $value : null;
    }

    private static function runtimeBoolean(string $key): ?bool
    {
        if (array_key_exists($key, $_ENV)) {
            return filter_var($_ENV[$key], FILTER_VALIDATE_BOOLEAN);
        }

        $value = getenv($key);

        if ($value === false) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
