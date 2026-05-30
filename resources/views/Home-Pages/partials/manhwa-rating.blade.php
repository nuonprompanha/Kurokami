<div
    class="manhwa-rating"
    data-manhwa-rating
    data-rating-scope="detail"
    data-rating-url="{{ route('manhwa.rate.store', $manhwa) }}"
    data-user-score="{{ $userRating ?? '' }}"
>
    <div class="manhwa-rating-summary">
        <span class="manhwa-rating-average" data-rating-average>{{ $manhwa->formattedAverageRating() }}</span>
        <span class="manhwa-rating-summary-text">
            / 5
            <span class="manhwa-rating-count-wrap">
                (<span data-rating-count>{{ number_format($manhwa->ratings_count) }}</span>
                <span data-rating-count-label>{{ $manhwa->ratings_count === 1 ? 'rating' : 'ratings' }}</span>)
            </span>
        </span>
    </div>

    @auth
        @if (auth()->user()->isSubscriber())
            <div class="manhwa-rating-interactive">
                <span class="manhwa-rating-label">Your rating:</span>
                <div
                    class="manhwa-rating-stars"
                    role="radiogroup"
                    aria-label="Rate {{ $manhwa->title }}"
                >
                    @for ($star = 1; $star <= 5; $star++)
                        <button
                            type="button"
                            class="manhwa-rating-star"
                            data-rating-star="{{ $star }}"
                            aria-label="Rate {{ $star }} out of 5"
                            @if (($userRating ?? 0) >= $star) aria-checked="true" @else aria-checked="false" @endif
                        >
                            <i class="{{ ($userRating ?? 0) >= $star ? 'fa-solid' : 'fa-regular' }} fa-star" aria-hidden="true"></i>
                        </button>
                    @endfor
                </div>
            </div>
        @else
            <p class="manhwa-rating-hint">Sign in as a reader to rate this series.</p>
        @endif
    @else
        <p class="manhwa-rating-hint">
            <a href="{{ route('login') }}">Sign in with Google</a> to rate this series.
        </p>
    @endauth
</div>
