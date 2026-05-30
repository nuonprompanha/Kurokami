@extends('Layouts.Home_Layouts')
@section('title', $manhwa->title.' - '.$chapter->displayTitle())
@section('content')
    @include('Home-Pages.partials.adult-content-gate', ['manhwa' => $manhwa])

    <section class="chapter-read-section">
        <div class="container">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <a href="{{ route('manhwa.show', $manhwa) }}">{{ $manhwa->title }}</a>
                <span>/</span>
                <span>{{ $chapter->displayTitle() }}</span>
            </nav>

            <div class="chapter-read-header">
                <h1 class="chapter-read-title">{{ $manhwa->title }}</h1>
                @if ($chapter->hasDistinctTitle())
                    <p class="chapter-read-subtitle">Chapter {{ $chapter->chapter_number }} — {{ $chapter->title }}</p>
                @endif
                @if ($manhwa->author || $manhwa->status || $manhwa->series_type)
                    <p class="chapter-read-series-meta">
                        @if ($manhwa->author)
                            <span><strong>Author:</strong> {{ $manhwa->author }}</span>
                        @endif
                        @if ($manhwa->status)
                            <span><strong>Status:</strong> {{ $manhwa->status }}</span>
                        @endif
                        @if ($manhwa->series_type)
                            <span><strong>Type:</strong> {{ $manhwa->series_type }}</span>
                        @endif
                    </p>
                @endif
                <div class="chapter-read-actions">
                    @include('Home-Pages.partials.engagement-like', [
                        'toggleUrl' => route('chapter.like.toggle', ['manhwa' => $manhwa, 'chapterNumber' => $chapter->chapter_number]),
                        'isLiked' => $isLiked,
                        'likesCount' => $chapter->likes_count,
                        'countId' => 'chapter-like-count',
                        'label' => 'Like chapter',
                    ])
                </div>
            </div>

            @include('Home-Pages.partials.chapter-nav')

            <div class="chapter-read-pages" id="chapter-read-pages">
                @forelse ($chapter->pages as $page)
                    <div class="chapter-read-page-wrap">
                        <img
                            src="{{ $page->url() }}"
                            alt="{{ $manhwa->title }} — Ch. {{ $chapter->chapter_number }} — Page {{ $page->sort_order }}"
                            class="chapter-read-page-img"
                            loading="lazy"
                            draggable="false"
                        >
                        <div class="chapter-read-page-shield" aria-hidden="true"></div>
                    </div>
                @empty
                    <div class="chapter-read-content">
                        <p>No pages uploaded yet for this chapter.</p>
                    </div>
                @endforelse
            </div>

            @include('Home-Pages.partials.chapter-nav', ['navClass' => 'chapter-read-nav-bottom'])

            @include('Home-Pages.partials.comments-section', [
                'comments' => $comments,
                'commentsTotal' => $chapter->comments_count,
                'storeUrl' => route('chapter.comments.store', ['manhwa' => $manhwa, 'chapterNumber' => $chapter->chapter_number]),
                'formId' => 'chapter-'.$chapter->chapter_number,
            ])
        </div>
    </section>

    <button
        type="button"
        class="chapter-go-top-btn"
        id="chapter-go-top-btn"
        aria-label="Go to top"
        title="Go to top"
    >
        <i class="fa-solid fa-arrow-up"></i>
    </button>
@endsection
