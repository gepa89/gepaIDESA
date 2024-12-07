<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (!Auth::guard($guard ?: 'sanctum')->check()) {
            return response()->json([
                'message' => 'You are not authenticated. Please log in.',
            ], 401);
        }
    
        return $next($request);
    }
}
