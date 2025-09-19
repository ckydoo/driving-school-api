<?php
// app/Http/Middleware/SuperAdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
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

// app/Http/Middleware/SchoolAdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SchoolAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Allow super admins and school admins
        if (!$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        return $next($request);
    }
}

// app/Http/Middleware/SchoolScopeMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\School;

class SchoolScopeMiddleware
{
    /**
     * Restrict school admins to their own school data
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Super admins can access everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // School admins can only access their school's data
        if ($user->isSchoolAdmin()) {
            $this->restrictToUserSchool($request, $user);
        }

        return $next($request);
    }

    /**
     * Restrict request to user's school
     */
    private function restrictToUserSchool(Request $request, $user)
    {
        // Get the school ID from route parameters
        $schoolId = $request->route('school')?->id ?? $request->route('school');
        
        // If accessing a specific school, ensure it's the user's school
        if ($schoolId && $schoolId != $user->school_id) {
            abort(403, 'Access denied. You can only manage your own school.');
        }

        // For API requests, add school filter to queries
        if ($request->is('api/*')) {
            $request->merge(['school_id' => $user->school_id]);
        }
    }
}

// Update app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Allow both super admins and school admins
        if (!$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        return $next($request);
    }
}