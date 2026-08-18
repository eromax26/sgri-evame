@extends('layouts.app')

@section('titre', 'Mes repas')

@section('fil')
    Mon espace / <strong>Menu de la semaine</strong>
@endsection

@section('contenu')

<h5 class="mb-3">Menu de la semaine - Selectionnez vos repas</h5>


<form method="POST" action="{{ route('selection.valider') }}">
    @csrf

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th style="width: 40px;"></th>
                <th>Jour</th>
                <th>Plat propose</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lignesDisponibles as $ligne)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="lignes[]" value="{{ $ligne->id }}" class="form-check-input">
                    </td>
                    <td>{{ ucfirst($ligne->date_repas->translatedFormat('l d/m/Y')) }}</td>
                    <td>{{ $ligne->plat->libelle }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted">Aucun menu disponible pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($lignesDisponibles->isNotEmpty())
        <button type="submit" class="btn btn-primary">Valider la selection et demander les tickets</button>
    @endif
</form>

@endsection