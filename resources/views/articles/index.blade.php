@extends('layouts.app')

@section('titre', 'Articles de stock')

@section('fil')
    Gestion du stock / <strong>Articles</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Articles de stock</h5>

<a href="{{ route('articles.create') }}" class="btn sg-btn-primary btn-sm mb-3">+ Nouvel article</a>

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
                    @if ($article->seuilAtteint())
                        <span class="sg-pill sg-pill--danger">Stock faible</span>
                    @else
                        <span class="sg-pill sg-pill--ok">Normal</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('articles.edit', $article) }}" class="btn btn-sm sg-btn-outline">Modifier</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Aucun article enregistre.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection