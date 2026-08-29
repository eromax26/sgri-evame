@extends('layouts.app')

@section('titre', 'Previsions de preparation')

@section('fil')
    Restauration / <strong>Previsions de preparation</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-3"><i class="bi bi-graph-up-arrow"></i>Prévisions de préparation</h5>

<p class="text-muted small">
    Nombre de repas reserves par les collaborateurs pour les 7 prochains jours.
</p>

<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Jour</th>
            <th>Plat prevu</th>
            <th>Repas a preparer</th>
            <th>Detail</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($previsions as $p)
            <tr>
                <td>
                    <strong>{{ ucfirst($p['date_repas']->translatedFormat('l')) }}</strong>
                    <div class="text-muted small">{{ $p['date_repas']->format('d/m/Y') }}</div>
                </td>
                <td>{{ $p['plat']->libelle }}</td>
                <td><span class="sg-badge-count" style="font-size: 13px;">{{ $p['total'] }}</span></td>
                <td class="small text-muted">
                    {{ $p['imprimes'] }} ticket(s) imprime(s)
                    @if ($p['en_attente'] > 0)
                        - {{ $p['en_attente'] }} en attente d'impression
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted"><i class="bi bi-graph-up-arrow me-2"></i>Aucune reservation pour les 7 prochains jours.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection