@extends('layouts.app')

@section('titre', 'Nouveau menu')

@section('fil')
    Restauration / Planification des menus / <strong>Nouveau menu</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Nouveau menu de la semaine</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('menus.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Date du lundi de la semaine *</label>
                <input type="date" name="date_debut_semaine" class="form-control" value="{{ old('date_debut_semaine') }}">
                <div class="form-text">La semaine ira automatiquement du lundi au vendredi.</div>
                @error('date_debut_semaine') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn sg-btn-primary">Créer le menu</button>
            <a href="{{ route('menus.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection