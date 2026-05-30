<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RecordsSubscriberRequestMeta;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Manhwa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    use RecordsSubscriberRequestMeta;

    public function storeManhwa(Request $request, Manhwa $manhwa): JsonResponse
    {
        $this->ensureCanComment($request);

        $comment = $this->createComment($request, $manhwa);

        return $this->jsonCommentResponse(
            $comment,
            $comment->isReply() ? 'Your reply was posted.' : 'Your comment was posted.',
            $this->storeUrlFor($manhwa)
        );
    }

    public function storeChapter(Request $request, Manhwa $manhwa, int $chapterNumber): JsonResponse
    {
        $this->ensureCanComment($request);

        $chapter = $manhwa->chapters()
            ->where('chapter_number', $chapterNumber)
            ->firstOrFail();

        $comment = $this->createComment($request, $chapter);

        return $this->jsonCommentResponse(
            $comment,
            $comment->isReply() ? 'Your reply was posted.' : 'Your comment was posted.',
            $this->storeUrlFor($chapter)
        );
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        abort_unless($comment->editableBy($request->user()), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $comment->update(['body' => $validated['body']]);

        return $this->jsonCommentResponse(
            $comment,
            'Your comment was updated.',
            $this->storeUrlFor($comment->commentable)
        );
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        abort_unless($request->user()?->isAdministrator(), 403);

        $commentable = $comment->commentable;
        $comment->delete();

        return response()->json([
            'comments_count' => $commentable->comments()->count(),
            'message' => 'Comment deleted.',
        ]);
    }

    public function react(Request $request, Comment $comment): JsonResponse
    {
        $this->ensureCanComment($request);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(CommentReaction::types()))],
        ]);

        $user = $request->user();
        $existing = $comment->reactions()->where('user_id', $user->id)->first();

        if ($existing && $existing->type === $validated['type']) {
            $existing->delete();
            $userReaction = null;
        } elseif ($existing) {
            $existing->update(['type' => $validated['type']]);
            $userReaction = $validated['type'];
        } else {
            $comment->reactions()->create([
                'user_id' => $user->id,
                'type' => $validated['type'],
            ]);
            $userReaction = $validated['type'];
        }

        $comment->load('reactions');

        return response()->json([
            'user_reaction' => $userReaction,
            'reaction_counts' => $comment->reactionCountsByType(),
            'message' => $userReaction ? 'Reaction added.' : 'Reaction removed.',
        ]);
    }

    protected function createComment(Request $request, Model $commentable): Comment
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = Comment::query()->findOrFail($parentId);
            abort_unless($parent->belongsToCommentable($commentable), 403);
            abort_unless($parent->parent_id === null, 422, 'Replies can only be added to top-level comments.');
        }

        return $commentable->comments()->create(array_merge(
            [
                'user_id' => $request->user()->id,
                'parent_id' => $parentId,
                'body' => $validated['body'],
            ],
            $this->requestMeta($request)
        ));
    }

    protected function storeUrlFor(Model $commentable): string
    {
        if ($commentable instanceof Manhwa) {
            return route('manhwa.comments.store', $commentable);
        }

        if ($commentable instanceof Chapter) {
            return route('chapter.comments.store', [
                'manhwa' => $commentable->manhwa,
                'chapterNumber' => $commentable->chapter_number,
            ]);
        }

        abort(404);
    }

    protected function jsonCommentResponse(Comment $comment, string $message, string $storeUrl): JsonResponse
    {
        $comment->load(['user', 'reactions', 'replies.user', 'replies.reactions']);

        $commentable = $comment->commentable;
        $commentsCount = $commentable->comments()->count();

        return response()->json([
            'html' => view('Home-Pages.partials.comment-item', [
                'comment' => $comment,
                'storeUrl' => $storeUrl,
                'isReply' => $comment->isReply(),
            ])->render(),
            'comments_count' => $commentsCount,
            'parent_id' => $comment->parent_id,
            'is_reply' => $comment->isReply(),
            'message' => $message,
        ]);
    }
}
