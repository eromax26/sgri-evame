@extends('layouts.app')

@section('titre', 'Journal des passages')

@section('fil')
    Tickets / <strong>Journal des passages</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-journal-text"></i>Journal des passages</h5>

<form method="GET" action="{{ route('agent.journal') }}" class="d-flex gap-2 mb-3">
    <input type="date" name="date" class="form-control" style="max-width: 200px;" value="{{ $date }}">
    <button type="submit" class="btn sg-btn-navy"><i class="bi bi-search"></i> Afficher</button>
</form>

<div class="card shadow-sm border-0 mb-3" style="max-width: 300px;">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <span class="text-muted">Passages ce jour</span>
            <strong>{{ $passages->count() }}</strong>
        </div>
    </div>
</div>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Heure</th>
            <th>Collaborateur</th>
            <th>Matricule</th>
            <th>Plat</th>
            <th>Numero du ticket</th>
            <th>Verifie par</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($passages as $passage)
            <tr>
                <td>{{ $passage->date_retrait->format('H:i') }}</td>
                <td>{{ $passage->collaborateur->nom }} {{ $passage->collaborateur->prenom }}</td>
                <td>{{ $passage->collaborateur->matricule }}</td>
                <td>{{ $passage->plat->libelle }}</td>
                <td class="small text-muted">{{ $passage->numero_ticket }}</td>
                <td>{{ $passage->agentSecurite->nom ?? '—' }} {{ $passage->agentSecurite->prenom ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted"><i class="bi bi-journal-text me-2"></i>Aucun passage enregistré pour cette date.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection