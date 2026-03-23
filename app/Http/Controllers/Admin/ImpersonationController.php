<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if (!$actor) {
            abort(401);
        }

        if ($user->id === $actor->id) {
            return redirect()->route('admin.users.index');
        }

        // Only allow if actor can manage this user (management tree + superiority),
        // and has elevated access (super-admin OR edit-users permission).
        if (!$actor->hasRole('super-admin') && !$actor->hasPermissionTo('edit-users')) {
            abort(403, 'You do not have permission to impersonate users.');
        }

        if (!$actor->canManage($user)) {
            abort(403, 'You cannot impersonate a user outside your management tree.');
        }

        // Remember who started impersonation.
        if (!session()->has('impersonator_id')) {
            session()->put('impersonator_id', $actor->id);
        }

        session()->put('impersonated_id', $user->id);

        $to = $request->query('to');

        // Prevent open-redirects; allow only relative URLs.
        if (!is_string($to) || $to === '' || !str_starts_with($to, '/')) {
            $to = route('dashboard');
        }

        return redirect()->to($to);
    }

    public function stop(Request $request): RedirectResponse
    {
        session()->forget(['impersonated_id', 'impersonator_id']);

        return redirect()->route('admin.users.index');
    }
}
