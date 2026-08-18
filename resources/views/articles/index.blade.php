@extends('layouts.app')

@section('titre', 'Articles de stock')

@section('contenu')

<h5 class="mb-3">Articles de stock</h5>

<a href="{{ route('articles.create') }}" class="btn btn-success btn-sm mb-3">+ Nouvel article</a>

<table class="table table-striped bg-white">
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
                        <span class="badge bg-danger">Stock faible</span>
                    @else
                        <span class="badge bg-success">Normal</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('articles.edit', $article) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Aucun article enregistre.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection