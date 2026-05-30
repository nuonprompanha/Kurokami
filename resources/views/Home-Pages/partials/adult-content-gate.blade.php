@if ($manhwa->is_adult_content)
    <div
        id="adult-content-gate"
        class="adult-content-gate"
        data-adult-slug="{{ $manhwa->slug }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adult-content-gate-title"
        hidden
    >
        <div class="adult-content-gate-panel">
            <span class="adult-content-gate-badge">18+</span>
            <h2 id="adult-content-gate-title" class="adult-content-gate-title">Adult content warning</h2>
            <p class="adult-content-gate-text">
                <strong>{{ $manhwa->title }}</strong> contains mature content intended for adults only.
                You must be 18 years or older to continue reading.
            </p>
            <div class="adult-content-gate-actions">
                <a href="{{ route('home') }}" class="adult-content-gate-btn adult-content-gate-btn-back">Go back</a>
                <button type="button" class="adult-content-gate-btn adult-content-gate-btn-confirm" data-adult-confirm>
                    I am 18+, continue reading
                </button>
            </div>
        </div>
    </div>
@endif
