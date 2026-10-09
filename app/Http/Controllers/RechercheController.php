<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Collaborateur;
use App\Models\Commande;
use App\Models\Departement;
use App\Models\Plat;
use Illuminate\Http\Request;

/**
 * Fragments HTML des listes filtrables, pour la recherche dynamique.
 *
 * Chaque méthode renvoie le meme contenu que la vue complete, mais limite au
 * <tbody> et a la pagination. Le filtrage reste entierement defini par les
 * contrôleurs concernes : on reutilise leurs requetes plutot que de les dupliquer,
 * afin qu'un critere ajoute a une liste s'applique aussi a la recherche dynamique.
 */
class RechercheController extends Controller
{
    public function articles(Request $request)
    {
        $articles = $this->requeteArticles($request)->paginate(15)->withQueryString();

        return $this->fragment('articles._lignes', compact('articles'));
    }

    public function collaborateurs(Request $request)
    {
        $collaborateurs = $this->requeteCollaborateurs($request)->paginate(15)->withQueryString();

        return $this->fragment('collaborateurs._lignes', compact('collaborateurs'));
    }

    public function plats(Request $request)
    {
        $plats = $this->requetePlats($request)->paginate(15)->withQueryString();

        return $this->fragment('plats._lignes', compact('plats'));
    }

    public function commandes(Request $request)
    {
        $commandes = $this->requeteCommandes($request)->paginate(15)->withQueryString();

        return $this->fragment('commandes._lignes', compact('commandes'));
    }

    /** Requetes de filtrage, alignees sur celles des controleurs de liste. */

    private function requeteArticles(Request $request)
    {
        $query = Article::query();

        if ($request->filled('recherche')) {
            $query->where('libelle', 'like', '%' . $request->recherche . '%');
        }

        return $query->orderBy('libelle');
    }

    private function requeteCollaborateurs(Request $request)
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

        return $query->orderBy('nom');
    }

    private function requetePlats(Request $request)
    {
        $query = Plat::query();

        if ($request->filled('recherche')) {
            $query->where('libelle', 'like', '%' . $request->recherche . '%');
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return $query->orderBy('libelle');
    }

    private function requeteCommandes(Request $request)
    {
        $query = Commande::with('createur')
            ->withCount('lignes')
            ->withSum('lignes', 'quantite_demandee')
            ->orderBy('date_commande', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->where('date_commande', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('date_commande', '<=', $request->date_fin);
        }

        if ($request->filled('recherche')) {
            $recherche = $request->recherche;
            $query->where(function ($q) use ($recherche) {
                $q->where('id', 'like', "%{$recherche}%")
                  ->orWhereHas('createur', fn ($q) => $q->where('nom', 'like', "%{$recherche}%")
                                                        ->orWhere('prenom', 'like', "%{$recherche}%"))
                  ->orWhereHas('lignes.article', fn ($q) => $q->where('libelle', 'like', "%{$recherche}%"));
            });
        }

        return $query;
    }

    /** Construit la reponse JSON consommee par public/js/live-search.js */
    private function fragment(string $vue, array $donnees)
    {
        $lignes = view($vue, $donnees)->render();

        // Les liens de pagination doivent pointer sur l'URL complete des controleurs,
        // le JS s'en sert pour recharger la bonne page.
        $pagination = view('layouts.pagination', ['elements' => reset($donnees)->links()])->render();

        return response()->json([
            'lignes' => $lignes,
            'pagination' => $pagination,
            'total' => reset($donnees)->total(),
        ]);
    }
}