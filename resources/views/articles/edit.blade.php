@extends('layouts.app')

@section('titre', 'Modifier un article')

@section('contenu')

<h5 class="mb-3">Modifier {{ $article->libelle }}</h5>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <form method="POST" action="{{ route('articles.update', $article) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Libellé *</label>
                <input type="text" name="libelle" class="form-control" value="{{ old('libelle', $article->libelle) }}">
                @error('libelle') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Unité de mesure *</label>
                <input type="text" name="unite_mesure" class="form-control" value="{{ old('unite_mesure', $article->unite_mesure) }}">
                @error('unite_mesure') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Seuil minimum *</label>
                <input type="number" name="seuil_minimum" class="form-control" value="{{ old('seuil_minimum', $article->seuil_minimum) }}" step="0.01" min="0">
                @error('seuil_minimum') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Quantité actuelle en stock</label>
                <input type="text" class="form-control" value="{{ $article->quantite_stock }} {{ $article->unite_mesure }}" disabled>
                <div class="form-text">Modifiable uniquement via les mouvements de stock.</div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection