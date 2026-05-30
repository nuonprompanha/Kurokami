@if ($user->hasTwoFactorEnabled())
    <div class="admin-two-factor-status admin-two-factor-status-enabled">
        <i class="fa-solid fa-shield-halved"></i>
        <div>
            <strong>Google Authenticator is enabled</strong>
            <p>A verification code is required every time you log in to the admin panel.</p>
        </div>
    </div>
@else
    <div class="admin-two-factor-status">
        <i class="fa-solid fa-mobile-screen-button"></i>
        <div>
            <strong>Setup required</strong>
            <p>Scan the QR code with Google Authenticator to secure your account.</p>
        </div>
    </div>

    <div class="admin-two-factor-setup">
        <p class="admin-two-factor-help">In Google Authenticator, tap <strong>+</strong> and scan this QR code. Then enter the code shown for <strong>Kurokami</strong>.</p>

        <div class="admin-two-factor-qr">
            {!! $qrCodeSvg !!}
        </div>

        <p class="admin-two-factor-secret">
            Manual key: <code>{{ $setupSecret }}</code>
        </p>

        <form action="{{ route('admin.profile.two-factor.confirm') }}" method="POST" class="admin-profile-form">
            @csrf

            <div class="admin-form-group">
                <label for="two_factor_code">Authentication Code</label>
                <input type="text" id="two_factor_code" name="code" inputmode="numeric" autocomplete="one-time-code" required @if ($tab === 'security') autofocus @endif placeholder="000000">
                @error('code')
                    <span class="admin-field-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="admin-btn admin-btn-primary">Confirm &amp; Enable</button>
        </form>

        <form action="{{ route('admin.profile.two-factor.reset') }}" method="POST" class="admin-two-factor-reset-form">
            @csrf
            <button type="submit" class="admin-btn admin-btn-outline">Generate new QR code</button>
        </form>
    </div>
@endif
