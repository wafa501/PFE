<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function AllUsers()
    {
        $users = User::all(); 
        return response()->json($users);
    }

    public function block($id)
    {
        $user = User::findOrFail($id);
        $user->blocked = !$user->blocked;
        $user->save();

        return response()->json(['message' => 'Utilisateur mis à jour avec succès!', 'user' => $user]);
    }
}
