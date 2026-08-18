@extends('layouts.app')

@section('titre', 'Verifier un ticket')

@section('contenu')

<h5 class="mb-3">Vérification du ticket</h5>

<div class="card mb-4" style="max-width: 700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('agent.verifier') }}" class="d-flex gap-2">
            @csrf
            <input type="text" name="numero_ticket" class="form-control" placeholder="Numero du ticket (ex: TCK-20260731-A1B2C3)" autofocus>
            <button type="submit" class="btn btn-primary text-nowrap">Verifier</button>
        </form>
        <div class="form-text mt-2">Le ticket doit correspondre a la date du jour.</div>
    </div>
</div>

@if (session('resultat'))
    @php $resultat = session('resultat'); @endphp

    @if ($resultat['type'] === 'invalide')
        <div class="alert alert-danger" style="max-width: 700px;">
            <strong>Ticket invalide</strong><br>
            {{ $resultat['message'] }}
        </div>
    @else
        @php $ligne = \App\Models\LigneMenu::with('plat', 'collaborateur')->find($resultat['ligne_id']); @endphp
        @if ($ligne)
        <div class="alert alert-success" style="max-width: 700px;">
            <strong>Ticket valide</strong>
            <table class="table table-sm mt-3 mb-3 bg-white">
                <tr><td class="text-muted">Collaborateur</td><td>{{ $ligne->collaborateur->prenom }} {{ $ligne->collaborateur->nom }}</td></tr>
                <tr><td class="text-muted">Matricule</td><td>{{ $ligne->collaborateur->matricule }}</td></tr>
                <tr><td class="text-muted">Plat prevu</td><td>{{ $ligne->plat->libelle }}</td></tr>
                <tr><td class="text-muted">Date du repas</td><td>{{ $ligne->date_repas->format('d/m/Y') }}</td></tr>
            </table>
            <form method="POST" action="{{ route('agent.confirmerRetrait') }}">
                @csrf
                <input type="hidden" name="ligne_menu_id" value="{{ $ligne->id }}">
                <button type="submit" class="btn btn-success">Confirmer et laisser entrer</button>
            </form>
        </div>
        @endif
    @endif
@endif

@endsection