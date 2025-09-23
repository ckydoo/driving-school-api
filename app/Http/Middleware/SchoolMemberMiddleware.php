<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SchoolMemberMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->school_id) {
            return response()->json(['error' => 'User does not belong to any school'], 403);
        }

        return $next($request);
    }
}


