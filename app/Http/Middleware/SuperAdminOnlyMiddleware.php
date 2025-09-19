<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SuperAdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     * Only allows Super Administrators
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        return $next($request);
    }
}
