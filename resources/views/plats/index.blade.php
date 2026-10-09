@extends('layouts.app')

@section('titre', 'Catalogue des plats')

@section('fil')
    Restauration / <strong>Catalogue des plats</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-egg-fried"></i>Catalogue des plats</h5>

<a href="{{ route('plats.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouveau plat</a>

<form method="GET" action="{{ route('recherche.plats') }}" data-sg-live-search class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="recherche" class="form-control" placeholder="Rechercher un plat" value="{{ request('recherche') }}">
    </div>
    <div class="col-auto">
        <select name="statut" class="form-select">
            <option value="">Tous les statuts</option>
            <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
            <option value="inactif" @selected(request('statut') === 'inactif')>Inactif</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn sg-btn-navy"><i class="bi bi-search"></i> Rechercher</button>
    </div>
</form>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Photo</th>
            <th>Code</th>
            <th>Libelle</th>
            <th>Categorie</th>
            <th>Prix</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody data-sg-live-target>
        @include('plats._lignes', ['plats' => $plats])
    </tbody>
</table>
</div>

<div data-sg-live-pagination>
    {{ $plats->links() }}
</div>

@push('scripts')
    <script src="{{ asset('js/live-search.js') }}"></script>
@endpush

@endsection