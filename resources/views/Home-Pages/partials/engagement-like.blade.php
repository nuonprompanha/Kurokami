@php
    $countId = $countId ?? 'like-count';
    $label = $label ?? 'Like';
@endphp

<div class="engagement-like" data-like-scope>
    @auth
        @if (auth()->user()->isSubscriber())
            <button
                type="button"
                class="engagement-like-btn {{ ($isLiked ?? false) ? 'is-liked' : '' }}"
                data-like-toggle
                data-like-url="{{ $toggleUrl }}"
                data-like-label="{{ $label }}"
                aria-pressed="{{ ($isLiked ?? false) ? 'true' : 'false' }}"
                aria-label="{{ ($isLiked ?? false) ? 'Unlike' : 'Like' }}"
            >
                <i class="{{ ($isLiked ?? false) ? 'fa-solid' : 'fa-regular' }} fa-heart" aria-hidden="true"></i>
                <span class="engagement-like-btn-text">{{ ($isLiked ?? false) ? 'Liked' : $label }}</span>
                <span class="engagement-like-count" id="{{ $countId }}" data-like-count>{{ number_format($likesCount ?? 0) }}</span>
            </button>
        @else
            <span class="engagement-like-guest">
                <i class="fa-regular fa-heart" aria-hidden="true"></i>
                <span data-like-count>{{ number_format($likesCount ?? 0) }}</span> likes
            </span>
        @endif
    @else
        <a href="{{ route('login') }}" class="engagement-like-btn engagement-like-btn-guest" aria-label="Login to like">
            <i class="fa-regular fa-heart" aria-hidden="true"></i>
            <span class="engagement-like-btn-text">{{ $label }}</span>
            <span class="engagement-like-count" data-like-count>{{ number_format($likesCount ?? 0) }}</span>
        </a>
    @endauth
</div>
