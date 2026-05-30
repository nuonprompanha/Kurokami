@php
    $hasSeriesDetails = filled($manhwa->alternative_titles)
        || filled($manhwa->author)
        || filled($manhwa->artist)
        || filled($manhwa->status)
        || filled($manhwa->series_type)
        || $manhwa->genres->isNotEmpty()
        || ! empty($manhwa->tags)
        || (empty($compact) && filled($manhwa->badge));
@endphp

@if ($hasSeriesDetails)
    @if (! empty($compact))
        <div class="manhwa-card-series">
            <p class="manhwa-card-series-title">Series details</p>
            <ul class="manhwa-card-series-list">
                @if ($manhwa->alternative_titles)
                    <li>
                        <span class="manhwa-card-series-label">Alt. titles</span>
                        <span class="manhwa-card-series-value">{{ Str::limit($manhwa->alternative_titles, 60) }}</span>
                    </li>
                @endif
                @if ($manhwa->author)
                    <li>
                        <span class="manhwa-card-series-label">Author</span>
                        <span class="manhwa-card-series-value">{{ $manhwa->author }}</span>
                    </li>
                @endif
                @if ($manhwa->artist)
                    <li>
                        <span class="manhwa-card-series-label">Artist</span>
                        <span class="manhwa-card-series-value">{{ $manhwa->artist }}</span>
                    </li>
                @endif
                @if ($manhwa->status)
                    <li>
                        <span class="manhwa-card-series-label">Status</span>
                        <span class="manhwa-card-series-value manhwa-card-status">{{ $manhwa->status }}</span>
                    </li>
                @endif
                @if ($manhwa->series_type)
                    <li>
                        <span class="manhwa-card-series-label">Type</span>
                        <span class="manhwa-card-series-value">{{ $manhwa->series_type }}</span>
                    </li>
                @endif
                @if ($manhwa->genres->isNotEmpty())
                    <li>
                        <span class="manhwa-card-series-label">Genres</span>
                        <span class="manhwa-card-series-value">{{ $manhwa->genres->pluck('name')->take(4)->join(', ') }}</span>
                    </li>
                @endif
                @if (! empty($manhwa->tags))
                    <li>
                        <span class="manhwa-card-series-label">Tags</span>
                        <span class="manhwa-card-series-value">{{ Str::limit(implode(', ', $manhwa->tags), 50) }}</span>
                    </li>
                @endif
            </ul>
        </div>
    @else
        <div class="manhwa-detail-info manhwa-series-details">
            <h2 class="manhwa-detail-info-title">Series details</h2>
            <dl class="manhwa-detail-info-list">
                @if ($manhwa->alternative_titles)
                    <div class="manhwa-detail-info-row">
                        <dt>Alternative titles</dt>
                        <dd>{{ $manhwa->alternative_titles }}</dd>
                    </div>
                @endif
                @if ($manhwa->author)
                    <div class="manhwa-detail-info-row">
                        <dt>Author</dt>
                        <dd>{{ $manhwa->author }}</dd>
                    </div>
                @endif
                @if ($manhwa->artist)
                    <div class="manhwa-detail-info-row">
                        <dt>Artist</dt>
                        <dd>{{ $manhwa->artist }}</dd>
                    </div>
                @endif
                @if ($manhwa->status)
                    <div class="manhwa-detail-info-row">
                        <dt>Status</dt>
                        <dd>
                            <span class="manhwa-detail-status manhwa-detail-status-{{ Str::slug($manhwa->status) }}">
                                {{ $manhwa->status }}
                            </span>
                        </dd>
                    </div>
                @endif
                @if ($manhwa->series_type)
                    <div class="manhwa-detail-info-row">
                        <dt>Type</dt>
                        <dd>{{ $manhwa->series_type }}</dd>
                    </div>
                @endif
                @if ($manhwa->badge)
                    <div class="manhwa-detail-info-row">
                        <dt>Badge</dt>
                        <dd>{{ $manhwa->badge }}</dd>
                    </div>
                @endif
                @if ($manhwa->genres->isNotEmpty())
                    <div class="manhwa-detail-info-row">
                        <dt>Genres</dt>
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
                    <div class="manhwa-detail-info-row">
                        <dt>Tags</dt>
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
    @endif
@endif
