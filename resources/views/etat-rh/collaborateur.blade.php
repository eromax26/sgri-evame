@extends('layouts.app')

@section('titre', 'Detail collaborateur')

@section('fil')
    Ressources humaines / <a href="{{ route('etat-rh.index', ['periode' => $periode]) }}">Etat mensuel</a> / <strong>{{ $collaborateur->nom }} {{ $collaborateur->prenom }}</strong>
@endsection

@section('contenu')

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h5 class="sg-page-title mb-1"><i class="bi bi-person-lines-fill"></i>{{ $collaborateur->nom }} {{ $collaborateur->prenom }}</h5>
        <div class="text-muted small">
            Matricule {{ $collaborateur->matricule }}
            @if ($collaborateur->departement)
                &middot; {{ $collaborateur->departement->nom }}
            @endif
            @if ($collaborateur->fonction)
                &middot; {{ $collaborateur->fonction }}
            @endif
        </div>
    </div>
    <a href="{{ route('etat-rh.index', ['periode' => $periode]) }}" class="btn sg-btn-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Retour a l'etat mensuel
    </a>
</div>

<form method="GET" action="{{ route('etat-rh.collaborateur', $collaborateur) }}" class="d-flex gap-2 mb-3">
    <input type="month" name="periode" class="form-control" style="max-width: 200px;" value="{{ $periode }}">
    <button type="submit" class="btn sg-btn-navy"><i class="bi bi-search"></i> Afficher</button>
</form>

<div class="card sg-card-form shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <span class="text-muted">Repas consommés</span>
            <strong>{{ $repas->count() }}</strong>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <span class="text-muted">Montant à retenir</span>
            <strong>{{ number_format($totalMontant, 0, ',', ' ') }} F</strong>
        </div>
        @if ($nbNonRetires > 0)
            <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                <span class="text-muted">Réservés mais non retirés <span class="small">(non facturés)</span></span>
                <span class="sg-pill sg-pill--warn">{{ $nbNonRetires }}</span>
            </div>
        @endif
    </div>
</div>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Date du repas</th>
            <th>Plat</th>
            <th>Montant</th>
            <th>Heure de retrait</th>
            <th>Numero de ticket</th>
            <th>Facturation</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($repas as $ligne)
            <tr>
                <td>{{ ucfirst($ligne->date_repas->translatedFormat('l d/m/Y')) }}</td>
                <td>{{ $ligne->plat?->libelle ?? 'Plat supprimé' }}</td>
                <td>{{ number_format($ligne->prix, 0, ',', ' ') }} F</td>
                {{-- Un ticket n'est validable que le jour du repas (AgentSecuriteController::confirmerRetrait),
                     la date de retrait est donc toujours celle affichee a gauche : seule l'heure informe. --}}
                <td class="text-muted small">{{ $ligne->date_retrait?->format('H:i') ?? '—' }}</td>
                <td class="text-muted small">{{ $ligne->numero_ticket }}</td>
                <td>
                    @if ($ligne->statut_facturation === 'verrouille')
                        <span class="sg-tag">Verrouillé</span>
                    @else
                        <span class="sg-pill sg-pill--warn">Brouillon</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted"><i class="bi bi-clock-history me-2"></i>Aucun repas consommé sur cette période.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection
