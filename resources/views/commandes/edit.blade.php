@extends('layouts.app')

@section('titre', 'Commande #' . $commande->id)

@section('fil')
    Gestion du stock / Commandes / <strong>Commande #{{ $commande->id }}</strong>
@endsection

@section('contenu')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="sg-page-title mb-0"><i class="bi bi-truck"></i>Commande #{{ $commande->id }}</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('commandes.show', $commande) }}" class="btn sg-btn-outline btn-sm"><i class="bi bi-eye"></i> Voir / Imprimer</a>
        <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour liste</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ===== ENTÊTE COMMANDE ===== --}}
<div class="card sg-card-wide shadow-sm border-0 mb-4">
    <div class="card-header bg-light">
        <strong>Informations commande</strong>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-muted small">Statut</label>
                <div>@include('commandes.partials._statut', ['commande' => $commande])</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Date commande</label>
                <div>{{ $commande->date_commande->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Créateur</label>
                <div>{{ $commande->createur->nom }} {{ $commande->createur->prenom }}</div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Total estimé</label>
                <div class="fw-bold">
                    @if ($commande->prix_estime_total)
                        {{ number_format($commande->prix_estime_total, 0, ',', ' ') }} F
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">Commentaire</label>
                <div>
                    @if ($commande->commentaire)
                        {{ $commande->commentaire }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>

        @if ($commande->est_brouillon)
        <hr class="my-3">
        <div class="d-flex justify-content-end gap-2">
            <form method="POST" action="{{ route('commandes.valider', $commande) }}" class="d-inline" onsubmit="return confirm('Valider cette commande ? Elle ne sera plus modifiable.');">
                @csrf
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Valider la commande</button>
            </form>
            <form method="POST" action="{{ route('commandes.annuler', $commande) }}" class="d-inline" onsubmit="return confirm('Annuler cette commande ?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Annuler</button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- ===== LIGNES DE COMMANDE ===== --}}
<div class="card sg-card-wide shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Articles commandés ({{ $commande->nb_articles }})</strong>
        @if ($commande->est_brouillon)
            <button type="button" class="btn sg-btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterLigne">
                <i class="bi bi-plus-lg"></i> Ajouter un article
            </button>
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
                    <th class="text-end">Qté livrée</th>
                    <th class="text-end">Prix réel</th>
                    <th class="text-end">Reste à livrer</th>
                    <th>Statut ligne</th>
                    <th style="width: 120px;">Actions</th>
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
                        <td class="text-end fw-bold">
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
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="@if ($ligne->reste_a_livrer > 0) text-danger fw-bold @else text-success @endif">
                                {{ number_format($ligne->reste_a_livrer, 2, ',', ' ') }}
                            </span>
                        </td>
                        <td>
                            @if ($ligne->est_totalement_livree)
                                <span class="sg-pill sg-pill--ok">Livrée</span>
                            @elseif ($ligne->est_partiellement_livree)
                                <span class="sg-pill sg-pill--warn">Partielle</span>
                            @else
                                <span class="sg-pill sg-pill--info">En attente</span>
                            @endif
                        </td>
                        <td>
                            <div class="sg-actions">
                                @if ($commande->est_brouillon)
                                    <button type="button" class="sg-btn-icon text-primary" title="Modifier" aria-label="Modifier"
                                        data-bs-toggle="modal" data-bs-target="#modalModifierLigne"
                                        data-ligne-id="{{ $ligne->id }}"
                                        data-article-id="{{ $ligne->article_id }}"
                                        data-quantite="{{ $ligne->quantite_demandee }}"
                                        data-prix="{{ $ligne->prix_estime_unitaire }}"
                                        data-commentaire="{{ $ligne->commentaire }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('commandes.lignes.destroy', [$commande, $ligne]) }}" class="d-inline" onsubmit="return confirm('Supprimer cette ligne ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sg-btn-icon text-danger" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                                    </form>
                                @else
                                    <span class="sg-btn-icon text-muted" title="Liste non modifiable"><i class="bi bi-dash-circle"></i></span>
                                @endif
                            </div>
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
        </table>
        </div>
    </div>
</div>

{{-- ===== TOTAUX ===== --}}
@if ($commande->nb_articles > 0)
<div class="card sg-card-wide shadow-sm border-0 mt-3">
    <div class="card-body">
        <div class="row text-end">
            <div class="col-md-6">
                <strong>Total quantités demandées :</strong> {{ number_format($commande->quantite_totale_demandee, 2, ',', ' ') }}<br>
                <strong>Total quantités livrées :</strong> {{ number_format($commande->quantite_totale_livree, 2, ',', ' ') }}<br>
                <strong>Total estimé :</strong>
                @if ($commande->prix_estime_total)
                    {{ number_format($commande->prix_estime_total, 0, ',', ' ') }} F
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>
            <div class="col-md-6">
                @if ($commande->est_validee || $commande->est_livree_partielle)
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Commande validée — rendez-vous sur la page « Voir / Imprimer » pour enregistrer les achats.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- ===== MODAL AJOUTER LIGNE ===== --}}
@include('commandes.partials._ligne_form', ['mode' => 'create', 'commande' => $commande, 'articles' => $articles])

{{-- ===== MODAL MODIFIER LIGNE ===== --}}
@include('commandes.partials._ligne_form', ['mode' => 'edit', 'commande' => $commande, 'articles' => $articles])

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal modifier ligne - pré-remplir
    const modalModifier = document.getElementById('modalModifierLigne');
    if (modalModifier) {
        modalModifier.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const form = modalModifier.querySelector('form');
            const updateUrl = "{{ route('commandes.lignes.update', [$commande, ':id']) }}";
            form.action = updateUrl.replace(':id', button.dataset.ligneId);
            modalModifier.querySelector('[name="article_id"]').value = button.dataset.articleId;
            modalModifier.querySelector('[name="quantite_demandee"]').value = button.dataset.quantite;
            modalModifier.querySelector('[name="prix_estime_unitaire"]').value = button.dataset.prix || '';
            modalModifier.querySelector('[name="commentaire"]').value = button.dataset.commentaire || '';
        });
    }
});
</script>
@endpush