<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsSeller;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'seller' => EnsureUserIsSeller::class,
            'auth.api' => \App\Http\Middleware\ApiAuthenticate::class,
            'auth.api.optional' => \App\Http\Middleware\ApiOptionalAuthenticate::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\TrackVisit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The uploaded images exceed the maximum allowed size on this server. Please upload smaller images.',
                ], 413);
            }

            return back()->with('error', 'The uploaded images exceed the maximum allowed size.');
        });
    })->create();
