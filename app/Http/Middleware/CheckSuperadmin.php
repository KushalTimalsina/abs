<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperadmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as superadmin
        if (!auth()->guard('superadmin')->check()) {
            return redirect()->route('superadmin.login')
                ->with('error', 'Please login as superadmin to access this area.');
        }

        // Check if superadmin account is active
        $superadmin = auth()->guard('superadmin')->user();
        if (!$superadmin->is_active) {
            auth()->guard('superadmin')->logout();
            return redirect()->route('superadmin.login')
                ->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
