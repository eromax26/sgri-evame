<?php

namespace App\Http\Controllers;

use App\Models\Plat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatController extends Controller
{
    public function index(Request $request)
    {
        $query = Plat::query();

        if ($request->filled('recherche')) {
            $query->where('libelle', 'like', '%' . $request->recherche . '%');
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $plats = $query->orderBy('libelle')->paginate(15)->withQueryString();

        return view('plats.index', compact('plats'));
    }

    public function create()
    {
        return view('plats.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_plat' => ['required', 'string', 'max:20', 'unique:plats,code_plat'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'prix' => ['required', 'numeric', 'min:0'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('plats', 'public');
        }

        Plat::create($validated);

        return redirect()->route('plats.index')->with('success', 'Plat créé avec succès.');
    }

    public function edit(Plat $plat)
    {
        return view('plats.edit', compact('plat'));
    }

    public function update(Request $request, Plat $plat)
    {
        $validated = $request->validate([
            'code_plat' => ['required', 'string', 'max:20', 'unique:plats,code_plat,' . $plat->id],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'prix' => ['required', 'numeric', 'min:0'],
            'statut' => ['required', 'in:actif,inactif'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($plat->photo) {
                Storage::disk('public')->delete($plat->photo);
            }
            $validated['photo'] = $request->file('photo')->store('plats', 'public');
        }

        $plat->update($validated);

        return redirect()->route('plats.index')->with('success', 'Plat modifié avec succès.');
    }

    public function destroy(Plat $plat)
    {
        $plat->update(['statut' => 'inactif']);

        return redirect()->route('plats.index')->with('success', 'Plat désactivé avec succès.');
    }

    public function activer(Plat $plat)
    {
        $plat->update(['statut' => 'actif']);

        return redirect()->route('plats.index')->with('success', 'Plat réactivé avec succès.');
    }
}