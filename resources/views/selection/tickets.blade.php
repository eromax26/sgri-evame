@extends('layouts.app')

@section('titre', 'Mes tickets')

@section('fil')
    Mon espace / <strong>Mes tickets</strong>
@endsection

@section('contenu')

<h5 class="mb-3">Mes tickets</h5>

<table class="table table-striped bg-white">
    <thead>
        <tr>
            <th>Date du repas</th>
            <th>Plat</th>
            <th>Numero du ticket</th>
            <th>Etat</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tickets as $ticket)
            <tr>
                <td>{{ ucfirst($ticket->date_repas->translatedFormat('l d/m/Y')) }}</td>
                <td>{{ $ticket->plat->libelle }}</td>
                <td>
                    @if ($ticket->numero_ticket)
                        {{ $ticket->numero_ticket }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if ($ticket->statut === 'demande')
                        <span class="badge bg-warning text-dark">En attente d'impression</span>
                    @else
                        <span class="badge bg-success">Ticket pret a retirer</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Aucun ticket en cours.</td></tr>
        @endforelse
    </tbody>
</table>

<p class="text-muted small">
    Presentez votre ticket imprime a l'agent de securite au hall d'entree de la cantine, le jour du repas.
</p>

@endsection