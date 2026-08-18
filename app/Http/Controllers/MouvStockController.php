<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\MouvStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MouvStockController extends Controller
{
    public function index()
    {
        $mouvements = MouvStock::with('article', 'collaborateur')
            ->orderBy('date_mouvement', 'desc')
            ->paginate(20);

        return view('mouv-stocks.index', compact('mouvements'));
    }

    public function create()
    {
        $articles = Article::orderBy('libelle')->get();

        return view('mouv-stocks.create', compact('articles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
            'type_mouvement' => ['required', 'in:entree,sortie'],
            'quantite' => ['required', 'numeric', 'min:0.01'],
            'date_mouvement' => ['required', 'date'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'motif_sortie' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['collaborateur_id'] = Auth::id();

        $erreurStock = null;

        // Verrou pessimiste : empeche deux sorties simultanees de faire passer le stock en negatif (RG11)
        $article = DB::transaction(function () use ($validated, &$erreurStock) {
            $article = Article::where('id', $validated['article_id'])->lockForUpdate()->firstOrFail();

            if ($validated['type_mouvement'] === 'sortie' && $validated['quantite'] > $article->quantite_stock) {
                $erreurStock = "Stock insuffisant : il ne reste que {$article->quantite_stock} {$article->unite_mesure}.";

                return null;
            }

            MouvStock::create($validated);

            if ($validated['type_mouvement'] === 'entree') {
                $article->increment('quantite_stock', $validated['quantite']);
            } else {
                $article->decrement('quantite_stock', $validated['quantite']);
            }

            return $article->refresh();
        });

        if ($erreurStock) {
            return back()->withInput()->with('error', $erreurStock);
        }

        $message = 'Mouvement enregistre avec succes.';

        if ($article->seuilAtteint()) {
            $message .= " Attention : le stock de {$article->libelle} est au seuil minimum.";
        }

        return redirect()->route('mouv-stocks.index')->with('success', $message);
    }
}