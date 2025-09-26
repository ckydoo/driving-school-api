<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SchoolAdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allows School Administrators (NOT Super Admins)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Must be admin but NOT super admin
        if (!$user->isAdmin() || $user->isSuperAdmin()) {
            abort(403, 'Access denied. School Administrator privileges required.');
        }

        return $next($request);
    }
}
