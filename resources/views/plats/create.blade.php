@extends('layouts.app')

@section('titre', 'Nouveau plat')

@section('fil')
    Restauration / Catalogue des plats / <strong>Nouveau plat</strong>
@endsection

@section('contenu')

<h5 class="mb-3">Nouveau plat</h5>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('plats.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Code du plat *</label>
                <input type="text" name="code_plat" class="form-control" value="{{ old('code_plat') }}">
                @error('code_plat') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Libelle *</label>
                <input type="text" name="libelle" class="form-control" value="{{ old('libelle') }}">
                @error('libelle') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Categorie</label>
                <input type="text" name="categorie" class="form-control" value="{{ old('categorie') }}" placeholder="Ex: Plat principal, Accompagnement...">
            </div>
            <div class="mb-3">
                <label class="form-label">Prix (F CFA) *</label>
                <input type="number" name="prix" class="form-control" value="{{ old('prix') }}" step="1" min="0">
                @error('prix') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('plats.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection