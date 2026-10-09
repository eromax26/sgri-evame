@php
    $isEdit = $mode === 'edit';
    $modalId = $isEdit ? 'modalModifierLigne' : 'modalAjouterLigne';
    $modalTitle = $isEdit ? 'Modifier la ligne' : 'Ajouter un article';
    $formAction = $isEdit 
        ? route('commandes.lignes.update', [$commande, ':id']) 
        : route('commandes.lignes.store', $commande);
    $submitLabel = $isEdit ? 'Enregistrer' : 'Ajouter';
    $icone = $isEdit ? 'pencil' : 'plus-lg';

    // En modification, les valeurs sont injectées par le JS : old() ne sert qu'à l'ajout.
    $quantite = $isEdit ? '' : old('quantite_demandee');
    $prix = $isEdit ? '' : old('prix_estime_unitaire');
    $commentaire = $isEdit ? '' : old('commentaire');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ $formAction }}" class="modal-content">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label"><i class="bi bi-{{ $icone }}"></i> {{ $modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Article *</label>
                    <select name="article_id" class="form-select" @if($isEdit) disabled @endif required>
                        <option value="">-- Choisir un article --</option>
                        @foreach ($articles as $article)
                            <option value="{{ $article->id }}" 
                                @if($isEdit) data-stock="{{ $article->quantite_stock }}" data-seuil="{{ $article->seuil_minimum }}" @endif
                                @selected(!$isEdit && old('article_id') == $article->id)>
                                {{ $article->libelle }} 
                                (stock : {{ number_format($article->quantite_stock, 2, ',', ' ') }} {{ $article->unite_mesure }})
                                @if ($article->seuil_minimum > 0)
                                    | Seuil : {{ number_format($article->seuil_minimum, 2, ',', ' ') }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('article_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantité demandée *</label>
                    <input type="number" name="quantite_demandee" class="form-control" step="0.01" min="0.01" value="{{ $quantite }}" required>
                    @error('quantite_demandee') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Prix estimé unitaire (F CFA)</label>
                    <input type="number" name="prix_estime_unitaire" class="form-control" step="1" min="0" value="{{ $prix }}" placeholder="Prix prévu / devis fournisseur">
                    @error('prix_estime_unitaire') <div class="text-danger small">{{ $message }}</div> @enderror
                    <div class="form-text">Laisser vide si prix inconnu. Sera comparé au prix réel à la livraison.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire" class="form-control" rows="2" placeholder="Notes sur cette ligne...">{{ $commentaire }}</textarea>
                    @error('commentaire') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Annuler</button>

@if (!$isEdit && old('article_id'))
    {{-- Erreur de validation lors d'un ajout : on réaffiche la modale avec la saisie conservée --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('modalAjouterLigne');
            if (modal) {
                new bootstrap.Modal(modal).show();
            }
        });
    </script>
    @endpush
@endif

                <button type="submit" class="btn sg-btn-primary"><i class="bi bi-{{ $isEdit ? 'check' : 'plus' }}"></i> {{ $submitLabel }}</button>
            </div>
        </form>
    </div>
</div>