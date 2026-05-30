@php
    $isReply = $isReply ?? $comment->isReply();
    $canComment = auth()->check() && (auth()->user()->isSubscriber() || auth()->user()->canAccessAdminPanel());
@endphp

<li
    @class([
        'comments-item',
        'comments-item-reply' => $isReply,
    ])
    data-comment-id="{{ $comment->id }}"
    @if ($comment->editableBy(auth()->user()))
        data-comment-update-url="{{ route('comments.update', $comment) }}"
    @endif
    @if (auth()->check() && auth()->user()->isAdministrator())
        data-comment-delete-url="{{ route('comments.destroy', $comment) }}"
    @endif
>
    <div class="comments-item-head">
        <img
            src="{{ $comment->user->avatarUrl() }}"
            alt="{{ $comment->user->commentAuthorName() }}"
            class="comments-item-avatar"
            width="40"
            height="40"
        >
        <div class="comments-item-meta">
            <strong @class([
                'comments-item-author',
                'comments-item-author-staff' => $comment->user->showsDepartmentOnComments(),
            ])>
                @if ($comment->user->showsDepartmentOnComments())
                    <span class="comments-item-department">{{ $comment->user->commentAuthorName() }}</span>
                @else
                    {{ $comment->user->commentAuthorName() }}
                @endif
            </strong>
            <time class="comments-item-date" datetime="{{ $comment->created_at->toIso8601String() }}">
                {{ $comment->created_at->diffForHumans() }}
                @if ($comment->wasEdited())
                    <span class="comments-item-edited">(edited)</span>
                @endif
            </time>
        </div>
        @if ($comment->editableBy(auth()->user()) || (auth()->check() && auth()->user()->isAdministrator()))
            <div class="comments-item-actions">
                @if ($comment->editableBy(auth()->user()))
                    <button type="button" class="comments-item-action-btn" data-comment-edit>
                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        Edit
                    </button>
                @endif
                @if (auth()->check() && auth()->user()->isAdministrator())
                    <button type="button" class="comments-item-action-btn comments-item-action-btn-danger" data-comment-delete>
                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        Delete
                    </button>
                @endif
            </div>
        @endif
    </div>
    <p class="comments-item-body" data-comment-body-display>{{ $comment->body }}</p>
    @if ($comment->editableBy(auth()->user()))
        <div class="comments-item-edit" data-comment-edit-panel hidden>
            <textarea
                class="comments-form-textarea comments-item-edit-textarea"
                data-comment-edit-body
                rows="3"
                maxlength="2000"
            >{{ $comment->body }}</textarea>
            <p class="comments-form-error comments-item-edit-error" data-comment-edit-error hidden></p>
            <div class="comments-item-edit-actions">
                <button type="button" class="comments-form-submit" data-comment-save>
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    Save
                </button>
                <button type="button" class="comments-item-action-btn" data-comment-cancel>Cancel</button>
            </div>
        </div>
    @endif

    <div class="comments-item-footer">
        @if ($canComment && ! $isReply)
            <button type="button" class="comments-item-action-btn" data-comment-reply>
                <i class="fa-solid fa-reply" aria-hidden="true"></i>
                Reply
            </button>
        @endif
        @include('Home-Pages.partials.comment-reactions', ['comment' => $comment])
    </div>

    @if ($canComment && ! $isReply)
        <div class="comments-reply-form" data-comment-reply-panel hidden>
            <textarea
                class="comments-form-textarea comments-reply-textarea"
                data-comment-reply-body
                rows="2"
                maxlength="2000"
                placeholder="Write a reply..."
            ></textarea>
            <p class="comments-form-error comments-reply-error" data-comment-reply-error hidden></p>
            <div class="comments-reply-actions">
                <button
                    type="button"
                    class="comments-form-submit"
                    data-comment-reply-submit
                    data-comment-reply-url="{{ $storeUrl }}"
                    data-parent-id="{{ $comment->id }}"
                >
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    Post reply
                </button>
                <button type="button" class="comments-item-action-btn" data-comment-reply-cancel>Cancel</button>
            </div>
        </div>

        <ul class="comments-replies" data-comment-replies="{{ $comment->id }}">
            @foreach ($comment->replies as $reply)
                @include('Home-Pages.partials.comment-item', [
                    'comment' => $reply,
                    'storeUrl' => $storeUrl,
                    'isReply' => true,
                ])
            @endforeach
        </ul>
    @endif
</li>
