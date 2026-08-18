<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $collaborateur = Auth::user()->load('departement.societe', 'roles');

        return view('profil.index', compact('collaborateur'));
    }

    public function changerMotDePasse(Request $request)
    {
        $validated = $request->validate([
            'ancien_mot_de_passe' => ['required'],
            'nouveau_mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $collaborateur = Auth::user();

        // Verifier que l'ancien mot de passe est correct
        if (! Hash::check($validated['ancien_mot_de_passe'], $collaborateur->password)) {
            return back()->with('error', 'L\'ancien mot de passe est incorrect.');
        }

        $collaborateur->update([
            'password' => $validated['nouveau_mot_de_passe'],
        ]);

        return back()->with('success', 'Mot de passe modifie avec succes.');
    }
}