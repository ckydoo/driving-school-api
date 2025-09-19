<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class FlexibleAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Super admins get UNLIMITED access to everything
     * School admins get restricted access
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admins can access EVERYTHING - no restrictions whatsoever
        if ($user->isSuperAdmin()) {
            // Add a flag to the request so controllers know this is a super admin
            $request->attributes->set('is_super_admin', true);
            return $next($request);
        }

        // Regular admin/school admin access
        if ($user->isAdmin()) {
            // Add school scoping for school admins
            if ($user->isSchoolAdmin() && $user->school_id) {
                $request->attributes->set('scoped_school_id', $user->school_id);
            }
            return $next($request);
        }

        // Not an admin at all
        abort(403, 'Access denied. Administrator privileges required.');
    }
}
