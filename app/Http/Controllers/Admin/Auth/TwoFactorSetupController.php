<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorSetupController extends Controller
{
    public function create(Request $request, TwoFactorService $twoFactor)
    {
        $user = $this->pendingLoginUser($request);

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.login.two-factor');
        }

        $secret = $twoFactor->beginSetup($user);

        return view('admin.auth.two-factor-setup', [
            'qrCodeSvg' => $twoFactor->getQrCodeSvg($user->email, $secret),
            'setupSecret' => $secret,
        ]);
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

        $user = $this->pendingLoginUser($request);

        if (! $user) {
            return redirect()->route('admin.login');
        }

        $user->refresh();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.login.two-factor');
        }

        if (! $user->two_factor_secret) {
            return redirect()->route('admin.login.two-factor.setup')
                ->withErrors(['code' => 'Setup expired. Scan the QR code again.']);
        }

        if (! $twoFactor->confirmSetup($user, $code)) {
            throw ValidationException::withMessages([
                'code' => __('The verification code does not match this QR code. Use the current code for "Kurokami" in Google Authenticator, or generate a new QR code below.'),
            ]);
        }

        $remember = (bool) $request->session()->get('login.remember', false);
        $request->session()->forget(['login.id', 'login.remember']);

        Auth::login($user->fresh(), $remember);
        $request->session()->regenerate();

        return redirect()->intended($user->adminHomeRoute());
    }

    public function reset(Request $request, TwoFactorService $twoFactor)
    {
        $user = $this->pendingLoginUser($request);

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.login.two-factor');
        }

        $twoFactor->beginSetup($user, forceNew: true);

        return redirect()
            ->route('admin.login.two-factor.setup')
            ->with('warning', 'A new QR code was generated. Delete the old Kurokami entry in Google Authenticator, then scan the new code.');
    }

    private function pendingLoginUser(Request $request): ?User
    {
        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return null;
        }

        $user = User::query()->find($userId);

        if (! $user?->canAccessAdminPanel()) {
            $request->session()->forget(['login.id', 'login.remember']);

            return null;
        }

        return $user;
    }
}
