<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->blocked_at) {
            // Get the manager who blocked them
            $manager = $user->managedBy;
            $managerName = $manager ? $manager->name : 'System Administrator';
            
            // Allow only logout and profile view (read-only)
            if (!$request->is('logout') && !$request->is('profile')) {
                return redirect()->route('profile.edit')
                    ->with('error', "Your account has been blocked by {$managerName}. Please contact them for assistance.");
            }
        }

        return $next($request);
    }
}
