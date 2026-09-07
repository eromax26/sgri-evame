@extends('layouts.app')

@section('titre', 'Etat mensuel RH')

@section('fil')
    Ressources humaines / <strong>Etat mensuel</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-file-earmark-spreadsheet"></i>Etat mensuel des retenues</h5>

<form method="GET" action="{{ route('etat-rh.index') }}" class="d-flex gap-2 mb-3">
    <input type="month" name="periode" class="form-control" style="max-width: 200px;" value="{{ $periode }}">
    <button type="submit" class="btn sg-btn-navy"><i class="bi bi-search"></i> Afficher</button>
    @if (count($etat) > 0)
        <a href="{{ route('etat-rh.export', ['periode' => $periode]) }}" class="btn sg-btn-outline"><i class="bi bi-file-earmark-excel"></i> Exporter en Excel</a>
    @endif
</form>

@if ($statutVerrouillage === 'complet')
    <span class="sg-tag mb-3">Période verrouillée</span>
@elseif ($statutVerrouillage === 'partiel')
    <span class="sg-pill sg-pill--warn mb-3">Verrouillée partiellement &middot; {{ $resteAVerrouiller }} repas en attente</span>
@else
    <span class="sg-pill sg-pill--warn mb-3">Non verrouillé - brouillon</span>
@endif

<div class="table-responsive">
<table class="table table-bordered bg-white sg-table">
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Collaborateur</th>
            <th>Repas consommes</th>
            <th>Montant a retenir</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($etat as $ligne)
            <tr>
                <td>{{ $ligne['collaborateur']->matricule }}</td>
                <td>{{ $ligne['collaborateur']->nom }} {{ $ligne['collaborateur']->prenom }}</td>
                <td>{{ $ligne['nb_repas'] }}</td>
                <td>{{ number_format($ligne['montant'], 0, ',', ' ') }} F</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Aucune consommation sur cette période.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@if ($statutVerrouillage !== 'complet' && count($etat) > 0)
    <form method="POST" action="{{ route('etat-rh.verrouiller') }}">
        @csrf
        <input type="hidden" name="periode" value="{{ $periode }}">
        <button type="submit" class="btn sg-btn-primary">
            <i class="bi bi-lock"></i> {{ $statutVerrouillage === 'partiel' ? "Verrouiller les {$resteAVerrouiller} repas restants" : "Valider et vérrouiller l'etat" }}
        </button>
    </form>
@endif

@endsection