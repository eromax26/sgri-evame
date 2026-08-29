@extends('layouts.app')

@section('titre', 'Mon profil')

@section('fil')
    Mon compte / <strong>Mon profil</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-person-circle"></i>Mon profil</h5>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-header bg-white sg-card-header">Mes informations</div>
            <div class="card-body">
                <table class="table table-sm mb-0 sg-table-info">
                    <tr><td>Matricule</td><td>{{ $collaborateur->matricule }}</td></tr>
                    <tr><td>Nom</td><td>{{ $collaborateur->nom }}</td></tr>
                    <tr><td>Prenom</td><td>{{ $collaborateur->prenom }}</td></tr>
                    <tr><td>Identifiant</td><td>{{ $collaborateur->identifiant }}</td></tr>
                    <tr><td>Email</td><td>{{ $collaborateur->email ?? '—' }}</td></tr>
                    <tr><td>Telephone</td><td>{{ $collaborateur->telephone ?? '—' }}</td></tr>
                    <tr><td>Societe</td><td>{{ $collaborateur->departement->societe->nom ?? '—' }}</td></tr>
                    <tr><td>Departement</td><td>{{ $collaborateur->departement->nom ?? '—' }}</td></tr>
                    <tr><td>Direction</td><td>{{ $collaborateur->direction ?? '—' }}</td></tr>
                    <tr><td>Fonction</td><td>{{ $collaborateur->fonction ?? '—' }}</td></tr>
                    <tr><td>Site</td><td>{{ $collaborateur->site ?? '—' }}</td></tr>
                    <tr><td>Date d'entree</td><td>{{ $collaborateur->date_entree?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr>
                        <td>Roles</td>
                        <td>
                            @foreach ($collaborateur->roles as $role)
                                <span class="sg-badge-role">{{ $role->libelle }}</span>
                            @endforeach
                        </td>
                    </tr>
                </table>
                <div class="form-text mt-2">
                    Pour toute modification de ces informations, contactez l'Administrateur DSII.
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white sg-card-header">Changer mon mot de passe</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profil.motDePasse') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Ancien mot de passe *</label>
                        <input type="password" name="ancien_mot_de_passe" class="form-control">
                        @error('ancien_mot_de_passe') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe *</label>
                        <input type="password" name="nouveau_mot_de_passe" class="form-control">
                        <div class="form-text">Au moins 8 caracteres.</div>
                        @error('nouveau_mot_de_passe') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer le nouveau mot de passe *</label>
                        <input type="password" name="nouveau_mot_de_passe_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn sg-btn-primary"><i class="bi bi-key"></i> Modifier le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection