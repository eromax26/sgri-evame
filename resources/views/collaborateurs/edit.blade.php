@extends('layouts.app')

@section('titre', 'Modifier un collaborateur')

@section('fil')
    Administration / Collaborateurs / <strong>Modifier un collaborateur</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Modifier {{ $collaborateur->prenom }} {{ $collaborateur->nom }}</h5>

<div class="card sg-card-form-lg shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('collaborateurs.update', $collaborateur) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Matricule *</label>
                    <input type="text" name="matricule" class="form-control" value="{{ old('matricule', $collaborateur->matricule) }}">
                    @error('matricule') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Identifiant de connexion *</label>
                    <input type="text" name="identifiant" class="form-control" value="{{ old('identifiant', $collaborateur->identifiant) }}">
                    @error('identifiant') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" value="{{ old('nom', $collaborateur->nom) }}">
                    @error('nom') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $collaborateur->prenom) }}">
                    @error('prenom') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $collaborateur->email) }}">
                    @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $collaborateur->telephone) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control">
                    <div class="form-text">Laisser vide pour conserver le mot de passe actuel.</div>
                    @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Statut *</label>
                    <select name="statut" class="form-select">
                        <option value="actif" @selected(old('statut', $collaborateur->statut) === 'actif')>Actif</option>
                        <option value="inactif" @selected(old('statut', $collaborateur->statut) === 'inactif')>Inactif</option>
                        <option value="bloque" @selected(old('statut', $collaborateur->statut) === 'bloque')>Bloque</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Département *</label>
                    <select name="departement_id" class="form-select">
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}" @selected(old('departement_id', $collaborateur->departement_id) == $dep->id)>{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                    @error('departement_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Direction</label>
                    <input type="text" name="direction" class="form-control" value="{{ old('direction', $collaborateur->direction) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fonction</label>
                    <input type="text" name="fonction" class="form-control" value="{{ old('fonction', $collaborateur->fonction) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Site</label>
                    <input type="text" name="site" class="form-control" value="{{ old('site', $collaborateur->site) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date d'entrée</label>
                    <input type="date" name="date_entree" class="form-control" value="{{ old('date_entree', $collaborateur->date_entree?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mode de facturation</label>
                    <input type="text" name="mode_facturation" class="form-control" value="{{ old('mode_facturation', $collaborateur->mode_facturation) }}">
                </div>
            </div>

            <button type="submit" class="btn sg-btn-primary">Enregistrer les modifications</button>
            <a href="{{ route('collaborateurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

@endsection