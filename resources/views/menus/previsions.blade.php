@extends('layouts.app')

@section('titre', 'Previsions de preparation')

@section('fil')
    Restauration / <strong>Previsions de preparation</strong>
@endsection

@section('contenu')

<p class="text-muted small">
    Nombre de repas reserves par les collaborateurs pour les 7 prochains jours.
</p>

<table class="table table-striped bg-white">
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
                <td><span class="badge bg-primary fs-6">{{ $p['total'] }}</span></td>
                <td class="small text-muted">
                    {{ $p['imprimes'] }} ticket(s) imprime(s)
                    @if ($p['en_attente'] > 0)
                        - {{ $p['en_attente'] }} en attente d'impression
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Aucune reservation pour les 7 prochains jours.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection