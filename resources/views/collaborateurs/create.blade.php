@extends('layouts.app')

@section('titre', 'Nouveau collaborateur')

@section('fil')
    Administration / Collaborateurs / <strong>Nouveau collaborateur</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-people"></i>Nouveau collaborateur</h5>

<div class="card sg-card-form-lg shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('collaborateurs.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Matricule *</label>
                    <input type="text" name="matricule" class="form-control" value="{{ old('matricule') }}">
                    @error('matricule') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Identifiant de connexion *</label>
                    <input type="text" name="identifiant" class="form-control" value="{{ old('identifiant') }}">
                    @error('identifiant') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" value="{{ old('nom') }}">
                    @error('nom') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}">
                    @error('prenom') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" class="form-control">
                    @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Département *</label>
                    <select name="departement_id" class="form-select">
                        <option value="">-- Choisir --</option>
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}" @selected(old('departement_id') == $dep->id)>{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                    @error('departement_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Direction</label>
                    <input type="text" name="direction" class="form-control" value="{{ old('direction') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fonction</label>
                    <input type="text" name="fonction" class="form-control" value="{{ old('fonction') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Site</label>
                    <input type="text" name="site" class="form-control" value="{{ old('site') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date d'entrée</label>
                    <input type="date" name="date_entree" class="form-control" value="{{ old('date_entree') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mode de facturation</label>
                    <input type="text" name="mode_facturation" class="form-control" value="{{ old('mode_facturation') }}">
                </div>
            </div>

            <button type="submit" class="btn sg-btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('collaborateurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection