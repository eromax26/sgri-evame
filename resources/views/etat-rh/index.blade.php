@extends('layouts.app')

@section('titre', 'Etat mensuel RH')

@section('contenu')

<h5 class="mb-3">Etat mensuel des retenues</h5>

<form method="GET" action="{{ route('etat-rh.index') }}" class="d-flex gap-2 mb-3">
    <input type="month" name="periode" class="form-control" style="max-width: 200px;" value="{{ $periode }}">
    <button type="submit" class="btn btn-primary">Afficher</button>
</form>

@if ($dejaVerrouille)
    <span class="badge bg-primary mb-3">Période verrouillée</span>
@else
    <span class="badge bg-warning text-dark mb-3">Non verrouillé - brouillon</span>
@endif

<table class="table table-bordered bg-white">
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
            <tr><td colspan="4" class="text-center text-muted">Aucune consommation sur cette période.</td></tr>
        @endforelse
    </tbody>
</table>

@unless ($dejaVerrouille)
    @if (count($etat) > 0)
        <form method="POST" action="{{ route('etat-rh.verrouiller') }}">
            @csrf
            <input type="hidden" name="periode" value="{{ $periode }}">
            <button type="submit" class="btn btn-success">Valider et vérrouiller l'etat</button>
        </form>
    @endif
@endunless

@endsection