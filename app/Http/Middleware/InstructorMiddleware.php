<?php
// app/Http/Middleware/InstructorMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class InstructorMiddleware
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

        // Allow super admins, school admins, and instructors
        if (!in_array($user->role, ['super_admin', 'admin', 'instructor'])) {
            abort(403, 'Access denied. Instructor privileges required.');
        }

        return $next($request);
    }
}

