<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication - Kurokami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('vendor/css/Admin.css') }}">
</head>

<body class="admin-login-body">
    <div class="admin-login-card">
        <div class="admin-login-brand">
            <img src="{{ asset('vendor/image/Korukami.png') }}" alt="Kurokami">
            <h1>Verify Identity</h1>
            <p>Enter the 6-digit code from Google Authenticator</p>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.two-factor.store') }}" method="POST" class="admin-login-form">
            @csrf

            <div class="admin-form-group">
                <label for="code">Authentication Code</label>
                <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus placeholder="000000">
            </div>

            <button type="submit" class="admin-btn admin-btn-primary">Verify &amp; Login</button>
        </form>

        <a href="{{ route('admin.login') }}" class="admin-login-back">
            <i class="fa-solid fa-arrow-left"></i> Back to login
        </a>
    </div>
</body>

</html>
