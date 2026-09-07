@extends('layouts.app')

@section('titre', 'Catalogue des plats')

@section('fil')
    Restauration / <strong>Catalogue des plats</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-egg-fried"></i>Catalogue des plats</h5>

<a href="{{ route('plats.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouveau plat</a>

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
    <tbody>
        @forelse ($plats as $plat)
            <tr>
                <td>
                    @if ($plat->photo)
                        <img src="{{ asset('storage/' . $plat->photo) }}" alt="{{ $plat->libelle }}" style="height: 44px; width: 44px; object-fit: cover; border-radius: 6px;">
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
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
                    <div class="sg-actions">
                        <a href="{{ route('plats.edit', $plat) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                        @if ($plat->statut === 'actif')
                            <form method="POST" action="{{ route('plats.destroy', $plat) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sg-btn-icon sg-btn-icon--danger" title="Désactiver" aria-label="Désactiver"><i class="bi bi-slash-circle"></i></button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('plats.activer', $plat) }}">
                                @csrf
                                <button type="submit" class="sg-btn-icon sg-btn-icon--ok" title="Réactiver" aria-label="Réactiver"><i class="bi bi-arrow-clockwise"></i></button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted"><i class="bi bi-egg-fried me-2"></i>Aucun plat trouvé.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

{{ $plats->links() }}

@endsection