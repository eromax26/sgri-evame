@extends('layouts.app')

@section('titre', 'Collaborateurs')

@section('fil')
    Administration / <strong>Collaborateurs</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Gestion des collaborateurs</h5>

<a href="{{ route('collaborateurs.create') }}" class="btn sg-btn-primary btn-sm mb-3">+ Nouveau collaborateur</a>

<form method="GET" action="{{ route('collaborateurs.index') }}" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="recherche" class="form-control" placeholder="Matricule, nom ou prenom" value="{{ request('recherche') }}">
    </div>
    <div class="col-auto">
        <select name="departement_id" class="form-select">
            <option value="">Tous les departements</option>
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
        <button type="submit" class="btn sg-btn-navy">Rechercher</button>
    </div>
</form>

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
    <tbody>
        @forelse ($collaborateurs as $c)
            <tr>
                <td>{{ $c->matricule }}</td>
                <td>{{ $c->prenom }} {{ $c->nom }}</td>
                <td>{{ $c->departement->nom ?? '—' }}</td>
                <td>{{ $c->fonction ?? '—' }}</td>
                <td>
                    @if ($c->statut === 'actif')
                        <span class="sg-pill sg-pill--ok">Actif</span>
                    @elseif ($c->statut === 'inactif')
                        <span class="sg-pill sg-pill--off">Inactif</span>
                    @else
                        <span class="sg-pill sg-pill--warn">Bloque</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('collaborateurs.edit', $c) }}" class="btn btn-sm sg-btn-outline">Modifier</a>
                    <a href="{{ route('roles.attribuerForm', $c) }}" class="btn btn-sm sg-btn-outline">Gérer les roles</a>
                    <form method="POST" action="{{ route('collaborateurs.destroy', $c) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm sg-btn-outline-danger">Désactiver</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Aucun collaborateur trouvé.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $collaborateurs->links() }}

@endsection