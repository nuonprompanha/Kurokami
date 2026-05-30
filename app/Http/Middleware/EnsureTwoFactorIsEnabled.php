<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsEnabled
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->canAccessAdminPanel() && ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.profile.show', ['tab' => 'security'])
                ->with('warning', 'Google Authenticator setup is required before you can access the admin panel.');
        }

        return $next($request);
    }
}
