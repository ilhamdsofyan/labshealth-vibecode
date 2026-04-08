<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\NgrokOverHttps;
use App\Http\Middleware\TrustPlatformProxies;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

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
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            $message = 'Sesi Anda sudah berakhir. Halaman akan dimuat ulang agar formulir bisa dipakai lagi.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'reload_url' => $request->headers->get('referer') ?: url('/'),
                ], 419);
            }

            $reloadUrl = $request->headers->get('referer') ?: url('/');

            return response()
                ->view('errors.419', compact('message', 'reloadUrl'), 419);
        });
    })->create();
