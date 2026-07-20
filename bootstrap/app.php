<?php

use App\Http\Middleware\ApiKeyAuth;
use App\Http\Middleware\BlockIfInstallerPresent;
use App\Http\Middleware\MaintenanceModeMiddleware;
use App\Http\Middleware\ProcessOnVisitMiddleware;
use App\Http\Middleware\RateLimitPublic;
use App\Http\Middleware\RequireTwoFactor;
use App\Http\Middleware\TrackUserSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: null,
        then: function () {
            // Webhook + cron: tanpa CSRF/session
            \Illuminate\Support\Facades\Route::middleware([])
                ->group(base_path('routes/webhooks.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [BlockIfInstallerPresent::class]);
        $middleware->alias([
            'jm.ratelimit'   => RateLimitPublic::class,
            'jm.2fa'         => RequireTwoFactor::class,
            'jm.apikey'      => ApiKeyAuth::class,
            'jm.maintenance' => MaintenanceModeMiddleware::class,
            'jm.session'     => TrackUserSession::class,
            'jm.tick'        => ProcessOnVisitMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',   // signature verify menggantikan CSRF di jalur webhook
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
