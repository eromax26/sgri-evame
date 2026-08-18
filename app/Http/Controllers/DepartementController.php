<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Societe;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    public function index()
    {
        $departements = Departement::with('societe')->withCount('collaborateurs')->orderBy('nom')->get();

        return view('departements.index', compact('departements'));
    }

    public function create()
    {
        $societes = Societe::orderBy('nom')->get();

        return view('departements.create', compact('societes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'societe_id' => ['required', 'exists:societes,id'],
            'nom' => ['required', 'string', 'max:255'],
        ]);

        Departement::create($validated);

        return redirect()->route('departements.index')->with('success', 'Departement cree avec succes.');
    }

    public function edit(Departement $departement)
    {
        $societes = Societe::orderBy('nom')->get();

        return view('departements.edit', compact('departement', 'societes'));
    }

    public function update(Request $request, Departement $departement)
    {
        $validated = $request->validate([
            'societe_id' => ['required', 'exists:societes,id'],
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $departement->update($validated);

        return redirect()->route('departements.index')->with('success', 'Departement modifie avec succes.');
    }

    public function destroy(Departement $departement)
    {
        if ($departement->collaborateurs()->exists()) {
            return back()->with('error', 'Impossible de supprimer un departement qui a des collaborateurs.');
        }

        $departement->delete();

        return redirect()->route('departements.index')->with('success', 'Departement supprime avec succes.');
    }
}