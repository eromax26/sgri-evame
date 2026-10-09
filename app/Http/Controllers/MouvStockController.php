<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\MouvStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MouvStockController extends Controller
{
    public function index()
    {
        $mouvements = MouvStock::with('article', 'collaborateur')
            ->orderBy('date_mouvement', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('mouv-stocks.index', compact('mouvements'));
    }

    public function create()
    {
        $articles = Article::orderBy('libelle')->get();
        $motifs = MouvStock::MOTIFS_SORTIE;

        return view('mouv-stocks.create', compact('articles', 'motifs'));
    }

    // Ce module ne traite que les sorties : les entrees sont creees par les receptions de commandes
    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
            'quantite' => ['required', 'numeric', 'min:0.01'],
            'date_mouvement' => ['required', 'date'],
            'motif_sortie' => ['required', Rule::in(MouvStock::MOTIFS_SORTIE)],
        ]);

        $article = Article::findOrFail($validated['article_id']);

        $enregistree = $article->enregistrerSortie(
            (float) $validated['quantite'],
            $validated['motif_sortie'],
            $validated['date_mouvement'],
            Auth::id(),
        );

        // RG11 : une sortie est refusee si la quantite depasse le stock disponible
        if (!$enregistree) {
            return back()->withInput()->with(
                'error',
                "Stock insuffisant : il ne reste que {$article->quantite_stock} {$article->unite_mesure} de {$article->libelle}."
            );
        }

        $message = 'Sortie de stock enregistree avec succes.';

        if ($article->seuilAtteint()) {
            $message .= " Attention : le stock de {$article->libelle} est au seuil minimum.";
        }

        return redirect()->route('mouv-stocks.index')->with('success', $message);
    }
}