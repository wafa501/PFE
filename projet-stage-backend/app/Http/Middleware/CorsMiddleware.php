<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $allowedOrigins = ['http://localhost:3000'];
        $origin = $request->header('Origin');
        
        // Set the appropriate origin
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            // If you want to allow specific origins only, you can leave this out
            // or set a default allowed origin
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Expose-Headers', 'Authorization');
        
        // Handle preflight OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(200);
            $response->setContent(null);
        }
        
        return $response;
    }
}