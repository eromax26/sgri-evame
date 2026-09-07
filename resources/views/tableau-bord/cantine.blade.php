@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('fil')
    Restauration / <strong>Tableau de bord</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-egg-fried"></i>Tableau de bord</h5>

@if (! $menuSemaine)
    <div class="alert alert-warning d-flex justify-content-between align-items-center gap-2 mb-4">
        <span><i class="bi bi-calendar-x me-2"></i>Aucun menu créé pour la semaine en cours.</span>
        <a href="{{ route('menus.create') }}" class="btn sg-btn-navy btn-sm text-nowrap"><i class="bi bi-plus-lg"></i> Créer le menu</a>
    </div>
@elseif (! $menuSemaine->estPublie())
    <div class="alert alert-warning d-flex justify-content-between align-items-center gap-2 mb-4">
        <span><i class="bi bi-calendar3 me-2"></i>Menu de la semaine en brouillon @if ($joursIncomplets > 0)&mdash; {{ $joursIncomplets }} jour(s) sans plat choisi @endif</span>
        <a href="{{ route('menus.edit', $menuSemaine) }}" class="btn sg-btn-navy btn-sm text-nowrap"><i class="bi bi-pencil"></i> Gérer le menu</a>
    </div>
@else
    <div class="alert alert-success d-flex justify-content-between align-items-center gap-2 mb-4">
        <span><i class="bi bi-check-circle-fill me-2"></i>Menu de la semaine publié</span>
        <a href="{{ route('menus.edit', $menuSemaine) }}" class="btn sg-btn-outline btn-sm text-nowrap"><i class="bi bi-eye"></i> Consulter</a>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="sg-kpi sg-kpi--red">
            <i class="bi bi-cup-hot sg-kpi-icon"></i>
            <small>Portions à préparer aujourd'hui</small>
            <b>{{ $portionsAujourdhui }}</b>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="sg-kpi">
            <i class="bi bi-calendar-plus sg-kpi-icon"></i>
            <small>Portions à préparer demain</small>
            <b>{{ $portionsDemain }}</b>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white sg-card-header"><i class="bi bi-exclamation-triangle"></i>Alertes de stock</div>
            <div class="card-body">
                @forelse ($articlesEnAlerte as $article)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $article->libelle }}</span>
                        @if ($article->estEpuise())
                            <span class="sg-pill sg-pill--critique">Stock épuisé !</span>
                        @else
                            <span class="sg-pill sg-pill--danger">{{ $article->quantite_stock }} {{ $article->unite_mesure }}</span>
                        @endif
                    </div>
                @empty
                    <div class="text-muted"><i class="bi bi-check-circle me-2"></i>Aucune alerte, tous les stocks sont au-dessus du seuil.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white sg-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up-arrow"></i>Prévisions (3 prochains jours)</span>
                <a href="{{ route('menus.previsions') }}" class="small">Voir tout &rarr;</a>
            </div>
            <div class="card-body">
                @forelse ($previsions as $prevision)
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ ucfirst($prevision['date_repas']->translatedFormat('l d/m')) }} &middot; {{ $prevision['plat']->libelle ?? 'Plat supprime' }}</span>
                        <span class="sg-pill sg-pill--info">{{ $prevision['total'] }}</span>
                    </div>
                @empty
                    <div class="text-muted"><i class="bi bi-calendar-x me-2"></i>Aucune réservation sur les 3 prochains jours.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white sg-card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart-line"></i>Classement des plats ce mois</span>
        @if ($maxRepas > 0)
            <small class="text-muted fw-normal">Du plus au moins commandé</small>
        @endif
    </div>
    <div class="card-body">
        @if ($classementPlats->isEmpty())
            <div class="text-muted"><i class="bi bi-calendar-x me-2"></i>Aucun plat proposé au menu ce mois.</div>
        @elseif ($maxRepas === 0)
            {{-- Classer des plats tous a zero n'aurait aucun sens : on l'annonce clairement. --}}
            <div class="text-muted"><i class="bi bi-egg-fried me-2"></i>{{ $classementPlats->count() }} plat(s) proposé(s) au menu, aucun repas consommé pour l'instant.</div>
        @else
            @foreach ($classementPlats as $index => $plat)
                <div class="sg-rang @if ($plat['total'] === 0) sg-rang--vide @endif">
                    <span class="sg-rang-num">{{ $index + 1 }}.</span>
                    <span class="sg-rang-nom">{{ $plat['libelle'] }}</span>
                    <span class="sg-rang-barre">
                        <span class="sg-rang-barre-remplie" style="width: {{ round($plat['total'] / $maxRepas * 100) }}%"></span>
                    </span>
                    <span class="sg-rang-total">{{ $plat['total'] }} repas</span>
                </div>
            @endforeach
        @endif
    </div>
</div>

@endsection
