<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessAdminCredentials()) {
            if ($request->expectsJson()) {
                abort(403, 'Access denied: Warehouse Administrators do not have access to Admin Credentials.');
            }

            return redirect()->route('admin.dashboard')
                ->withErrors(['error' => 'Access denied: Warehouse Administrators do not have access to Admin Credentials. Only authorized IT Administrators may manage credentials, user accounts, and security roles.']);
        }

        return $next($request);
    }
}
