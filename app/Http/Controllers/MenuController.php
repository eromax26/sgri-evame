<?php

namespace App\Http\Controllers;

use App\Models\LigneMenu;
use App\Models\Menu;
use App\Models\Plat;
use App\Models\SelectionRepas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('date_debut_semaine', 'desc')->paginate(10);

        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_debut_semaine' => ['required', 'date'],
        ]);

        $debut = Carbon::parse($validated['date_debut_semaine']);
        $fin = $debut->copy()->addDays(4); // semaine du lundi au vendredi

        $existant = Menu::whereDate('date_debut_semaine', $debut->toDateString())->first();

        if ($existant) {
            return redirect()->route('menus.edit', $existant)
                ->with('error', 'Un menu existe deja pour cette semaine, vous avez ete redirige vers son edition.');
        }

        $platParDefaut = Plat::where('statut', 'actif')->first();

        $menu = DB::transaction(function () use ($debut, $fin, $platParDefaut) {
            $menu = Menu::create([
                'date_debut_semaine' => $debut,
                'date_fin_semaine' => $fin,
                'statut_publication' => 'brouillon',
            ]);

            // Cree automatiquement les 5 lignes (lundi a vendredi), sans plat si aucun n'est actif
            for ($i = 0; $i < 5; $i++) {
                LigneMenu::create([
                    'menu_id' => $menu->id,
                    'plat_id' => $platParDefaut?->id,
                    'date_repas' => $debut->copy()->addDays($i),
                ]);
            }

            return $menu;
        });

        return redirect()->route('menus.edit', $menu)->with('success', 'Menu cree. Choisissez maintenant un plat pour chaque jour.');
    }

    public function edit(Menu $menu)
    {
        $lignesMenu = $menu->lignesMenu()->orderBy('date_repas')->get();
        $plats = Plat::where('statut', 'actif')->orderBy('libelle')->get();

        return view('menus.edit', compact('menu', 'lignesMenu', 'plats'));
    }

    public function updateLignes(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'plats' => ['required', 'array'],
            'plats.*' => ['nullable', 'exists:plats,id'],
        ]);

        DB::transaction(function () use ($menu, $validated) {
            foreach ($validated['plats'] as $ligneId => $platId) {
                $menu->lignesMenu()->whereKey($ligneId)->update(['plat_id' => $platId]);
            }
        });

        return redirect()->route('menus.edit', $menu)->with('success', 'Menu enregistre.');
    }

    public function publier(Menu $menu)
    {
        $joursIncomplets = $menu->lignesMenu()->whereNull('plat_id')->count();

        if ($joursIncomplets > 0) {
            return back()->with('error', "Il reste {$joursIncomplets} jour(s) sans plat choisi.");
        }

        $menu->update(['statut_publication' => 'publie']);

        return redirect()->route('menus.index')->with('success', 'Menu publie avec succes.');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->estPublie()) {
            return back()->with('error', 'Un menu publie ne peut pas etre supprime.');
        }

        DB::transaction(function () use ($menu) {
            $menu->lignesMenu()->delete();
            $menu->delete();
        });

        return redirect()->route('menus.index')->with('success', 'Menu brouillon supprime.');
    }

    public function previsions()
    {
        $debut = now()->toDateString();
        $fin = now()->addDays(6)->toDateString();

        $previsions = SelectionRepas::with('ligneMenu.plat')
            ->whereIn('statut', ['demande', 'imprime'])
            ->whereHas('ligneMenu', fn ($q) => $q->whereBetween('date_repas', [$debut, $fin]))
            ->get()
            ->groupBy(fn ($selection) => $selection->ligneMenu->date_repas->toDateString() . '|' . $selection->ligneMenu->plat_id)
            ->map(function ($selections) {
                $premiere = $selections->first();

                return [
                    'date_repas' => $premiere->ligneMenu->date_repas,
                    'plat' => $premiere->ligneMenu->plat,
                    'total' => $selections->count(),
                    'imprimes' => $selections->where('statut', 'imprime')->count(),
                    'en_attente' => $selections->where('statut', 'demande')->count(),
                ];
            })
            ->sortBy('date_repas');

        return view('menus.previsions', compact('previsions'));
    }
}