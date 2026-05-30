@extends('Layouts.Home_Layouts')
@section('title', $manhwa->title)
@section('content')
    @include('Home-Pages.partials.adult-content-gate', ['manhwa' => $manhwa])

    <section class="manhwa-detail-section">
        <div class="container">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <span>{{ $manhwa->title }}</span>
            </nav>

            <article class="manhwa-detail-hero">
                <div class="row g-4 align-items-start">
                    <div class="col-md-4 col-lg-3">
                        <div class="manhwa-cover-wrap manhwa-detail-cover-wrap">
                            <img
                                src="{{ $manhwa->coverUrl() }}"
                                alt="{{ $manhwa->title }}"
                                class="manhwa-detail-cover"
                            >
                            @include('Home-Pages.partials.manhwa-cover-adult-overlay', ['manhwa' => $manhwa])
                            @if ($manhwa->badge)
                                <span class="manhwa-detail-cover-badge">{{ $manhwa->badge }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-8 col-lg-9 manhwa-detail-main">
                        <header class="manhwa-detail-header">
                            <span class="manhwa-detail-category-pill">{{ $manhwa->category }}</span>
                            <h1 class="manhwa-detail-title">{{ $manhwa->title }}</h1>
                            <div class="manhwa-detail-meta">
                                @if ($manhwa->badge)
                                    <span class="manhwa-detail-meta-badge">{{ $manhwa->badge }}</span>
                                @endif
                                @if ($manhwa->series_type)
                                    <span class="manhwa-detail-meta-chip">{{ $manhwa->series_type }}</span>
                                @endif
                                <span class="manhwa-detail-meta-chip">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                    {{ number_format($manhwa->views) }} views
                                </span>
                                <span class="manhwa-detail-meta-chip">
                                    <i class="fa-regular fa-heart" aria-hidden="true"></i>
                                    <span data-like-count-display>{{ number_format($manhwa->likes_count) }}</span> likes
                                </span>
                                <span class="manhwa-detail-meta-chip">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    <span data-rating-average-display>{{ $manhwa->formattedAverageRating() }}</span>/5
                                    (<span data-rating-count-display>{{ number_format($manhwa->ratings_count) }}</span>)
                                </span>
                            </div>
                        </header>

                        <div class="manhwa-detail-actions">
                            @include('Home-Pages.partials.manhwa-bookmark', ['manhwa' => $manhwa, 'isBookmarked' => $isBookmarked])
                            @include('Home-Pages.partials.engagement-like', [
                                'toggleUrl' => route('manhwa.like.toggle', $manhwa),
                                'isLiked' => $isLiked,
                                'likesCount' => $manhwa->likes_count,
                                'countId' => 'manhwa-like-count',
                                'label' => 'Like',
                            ])
                            @include('Home-Pages.partials.manhwa-share', ['manhwa' => $manhwa])

                            <div class="manhwa-detail-rating-wrap">
                                <h2 class="manhwa-detail-rating-title">
                                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    Rate this series
                                </h2>
                                @include('Home-Pages.partials.manhwa-rating', [
                                    'manhwa' => $manhwa,
                                    'userRating' => $userRating,
                                ])
                            </div>
                        </div>

                        @include('Home-Pages.partials.manhwa-detail-panel', ['manhwa' => $manhwa])

                        @if ($manhwa->description)
                            <div class="manhwa-detail-synopsis">
                                <h2 class="manhwa-detail-block-title">
                                    <i class="fa-solid fa-align-left" aria-hidden="true"></i>
                                    Synopsis
                                </h2>
                                <p class="manhwa-detail-desc">{{ $manhwa->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </article>

            <section class="manhwa-detail-chapters-block">
                <div class="manhwa-detail-chapters-head">
                    <h2 class="manhwa-detail-block-title">
                        <i class="fa-solid fa-list" aria-hidden="true"></i>
                        Chapters
                        @if ($manhwa->chapters->isNotEmpty())
                            <span class="manhwa-detail-chapter-count">({{ $manhwa->chapters->count() }})</span>
                        @endif
                    </h2>
                </div>

                <ul class="manhwa-chapter-list">
                    @forelse ($manhwa->chapters as $chapter)
                        <li class="manhwa-chapter-item">
                            <a
                                href="{{ route('chapter.show', ['manhwa' => $manhwa->slug, 'chapterNumber' => $chapter->chapter_number]) }}"
                                class="manhwa-chapter-link"
                                {!! $manhwa->adultLinkAttributes() !!}
                            >
                                <span class="manhwa-chapter-title">{{ $chapter->displayTitle() }}</span>
                                <i class="fa-solid fa-chevron-right manhwa-chapter-arrow" aria-hidden="true"></i>
                            </a>
                        </li>
                    @empty
                        <li class="manhwa-chapter-empty">No chapters uploaded yet for this manhwa.</li>
                    @endforelse
                </ul>
            </section>

            @include('Home-Pages.partials.comments-section', [
                'comments' => $comments,
                'commentsTotal' => $manhwa->comments_count,
                'storeUrl' => route('manhwa.comments.store', $manhwa),
                'formId' => 'manhwa',
            ])
        </div>
    </section>
@endsection
