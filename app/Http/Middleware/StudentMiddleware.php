<?php

// app/Http/Middleware/StudentMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class StudentMiddleware
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

        // Allow all authenticated users (they can access student features)
        // But students can only see their own data
        if ($user->role === 'student') {
            // Add student ID to request for automatic filtering
            $request->merge(['student_id' => $user->id]);
        }

        return $next($request);
    }
}
