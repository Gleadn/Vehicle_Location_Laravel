<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * Traiter la connexion
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/')->with('success', 'Connexion réussie !');
        }

        return back()->withErrors([
            'email' => 'Les identifiants ne correspondent pas.',
        ])->onlyInput('email');
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegister()
    {
        return view('register');
    }

    /**
     * Traiter l'inscription
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect('/')->with('success', 'Inscription réussie ! Bienvenue !');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Déconnexion réussie.');
    }

    /**
     * Supprimer le compte (soft delete)
     */
    public function deleteAccount(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Soft delete l'utilisateur AVANT de logout
        $user->delete();
        
        // Ensuite déconnecter et invalider la session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Votre compte a été supprimé avec succès.');
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        // Mettre à jour le nom et l'email
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Mettre à jour le mot de passe seulement si fourni
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profil mis à jour avec succès !');
    }
}
