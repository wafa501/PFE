<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        return User::all();
    }

public function toggleBlock($id)
{
    $user = User::findOrFail($id);

    // Optionnel : empêcher de se bloquer soi-même
    if (auth()->id() === $user->id) {
        return response()->json([
            'message' => "Vous ne pouvez pas bloquer votre propre compte."
        ], 403);
    }

    // Inverser le statut blocked
    $user->blocked = !$user->blocked;
    $user->save();

    return response()->json([
        'message' => $user->blocked ? 'Utilisateur bloqué' : 'Utilisateur débloqué',
        'blocked' => $user->blocked,
    ]);
}

public function toggleRole($id)
{
    $user = User::findOrFail($id);

    if (auth()->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Seuls les administrateurs peuvent changer le rôle.'
        ], 403);
    }

    if (auth()->id() === $user->id) {
        return response()->json([
            'message' => 'Vous ne pouvez pas modifier votre propre rôle.'
        ], 403);
    }

    // Changer le rôle de l'utilisateur ciblé
    $user->role = $user->role === 'admin' ? 'manager' : 'admin';
    $user->save();

    // Bloquer l'utilisateur courant si le rôle a été donné à quelqu'un d'autre
    if ($user->role === 'admin') {
        $currentUser = auth()->user();
        $currentUser->blocked = true;
        $currentUser->save();
    }

    return response()->json([
        'message' => 'Rôle mis à jour avec succès',
        'role' => $user->role
    ]);
}

public function getRoleByEmail(Request $request)
{
    $email = $request->query('email');

    if (!$email) {
        return response()->json(['message' => 'Email manquant'], 400);
    }

    $user = User::where('email', $email)->first();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
    }

    return response()->json([
        'email' => $user->email,
        'role' => $user->role
    ]);
}


}
