@extends('layouts.app')

@section('titre', 'Nouvelle societe')

@section('fil')
    Administration / Société / <strong>Nouvelle Société</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-building"></i>Nouvelle société</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('societes.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nom *</label>
                <input type="text" name="nom" class="form-control" value="{{ old('nom') }}">
                @error('nom') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Sigle</label>
                <input type="text" name="sigle" class="form-control" value="{{ old('sigle') }}">
            </div>
            <button type="submit" class="btn sg-btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('societes.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection