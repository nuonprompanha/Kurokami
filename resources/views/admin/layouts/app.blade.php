<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Kurokami Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('vendor/css/Admin.css') }}?v={{ filemtime(public_path('vendor/css/Admin.css')) }}">
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <a href="{{ auth()->user()->hasTwoFactorEnabled() ? auth()->user()->adminHomeRoute() : route('admin.profile.show') }}">
                    <img src="{{ asset('vendor/image/Korukami.png') }}" alt="Kurokami">
                    <span>Admin Panel</span>
                </a>
            </div>

            <nav class="admin-nav">
                @if (auth()->user()->hasTwoFactorEnabled())
                    @if (auth()->user()->isAdministrator())
                        <a href="{{ route('admin.dashboard') }}" @class(['admin-nav-link', 'active' => request()->routeIs('admin.dashboard')])>
                            <i class="fa-solid fa-gauge-high"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.visitors.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('admin.visitors.*')])>
                            <i class="fa-solid fa-chart-line"></i>
                            Visitors
                        </a>
                    @endif
                    <a href="{{ route('admin.manhwas.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('admin.manhwas.*')])>
                        <i class="fa-solid fa-book-open"></i>
                        Manhwa
                    </a>
                    <a href="{{ route('admin.genres.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('admin.genres.*')])>
                        <i class="fa-solid fa-tags"></i>
                        Genres
                    </a>
                @endif
                @if (auth()->user()->hasTwoFactorEnabled() && auth()->user()->isAdministrator())
                    <a href="{{ route('admin.users.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('admin.users.*')])>
                        <i class="fa-solid fa-users"></i>
                        Users
                    </a>
                @endif
                <a href="{{ route('admin.profile.show') }}" @class(['admin-nav-link', 'active' => request()->routeIs('admin.profile.*')])>
                    <i class="fa-solid fa-user"></i>
                    Profile
                </a>
                @if (auth()->user()->hasTwoFactorEnabled())
                    <a href="{{ route('home') }}" class="admin-nav-link" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        View Site
                    </a>
                @endif
            </nav>

            <form action="{{ route('admin.logout') }}" method="POST" class="admin-logout-form">
                @csrf
                <button type="submit" class="admin-nav-link admin-logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </button>
            </form>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h1 class="admin-page-title">@yield('page-title', 'Dashboard')</h1>
                    @hasSection('page-subtitle')
                        <p class="admin-page-subtitle">@yield('page-subtitle')</p>
                    @endif
                </div>
                <div class="admin-user">
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="admin-user-avatar">
                    <span class="admin-user-info">
                        <span class="admin-user-name">{{ auth()->user()->name }}</span>
                        <span class="admin-user-role">{{ auth()->user()->roleLabel() }}</span>
                    </span>
                </div>
            </header>

            <main class="admin-content">
                @if (session('warning'))
                    <div class="admin-alert admin-alert-warning">{{ session('warning') }}</div>
                @endif

                @if (session('success'))
                    <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
