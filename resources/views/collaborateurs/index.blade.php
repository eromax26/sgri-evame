@extends('layouts.app')

@section('titre', 'Collaborateurs')

@section('fil')
    Administration / <strong>Collaborateurs</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-people"></i>Gestion des collaborateurs</h5>

<a href="{{ route('collaborateurs.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouveau collaborateur</a>

<form method="GET" action="{{ route('recherche.collaborateurs') }}" data-sg-live-search class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="recherche" class="form-control" placeholder="Matricule, nom ou prenom" value="{{ request('recherche') }}">
    </div>
    <div class="col-auto">
        <select name="departement_id" class="form-select">
            <option value="">Tous les départements</option>
            @foreach ($departements as $dep)
                <option value="{{ $dep->id }}" @selected(request('departement_id') == $dep->id)>{{ $dep->nom }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="statut" class="form-select">
            <option value="">Tous les statuts</option>
            <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
            <option value="inactif" @selected(request('statut') === 'inactif')>Inactif</option>
            <option value="bloque" @selected(request('statut') === 'bloque')>Bloque</option>
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
            <th>Matricule</th>
            <th>Nom</th>
            <th>Departement</th>
            <th>Fonction</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody data-sg-live-target>
        @include('collaborateurs._lignes', ['collaborateurs' => $collaborateurs])
    </tbody>
</table>
</div>

<div data-sg-live-pagination>
    {{ $collaborateurs->links() }}
</div>

@push('scripts')
    <script src="{{ asset('js/live-search.js') }}"></script>
@endpush

@endsection