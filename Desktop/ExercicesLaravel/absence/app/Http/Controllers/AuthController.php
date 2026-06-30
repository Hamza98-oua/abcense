<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Affiche la page de connexion.
     */
    public function showLogin()
    {
        // Si déjà connecté, rediriger selon le rôle
        if (Auth::check()) {
            return $this->redirigerSelonRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Authentifie l'utilisateur via email + password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Email ou mot de passe incorrect.',
            ])->withInput($request->except('password'));
        }

        Auth::login($user);

        return $this->redirigerSelonRole($user)->with(
            'success',
            'Bienvenue ' . $user->name . ' ! Vous êtes connecté.'
        );
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Vous avez été déconnecté.');
    }

    /**
     * Redirige l'utilisateur vers son espace de travail selon son rôle.
     */
    private function redirigerSelonRole($user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isFormateur()) {
            return redirect()->route('formateur.dashboard');
        }
        if ($user->isGestionnaire()) {
            return redirect()->route('gestionnaire.dashboard');
        }

        // Par défaut pour l'admin ou autre rôle
        return redirect()->route('login');
    }
}
