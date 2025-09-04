<?php

use App\Http\Middleware\CrmAccessControl;
use App\Http\Middleware\DemoSessionBootstrap;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'crm.access' => CrmAccessControl::class,
        ]);

        // Apply demo token bootstrap to ALL API routes when APP_DEMO=true
        $middleware->priority([
            StartSession::class,
            DemoSessionBootstrap::class,
        ]);

        $middleware->api([
            StartSession::class,
            DemoSessionBootstrap::class,
        ]);

        // Configure throttling middleware
        $middleware->throttleApi();
    })
    ->booted(function (Application $app) {
        // Define custom rate limiters after app is booted
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ? : $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
