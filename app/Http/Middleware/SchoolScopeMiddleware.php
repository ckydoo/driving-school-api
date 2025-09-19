<?php
// app/Http/Middleware/SchoolScopeMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin can see everything
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Regular admins must have a school_id
        if (!$user->school_id) {
            abort(403, 'User not assigned to any school');
        }

        // Add school_id to request for scoping queries
        $request->merge(['scoped_school_id' => $user->school_id]);

        return $next($request);
    }
}
