@extends('layouts.app')

@section('titre', 'Verifier un ticket')

@section('fil')
    Tickets / <strong>Vérifier un ticket</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Vérifier un ticket</h5>

<form method="GET" action="{{ route('agent.verifierForm') }}" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label class="form-label mb-1">Date</label>
        <input type="date" name="date" class="form-control" style="width: 200px;" value="{{ $date }}">
    </div>
    <div class="col-auto" style="min-width: 280px;">
        <label class="form-label mb-1">Recherche</label>
        <input type="text" name="recherche" class="form-control" placeholder="Matricule, nom ou numéro de ticket" value="{{ request('recherche') }}" autofocus>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn sg-btn-navy">Rechercher</button>
    </div>
    <div class="col-auto">
        <a href="{{ route('agent.verifierForm') }}" class="btn sg-btn-outline">Réinitialiser</a>
    </div>
</form>

<div class="d-flex gap-2 mb-3">
    <span class="sg-pill sg-pill--warn">{{ $attendus }} attendu(s)</span>
    <span class="sg-pill sg-pill--ok">{{ $passes }} déjà passé(s)</span>
</div>

<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Collaborateur</th>
            <th>Matricule</th>
            <th>Plat</th>
            <th>Numéro du ticket</th>
            <th>État</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tickets as $ticket)
            <tr class="{{ $ticket->statut === 'consomme' ? 'table-secondary text-muted' : '' }}">
                <td>{{ $ticket->collaborateur->prenom }} {{ $ticket->collaborateur->nom }}</td>
                <td>{{ $ticket->collaborateur->matricule }}</td>
                <td>{{ $ticket->plat->libelle }}</td>
                <td class="text-muted small">{{ $ticket->numero_ticket }}</td>
                <td>
                    @if ($ticket->statut === 'imprime')
                        <span class="sg-pill sg-pill--warn">Attendu</span>
                    @else
                        <span class="sg-pill sg-pill--off">Déjà passé</span>
                    @endif
                </td>
                <td>
                    @if ($ticket->statut === 'imprime')
                        <form method="POST" action="{{ route('agent.confirmerRetrait') }}">
                            @csrf
                            <input type="hidden" name="ligne_menu_id" value="{{ $ticket->id }}">
                            <button type="submit" class="btn btn-sm sg-btn-primary">Valider l'entrée</button>
                        </form>
                    @else
                        <span class="text-muted small">{{ $ticket->date_retrait->format('H:i') }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">
                    @if (request('recherche'))
                        Aucun résultat pour cette recherche.
                    @else
                        Aucun ticket pour cette date.
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection
