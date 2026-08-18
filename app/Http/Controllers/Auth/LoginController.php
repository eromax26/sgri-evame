<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    private const MAX_TENTATIVES = 3;

    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $collaborateur = Collaborateur::where('identifiant', $request->identifiant)->first();

        if (! $collaborateur) {
            return back()->withErrors([
                'identifiant' => 'Identifiant ou mot de passe incorrect.',
            ])->onlyInput('identifiant');
        }

        if ($collaborateur->statut !== 'actif') {
            return back()->withErrors([
                'identifiant' => 'Ce compte est desactivé. Contactez l\'Administrateur DSII.',
            ])->onlyInput('identifiant');
        }

        if ($collaborateur->tentatives_echouees >= self::MAX_TENTATIVES) {
            return back()->withErrors([
                'identifiant' => 'Compte bloqué suite à 3 échecs. Contactez l\'Administrateur DSII.',
            ])->onlyInput('identifiant');
        }

        if (Auth::attempt($request->only('identifiant', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            $collaborateur->update(['tentatives_echouees' => 0]);

            return redirect($this->pageAccueilSelonRole($collaborateur));
        }

        $collaborateur->increment('tentatives_echouees');

        if ($collaborateur->tentatives_echouees >= self::MAX_TENTATIVES) {
            $collaborateur->update(['statut' => 'bloque']);

            return back()->withErrors([
                'identifiant' => 'Compte bloqué après 3 échecs. Contactez l\'Administrateur DSII.',
            ])->onlyInput('identifiant');
        }

        $restantes = self::MAX_TENTATIVES - $collaborateur->tentatives_echouees;

        return back()->withErrors([
            'identifiant' => "Identifiant ou mot de passe incorrect. ({$restantes} tentative(s) restante(s))",
        ])->onlyInput('identifiant');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // Renvoie la page d'accueil correspondant au role du collaborateur connecte
    private function pageAccueilSelonRole($collaborateur)
    {
        if ($collaborateur->aLeRole('Administrateur DSII')) {
            return route('tableau-bord.index');
        }

        if ($collaborateur->aLeRole('Responsable cantine')) {
            return route('plats.index');
        }

        if ($collaborateur->aLeRole('Agent de securite')) {
            return route('agent.demandes');
        }

        if ($collaborateur->aLeRole('Ressources Humaines')) {
            return route('etat-rh.index');
        }

        if ($collaborateur->aLeRole('Direction Generale')) {
            return route('tableau-bord.index');
        }

        // Par defaut : espace collaborateur
        return route('selection.index');
    }
}