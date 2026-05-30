@extends('Layouts.Home_Layouts')

@section('title', 'About - Kurokami')

@section('content')
    <section class="static-page-hero">
        <div class="container">
            <h1 class="static-page-title">About Kurokami</h1>
            <p class="static-page-lead">
                Your destination for reading manhwa online — free, fast, and updated regularly.
            </p>
        </div>
    </section>

    <section class="static-page-section">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="static-page-card">
                        <h2 class="static-page-heading">Who We Are</h2>
                        <p>
                            Kurokami is a manhwa reading platform built for fans who want a clean, simple way to
                            discover and follow their favorite series. From action and fantasy to romance and drama,
                            we bring together titles across many genres in one place.
                        </p>
                        <p class="mb-0">
                            Our goal is to make every reading session smooth — whether you are catching up on a
                            long-running series or exploring something new for the first time.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="static-page-card static-page-card-accent">
                        <h2 class="static-page-heading">What We Offer</h2>
                        <ul class="static-page-list">
                            <li><i class="fas fa-book-open"></i> High-quality chapter reading experience</li>
                            <li><i class="fas fa-bolt"></i> Regular updates when new chapters are published</li>
                            <li><i class="fas fa-tags"></i> Browse by genre to find your next favorite series</li>
                            <li><i class="fas fa-bookmark"></i> Bookmark series when signed in with Google</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="static-page-section static-page-section-muted">
        <div class="container">
            <h2 class="static-page-heading text-center mb-4">Why Readers Choose Us</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="static-page-feature">
                        <div class="static-page-feature-icon">
                            <i class="fas fa-mobile-screen-button"></i>
                        </div>
                        <h3>Read Anywhere</h3>
                        <p>Responsive pages that work well on desktop, tablet, and mobile devices.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="static-page-feature">
                        <div class="static-page-feature-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h3>Organized Library</h3>
                        <p>Filter by New, Popular, or Last Update to stay on top of trending titles.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="static-page-feature">
                        <div class="static-page-feature-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h3>Built for Readers</h3>
                        <p>A focused reading experience without clutter — just the story you came for.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
