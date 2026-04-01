<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\NgrokOverHttps;
use App\Http\Middleware\TrustPlatformProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
        
        // Respect HTTPS/host forwarded by Railway and other reverse proxies.
        $middleware->append(TrustPlatformProxies::class);
        $middleware->append(NgrokOverHttps::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
