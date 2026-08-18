<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Collaborateur;
use App\Models\LigneMenu;
use App\Models\Plat;
use Illuminate\Support\Facades\DB;

class TableauBordController extends Controller
{
    public function index()
    {
        // Repas servis aujourd'hui
        $repasAujourdhui = LigneMenu::where('statut', 'consomme')
            ->whereDate('date_repas', now()->toDateString())
            ->count();

        // Repas servis ce mois
        $repasCeMois = LigneMenu::where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [now()->format('Y-m')])
            ->count();

        // Tickets en attente d'impression
        $ticketsEnAttente = LigneMenu::where('statut', 'demande')->count();

        // Tickets imprimes mais pas encore retires
        $ticketsNonRetires = LigneMenu::where('statut', 'imprime')->count();

        // Montant total facture ce mois
        $montantCeMois = LigneMenu::where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [now()->format('Y-m')])
            ->sum('prix');

        // Taux de frequentation ce mois (collaborateurs ayant consomme / total collaborateurs actifs)
        $collaborateursActifs = Collaborateur::where('statut', 'actif')->count();
        $collaborateursAyantConsomme = LigneMenu::where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [now()->format('Y-m')])
            ->distinct('collaborateur_id')
            ->count('collaborateur_id');
        $tauxFrequentation = $collaborateursActifs > 0
            ? round(($collaborateursAyantConsomme / $collaborateursActifs) * 100)
            : 0;

        // Top 5 des plats les plus consommes ce mois
        $platsPopulaires = LigneMenu::select('plat_id', DB::raw('COUNT(*) as total'))
            ->where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [now()->format('Y-m')])
            ->groupBy('plat_id')
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