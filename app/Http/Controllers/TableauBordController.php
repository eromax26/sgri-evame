<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Collaborateur;
use App\Models\Plat;
use App\Models\SelectionRepas;
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

        // Tickets en attente d'impression
        $ticketsEnAttente = SelectionRepas::where('statut', 'demande')->count();

        // Tickets imprimes mais pas encore retires
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
        $articlesEnAlerte = Article::whereColumn('quantite_stock', '<=', 'seuil_minimum')
            ->orderBy('libelle')
            ->get();

        return view('tableau-bord.index', compact(
            'repasAujourdhui',
            'repasCeMois',
            'ticketsEnAttente',
            'ticketsNonRetires',
            'montantCeMois',
            'tauxFrequentation',
            'platsPopulaires',
            'articlesEnAlerte'
        ));
    }
}