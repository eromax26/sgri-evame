@extends('layouts.app')

@section('titre', 'Nouveau mouvement')

@section('contenu')

<h5 class="mb-3">Nouveau mouvement de stock</h5>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <form method="POST" action="{{ route('mouv-stocks.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Article *</label>
                <select name="article_id" class="form-select">
                    <option value="">-- Choisir --</option>
                    @foreach ($articles as $article)
                        <option value="{{ $article->id }}" @selected(old('article_id') == $article->id)>
                            {{ $article->libelle }} (stock : {{ $article->quantite_stock }} {{ $article->unite_mesure }})
                        </option>
                    @endforeach
                </select>
                @error('article_id') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Type de mouvement *</label>
                <select name="type_mouvement" class="form-select">
                    <option value="entree" @selected(old('type_mouvement') === 'entree')>Entree (reapprovisionnement)</option>
                    <option value="sortie" @selected(old('type_mouvement') === 'sortie')>Sortie (utilisation cuisine)</option>
                </select>
                @error('type_mouvement') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Quantite *</label>
                <input type="number" name="quantite" class="form-control" value="{{ old('quantite') }}" step="0.01" min="0.01">
                @error('quantite') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Date du mouvement *</label>
                <input type="date" name="date_mouvement" class="form-control" value="{{ old('date_mouvement', now()->format('Y-m-d')) }}">
                @error('date_mouvement') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3" id="bloc-prix">
                <label class="form-label">Prix d'achat (F CFA)</label>
                <input type="number" name="prix_achat" class="form-control" value="{{ old('prix_achat') }}" step="1" min="0">
            </div>

            <div class="mb-3" id="bloc-motif">
                <label class="form-label">Motif de sortie</label>
                <input type="text" name="motif_sortie" class="form-control" value="{{ old('motif_sortie') }}" placeholder="Ex: preparation repas du jour">
            </div>

            <button type="submit" class="btn btn-primary">Enregistrer le mouvement</button>
            <a href="{{ route('mouv-stocks.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>

<script>
    const selectType = document.querySelector('select[name="type_mouvement"]');
    const blocPrix = document.getElementById('bloc-prix');
    const blocMotif = document.getElementById('bloc-motif');

    function afficherChamps() {
        if (selectType.value === 'entree') {
            blocPrix.style.display = 'block';
            blocMotif.style.display = 'none';
        } else {
            blocPrix.style.display = 'none';
            blocMotif.style.display = 'block';
        }
    }

    selectType.addEventListener('change', afficherChamps);
    afficherChamps();
</script>

@endsection