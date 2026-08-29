@extends('layouts.app')

@section('titre', 'Mes tickets')

@section('fil')
    Mon espace / <strong>Mes tickets</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-ticket-perforated"></i>Mes tickets</h5>

<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Date du repas</th>
            <th>Plat</th>
            <th>Numero du ticket</th>
            <th>Etat</th>
            <th></th>
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
                        <span class="sg-pill sg-pill--warn">En attente d'impression</span>
                    @else
                        <span class="sg-pill sg-pill--ok">Ticket prèt à retirer</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('selection.imprimerTicket', $ticket) }}" target="_blank" class="btn btn-sm sg-btn-outline">
                        <i class="bi bi-printer"></i> Imprimer
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted"><i class="bi bi-ticket-perforated me-2"></i>Aucun ticket en cours.</td></tr>
        @endforelse
    </tbody>
</table>

<p class="text-muted small">
    Presentez votre ticket imprimé à l'agent de sécurité au hall d'entrée de la cantine, le jour du repas.
</p>

@endsection