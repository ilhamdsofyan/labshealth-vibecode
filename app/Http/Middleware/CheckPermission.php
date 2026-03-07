<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        // SuperAdmin bypasses all permission checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // If route has no name, allow access (e.g. fallback routes)
        if (!$routeName) {
            return $next($request);
        }

        // Skip permission check for common routes
        $skipRoutes = ['dashboard', 'login', 'login.post', 'logout', 'auth.google', 'auth.google.callback'];
        if (in_array($routeName, $skipRoutes)) {
            return $next($request);
        }

        if (!$user->hasPermission($routeName)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
