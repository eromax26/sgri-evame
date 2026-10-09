<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Commande;
use App\Models\LigneCommande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommandeController extends Controller
{
    /* ==================== LISTE ==================== */

    public function index(Request $request)
    {
        // withCount / withSum évitent une requête par commande pour les totaux
        $query = Commande::with('createur')
            ->withCount('lignes')
            ->withSum('lignes', 'quantite_demandee')
            ->withSum('lignes', 'quantite_livree')
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

        $commandes = $query->paginate(15)->withQueryString();

        return view('commandes.index', compact('commandes'));
    }

    /* ==================== CRÉATION (ÉTAPE 1 : ENTÊTE) ==================== */

    public function create()
    {
        return view('commandes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_commande' => ['required', 'date'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['collaborateur_id'] = Auth::id();
        $validated['statut'] = 'brouillon';

        $commande = Commande::create($validated);

        return redirect()->route('commandes.edit', $commande)
            ->with('success', 'Liste créée. Ajoutez les articles puis validez.');
    }

    /* ==================== DÉTAIL (IMPRESSION + SAISIE DES ACHATS) ==================== */

    public function show(Commande $commande)
    {
        $commande->load(['createur', 'lignes.article']);

        return view('commandes.show', compact('commande'));
    }

    /* ==================== GESTION DES LIGNES ==================== */

    public function edit(Commande $commande)
    {
        if (!$commande->peut_etre_modifiee) {
            return redirect()->route('commandes.show', $commande)
                ->with('error', 'Cette commande ne peut plus être modifiée.');
        }

        $commande->load(['createur', 'lignes.article']);
        $articles = Article::orderBy('libelle')
            ->get(['id', 'libelle', 'unite_mesure', 'quantite_stock', 'seuil_minimum']);

        return view('commandes.edit', compact('commande', 'articles'));
    }

    public function ajouterLigne(Request $request, Commande $commande)
    {
        if (!$commande->peut_etre_modifiee) {
            return back()->with('error', "Impossible d'ajouter une ligne : la commande ne peut plus être modifiée.");
        }

        $validated = $request->validate([
            'article_id' => [
                'required',
                'exists:articles,id',
                Rule::unique('ligne_commandes')->where('commande_id', $commande->id),
            ],
            'quantite_demandee' => ['required', 'numeric', 'min:0.01'],
            'prix_estime_unitaire' => ['nullable', 'numeric', 'min:0'],
            'commentaire' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['commande_id'] = $commande->id;

        LigneCommande::create($validated);
        $commande->recalculerTotalEstime();

        return back()->with('success', 'Article ajouté à la liste.');
    }

    public function modifierLigne(Request $request, Commande $commande, LigneCommande $ligne)
    {
        if ($ligne->commande_id !== $commande->id) {
            abort(404);
        }

        if (!$commande->peut_etre_modifiee) {
            return back()->with('error', 'Impossible de modifier : la commande ne peut plus être modifiée.');
        }

        $validated = $request->validate([
            'quantite_demandee' => ['required', 'numeric', 'min:0.01'],
            'prix_estime_unitaire' => ['nullable', 'numeric', 'min:0'],
            'commentaire' => ['nullable', 'string', 'max:500'],
        ]);

        $ligne->update($validated);
        $commande->recalculerTotalEstime();

        return back()->with('success', 'Ligne modifiée.');
    }

    public function supprimerLigne(Commande $commande, LigneCommande $ligne)
    {
        if ($ligne->commande_id !== $commande->id) {
            abort(404);
        }

        if (!$commande->peut_etre_modifiee) {
            return back()->with('error', 'Impossible de supprimer : la commande ne peut plus être modifiée.');
        }

        $ligne->delete();
        $commande->recalculerTotalEstime();

        return back()->with('success', 'Ligne supprimée.');
    }

    /* ==================== VALIDER (LISTE PRÊTE À IMPRIMER) ==================== */

    public function valider(Commande $commande)
    {
        if (!$commande->peut_etre_validee) {
            return back()->with('error', 'Commande non validable (liste vide ou déjà validée).');
        }

        $commande->valider();

        return redirect()->route('commandes.show', $commande)
            ->with('success', 'Liste validée. Vous pouvez l\'imprimer.');
    }

    /* ==================== ENREGISTRER LES ACHATS (TOUTES LES LIGNES) ==================== */

    public function enregistrerAchats(Request $request, Commande $commande)
    {
        if (!in_array($commande->statut, ['validee', 'livree_partielle'])) {
            return back()->with('error', "Cette commande n'accepte plus d'achats.");
        }

        $validated = $request->validate([
            'date_achat' => ['required', 'date'],
            'lignes' => ['required', 'array'],
            'lignes.*.quantite' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.prix' => ['nullable', 'numeric', 'min:0'],
        ]);

        $nbEnregistrees = 0;

        try {
            // Tout ou rien : une erreur sur une ligne annule l'ensemble
            DB::transaction(function () use ($validated, $commande, &$nbEnregistrees) {
                foreach ($validated['lignes'] as $ligneId => $data) {
                    $quantite = (float) ($data['quantite'] ?? 0);

                    if ($quantite <= 0) {
                        continue; // article non acheté
                    }

                    $ligne = $commande->lignes()->with('article')->findOrFail($ligneId);

                    if (!isset($data['prix']) || $data['prix'] === '') {
                        throw new \RuntimeException("Prix manquant pour « {$ligne->article->libelle} ».");
                    }

                    $ok = $ligne->livrer($quantite, (float) $data['prix'], $validated['date_achat']);

                    if (!$ok) {
                        throw new \RuntimeException(
                            "Quantité refusée pour « {$ligne->article->libelle} » (reste à acheter : {$ligne->reste_a_livrer})."
                        );
                    }

                    $nbEnregistrees++;
                }

                if ($nbEnregistrees === 0) {
                    throw new \RuntimeException('Aucune quantité saisie.');
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $commande->refresh();

        $message = "{$nbEnregistrees} article(s) enregistré(s) en stock.";
        if ($commande->est_livree) {
            $message .= ' Tous les articles de la liste sont achetés.';
        } elseif ($commande->est_livree_partielle) {
            $message .= ' Il reste des articles à acheter.';
        }

        return redirect()->route('commandes.show', $commande)->with('success', $message);
    }

    /* ==================== ACHAT D'UNE SEULE LIGNE (OPTIONNEL) ==================== */

    public function livrerLigne(Request $request, Commande $commande, LigneCommande $ligne)
    {
        if ($ligne->commande_id !== $commande->id) {
            abort(404);
        }

        if (!$ligne->peut_etre_livree) {
            return back()->with('error', 'Cette ligne ne peut pas être enregistrée (commande non validée ou déjà totalement achetée).');
        }

        $validated = $request->validate([
            'quantite_livree' => ['required', 'numeric', 'min:0.01', 'max:' . $ligne->reste_a_livrer],
            'prix_achat_reel' => ['required', 'numeric', 'min:0'],
            'date_livraison' => ['required', 'date'],
        ]);

        $success = $ligne->livrer(
            (float) $validated['quantite_livree'],
            (float) $validated['prix_achat_reel'],
            $validated['date_livraison']
        );

        if (!$success) {
            return back()->with('error', "Erreur lors de l'enregistrement de l'achat.");
        }

        $commande->refresh();

        $message = "Achat enregistré ({$validated['quantite_livree']} {$ligne->article->unite_mesure}).";

        if ($commande->est_livree) {
            $message .= ' Tous les articles de la liste sont achetés.';
        } elseif ($commande->est_livree_partielle) {
            $message .= ' Il reste des articles à acheter.';
        }

        return back()->with('success', $message);
    }

    /* ==================== ANNULER / SUPPRIMER ==================== */

    public function annuler(Commande $commande)
    {
        if (!$commande->peut_etre_annulee) {
            return back()->with('error', 'Commande non annulable (achats déjà enregistrés ou déjà annulée).');
        }

        $commande->annuler();

        return redirect()->route('commandes.index')
            ->with('success', 'Commande annulée.');
    }

    /* ==================== CLÔTURER (RG16) ==================== */

    // RG16 : une liste partiellement livrée est déclarée terminée, le reste à recevoir est abandonné
    public function cloturer(Commande $commande)
    {
        if (!$commande->peut_etre_cloturee) {
            return back()->with('error', 'Seule une liste partiellement livrée peut être clôturée.');
        }

        $commande->cloturer();

        return redirect()->route('commandes.show', $commande)
            ->with('success', 'Liste clôturée : le reste à recevoir n\'est plus attendu.');
    }

    // Suppression réservée aux brouillons (les lignes sont supprimées en cascade)
    public function destroy(Commande $commande)
    {
        if (!$commande->est_brouillon) {
            return back()->with('error', 'Seul un brouillon peut être supprimé. Annulez la commande à la place.');
        }

        $commande->delete();

        return redirect()->route('commandes.index')
            ->with('success', 'Brouillon supprimé.');
    }
}