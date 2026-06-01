<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'administrator' => \App\Http\Middleware\EnsureUserIsAdministrator::class,
            'two_factor' => \App\Http\Middleware\EnsureTwoFactorIsEnabled::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('admin/login') || $request->is('admin/login/two-factor') || $request->is('admin/login/two-factor/setup')) {
                return $request->user()?->adminHomeRoute() ?? route('admin.dashboard');
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $exception, Request $request) {
            $limit = ini_get('post_max_size') ?: 'unknown';
            $message = "Upload too large for PHP (post_max_size is {$limit}). "
                .'Restart the server with: composer serve - or increase post_max_size and upload_max_filesize in php.ini.';

            if ($request->is('admin/*')) {
                return redirect()->back()->withInput()->withErrors(['chapters_zip' => $message]);
            }

            return redirect()->back()->withInput()->withErrors(['upload' => $message]);
        });
    })->create();
