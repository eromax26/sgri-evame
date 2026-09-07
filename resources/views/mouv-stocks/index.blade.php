@extends('layouts.app')

@section('titre', 'Mouvements de stock')

@section('fil')
    Gestion du stock / <strong>Mouvements de stock</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-arrow-left-right"></i>Mouvements de stock</h5>

<a href="{{ route('mouv-stocks.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouveau mouvement</a>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Article</th>
            <th>Type</th>
            <th>Quantite</th>
            <th>Motif / Prix</th>
            <th>Enregistre par</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($mouvements as $mouv)
            <tr>
                <td>{{ $mouv->date_mouvement->format('d/m/Y') }}</td>
                <td>{{ $mouv->article->libelle }}</td>
                <td>
                    @if ($mouv->type_mouvement === 'entree')
                        <span class="sg-pill sg-pill--ok">Entree</span>
                    @else
                        <span class="sg-pill sg-pill--danger">Sortie</span>
                    @endif
                </td>
                <td>{{ $mouv->quantite }} {{ $mouv->article->unite_mesure }}</td>
                <td>
                    @if ($mouv->type_mouvement === 'entree')
                        {{ $mouv->prix_achat ? number_format($mouv->prix_achat, 0, ',', ' ') . ' F' : '—' }}
                    @else
                        {{ $mouv->motif_sortie ?? '—' }}
                    @endif
                </td>
                <td>{{ $mouv->collaborateur->nom }} {{ $mouv->collaborateur->prenom }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted"><i class="bi bi-arrow-left-right me-2"></i>Aucun mouvement enregistre.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

{{ $mouvements->links() }}

@endsection