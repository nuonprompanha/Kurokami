<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    protected function googleDriver()
    {
        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.callback'))
            ->setHttpClient(new Client([
                'verify' => CaBundle::getSystemCaRootBundlePath(),
            ]));
    }

    public function redirect()
    {
        return $this->googleDriver()->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = $this->googleDriver()->user();
        } catch (InvalidStateException) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google sign-in expired or could not verify your session. Use the same address you opened the site with (e.g. http://localhost:8283, not 127.0.0.1), then try again.',
                ]);
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::query()->where('email', $googleUser->getEmail())->first();

            if ($user) {
                if (! $user->isSubscriber()) {
                    return redirect()
                        ->route('login')
                        ->withErrors([
                            'email' => 'Staff accounts must use the admin login page.',
                        ]);
                }

                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                ]);
            } else {
                $user = User::query()->create([
                    'name' => $googleUser->getName() ?: 'Google User',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'role' => User::ROLE_SUBSCRIBER,
                    'password' => null,
                ]);
            }
        }

        Auth::login($user, remember: true);

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
