<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super-admins don't need verification
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Allow access to profile page regardless of verification status
        if ($request->routeIs('profile.edit') || $request->routeIs('profile.update')) {
            return $next($request);
        }

        // Check if user is verified
        if (!$user->email_verified || !$user->phone_verified) {
            return redirect()->route('profile.edit')
                ->with('error', 'Please verify your email and phone number to access this page.');
        }

        return $next($request);
    }
}
