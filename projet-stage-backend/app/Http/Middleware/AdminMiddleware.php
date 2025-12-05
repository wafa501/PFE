<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'super admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Access Denied'], 403);
    }
}
