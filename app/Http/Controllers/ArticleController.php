<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->filled('recherche')) {
            $query->where('libelle', 'like', '%' . $request->recherche . '%');
        }

        $articles = $query->orderBy('libelle')->paginate(15)->withQueryString();

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'unite_mesure' => ['required', 'string', 'max:20'],
            'seuil_minimum' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['quantite_stock'] = 0;

        Article::create($validated);

        return redirect()->route('articles.index')->with('success', 'Article créé avec succès.');
    }

    public function edit(Article $article)
    {
        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'unite_mesure' => ['required', 'string', 'max:20'],
            'seuil_minimum' => ['required', 'numeric', 'min:0'],
        ]);

        $article->update($validated);

        return redirect()->route('articles.index')->with('success', 'Article modifié avec succès.');
    }
}