@extends('layouts.app')

@section('titre', 'Articles de stock')

@section('fil')
    Gestion du stock / <strong>Articles</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-box-seam"></i>Articles de stock</h5>

<a href="{{ route('articles.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouvel article</a>

<form method="GET" action="{{ route('articles.index') }}" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="recherche" class="form-control" placeholder="Rechercher un article" value="{{ request('recherche') }}">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn sg-btn-navy"><i class="bi bi-search"></i> Rechercher</button>
    </div>
</form>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Article</th>
            <th>Unite</th>
            <th>Quantite en stock</th>
            <th>Seuil minimum</th>
            <th>Etat</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($articles as $article)
            <tr>
                <td>{{ $article->libelle }}</td>
                <td>{{ $article->unite_mesure }}</td>
                <td>{{ $article->quantite_stock }}</td>
                <td>{{ $article->seuil_minimum }}</td>
                <td>
                    @if ($article->estEpuise())
                        <span class="sg-pill sg-pill--critique">Stock épuisé !</span>
                    @elseif ($article->seuilAtteint())
                        <span class="sg-pill sg-pill--danger">Stock faible</span>
                    @else
                        <span class="sg-pill sg-pill--ok">Normal</span>
                    @endif
                </td>
                <td>
                    <div class="sg-actions">
                        <a href="{{ route('articles.edit', $article) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted"><i class="bi bi-box-seam me-2"></i>Aucun article enregistré.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

{{ $articles->links() }}

@endsection