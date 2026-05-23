<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateUser
{
    /**
     * Route names where impersonation should NOT be applied.
     */
    private const IGNORE_ROUTE_NAMES = [
        'admin.users.impersonate',
        'admin.impersonate.stop',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();
        if (is_string($routeName) && in_array($routeName, self::IGNORE_ROUTE_NAMES, true)) {
            return $next($request);
        }

        $impersonatedId = session('impersonated_id');
        if (!$impersonatedId) {
            return $next($request);
        }

        $impersonatorId = session('impersonator_id') ?: auth()->id();
        session()->put('impersonator_id', $impersonatorId);

        $impersonatedUser = User::find($impersonatedId);
        if (!$impersonatedUser) {
            session()->forget(['impersonated_id', 'impersonator_id']);
            return $next($request);
        }

        auth()->setUser($impersonatedUser);
        $request->setUserResolver(fn () => $impersonatedUser);

        view()->share('impersonation', [
            'active' => true,
            'impersonator_id' => $impersonatorId,
            'impersonated' => $impersonatedUser,
        ]);

        return $next($request);
    }
}
