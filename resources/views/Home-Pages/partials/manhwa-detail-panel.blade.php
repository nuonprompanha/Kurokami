@php
    $chapterCount = $manhwa->published_chapters_count ?? $manhwa->chapters->count();
@endphp

<div class="manhwa-detail-panel">
    <div class="manhwa-detail-panel-head">
        <h2 class="manhwa-detail-panel-title">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            Manhwa details
        </h2>
        <div class="manhwa-detail-panel-stats">
            <span class="manhwa-detail-panel-stat">
                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                {{ number_format($manhwa->views) }} views
            </span>
            @if ($chapterCount > 0)
                <span class="manhwa-detail-panel-stat">
                    <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                    {{ $chapterCount }} {{ $chapterCount === 1 ? 'chapter' : 'chapters' }}
                </span>
            @endif
        </div>
    </div>

    <dl class="manhwa-detail-panel-list">
        <div class="manhwa-detail-panel-row">
            <dt><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Category</dt>
            <dd><span class="manhwa-detail-panel-pill">{{ $manhwa->category }}</span></dd>
        </div>
        @if ($manhwa->badge)
            <div class="manhwa-detail-panel-row">
                <dt><i class="fa-solid fa-certificate" aria-hidden="true"></i> Badge</dt>
                <dd><span class="manhwa-detail-panel-pill manhwa-detail-panel-pill-badge">{{ $manhwa->badge }}</span></dd>
            </div>
        @endif
        @if ($manhwa->status)
            <div class="manhwa-detail-panel-row">
                <dt><i class="fa-solid fa-signal" aria-hidden="true"></i> Status</dt>
                <dd>
                    <span class="manhwa-detail-status manhwa-detail-status-{{ Str::slug($manhwa->status) }}">
                        {{ $manhwa->status }}
                    </span>
                </dd>
            </div>
        @endif
        @if ($manhwa->series_type)
            <div class="manhwa-detail-panel-row">
                <dt><i class="fa-solid fa-book" aria-hidden="true"></i> Type</dt>
                <dd>{{ $manhwa->series_type }}</dd>
            </div>
        @endif
        @if ($manhwa->author)
            <div class="manhwa-detail-panel-row">
                <dt><i class="fa-solid fa-pen-nib" aria-hidden="true"></i> Author</dt>
                <dd>{{ $manhwa->author }}</dd>
            </div>
        @endif
        @if ($manhwa->artist)
            <div class="manhwa-detail-panel-row">
                <dt><i class="fa-solid fa-palette" aria-hidden="true"></i> Artist</dt>
                <dd>{{ $manhwa->artist }}</dd>
            </div>
        @endif
        @if (filled($manhwa->alternative_titles))
            <div class="manhwa-detail-panel-row manhwa-detail-panel-row-stack">
                <dt><i class="fa-solid fa-tags" aria-hidden="true"></i> Alternative titles</dt>
                <dd>{{ $manhwa->alternative_titles }}</dd>
            </div>
        @endif
        @if ($manhwa->genres->isNotEmpty())
            <div class="manhwa-detail-panel-row manhwa-detail-panel-row-stack">
                <dt><i class="fa-solid fa-masks-theater" aria-hidden="true"></i> Genres</dt>
                <dd>
                    <div class="manhwa-detail-tags">
                        @foreach ($manhwa->genres as $genre)
                            <span class="manhwa-detail-tag manhwa-detail-tag-genre">{{ $genre->name }}</span>
                        @endforeach
                    </div>
                </dd>
            </div>
        @endif
        @if (! empty($manhwa->tags))
            <div class="manhwa-detail-panel-row manhwa-detail-panel-row-stack">
                <dt><i class="fa-solid fa-hashtag" aria-hidden="true"></i> Tags</dt>
                <dd>
                    <div class="manhwa-detail-tags">
                        @foreach ($manhwa->tags as $tag)
                            <span class="manhwa-detail-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                </dd>
            </div>
        @endif
    </dl>
</div>
