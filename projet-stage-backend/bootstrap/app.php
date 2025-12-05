<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enable built-in CORS - AJOUTEZ CETTE LIGNE
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        $middleware->trustProxies(at: '*');
        $middleware->trustHosts(at: ['localhost', '*.localhost']);
        
        // CSRF exceptions for your API routes
        $middleware->validateCsrfTokens(except: [
            'getMyPosts/*',
            'otherOrganization/*',
            'api/*',
        ]);
        
        // Register your admin middleware
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();