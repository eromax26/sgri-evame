@extends('layouts.app')

@section('titre', 'Commandes fournisseurs')

@section('fil')
    Gestion du stock / <strong>Commandes</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-truck"></i>Commandes </h5>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <a href="{{ route('commandes.create') }}" class="btn sg-btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nouvelle commande</a>

    <form method="GET" action="{{ route('recherche.commandes') }}" data-sg-live-search class="row g-2 flex-grow-1 d-flex align-items-end" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="recherche" class="form-control form-control-sm" placeholder="Rechercher (N°, fournisseur, article...)" value="{{ request('recherche') }}">
        </div>
        <div class="col-auto">
            <select name="statut" class="form-select form-select-sm">
                <option value="">Tous statuts</option>
                <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                <option value="validee" {{ request('statut') === 'validee' ? 'selected' : '' }}>Validée</option>
                <option value="livree_partielle" {{ request('statut') === 'livree_partielle' ? 'selected' : '' }}>Livrée partiellement</option>
                <option value="livree" {{ request('statut') === 'livree' ? 'selected' : '' }}>Livrée</option>
                <option value="cloturee" {{ request('statut') === 'cloturee' ? 'selected' : '' }}>Clôturée</option>
                <option value="annulee" {{ request('statut') === 'annulee' ? 'selected' : '' }}>Annulée</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}" title="Date début">
        </div>
        <div class="col-auto">
            <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}" title="Date fin">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i> Filtrer</button>
        </div>
        <div class="col-auto">
            <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
        </div>
    </form>
</div>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>N°</th>
            <th>Date</th>
            <th>Créateur</th>
            <th>Nb articles</th>
            <th>Qté totale</th>
            <th>Total estimé</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody data-sg-live-target>
        @include('commandes._lignes', ['commandes' => $commandes])
    </tbody>
</table>
</div>

<div data-sg-live-pagination>
    {{ $commandes->links() }}
</div>

@push('scripts')
    <script src="{{ asset('js/live-search.js') }}"></script>
@endpush

@endsection