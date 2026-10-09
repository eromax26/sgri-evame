@extends('layouts.app')

@section('titre', 'Articles de stock')

@section('fil')
    Gestion du stock / <strong>Articles</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-box-seam"></i>Articles de stock</h5>

<a href="{{ route('articles.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouvel article</a>

<form method="GET" action="{{ route('recherche.articles') }}" data-sg-live-search class="row g-2 mb-3">
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
            <th>À recevoir</th>
            <th>Valeur estimée</th>
            <th>Etat</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody data-sg-live-target>
        @include('articles._lignes', ['articles' => $articles])
    </tbody>
</table>
</div>

<div data-sg-live-pagination>
    {{ $articles->links() }}
</div>

@push('scripts')
    <script src="{{ asset('js/live-search.js') }}"></script>
@endpush

@endsection