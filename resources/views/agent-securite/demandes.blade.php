@extends('layouts.app')

@section('titre', 'Demandes de tickets')

@section('fil')
    Tickets / <strong>Demandes en attente</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-inbox"></i>Demandes de tickets en attente</h5>

<form method="POST" action="{{ route('agent.imprimer') }}">
    @csrf

    <div class="table-responsive">
    <table class="table table-bordered bg-white sg-table">
        <thead>
            <tr>
                <th style="width: 40px;"></th>
                <th>Collaborateur</th>
                <th>Jour du repas</th>
                <th>Plat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($demandes as $ligne)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="lignes[]" value="{{ $ligne->id }}" class="form-check-input">
                    </td>
                    <td>{{ $ligne->collaborateur->nom }} {{ $ligne->collaborateur->prenom }} ({{ $ligne->collaborateur->matricule }})</td>
                    <td>{{ ucfirst($ligne->date_repas->translatedFormat('l d/m/Y')) }}</td>
                    <td>{{ $ligne->plat->libelle }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted"><i class="bi bi-inbox me-2"></i>Aucune demande en attente.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    @if ($demandes->isNotEmpty())
        <div class="d-flex gap-2">
            <button type="submit" name="format" value="thermique" class="btn sg-btn-primary">
                <i class="bi bi-receipt"></i> Imprimer (imprimante thermique)
            </button>
            <button type="submit" name="format" value="a4" class="btn sg-btn-outline">
                <i class="bi bi-file-earmark-text"></i> Imprimer (feuille A4)
            </button>
        </div>
        <div class="form-text mt-2">Les tickets s'ouvriront dans une page prête à imprimer.</div>
    @endif
</form>

@endsection