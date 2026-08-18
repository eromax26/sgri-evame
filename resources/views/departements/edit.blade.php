@extends('layouts.app')

@section('titre', 'Modifier un departement')

@section('contenu')

<h5 class="mb-3">Modifier {{ $departement->nom }}</h5>

<div class="card" style="max-width: 500px;">
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
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('departements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection