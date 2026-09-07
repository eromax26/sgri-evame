<?php

namespace App\Http\Controllers;

use App\Models\Collaborateur;
use App\Models\Departement;
use Illuminate\Http\Request;

class CollaborateurController extends Controller
{
    public function index(Request $request)
    {
        $query = Collaborateur::with('departement');

        if ($request->filled('recherche')) {
            $recherche = $request->recherche;
            $query->where(function ($q) use ($recherche) {
                $q->where('matricule', 'like', "%{$recherche}%")
                  ->orWhere('nom', 'like', "%{$recherche}%")
                  ->orWhere('prenom', 'like', "%{$recherche}%");
            });
        }

        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $collaborateurs = $query->orderBy('nom')->paginate(15)->withQueryString();
        $departements = Departement::orderBy('nom')->get();

        return view('collaborateurs.index', compact('collaborateurs', 'departements'));
    }

    public function create()
    {
         //
        $departements = Departement::orderBy('nom')->get();
        return view('collaborateurs.create', compact('departements'));
       
    }

    public function store(Request $request)
    {
        //
         $validated = $request->validate([
            'matricule' => ['required', 'string', 'max:20', 'unique:collaborateurs,matricule'],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'identifiant' => ['required', 'string', 'max:255', 'unique:collaborateurs,identifiant'],
            'email' => ['nullable', 'email', 'max:255', 'unique:collaborateurs,email'],
            'password' => ['required', 'string', 'min:8'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'departement_id' => ['required', 'exists:departements,id'],
            'direction' => ['nullable', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'site' => ['nullable', 'string', 'max:255'],
            'date_entree' => ['nullable', 'date'],
            'mode_facturation' => ['nullable', 'string', 'max:30'],
        ]);

        Collaborateur::create($validated);

        return redirect()->route('collaborateurs.index')
            ->with('success', 'Collaborateur crée avec succès.');
    }

    public function show(Collaborateur $collaborateur)
    {
        //
    }

    public function edit(Collaborateur $collaborateur)
    {
        //
        $departements = Departement::orderBy('nom')->get();
        return view('collaborateurs.edit', compact('collaborateur', 'departements'));
    }

public function update(Request $request, Collaborateur $collaborateur)
{
    $validated = $request->validate([
        'matricule' => ['required', 'string', 'max:20', 'unique:collaborateurs,matricule,' . $collaborateur->id],
        'nom' => ['required', 'string', 'max:255'],
        'prenom' => ['required', 'string', 'max:255'],
        'identifiant' => ['required', 'string', 'max:255', 'unique:collaborateurs,identifiant,' . $collaborateur->id],
        'email' => ['nullable', 'email', 'max:255', 'unique:collaborateurs,email,' . $collaborateur->id],
        'password' => ['nullable', 'string', 'min:8'],
        'telephone' => ['nullable', 'string', 'max:20'],
        'departement_id' => ['required', 'exists:departements,id'],
        'direction' => ['nullable', 'string', 'max:255'],
        'fonction' => ['nullable', 'string', 'max:255'],
        'site' => ['nullable', 'string', 'max:255'],
        'date_entree' => ['nullable', 'date'],
        'mode_facturation' => ['nullable', 'string', 'max:30'],
        'statut' => ['required', 'in:actif,inactif,bloque'],
    ]);

    if (empty($validated['password'])) {
        unset($validated['password']);
    }

    // Si l'admin reactive un compte, on remet le compteur d'echecs a zero
    if ($validated['statut'] === 'actif' && $collaborateur->statut !== 'actif') {
        $validated['tentatives_echouees'] = 0;
    }

    $collaborateur->update($validated);

    return redirect()->route('collaborateurs.index')
        ->with('success', 'Collaborateur modifie avec succes.');
}

    public function destroy(Collaborateur $collaborateur)
    {
        //
        $collaborateur->update(['statut' => 'inactif']);

        return redirect()->route('collaborateurs.index')
            ->with('success', 'Collaborateur desactive avec succes.');
    }

    public function activer(Collaborateur $collaborateur)
    {
        $collaborateur->update(['statut' => 'actif', 'tentatives_echouees' => 0]);

        return redirect()->route('collaborateurs.index')
            ->with('success', 'Collaborateur reactive avec succes.');
    }
}