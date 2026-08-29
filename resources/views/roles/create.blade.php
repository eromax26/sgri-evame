@extends('layouts.app')

@section('titre', 'Nouveau role')

@section('fil')
    Administration / Roles / <strong>Nouveau role</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-shield-lock"></i>Nouveau role</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Libellé *</label>
                <input type="text" name="libelle" class="form-control" value="{{ old('libelle') }}">
                @error('libelle') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}">
            </div>
            <button type="submit" class="btn sg-btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection