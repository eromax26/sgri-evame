<?php

namespace App\Http\Controllers;

use App\Models\LigneMenu;
use App\Models\Menu;
use App\Models\Plat;
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
                    'statut' => 'prevu',
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

    public function updateLigne(Request $request, LigneMenu $ligneMenu)
    {
        $validated = $request->validate([
            'plat_id' => ['required', 'exists:plats,id'],
        ]);

        $ligneMenu->update($validated);

        return back()->with('success', 'Plat mis a jour pour ce jour.');
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

        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu brouillon supprime.');
    }

    public function previsions()
    {
        $debut = now()->toDateString();
        $fin = now()->addDays(6)->toDateString();

        $previsions = LigneMenu::with('plat')
            ->whereIn('statut', ['demande', 'imprime'])
            ->whereBetween('date_repas', [$debut, $fin])
            ->get()
            ->groupBy(function ($ligne) {
                return $ligne->date_repas->toDateString() . '|' . $ligne->plat_id;
            })
            ->map(function ($lignes) {
                $premiere = $lignes->first();

                return [
                    'date_repas' => $premiere->date_repas,
                    'plat' => $premiere->plat,
                    'total' => $lignes->count(),
                    'imprimes' => $lignes->where('statut', 'imprime')->count(),
                    'en_attente' => $lignes->where('statut', 'demande')->count(),
                ];
            })
            ->sortBy('date_repas');

        return view('menus.previsions', compact('previsions'));
    }
}