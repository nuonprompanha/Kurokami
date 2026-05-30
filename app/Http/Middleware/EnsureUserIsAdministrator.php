<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdministrator
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdministrator()) {
            return $next($request);
        }

        if ($request->user()?->isEditor()) {
            return redirect()
                ->route('admin.manhwas.index')
                ->withErrors(['message' => 'You only have permission to manage Manhwa.']);
        }

        abort(403, 'Only administrators can perform this action.');
    }
}
