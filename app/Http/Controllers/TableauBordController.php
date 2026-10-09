<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Collaborateur;
use App\Models\LigneMenu;
use App\Models\Menu;
use App\Models\Plat;
use App\Models\SelectionRepas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TableauBordController extends Controller
{
    public function index()
    {
        // Repas servis aujourd'hui
        $repasAujourdhui = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereDate('date_repas', now()->toDateString()))
            ->count();

        // Repas servis ce mois
        $repasCeMois = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [now()->format('Y-m')]))
            ->count();

        // Tickets emis (des la selection) et pas encore retires
        $ticketsNonRetires = SelectionRepas::where('statut', 'imprime')->count();

        // Montant total facture ce mois
        $montantCeMois = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [now()->format('Y-m')]))
            ->sum('prix');

        // Taux de frequentation ce mois (collaborateurs ayant consomme / total collaborateurs actifs)
        $collaborateursActifs = Collaborateur::where('statut', 'actif')->count();
        $collaborateursAyantConsomme = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [now()->format('Y-m')]))
            ->distinct('collaborateur_id')
            ->count('collaborateur_id');
        $tauxFrequentation = $collaborateursActifs > 0
            ? round(($collaborateursAyantConsomme / $collaborateursActifs) * 100)
            : 0;

        // Top 5 des plats les plus consommes ce mois
        $platsPopulaires = SelectionRepas::join('ligne_menus', 'ligne_menus.id', '=', 'selections_repas.ligne_menu_id')
            ->select('ligne_menus.plat_id', DB::raw('COUNT(*) as total'))
            ->where('selections_repas.statut', 'consomme')
            ->whereRaw("DATE_FORMAT(ligne_menus.date_repas, '%Y-%m') = ?", [now()->format('Y-m')])
            ->groupBy('ligne_menus.plat_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($ligne) {
                $plat = Plat::find($ligne->plat_id);
                return [
                    'libelle' => $plat ? $plat->libelle : 'Plat supprime',
                    'total' => $ligne->total,
                ];
            });

        // Articles sous le seuil minimum
        $articlesEnAlerte = Article::enAlerte()
            ->orderBy('libelle')
            ->get();

        return view('tableau-bord.index', compact(
            'repasAujourdhui',
            'repasCeMois',
            'ticketsNonRetires',
            'montantCeMois',
            'tauxFrequentation',
            'platsPopulaires',
            'articlesEnAlerte'
        ));
    }

    public function cantine()
    {
        // Portions a preparer (toutes les selections du jour, quel que soit leur statut)
        $portionsAujourdhui = SelectionRepas::whereHas('ligneMenu', fn ($q) => $q->whereDate('date_repas', now()->toDateString()))->count();
        $portionsDemain = SelectionRepas::whereHas('ligneMenu', fn ($q) => $q->whereDate('date_repas', now()->addDay()->toDateString()))->count();

        // Statut du menu de la semaine en cours
        $debutSemaine = now()->startOfWeek(Carbon::MONDAY);
        $menuSemaine = Menu::where('date_debut_semaine', $debutSemaine->toDateString())->first();
        $joursIncomplets = $menuSemaine ? $menuSemaine->lignesMenu()->whereNull('plat_id')->count() : 0;

        // Articles sous le seuil minimum
        $articlesEnAlerte = Article::enAlerte()
            ->orderBy('libelle')
            ->get();

        // Plats les plus et les moins commandes ce mois.
        // On part des plats proposes au menu (ligne_menus) et non des selections, pour que
        // les plats proposes mais jamais consommes apparaissent bien avec un total de 0.
        $totauxPlats = LigneMenu::leftJoin('selections_repas', function ($jointure) {
                $jointure->on('selections_repas.ligne_menu_id', '=', 'ligne_menus.id')
                    ->where('selections_repas.statut', 'consomme');
            })
            ->select('ligne_menus.plat_id', DB::raw('COUNT(selections_repas.id) as total'))
            ->whereNotNull('ligne_menus.plat_id')
            ->whereRaw("DATE_FORMAT(ligne_menus.date_repas, '%Y-%m') = ?", [now()->format('Y-m')])
            ->groupBy('ligne_menus.plat_id')
            ->get();

        $libelles = Plat::whereIn('id', $totauxPlats->pluck('plat_id'))->pluck('libelle', 'id');

        // Un seul classement, du plus au moins commande : avec une dizaine de plats au
        // catalogue, deux listes inversees de 5 affichaient deux fois le meme contenu.
        // values() reindexe les cles apres le tri, sinon la numerotation affichee est fausse.
        $classementPlats = $totauxPlats->sortByDesc('total')->values()->map(fn ($ligne) => [
            'libelle' => $libelles[$ligne->plat_id] ?? 'Plat supprime',
            'total' => (int) $ligne->total,
        ]);

        // Sert d'echelle pour la barre de proportion affichee sur chaque ligne.
        $maxRepas = (int) $classementPlats->max('total');

        // Previsions des 3 prochains jours
        $previsions = SelectionRepas::with('ligneMenu.plat')
            ->whereIn('statut', ['imprime', 'consomme'])
            ->whereHas('ligneMenu', fn ($q) => $q->whereBetween('date_repas', [now()->toDateString(), now()->addDays(2)->toDateString()]))
            ->get()
            ->groupBy(fn ($selection) => $selection->ligneMenu->date_repas->toDateString() . '|' . $selection->ligneMenu->plat_id)
            ->map(function ($selections) {
                $premiere = $selections->first();

                return [
                    'date_repas' => $premiere->ligneMenu->date_repas,
                    'plat' => $premiere->ligneMenu->plat,
                    'total' => $selections->count(),
                ];
            })
            ->sortBy('date_repas');

        return view('tableau-bord.cantine', compact(
            'portionsAujourdhui',
            'portionsDemain',
            'menuSemaine',
            'joursIncomplets',
            'articlesEnAlerte',
            'classementPlats',
            'maxRepas',
            'previsions'
        ));
    }
}