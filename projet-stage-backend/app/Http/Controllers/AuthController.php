<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\LinkedInToken;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function checkAuth(Request $request)
    {
        $tokens = LinkedInToken::all();
        
        $exists = $tokens->isNotEmpty();
        
        Log::info("Number of tokens: " . $tokens->count());
        
        return response()->json(['authenticated' => $exists]);
    }
    public function deconnexion(Request $request)
{
    $tokens = LinkedInToken::all();
    
    $exists = $tokens->isNotEmpty();
        if ($exists) {
        LinkedInToken::truncate(); 
    }
    
    return response()->json([
        'authenticated' => !$exists,
        'message' => 'Tokens removed'
    ]);
}
public function logout(Request $request)
{
    $user = Auth::user();

    if ($user) {
        $user->linkedin_token = null;
        $user->save(); 
    }

    Auth::logout();

    return response()->json(['message' => 'Déconnexion réussie'], 200);
}
    
}
