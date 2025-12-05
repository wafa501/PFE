<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Toutes les routes API protégées par Sanctum
Route::middleware('auth:sanctum')->get('/usersAll', [UserController::class, 'AllUsers']);
Route::middleware('auth:sanctum')->get('/block/{id}', [UserController::class, 'block']);

Route::get('/proxy/ip-info', function () {
    $response = Http::withHeaders([
        'User-Agent' => request()->userAgent()
    ])->get('https://api.ipify.org?format=json');
    
    return $response->json();
});