@php
    $reactionCounts = $comment->reactionCountsByType();
    $userReaction = auth()->check() ? $comment->userReactionType(auth()->user()) : null;
@endphp

<div
    class="comments-reactions"
    data-comment-reactions
    data-comment-react-url="{{ route('comments.react', $comment) }}"
    data-user-reaction="{{ $userReaction ?? '' }}"
>
    @foreach (\App\Models\CommentReaction::types() as $type => $meta)
        @php
            $count = $reactionCounts[$type] ?? 0;
            $isActive = $userReaction === $type;
        @endphp
        <button
            type="button"
            class="comments-reaction-btn {{ $isActive ? 'is-active' : '' }}"
            data-comment-react
            data-reaction-type="{{ $type }}"
            aria-pressed="{{ $isActive ? 'true' : 'false' }}"
            aria-label="{{ $meta['label'] }} ({{ $count }})"
            @guest disabled @endguest
        >
            <i class="fa-solid {{ $meta['icon'] }}" aria-hidden="true"></i>
            <span data-reaction-count="{{ $type }}">{{ $count > 0 ? $count : '' }}</span>
        </button>
    @endforeach
</div>
