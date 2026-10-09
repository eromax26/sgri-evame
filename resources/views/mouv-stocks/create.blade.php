@extends('layouts.app')

@section('titre', 'Nouvelle sortie de stock')

@section('fil')
    Gestion du stock / Mouvements de stock / <strong>Nouvelle sortie</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-box-arrow-up"></i>Nouvelle sortie de stock</h5>

<p class="text-muted small">
    <i class="bi bi-info-circle"></i> Les entrées de stock sont créées automatiquement par le module
    <strong>Commandes</strong> (réception des achats). Ce formulaire sert à enregistrer une sortie.
</p>

<div class="card sg-card-form shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('mouv-stocks.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Article *</label>
                <select name="article_id" class="form-select">
                    <option value="">-- Choisir --</option>
                    @foreach ($articles as $article)
                        <option value="{{ $article->id }}"
                                data-stock="{{ $article->quantite_stock }}"
                                data-unite="{{ $article->unite_mesure }}"
                                @selected(old('article_id') == $article->id)>
                            {{ $article->libelle }} (stock : {{ $article->quantite_stock }} {{ $article->unite_mesure }})
                        </option>
                    @endforeach
                </select>
                @error('article_id') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Quantité à sortir *</label>
                <div class="input-group">
                    <input type="number" name="quantite" id="quantite" class="form-control" value="{{ old('quantite') }}" step="0.01" min="0.01">
                    <span class="input-group-text" id="unite">unité</span>
                </div>
                <div class="form-text" id="stock-disponible">Stock disponible : --</div>
                @error('quantite') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Motif de sortie *</label>
                <select name="motif_sortie" class="form-select">
                    <option value="">-- Choisir --</option>
                    @foreach ($motifs as $motif)
                        <option value="{{ $motif }}" @selected(old('motif_sortie') === $motif)>{{ $motif }}</option>
                    @endforeach
                </select>
                @error('motif_sortie') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Date de la sortie *</label>
                <input type="date" name="date_mouvement" class="form-control" value="{{ old('date_mouvement', now()->format('Y-m-d')) }}">
                @error('date_mouvement') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn sg-btn-primary"><i class="bi bi-save"></i> Enregistrer la sortie</button>
            <a href="{{ route('mouv-stocks.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

<script>
    const selectArticle = document.querySelector('select[name="article_id"]');
    const champQuantite = document.getElementById('quantite');
    const champUnite = document.getElementById('unite');
    const infoStock = document.getElementById('stock-disponible');

    // Affiche l'unité et le stock disponible, et borne la quantité saisie (RG11 côté saisie)
    function afficherStock() {
        const option = selectArticle.selectedOptions[0];
        const stock = option.dataset.stock ?? '';
        const unite = option.dataset.unite ?? '';

        champUnite.textContent = unite !== '' ? unite : 'unité';

        if (stock === '') {
            infoStock.textContent = 'Stock disponible : --';
            champQuantite.removeAttribute('max');
        } else {
            infoStock.textContent = 'Stock disponible : ' + stock + ' ' + unite;
            champQuantite.max = stock;
        }
    }

    selectArticle.addEventListener('change', afficherStock);
    afficherStock();
</script>

@endsection
