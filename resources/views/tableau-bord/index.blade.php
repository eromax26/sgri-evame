@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('fil')
    Pilotage / <strong>Tableau de bord</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-speedometer2"></i>Tableau de bord</h5>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="sg-kpi sg-kpi--red">
            <i class="bi bi-cup-hot sg-kpi-icon"></i>
            <small>Repas servis aujourd'hui</small>
            <b>{{ $repasAujourdhui }}</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sg-kpi">
            <i class="bi bi-calendar-month sg-kpi-icon"></i>
            <small>Repas servis ce mois</small>
            <b>{{ $repasCeMois }}</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sg-kpi sg-kpi--ok">
            <i class="bi bi-graph-up sg-kpi-icon"></i>
            <small>Taux de fréquentation</small>
            <b>{{ $tauxFrequentation }}%</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sg-kpi sg-kpi--warn">
            <i class="bi bi-cash-stack sg-kpi-icon"></i>
            <small>Montant facturé ce mois</small>
            <b>{{ number_format($montantCeMois, 0, ',', ' ') }} F</b>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white sg-card-header"><i class="bi bi-ticket-perforated"></i>Suivi des tickets</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Demandes en attente d'impression</span>
                    <span class="sg-pill sg-pill--warn">{{ $ticketsEnAttente }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Tickets imprimés non retirés</span>
                    <span class="sg-pill sg-pill--info">{{ $ticketsNonRetires }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white sg-card-header"><i class="bi bi-exclamation-triangle"></i>Alertes de stock</div>
            <div class="card-body">
                @forelse ($articlesEnAlerte as $article)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $article->libelle }}</span>
                        <span class="sg-pill sg-pill--danger">{{ $article->quantite_stock }} {{ $article->unite_mesure }}</span>
                    </div>
                @empty
                    <div class="text-muted"><i class="bi bi-check-circle me-2"></i>Aucune alerte, tous les stocks sont au-dessus du seuil.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white sg-card-header"><i class="bi bi-egg-fried"></i>Plats les plus consommés ce mois</div>
    <div class="card-body">
        @forelse ($platsPopulaires as $index => $plat)
            <div class="d-flex justify-content-between mb-2">
                <span><strong style="color: var(--sg-bleu-marine);">{{ $index + 1 }}.</strong> {{ $plat['libelle'] }}</span>
                <span class="text-muted">{{ $plat['total'] }} repas</span>
            </div>
        @empty
            <div class="text-muted"><i class="bi bi-egg-fried me-2"></i>Aucune consommation enregistrée ce mois.</div>
        @endforelse
    </div>
</div>

@endsection