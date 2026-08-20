@extends('layouts.app')

@section('titre', 'Nouveau departement')

@section('fil')
    Administration / Départements / <strong>Nouveau département</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Nouveau département</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('departements.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Sociéte *</label>
                <select name="societe_id" class="form-select">
                    <option value="">-- Choisir --</option>
                    @foreach ($societes as $s)
                        <option value="{{ $s->id }}" @selected(old('societe_id') == $s->id)>{{ $s->nom }}</option>
                    @endforeach
                </select>
                @error('societe_id') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Nom du département *</label>
                <input type="text" name="nom" class="form-control" value="{{ old('nom') }}">
                @error('nom') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn sg-btn-primary">Enregistrer</button>
            <a href="{{ route('departements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection