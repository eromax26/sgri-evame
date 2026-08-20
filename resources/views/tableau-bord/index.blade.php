@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('fil')
    Pilotage / <strong>Tableau de bord</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Tableau de bord</h5>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="sg-kpi sg-kpi--red">
            <small>Repas servis aujourd'hui</small>
            <b>{{ $repasAujourdhui }}</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sg-kpi">
            <small>Repas servis ce mois</small>
            <b>{{ $repasCeMois }}</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sg-kpi sg-kpi--ok">
            <small>Taux de frequentation</small>
            <b>{{ $tauxFrequentation }}%</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sg-kpi sg-kpi--warn">
            <small>Montant facture ce mois</small>
            <b>{{ number_format($montantCeMois, 0, ',', ' ') }} F</b>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white sg-card-header">Suivi des tickets</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Demandes en attente d'impression</span>
                    <span class="sg-pill sg-pill--warn">{{ $ticketsEnAttente }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Tickets imprimes non retires</span>
                    <span class="sg-pill sg-pill--info">{{ $ticketsNonRetires }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white sg-card-header">Alertes de stock</div>
            <div class="card-body">
                @forelse ($articlesEnAlerte as $article)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $article->libelle }}</span>
                        <span class="sg-pill sg-pill--danger">{{ $article->quantite_stock }} {{ $article->unite_mesure }}</span>
                    </div>
                @empty
                    <div class="text-muted">Aucune alerte, tous les stocks sont au-dessus du seuil.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white sg-card-header">Plats les plus consommes ce mois</div>
    <div class="card-body">
        @forelse ($platsPopulaires as $index => $plat)
            <div class="d-flex justify-content-between mb-2">
                <span><strong style="color: var(--sg-bleu-marine);">{{ $index + 1 }}.</strong> {{ $plat['libelle'] }}</span>
                <span class="text-muted">{{ $plat['total'] }} repas</span>
            </div>
        @empty
            <div class="text-muted">Aucune consommation enregistree ce mois.</div>
        @endforelse
    </div>
</div>

@endsection