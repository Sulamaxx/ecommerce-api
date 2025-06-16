<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminOrStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $user = $request->user();
        
        // Check if user has admin or staff role
        if (in_array($user->user_type, ['admin', 'staff'])) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Access denied. Admin or staff privileges required.'
        ], 403);
    }
}