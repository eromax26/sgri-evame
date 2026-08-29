@extends('layouts.app')

@section('titre', 'Modifier un article')

@section('fil')
    Gestion du stock / Articles / <strong>Modifier un article</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-box-seam"></i>Modifier {{ $article->libelle }}</h5>

<div class="card sg-card-form shadow-sm border-0">
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
            <button type="submit" class="btn sg-btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection