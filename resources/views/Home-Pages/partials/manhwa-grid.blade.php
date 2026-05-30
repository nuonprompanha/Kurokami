<div class="row g-4">
    @forelse ($manhwas as $manhwa)
        <div class="col-manhwa-6 col-md-4 col-6">
            <article class="manhwa-card">
                <a
                    href="{{ route('manhwa.show', $manhwa) }}"
                    class="manhwa-card-link"
                    {!! $manhwa->adultLinkAttributes() !!}
                >
                    <div class="manhwa-card-cover">
                        <img
                            src="{{ $manhwa->coverUrl() }}"
                            alt="{{ $manhwa->title }}"
                            loading="lazy"
                        >
                        @include('Home-Pages.partials.manhwa-cover-adult-overlay', ['manhwa' => $manhwa])
                        @if ($manhwa->badge)
                            <span @class([
                                'manhwa-badge',
                                'manhwa-badge-new' => $manhwa->badge === 'New',
                                'manhwa-badge-hot' => $manhwa->badge === 'Hot',
                            ])>{{ $manhwa->badge }}</span>
                        @endif
                    </div>
                    <div class="manhwa-card-body">
                        <h3 class="manhwa-card-title">{{ $manhwa->title }}</h3>
                        <p class="manhwa-card-views">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            {{ number_format($manhwa->views) }} views
                            @if (($manhwa->ratings_count ?? 0) > 0)
                                <span class="manhwa-card-rating">
                                    · <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    {{ number_format((float) $manhwa->ratings_avg_score, 1) }}
                                </span>
                            @endif
                            @if ($manhwa->published_chapters_count > 0)
                                <span class="manhwa-card-chapter-count">
                                    · {{ $manhwa->published_chapters_count }} {{ $manhwa->published_chapters_count === 1 ? 'chapter' : 'chapters' }}
                                </span>
                            @endif
                        </p>
                    </div>
                </a>
                @if ($manhwa->chapters->isNotEmpty())
                    <div class="manhwa-card-footer">
                        <div class="manhwa-card-chapters">
                            @foreach ($manhwa->chapters as $chapter)
                                <a
                                    href="{{ route('chapter.show', ['manhwa' => $manhwa->slug, 'chapterNumber' => $chapter->chapter_number]) }}"
                                    class="manhwa-chapter-btn"
                                    {!! $manhwa->adultLinkAttributes() !!}
                                >
                                    {{ $chapter->displayTitle() }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>
        </div>
    @empty
        <div class="col-12">
            <p class="manhwa-empty">{{ $emptyMessage ?? 'No manhwa available yet.' }}</p>
        </div>
    @endforelse
</div>
