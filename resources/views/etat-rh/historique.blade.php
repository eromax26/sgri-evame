@extends('layouts.app')

@section('titre', 'Historique des etats')

@section('fil')
    Ressources humaines / <strong>Historique des états</strong>
@endsection

@section('contenu')

<table class="table table-striped bg-white">
    <thead>
        <tr>
            <th>Periode</th>
            <th>Collaborateurs concernes</th>
            <th>Repas consommes</th>
            <th>Montant total</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($periodes as $p)
            <tr>
                <td>{{ $p['periode'] }}</td>
                <td>{{ $p['nb_collaborateurs'] }}</td>
                <td>{{ $p['nb_repas'] }}</td>
                <td><strong>{{ number_format($p['montant_total'], 0, ',', ' ') }} F</strong></td>
                <td>
                    <a href="{{ route('etat-rh.index', ['periode' => $p['periode']]) }}" class="btn btn-sm btn-outline-primary">
                        Consulter le détail
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">Aucun état verrouillé pour le moment.</td></tr>
        @endforelse
    </tbody>
</table>

<p class="text-muted small">
    Les états vérrouillés ne peuvent plus être modifiés. Ils sont conservés a titre d'archive.
</p>

@endsection