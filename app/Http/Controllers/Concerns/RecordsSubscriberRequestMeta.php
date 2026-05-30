<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait RecordsSubscriberRequestMeta
{
    protected function ensureSubscriber(Request $request): void
    {
        abort_unless($request->user()?->isSubscriber(), 403);
    }

    protected function ensureCanComment(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->isSubscriber() || $user->canAccessAdminPanel()),
            403
        );
    }

    /**
     * @return array{ip_address: ?string, user_agent: ?string, page_url: ?string, referer: ?string}
     */
    protected function requestMeta(Request $request): array
    {
        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'page_url' => $request->input('page_url', $request->headers->get('referer')),
            'referer' => $request->headers->get('referer'),
        ];
    }
}
