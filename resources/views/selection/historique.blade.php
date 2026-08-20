@extends('layouts.app')

@section('titre', 'Historique des repas')

@section('fil')
    Mon espace / <strong>Historique des repas</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Historique de mes repas</h5>

<form method="GET" action="{{ route('selection.historique') }}" class="d-flex gap-2 mb-3">
    <input type="month" name="periode" class="form-control" style="max-width: 200px;" value="{{ $periode }}">
    <button type="submit" class="btn sg-btn-navy">Afficher</button>
</form>

<div class="card sg-card-form shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <span class="text-muted">Repas consommes</span>
            <strong>{{ $repas->count() }}</strong>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <span class="text-muted">Montant a retenir</span>
            <strong>{{ number_format($totalMontant, 0, ',', ' ') }} F</strong>
        </div>
    </div>
</div>

<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Plat</th>
            <th>Montant</th>
            <th>Numero de ticket</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($repas as $ligne)
            <tr>
                <td>{{ ucfirst($ligne->date_repas->translatedFormat('l d/m/Y')) }}</td>
                <td>{{ $ligne->plat->libelle }}</td>
                <td>{{ number_format($ligne->prix, 0, ',', ' ') }} F</td>
                <td class="text-muted small">{{ $ligne->numero_ticket }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Aucun repas consomme sur cette periode.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection