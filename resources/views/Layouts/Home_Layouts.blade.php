<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="shortcut icon" href="{{ asset('vendor/image/KUROKAMI.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('vendor/css/Home_Layouts.css') }}?v={{ filemtime(public_path('vendor/css/Home_Layouts.css')) }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-TBKPTCLN1C"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-TBKPTCLN1C');
</script>

    @if (config('services.google_analytics.measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode(config('services.google_analytics.measurement_id')) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json(config('services.google_analytics.measurement_id')));
        </script>
    @endif
</head>

<body class="is-loading" style="--chapter-reader-max-width: {{ max(0, (int) config('manhwa.chapter_reader_max_width', 0)) > 0 ? max(0, (int) config('manhwa.chapter_reader_max_width', 0)).'px' : '100%' }};">
    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="page-loader-inner">
            <img src="{{ asset('vendor/image/Korukami.png') }}" alt="Korukami" class="page-loader-logo">
            <div class="page-loader-spinner" role="status" aria-label="Loading"></div>
            <p class="page-loader-text">Loading<span class="page-loader-dots"></span></p>
        </div>
    </div>
    <!-- End Page Loader -->
    <!-- Header -->
     <header>
        <div class="container-fluid p-0 m-0 top-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <nav class="top-header-nav top-header-nav-left">
                            <ul>
                                <li><a href="{{ route('home') }}" @class(['is-active' => request()->routeIs('home')])>Home</a></li>
                                <li><a href="{{ route('contact') }}" @class(['is-active' => request()->routeIs('contact')])>Contact</a></li>
                                <li><a href="{{ route('about') }}" @class(['is-active' => request()->routeIs('about')])>About</a></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="col-md-6">
                        <nav class="top-header-nav top-header-nav-right">
                            <ul>
                                @auth
                                    @if (auth()->user()->isSubscriber())
                                        <li>
                                            <a href="{{ route('bookmarks.index') }}" @class(['is-active' => request()->routeIs('bookmarks.index')])>
                                                <i class="fa-solid fa-bookmark" aria-hidden="true"></i> Bookmarks
                                            </a>
                                        </li>
                                    @endif
                                    <li class="top-header-user">
                                        <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="top-header-user-avatar">
                                        <span>{{ auth()->user()->name }}</span>
                                    </li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST" class="top-header-logout-form">
                                            @csrf
                                            <button type="submit">Logout</button>
                                        </form>
                                    </li>
                                @else
                                    <li><a href="{{ route('login') }}">Login</a></li>
                                @endauth
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-logo">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12 text-center">
                        <a href="{{ route('home') }}" class="header-logo-link">
                            <img src="{{ asset('vendor/image/Korukami.png') }}" alt="Logo" class="header-logo-img">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-nav">
            <div class="container">
                <nav class="header-nav-menu">
                    <ul>
                        @foreach ($genres as $genre)
                            <li>
                                <a
                                    href="{{ route('home', ['genre' => $genre->slug]) }}"
                                    @class(['is-active' => request('genre') === $genre->slug])
                                >
                                    {{ $genre->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </div>
     </header>

    <!-- End Header -->
    <!-- Content -->
    @yield('content')
    <!-- End Content -->
    <!-- Footer -->
     <footer class="site-footer">
        <div class="container">
            <div class="row g-4 site-footer-top">
                <div class="col-md-4">
                    <a href="{{ route('home') }}" class="site-footer-logo">
                        <img src="{{ asset('vendor/image/Korukami.png') }}" alt="Korukami Logo">
                    </a>
                    <p class="site-footer-desc">
                        Read the latest manhwa online. Discover action, romance, fantasy, and more — updated regularly.
                    </p>
                </div>
                <div class="col-md-4">
                    <h5 class="site-footer-title">Quick Links</h5>
                    <ul class="site-footer-links">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('about') }}">About</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                        @auth
                            @if (auth()->user()->isSubscriber())
                                <li><a href="{{ route('bookmarks.index') }}">My Bookmarks</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}">Login</a></li>
                        @endauth
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="site-footer-title">Genres</h5>
                    <ul class="site-footer-links">
                        @foreach ($genres->take(5) as $genre)
                            <li>
                                <a href="{{ route('home', ['genre' => $genre->slug]) }}">
                                    {{ $genre->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="site-footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="site-footer-copy">&copy; {{ date('Y') }} Korukami. All rights reserved.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="site-footer-social">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                            <a href="#" aria-label="Discord"><i class="fab fa-discord"></i></a>
                            <a href="#" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </footer>
    <!-- End Footer -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/js/all.min.js" integrity="sha512-6BTOlkauINO65nLhXhthZMtepgJSghyimIalb+crKRPhvhmsCdnIuGcVbR5/aQY2A+260iC1OPy1oCdB6pSSwQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('vendor/js/Home_Layouts.js') }}?v={{ filemtime(public_path('vendor/js/Home_Layouts.js')) }}"></script>

</body>

</html>
