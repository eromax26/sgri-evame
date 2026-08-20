@extends('layouts.app')

@section('titre', 'Modifier un role')

@section('fil')
    Administration / Roles / <strong>Modifier un role</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Modifier le role</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Libelle *</label>
                <input type="text" name="libelle" class="form-control" value="{{ old('libelle', $role->libelle) }}">
                @error('libelle') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $role->description) }}">
            </div>
            <button type="submit" class="btn sg-btn-primary">Enregistrer</button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection