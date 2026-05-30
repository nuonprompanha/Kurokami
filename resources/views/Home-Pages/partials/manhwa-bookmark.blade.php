<div class="manhwa-detail-bookmark">
    @auth
        <button
            type="button"
            class="manhwa-bookmark-btn {{ $isBookmarked ? 'is-bookmarked' : '' }}"
            data-bookmark-toggle
            data-bookmark-url="{{ route('manhwa.bookmark.toggle', $manhwa) }}"
            data-bookmark-title="{{ $manhwa->title }}"
            aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}"
            aria-label="{{ $isBookmarked ? 'Remove '.$manhwa->title.' from bookmarks' : 'Bookmark '.$manhwa->title }}"
        >
            <i class="{{ $isBookmarked ? 'fa-solid' : 'fa-regular' }} fa-bookmark" aria-hidden="true"></i>
            <span class="manhwa-bookmark-btn-text">{{ $isBookmarked ? 'Bookmarked' : 'Bookmark' }}</span>
        </button>
    @else
        <a
            href="{{ route('login') }}"
            class="manhwa-bookmark-btn"
            aria-label="Login to bookmark {{ $manhwa->title }}"
        >
            <i class="fa-regular fa-bookmark" aria-hidden="true"></i>
            <span class="manhwa-bookmark-btn-text">Bookmark</span>
        </a>
    @endauth
</div>
