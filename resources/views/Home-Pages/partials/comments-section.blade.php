<section class="comments-section" id="comments" data-comments-section>
    <div class="comments-section-head">
        <h2 class="manhwa-detail-block-title">
            <i class="fa-solid fa-comments" aria-hidden="true"></i>
            Comments
            <span class="comments-section-count" data-comments-count>({{ number_format($commentsTotal ?? $comments->count()) }})</span>
        </h2>
    </div>

    <div class="comments-alert comments-alert-success" data-comments-success hidden role="status"></div>

    @auth
        @if (auth()->user()->isSubscriber() || auth()->user()->canAccessAdminPanel())
            <form
                action="{{ $storeUrl }}"
                method="POST"
                class="comments-form"
                data-comment-form
            >
                @csrf
                <input type="hidden" name="page_url" value="{{ url()->current() }}">
                <label class="comments-form-label" for="comment-body-{{ $formId }}">Add a comment</label>
                <textarea
                    id="comment-body-{{ $formId }}"
                    name="body"
                    class="comments-form-textarea"
                    data-comment-body
                    rows="4"
                    maxlength="2000"
                    placeholder="Share your thoughts..."
                    required
                ></textarea>
                <p class="comments-form-error" data-comment-error hidden></p>
                <button type="submit" class="comments-form-submit" data-comment-submit>
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    Post comment
                </button>
            </form>
        @endif
    @else
        <p class="comments-login-hint">
            <a href="{{ route('login') }}">Sign in with Google</a> to leave a comment.
        </p>
    @endauth

    <ul class="comments-list" data-comments-list>
        @forelse ($comments as $comment)
            @include('Home-Pages.partials.comment-item', [
                'comment' => $comment,
                'storeUrl' => $storeUrl,
            ])
        @empty
            <li class="comments-empty" data-comments-empty>No comments yet. Be the first to share your thoughts.</li>
        @endforelse
    </ul>
</section>
