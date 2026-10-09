@extends('layouts.app')

@section('titre', 'Nouvelle commande')

@section('fil')
    Gestion du stock / Commandes / <strong>Nouvelle commande</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-truck"></i>Nouvelle commande</h5>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('commandes.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Date de commande *</label>
                    <input type="date" name="date_commande" class="form-control" value="{{ old('date_commande', now()->format('Y-m-d')) }}" required>
                    @error('date_commande') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Créateur</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->nom }} {{ Auth::user()->prenom }}" readonly>
                    <div class="form-text text-muted">Vous (Responsable cantine)</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire" class="form-control" rows="3" placeholder="Notes, fournisseur, conditions...">{{ old('commentaire') }}</textarea>
                    @error('commentaire') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>

            <hr class="my-3">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i> Annuler</a>
                <button type="submit" class="btn sg-btn-primary"><i class="bi bi-plus-lg"></i> Créer la commande</button>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info mt-3">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Prochaine étape :</strong> Après création, vous pourrez ajouter les articles (lignes) puis valider la commande.
</div>

@endsection