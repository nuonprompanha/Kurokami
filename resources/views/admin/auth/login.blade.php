<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Kurokami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('vendor/css/Admin.css') }}">
</head>

<body class="admin-login-body">
    <div class="admin-login-card">
        <div class="admin-login-brand">
            <img src="{{ asset('vendor/image/Korukami.png') }}" alt="Kurokami">
            <h1>Admin Panel</h1>
            <p>Staff login for Administrator and Editor only</p>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.store') }}" method="POST" class="admin-login-form">
            @csrf

            <div class="admin-form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="admin-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>

            <button type="submit" class="admin-btn admin-btn-primary">Login</button>
        </form>

        <a href="{{ route('home') }}" class="admin-login-back">
            <i class="fa-solid fa-arrow-left"></i> Back to site
        </a>
    </div>
</body>

</html>
