@extends('layouts.app')

@section('titre', 'Mouvements de stock')

@section('contenu')

<h5 class="mb-3">Mouvements de stock</h5>

<a href="{{ route('mouv-stocks.create') }}" class="btn btn-success btn-sm mb-3">+ Nouveau mouvement</a>

<table class="table table-striped bg-white">
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
                        <span class="badge bg-success">Entree</span>
                    @else
                        <span class="badge bg-warning text-dark">Sortie</span>
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
                <td>{{ $mouv->collaborateur->prenom }} {{ $mouv->collaborateur->nom }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Aucun mouvement enregistre.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $mouvements->links() }}

@endsection