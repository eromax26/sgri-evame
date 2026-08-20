@extends('layouts.app')

@section('titre', 'Nouvel article')

@section('fil')
    Gestion du stock / Articles / <strong>Nouvel article</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Nouvel article</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('articles.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Libelle *</label>
                <input type="text" name="libelle" class="form-control" value="{{ old('libelle') }}" placeholder="Ex: Riz local, Huile de palme...">
                @error('libelle') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Unite de mesure *</label>
                <input type="text" name="unite_mesure" class="form-control" value="{{ old('unite_mesure') }}" placeholder="kg, litre, carton, unite...">
                @error('unite_mesure') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Seuil minimum *</label>
                <input type="number" name="seuil_minimum" class="form-control" value="{{ old('seuil_minimum', 0) }}" step="0.01" min="0">
                <div class="form-text">Une alerte s'affichera quand le stock descend a ce niveau.</div>
                @error('seuil_minimum') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn sg-btn-primary">Enregistrer</button>
            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection