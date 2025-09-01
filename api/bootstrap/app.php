<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        then: function () {
            // Rate limiters (you already added)
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute((int) env('RATE_LIMIT_API', 120))
                ->by(optional($request->user())->id ?: $request->ip());
            });
            RateLimiter::for('admin-api', function (Request $request) {
                return Limit::perMinute((int) env('RATE_LIMIT_ADMIN', 60))
                ->by('admin:' . (optional($request->user())->id ?: $request->ip()));
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Spatie aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,

        ]);

        // Our API middlewares
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\ClampPerPage::class,
            \App\Http\Middleware\RequestId::class,
            \App\Http\Middleware\VersionHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // keep defaults; Handler.php renderables format JSON errors
    })
    ->create();
