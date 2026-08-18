@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('contenu')

<h5 class="mb-3">Tableau de bord</h5>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Repas servis aujourd'hui</div>
                <div class="fs-3 fw-bold text-primary">{{ $repasAujourdhui }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Repas servis ce mois</div>
                <div class="fs-3 fw-bold text-primary">{{ $repasCeMois }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Taux de frequentation</div>
                <div class="fs-3 fw-bold text-primary">{{ $tauxFrequentation }}%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Montant facture ce mois</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($montantCeMois, 0, ',', ' ') }} F</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">Suivi des tickets</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Demandes en attente d'impression</span>
                    <span class="badge bg-warning text-dark">{{ $ticketsEnAttente }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Tickets imprimes non retires</span>
                    <span class="badge bg-info text-dark">{{ $ticketsNonRetires }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">Alertes de stock</div>
            <div class="card-body">
                @forelse ($articlesEnAlerte as $article)
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $article->libelle }}</span>
                        <span class="badge bg-danger">{{ $article->quantite_stock }} {{ $article->unite_mesure }}</span>
                    </div>
                @empty
                    <div class="text-muted">Aucune alerte, tous les stocks sont au-dessus du seuil.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">Plats les plus consommes ce mois</div>
    <div class="card-body">
        @forelse ($platsPopulaires as $index => $plat)
            <div class="d-flex justify-content-between mb-2">
                <span><strong class="text-primary">{{ $index + 1 }}.</strong> {{ $plat['libelle'] }}</span>
                <span class="text-muted">{{ $plat['total'] }} repas</span>
            </div>
        @empty
            <div class="text-muted">Aucune consommation enregistree ce mois.</div>
        @endforelse
    </div>
</div>

@endsection