@extends('layouts.app')

@section('titre', 'Catalogue des plats')

@section('fil')
    Restauration / <strong>Catalogue des plats</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Catalogue des plats</h5>

<a href="{{ route('plats.create') }}" class="btn sg-btn-primary btn-sm mb-3">+ Nouveau plat</a>

<form method="GET" action="{{ route('plats.index') }}" class="row g-2 mb-3">
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
        <button type="submit" class="btn sg-btn-navy">Rechercher</button>
    </div>
</form>

<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Libelle</th>
            <th>Categorie</th>
            <th>Prix</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($plats as $plat)
            <tr>
                <td>{{ $plat->code_plat }}</td>
                <td>{{ $plat->libelle }}</td>
                <td>{{ $plat->categorie ?? '—' }}</td>
                <td>{{ $plat->prix ? number_format($plat->prix, 0, ',', ' ') . ' F' : 'A definir' }}</td>
                <td>
                    <span class="sg-pill {{ $plat->statut === 'actif' ? 'sg-pill--ok' : 'sg-pill--off' }}">
                        {{ ucfirst($plat->statut) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('plats.edit', $plat) }}" class="btn btn-sm sg-btn-outline">Modifier</a>
                    <form method="POST" action="{{ route('plats.destroy', $plat) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm sg-btn-outline-danger">Desactiver</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Aucun plat trouve.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $plats->links() }}

@endsection