<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    private const VERIFICATION_WINDOW = 4;

    private const ISSUER = 'Kurokami';

    public function __construct(
        private Google2FA $google2fa = new Google2FA,
    ) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function beginSetup(User $user, bool $forceNew = false): string
    {
        if (! $forceNew && $user->hasPendingTwoFactorSetup()) {
            return $user->two_factor_secret;
        }

        $secret = $this->generateSecret();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ]);

        return $secret;
    }

    public function confirmSetup(User $user, string $code): bool
    {
        if ($user->hasTwoFactorEnabled() || ! $user->two_factor_secret) {
            return false;
        }

        if (! $this->verify($user->two_factor_secret, $code)) {
            return false;
        }

        $user->update([
            'two_factor_confirmed_at' => now(),
        ]);

        return true;
    }

    public function getQrCodeSvg(string $email, string $secret): string
    {
        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            self::ISSUER,
            $email,
            $secret,
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($otpauthUrl);
    }

    public function normalizeCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $code = preg_replace('/\D+/', '', $code);

        if ($code === '' || strlen($code) !== 6) {
            return null;
        }

        return $code;
    }

    public function verify(string $secret, string $code): bool
    {
        $code = $this->normalizeCode($code);

        if ($code === null || $secret === '') {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $code, self::VERIFICATION_WINDOW);
    }
}
