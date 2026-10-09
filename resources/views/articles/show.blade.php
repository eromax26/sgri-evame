@extends('layouts.app')

@section('titre', 'Historique du stock')

@section('fil')
    Gestion du stock / Articles / <strong>{{ $article->libelle }}</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-clock-history"></i>Historique du stock : {{ $article->libelle }}</h5>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small">Stock actuel</div>
                <div class="fs-4">{{ number_format($article->quantite_stock, 2, ',', ' ') }} {{ $article->unite_mesure }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small">Seuil minimum</div>
                <div class="fs-4">{{ number_format($article->seuil_minimum, 2, ',', ' ') }} {{ $article->unite_mesure }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small">État</div>
                <div class="fs-5">
                    @if ($article->estEpuise())
                        <span class="sg-pill sg-pill--critique">Stock épuisé</span>
                    @elseif ($article->seuilAtteint())
                        <span class="sg-pill sg-pill--danger">Stock faible</span>
                    @else
                        <span class="sg-pill sg-pill--ok">Normal</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<a href="{{ route('mouv-stocks.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-box-arrow-up"></i> Enregistrer une sortie</a>
<a href="{{ route('articles.edit', $article) }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-pencil"></i> Modifier l'article</a>
<a href="{{ route('articles.index') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Retour aux articles</a>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Quantité</th>
            <th>Motif / Prix</th>
            <th>Origine</th>
            <th>Enregistré par</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($mouvements as $mouv)
            <tr>
                <td>{{ $mouv->date_mouvement->format('d/m/Y') }}</td>
                <td>
                    @if ($mouv->est_entree)
                        <span class="sg-pill sg-pill--ok">Entrée</span>
                    @else
                        <span class="sg-pill sg-pill--danger">Sortie</span>
                    @endif
                </td>
                <td>
                    <span class="{{ $mouv->est_entree ? 'text-success' : 'text-danger' }}">
                        {{ $mouv->est_entree ? '+' : '-' }}{{ number_format($mouv->quantite, 2, ',', ' ') }}
                    </span>
                    {{ $article->unite_mesure }}
                </td>
                <td>
                    @if ($mouv->est_entree)
                        {{ $mouv->prix_achat ? number_format($mouv->prix_achat, 0, ',', ' ') . ' F / unité' : '—' }}
                    @else
                        {{ $mouv->motif_sortie ?? '—' }}
                    @endif
                </td>
                <td>
                    @if ($mouv->est_lie_a_commande)
                        <a href="{{ route('commandes.show', $mouv->commande_id) }}" class="text-decoration-none">Commande #{{ $mouv->commande_id }}</a>
                        @if ($mouv->ligne_commande_id)
                            <small class="text-muted">- Ligne #{{ $mouv->ligne_commande_id }}</small>
                        @endif
                    @else
                        <small class="text-muted">Saisie manuelle</small>
                    @endif
                </td>
                <td>{{ $mouv->collaborateur->nom }} {{ $mouv->collaborateur->prenom }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted"><i class="bi bi-clock-history me-2"></i>Aucun mouvement enregistré pour cet article.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

{{ $mouvements->links() }}

@endsection
