@php
    $shareUrl = route('manhwa.show', $manhwa, absolute: true);
    $facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u='.urlencode($shareUrl);
@endphp

<div class="manhwa-detail-share">
    <span class="manhwa-detail-share-label">Share</span>
    <div class="manhwa-detail-share-actions">
        <a
            href="{{ $facebookShareUrl }}"
            class="manhwa-share-btn manhwa-share-btn-facebook"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Share {{ $manhwa->title }} on Facebook"
        >
            <i class="fab fa-facebook-f" aria-hidden="true"></i>
            Facebook
        </a>
        <button
            type="button"
            class="manhwa-share-btn manhwa-share-btn-copy"
            data-copy-share-url="{{ $shareUrl }}"
            aria-label="Copy link to {{ $manhwa->title }}"
        >
            <i class="fa-solid fa-link" aria-hidden="true"></i>
            <span class="manhwa-share-btn-copy-text">Copy link</span>
        </button>
    </div>
</div>
