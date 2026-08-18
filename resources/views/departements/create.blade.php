@extends('layouts.app')

@section('titre', 'Nouveau departement')

@section('contenu')

<h5 class="mb-3">Nouveau département</h5>

<div class="card" style="max-width: 500px;">
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
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('departements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection