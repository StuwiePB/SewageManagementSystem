<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureCustomerNameInUrl;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LogoutBeforeLoginSwitch;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/sns',
        ]);

        // Trust Herd Share / tunnel proxies so session & CSRF work when accessing from phone
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            LogoutBeforeLoginSwitch::class,
            EnsureAccountIsActive::class,
            SetLocale::class,
        ]);
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'customer.name' => EnsureCustomerNameInUrl::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
