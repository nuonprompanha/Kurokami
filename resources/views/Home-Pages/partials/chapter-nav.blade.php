<div class="chapter-read-nav{{ isset($navClass) ? ' '.$navClass : '' }}">
    @if ($prevChapter)
        <a
            href="{{ route('chapter.show', ['manhwa' => $manhwa->slug, 'chapterNumber' => $prevChapter->chapter_number]) }}"
            class="chapter-nav-btn"
            {!! $manhwa->adultLinkAttributes() !!}
        >
            <i class="fa-solid fa-chevron-left"></i> Ch. {{ $prevChapter->chapter_number }}
        </a>
    @else
        <span class="chapter-nav-btn chapter-nav-btn-disabled">Prev</span>
    @endif

    <a
        href="{{ route('manhwa.show', $manhwa) }}"
        class="chapter-nav-btn chapter-nav-btn-list"
        {!! $manhwa->adultLinkAttributes() !!}
    >
        All Chapters
    </a>

    @if ($nextChapter)
        <a
            href="{{ route('chapter.show', ['manhwa' => $manhwa->slug, 'chapterNumber' => $nextChapter->chapter_number]) }}"
            class="chapter-nav-btn"
            {!! $manhwa->adultLinkAttributes() !!}
        >
            Ch. {{ $nextChapter->chapter_number }} <i class="fa-solid fa-chevron-right"></i>
        </a>
    @else
        <span class="chapter-nav-btn chapter-nav-btn-disabled">Next</span>
    @endif
</div>
