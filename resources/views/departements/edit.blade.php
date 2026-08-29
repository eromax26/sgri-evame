@extends('layouts.app')

@section('titre', 'Modifier un departement')

@section('fil')
    Administration / Départements / <strong>Modifier un département</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-diagram-3"></i>Modifier {{ $departement->nom }}</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('departements.update', $departement) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Societe *</label>
                <select name="societe_id" class="form-select">
                    @foreach ($societes as $s)
                        <option value="{{ $s->id }}" @selected(old('societe_id', $departement->societe_id) == $s->id)>{{ $s->nom }}</option>
                    @endforeach
                </select>
                @error('societe_id') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Nom du département *</label>
                <input type="text" name="nom" class="form-control" value="{{ old('nom', $departement->nom) }}">
                @error('nom') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn sg-btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('departements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection