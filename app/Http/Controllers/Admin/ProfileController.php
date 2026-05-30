<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request, TwoFactorService $twoFactor)
    {
        $user = Auth::user();
        $setupSecret = null;
        $qrCodeSvg = null;

        if (! $user->hasTwoFactorEnabled()) {
            $setupSecret = $twoFactor->beginSetup($user);
            $qrCodeSvg = $twoFactor->getQrCodeSvg($user->email, $setupSecret);
        }

        return view('admin.profile.show', [
            'user' => $user,
            'tab' => $this->resolveTab($request, $user),
            'qrCodeSvg' => $qrCodeSvg,
            'setupSecret' => $setupSecret,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'bio' => $validated['bio'] ?? null,
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        return $this->redirectToTab('profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        return $this->redirectToTab('password')
            ->with('success', 'Password updated successfully.');
    }

    public function confirmTwoFactor(Request $request, TwoFactorService $twoFactor)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $code = $twoFactor->normalizeCode($request->input('code'));

        if ($code === null) {
            throw ValidationException::withMessages([
                'code' => __('Enter a valid 6-digit verification code.'),
            ])->redirectTo($this->profileTabUrl('security'));
        }

        $user = Auth::user()->fresh();

        if ($user->hasTwoFactorEnabled()) {
            return $this->redirectToTab('security');
        }

        if (! $user->two_factor_secret) {
            return $this->redirectToTab('security')
                ->withErrors(['code' => 'Setup expired. Scan the QR code again.']);
        }

        if (! $twoFactor->confirmSetup($user, $code)) {
            throw ValidationException::withMessages([
                'code' => __('The verification code does not match this QR code. Use the current code for "Kurokami" in Google Authenticator, or generate a new QR code below.'),
            ])->redirectTo($this->profileTabUrl('security'));
        }

        return redirect()->intended($user->adminHomeRoute())
            ->with('success', 'Google Authenticator has been enabled for your account.');
    }

    public function resetTwoFactor(TwoFactorService $twoFactor)
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return $this->redirectToTab('security');
        }

        $twoFactor->beginSetup($user, forceNew: true);

        return $this->redirectToTab('security')
            ->with('warning', 'A new QR code was generated. Delete the old Kurokami entry in Google Authenticator, then scan the new code.');
    }

    private function resolveTab(Request $request, User $user): string
    {
        if (! $user->hasTwoFactorEnabled()) {
            return 'security';
        }

        $tab = $request->query('tab', 'profile');

        return in_array($tab, ['profile', 'password', 'security'], true) ? $tab : 'profile';
    }

    private function profileTabUrl(string $tab): string
    {
        return route('admin.profile.show', ['tab' => $tab]);
    }

    private function redirectToTab(string $tab): RedirectResponse
    {
        return redirect()->to($this->profileTabUrl($tab));
    }
}
