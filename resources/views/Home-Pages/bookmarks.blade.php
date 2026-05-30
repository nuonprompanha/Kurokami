@extends('Layouts.Home_Layouts')

@section('title', 'My Bookmarks')

@section('content')
    <section class="manhwa-section">
        <div class="container">
            <div class="manhwa-section-header manhwa-section-header-stack">
                <div>
                    <h2 class="manhwa-section-title">My Bookmarks</h2>
                    <p class="manhwa-section-subtitle">Series you saved while signed in</p>
                </div>
            </div>

            @include('Home-Pages.partials.manhwa-grid', [
                'manhwas' => $manhwas,
                'emptyMessage' => 'You have not bookmarked any series yet. Open a manhwa and tap Bookmark to save it here.',
            ])
        </div>
    </section>
@endsection
