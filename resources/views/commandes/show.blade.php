@extends('layouts.app')

@section('titre', 'Commande #' . $commande->id)

@section('fil')
    Gestion du stock / <a href="{{ route('commandes.index') }}">Commandes</a> / <strong>#{{ $commande->id }}</strong>
@endsection

@push('styles')
<style>
    /* Titre réservé à la version imprimée (bon de commande fournisseur) */
    .sg-print-title { display: none; }

    @media print {
        .sg-sidebar,
        .sg-sidebar-backdrop,
        .sg-topbar,
        .sg-no-print,
        .alert {
            display: none !important;
        }
        .sg-main {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        body { background: #fff !important; }
        .card {
            border: 0 !important;
            box-shadow: none !important;
        }
        .sg-print-title { display: block !important; }
        a[href]::after { content: none !important; }
    }
</style>
@endpush

@section('contenu')

@php
    $resteTotal = $commande->lignes->sum(fn ($l) => $l->reste_a_livrer);
    $totalReel = $commande->lignes->sum(fn ($l) => $l->total_reel);
    $peutReceptionner = in_array($commande->statut, ['validee', 'livree_partielle']) && $resteTotal > 0;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 sg-no-print">
    <h5 class="sg-page-title mb-0"><i class="bi bi-truck"></i>Commande #{{ $commande->id }}</h5>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn sg-btn-outline btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimer</button>

        @if ($commande->peut_etre_modifiee)
            <a href="{{ route('commandes.edit', $commande) }}" class="btn sg-btn-outline btn-sm"><i class="bi bi-pencil"></i> Modifier la liste</a>
        @endif

        @if ($commande->peut_etre_validee)
            <form method="POST" action="{{ route('commandes.valider', $commande) }}" onsubmit="return confirm('Valider cette commande ? Elle ne sera plus modifiable.');">
                @csrf
                <button type="submit" class="btn sg-btn-primary btn-sm"><i class="bi bi-check-circle"></i> Valider la liste</button>
            </form>
        @endif

        @if ($commande->peut_etre_annulee)
            <form method="POST" action="{{ route('commandes.annuler', $commande) }}" onsubmit="return confirm('Annuler cette commande ?');">
                @csrf
                <button type="submit" class="btn sg-btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Annuler</button>
            </form>
        @endif

        {{-- RG16 : termine une liste dont une partie des achats ne sera jamais faite --}}
        @if ($commande->peut_etre_cloturee)
            <form method="POST" action="{{ route('commandes.cloturer', $commande) }}" onsubmit="return confirm('Clôturer cette liste ? Le reste a recevoir ne sera plus attendu.');">
                @csrf
                <button type="submit" class="btn sg-btn-outline btn-sm" title="Terminer la liste sans acheter le reste"><i class="bi bi-check2-square"></i> Clôturer la liste</button>
            </form>
        @endif

        @if ($commande->est_brouillon)
            <form method="POST" action="{{ route('commandes.destroy', $commande) }}" onsubmit="return confirm('Supprimer définitivement ce brouillon ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Supprimer</button>
            </form>
        @endif

        <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>
</div>

{{-- ===== ENTÊTE / BON DE COMMANDE ===== --}}
<div class="card sg-card-wide shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="sg-print-title fw-bold" style="font-size: 1.05rem;">CANTINE EVAME &mdash; Liste de commande n°{{ $commande->id }}</div>
                <div class="sg-print-title text-muted small mb-2">Éditée le {{ now()->format('d/m/Y') }}</div>
            </div>
            <div>@include('commandes.partials._statut', ['commande' => $commande])</div>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-muted small mb-0">Date de commande</label>
                <div>{{ $commande->date_commande->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small mb-0">Première réception</label>
                <div>
                    @if ($commande->date_achat)
                        {{ $commande->date_achat->format('d/m/Y') }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small mb-0">Créateur</label>
                <div>{{ $commande->createur->nom }} {{ $commande->createur->prenom }}</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small mb-0">Total estimé</label>
                <div class="fw-bold">
                    @if ($commande->prix_estime_total)
                        {{ number_format($commande->prix_estime_total, 0, ',', ' ') }} F
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small mb-0">Commentaire</label>
                <div>
                    @if ($commande->commentaire)
                        {{ $commande->commentaire }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== LIGNES / BON DE COMMANDE ===== --}}
<div class="card sg-card-wide shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Articles commandés ({{ $commande->nb_articles }})</strong>
        @if ($peutReceptionner)
            <span class="sg-pill sg-pill--info sg-no-print">Reste à recevoir : {{ number_format($resteTotal, 2, ',', ' ') }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-striped mb-0 sg-table sg-table-commande">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Unité</th>
                    <th class="text-end">Qté demandée</th>
                    <th class="text-end">Prix est. unitaire</th>
                    <th class="text-end">Total estimé</th>
                    <th class="text-end">Qté reçue</th>
                    <th class="text-end">Prix réel</th>
                    <th class="text-end">Reste à recevoir</th>
                    <th>Statut</th>
                    <th class="sg-no-print" style="width: 90px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($commande->lignes as $ligne)
                    <tr>
                        <td>
                            <strong>{{ $ligne->article->libelle }}</strong>
                            @if ($ligne->commentaire)
                                <br><small class="text-muted">{{ $ligne->commentaire }}</small>
                            @endif
                        </td>
                        <td>{{ $ligne->article->unite_mesure }}</td>
                        <td class="text-end">{{ number_format($ligne->quantite_demandee, 2, ',', ' ') }}</td>
                        <td class="text-end">
                            @if ($ligne->prix_estime_unitaire)
                                {{ number_format($ligne->prix_estime_unitaire, 0, ',', ' ') }} F
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($ligne->total_estime > 0)
                                {{ number_format($ligne->total_estime, 0, ',', ' ') }} F
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($ligne->quantite_livree, 2, ',', ' ') }}</td>
                        <td class="text-end">
                            @if ($ligne->prix_achat_reel)
                                {{ number_format($ligne->prix_achat_reel, 0, ',', ' ') }} F
                                <br><small class="text-muted">{{ number_format($ligne->total_reel, 0, ',', ' ') }} F</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($ligne->reste_a_livrer > 0)
                                <span class="fw-bold">{{ number_format($ligne->reste_a_livrer, 2, ',', ' ') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($ligne->est_totalement_livree)
                                <span class="sg-pill sg-pill--ok">Reçue</span>
                            @elseif ($ligne->est_partiellement_livree)
                                <span class="sg-pill sg-pill--warn">Partielle</span>
                            @elseif ($commande->est_brouillon)
                                <span class="sg-pill sg-pill--off">Brouillon</span>
                            @elseif ($commande->est_cloturee)
                                <span class="sg-pill sg-pill--off">Non reçue</span>
                            @else
                                <span class="sg-pill sg-pill--info">En attente</span>
                            @endif
                        </td>
                        <td class="sg-no-print">
                            @if ($ligne->peut_etre_livree)
                                <button type="button" class="sg-btn-icon text-success" title="Enregistrer une réception" aria-label="Enregistrer une réception"
                                    data-bs-toggle="modal" data-bs-target="#modalLivrerLigne"
                                    data-ligne-id="{{ $ligne->id }}"
                                    data-article="{{ $ligne->article->libelle }}"
                                    data-unite="{{ $ligne->article->unite_mesure }}"
                                    data-reste="{{ $ligne->reste_a_livrer }}"
                                    data-prix-estime="{{ $ligne->prix_estime_unitaire }}">
                                    <i class="bi bi-truck"></i>
                                </button>
                            @else
                                <span class="sg-btn-icon text-muted" title="Aucune réception possible" aria-label="Aucune réception possible"><i class="bi bi-dash-circle"></i></span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-box-seam me-2"></i>Aucun article dans cette commande.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($commande->nb_articles > 0)
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">Totaux</td>
                        <td class="text-end">{{ number_format($commande->quantite_totale_demandee, 2, ',', ' ') }}</td>
                        <td></td>
                        <td class="text-end">{{ number_format($commande->prix_estime_total ?? $commande->lignes->sum(fn ($l) => $l->total_estime), 0, ',', ' ') }} F</td>
                        <td class="text-end">{{ number_format($commande->quantite_totale_livree, 2, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($totalReel, 0, ',', ' ') }} F</td>
                        <td class="text-end">{{ number_format($resteTotal, 2, ',', ' ') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
        </div>
    </div>
</div>

{{-- ===== RÉCEPTION DES ACHATS (saisie groupée) ===== --}}
@if ($peutReceptionner)
<div class="card sg-card-wide shadow-sm border-0 mb-4 sg-no-print">
    <div class="card-header"><strong><i class="bi bi-box-arrow-in-down"></i> Réception des achats</strong></div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('commandes.achats', $commande) }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Date de réception *</label>
                    <input type="date" name="date_achat" class="form-control @error('date_achat') is-invalid @enderror"
                           value="{{ old('date_achat', now()->format('Y-m-d')) }}" required>
                    @error('date_achat') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="form-text mb-0">
                        Saisissez la quantité réellement reçue et le prix facturé. Laissez vide les articles non livrés :
                        chaque saisie crée une entrée en stock et met à jour l'état de la commande.
                    </div>
                </div>
            </div>

            <div class="table-responsive">
            <table class="table table-sm align-middle sg-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th class="text-end" style="width: 140px;">Reste à recevoir</th>
                        <th style="width: 150px;">Quantité reçue</th>
                        <th style="width: 180px;">Prix réel unitaire (F)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($commande->lignes as $ligne)
                        @continue($ligne->reste_a_livrer <= 0)
                        <tr>
                            <td>
                                {{ $ligne->article->libelle }}
                                <small class="text-muted">({{ $ligne->article->unite_mesure }})</small>
                            </td>
                            <td class="text-end">{{ number_format($ligne->reste_a_livrer, 2, ',', ' ') }}</td>
                            <td>
                                <input type="number" step="0.01" min="0" max="{{ $ligne->reste_a_livrer }}"
                                       name="lignes[{{ $ligne->id }}][quantite]" class="form-control form-control-sm"
                                       placeholder="0" value="{{ old('lignes.' . $ligne->id . '.quantite') }}">
                            </td>
                            <td>
                                <input type="number" step="1" min="0"
                                       name="lignes[{{ $ligne->id }}][prix]" class="form-control form-control-sm"
                                       value="{{ old('lignes.' . $ligne->id . '.prix', $ligne->prix_estime_unitaire) }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn sg-btn-ok"><i class="bi bi-check-circle"></i> Enregistrer les achats</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ===== ÉTATS / AIDE ===== --}}
@if ($commande->est_brouillon)
    <div class="alert alert-info sg-no-print">
        <i class="bi bi-info-circle me-2"></i>
        Liste en <strong>brouillon</strong> : ajoutez ou modifiez les articles depuis
        <a href="{{ route('commandes.edit', $commande) }}">la gestion de la liste</a>, puis validez-la pour pouvoir l'imprimer et saisir les réceptions.
    </div>
@elseif ($commande->est_livree)
    <div class="alert alert-success sg-no-print">
        <i class="bi bi-check-circle me-2"></i> Commande totalement livrée : toutes les quantités demandées sont entrées en stock.
    </div>
@elseif ($commande->est_annulee)
    <div class="alert alert-danger sg-no-print">
        <i class="bi bi-x-circle me-2"></i> Commande annulée : aucune réception ne sera enregistrée.
    </div>
@elseif ($commande->est_cloturee)
    <div class="alert alert-secondary sg-no-print">
        <i class="bi bi-check2-square me-2"></i> Liste clôturée : les quantités reçues sont en stock, le reste à recevoir n'est plus attendu.
    </div>
@endif

{{-- ===== MODAL RÉCEPTION D'UNE SEULE LIGNE ===== --}}
@if ($peutReceptionner)
    @include('commandes.partials._livraison_modal', ['commande' => $commande])
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalLivrer = document.getElementById('modalLivrerLigne');
    if (!modalLivrer) {
        return;
    }

    const livrerUrl = "{{ route('commandes.lignes.livrer', [$commande, ':id']) }}";

    modalLivrer.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const form = modalLivrer.querySelector('form');

        form.action = livrerUrl.replace(':id', button.dataset.ligneId);

        const reste = parseFloat(button.dataset.reste);
        const prixEstime = button.dataset.prixEstime ? parseFloat(button.dataset.prixEstime) : 0;

        modalLivrer.querySelector('#livrerArticleLabel').textContent = button.dataset.article + ' (' + button.dataset.unite + ')';
        modalLivrer.querySelector('#livrerResteLabel').textContent = 'Reste à recevoir : ' +
            reste.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const qteInput = modalLivrer.querySelector('[name="quantite_livree"]');
        qteInput.max = reste;
        qteInput.value = reste;

        const prixInput = modalLivrer.querySelector('[name="prix_achat_reel"]');
        prixInput.value = prixEstime > 0 ? prixEstime : '';

        modalLivrer.querySelector('[name="date_livraison"]').value = new Date().toISOString().split('T')[0];
    });
});
</script>
@endpush
