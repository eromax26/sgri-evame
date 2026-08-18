@extends('layouts.app')

@section('titre', 'Modifier un plat')

@section('fil')
    Restauration / Catalogue des plats / <strong>Modifier un plat</strong>
@endsection

@section('contenu')

<h5 class="mb-3">Modifier {{ $plat->libelle }}</h5>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('plats.update', $plat) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Code du plat *</label>
                <input type="text" name="code_plat" class="form-control" value="{{ old('code_plat', $plat->code_plat) }}">
                @error('code_plat') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Libelle *</label>
                <input type="text" name="libelle" class="form-control" value="{{ old('libelle', $plat->libelle) }}">
                @error('libelle') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $plat->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Categorie</label>
                <input type="text" name="categorie" class="form-control" value="{{ old('categorie', $plat->categorie) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Prix (F CFA) *</label>
                <input type="number" name="prix" class="form-control" value="{{ old('prix', $plat->prix) }}" step="1" min="0">
                @error('prix') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="actif" @selected($plat->statut === 'actif')>Actif</option>
                    <option value="inactif" @selected($plat->statut === 'inactif')>Inactif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('plats.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection