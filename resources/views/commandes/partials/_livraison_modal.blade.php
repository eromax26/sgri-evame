<div class="modal fade" id="modalLivrerLigne" tabindex="-1" aria-labelledby="modalLivrerLigneLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('commandes.lignes.livrer', [$commande, ':id']) }}" class="modal-content" id="formLivrerLigne">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalLivrerLigneLabel"><i class="bi bi-truck"></i> Enregistrer livraison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <strong id="livrerArticleLabel"></strong><br>
                    <small id="livrerResteLabel" class="text-muted"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantité livrée *</label>
                    <input type="number" name="quantite_livree" class="form-control" step="0.01" min="0.01" required>
                    <div class="form-text">Maximum : <strong id="livrerMaxQte">—</strong></div>
                    @error('quantite_livree') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Prix réel unitaire (F CFA) *</label>
                    <input type="number" name="prix_achat_reel" class="form-control" step="1" min="0" required>
                    @error('prix_achat_reel') <div class="text-danger small">{{ $message }}</div> @enderror
                    <div class="form-text">Prix facturé par le fournisseur à la réception.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date de livraison *</label>
                    <input type="date" name="date_livraison" class="form-control" required>
                    @error('date_livraison') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Annuler</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Confirmer la livraison</button>
            </div>
        </form>
    </div>
</div>