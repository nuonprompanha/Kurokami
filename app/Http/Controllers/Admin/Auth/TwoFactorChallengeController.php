<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request)
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('admin.login');
        }

        $user = User::query()->find($request->session()->get('login.id'));

        if (! $user?->hasTwoFactorEnabled()) {
            return redirect()->route('admin.login.two-factor.setup');
        }

        return view('admin.auth.two-factor');
    }

    public function store(Request $request, TwoFactorService $twoFactor)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $code = $twoFactor->normalizeCode($request->input('code'));

        if ($code === null) {
            throw ValidationException::withMessages([
                'code' => __('Enter a valid 6-digit verification code.'),
            ]);
        }

        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('admin.login');
        }

        $user = User::query()->find($userId);

        if (! $user?->hasTwoFactorEnabled()) {
            return redirect()->route('admin.login.two-factor.setup');
        }

        if (! $twoFactor->verify($user->two_factor_secret, $code)) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or expired. Open Google Authenticator and enter the current code for Kurokami.'),
            ]);
        }

        $remember = (bool) $request->session()->get('login.remember', false);
        $request->session()->forget(['login.id', 'login.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended($user->adminHomeRoute());
    }
}
