<?php

namespace App\Http\Controllers;

use App\Models\Societe;
use Illuminate\Http\Request;

class SocieteController extends Controller
{
    public function index()
    {
        $societes = Societe::withCount('departements')->orderBy('nom')->get();

        return view('societes.index', compact('societes'));
    }

    public function create()
    {
        return view('societes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:20'],
        ]);

        Societe::create($validated);

        return redirect()->route('societes.index')->with('success', 'Societe creee avec succes.');
    }

    public function edit(Societe $societe)
    {
        return view('societes.edit', compact('societe'));
    }

    public function update(Request $request, Societe $societe)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:20'],
        ]);

        $societe->update($validated);

        return redirect()->route('societes.index')->with('success', 'Societe modifiee avec succes.');
    }

    public function destroy(Societe $societe)
    {
        if ($societe->departements()->exists()) {
            return back()->with('error', 'Impossible de supprimer une societe qui a des departements.');
        }

        $societe->delete();

        return redirect()->route('societes.index')->with('success', 'Societe supprimee avec succes.');
    }
}