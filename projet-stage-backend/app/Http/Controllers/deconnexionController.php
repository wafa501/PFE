<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\LinkedInToken;

class deconnexionController extends Controller
{
   
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
public function deconnexionFacebook(Request $request)
{
    if (Auth::guard('facebook')->check()) {
        Auth::guard('facebook')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Déconnexion réussie.'], 200);
    }

    return response()->json(['error' => 'Vous n\'êtes pas connecté.'], 401);
}




}
