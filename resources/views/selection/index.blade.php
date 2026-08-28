@extends('layouts.app')

@section('titre', 'Mes repas')

@section('fil')
    Mon espace / <strong>Menu de la semaine</strong>
@endsection

@section('contenu')

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h5 class="sg-page-title mb-1">Menu de la semaine</h5>
        <div class="text-muted small">Du {{ $debutSemaine->translatedFormat('l d') }} au {{ $finSemaine->translatedFormat('l d F Y') }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('selection.index', ['semaine' => $semainePrecedente]) }}" class="btn sg-btn-outline btn-sm">&larr; Semaine precedente</a>
        <a href="{{ route('selection.index', ['semaine' => $semaineSuivante]) }}" class="btn sg-btn-outline btn-sm">Semaine suivante &rarr;</a>
    </div>
</div>

<div class="alert alert-warning py-2 small mb-4">
    Sélectionnez les repas que vous souhaitez consommer. Une fois votre sélection validée, votre demande de ticket sera transmise à l'agent de sécurité pour impression.
</div>

@if (! $menu)
    <p class="text-center text-muted py-4">Aucun menu publié pour cette semaine.</p>
@else
    @php
        $nbDejaSelectionnes = $jours->filter(fn ($j) => $j['maSelection'])->count();
        $montantDejaSelectionne = $jours->filter(fn ($j) => $j['maSelection'])->sum(fn ($j) => $j['ligne']->plat->prix);
    @endphp
    <form method="POST" action="{{ route('selection.valider') }}" id="form-selection">
        @csrf

        <div class="row g-3 mb-3">
            @foreach ($jours as $jour)
                @php
                    $ligne = $jour['ligne'];
                    $estMoi = (bool) $jour['maSelection'];
                    $estOuvert = $ligne && $ligne->plat_id && ! $estMoi && $jour['date']->toDateString() >= now()->toDateString();
                @endphp
                <div class="col-sm-6 col-lg">
                    <div class="sg-meal-card {{ $estMoi ? 'sg-meal-card--selected' : '' }}">
                        <div class="sg-meal-card-date">
                            <b>{{ $jour['date']->format('d') }}</b>
                            <small>{{ Illuminate\Support\Str::upper($jour['date']->translatedFormat('M')) }}</small>
                        </div>
                        <div class="sg-meal-card-photo">
                            @if ($ligne && $ligne->plat->photo)
                                <img src="{{ asset('storage/' . $ligne->plat->photo) }}" alt="{{ $ligne->plat->libelle }}">
                            @else
                                <i class="bi bi-egg-fried"></i>
                            @endif
                        </div>
                        <div class="sg-meal-card-body">
                            <div class="sg-meal-card-jour">{{ Illuminate\Support\Str::upper($jour['date']->translatedFormat('l')) }}</div>
                            @if ($ligne)
                                <div class="sg-meal-card-titre">{{ $ligne->plat->libelle }}</div>
                                @if ($ligne->plat->description)
                                    <p class="sg-meal-card-desc">{{ $ligne->plat->description }}</p>
                                @endif
                                @if ($ligne->plat->prix)
                                    <div class="sg-meal-card-prix">{{ number_format($ligne->plat->prix, 0, ',', ' ') }} <small>F CFA</small></div>
                                @endif

                                @if ($estMoi)
                                    <button type="button" class="btn sg-btn-primary btn-sm w-100" disabled>&check; Sélectionné</button>
                                @elseif ($estOuvert)
                                    <label class="sg-meal-toggle-label">
                                        <input type="checkbox" name="lignes[]" value="{{ $ligne->id }}" class="sg-meal-toggle" data-prix="{{ (float) $ligne->plat->prix }}" hidden>
                                        <span class="btn sg-btn-outline-danger btn-sm w-100 sg-meal-toggle-off">Sélectionner ce repas</span>
                                        <span class="btn sg-btn-primary btn-sm w-100 sg-meal-toggle-on">&check; Sélectionne</span>
                                    </label>    
                                @else
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" disabled>Non disponible</button>
                                @endif
                            @else
                                <div class="sg-meal-card-titre text-muted">Aucun repas prevu</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="sg-selection-bar d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span id="sg-selection-count">{{ $nbDejaSelectionnes }}</span> repas selectionnés
                &middot; Montant estimé : <span id="sg-selection-total">{{ number_format($montantDejaSelectionne, 0, ',', ' ') }}</span> F CFA
                <div class="text-muted small">Le montant sera retenu sur votre salaire en fin de mois.</div>
            </div>
            <button type="submit" class="btn sg-btn-primary" id="sg-btn-valider" disabled>Valider ma sélection</button>
        </div>
    </form>

    <script>
        (function () {
            var baseCount = {{ $nbDejaSelectionnes }};
            var baseTotal = {{ (float) $montantDejaSelectionne }};
            var checkboxes = document.querySelectorAll('.sg-meal-toggle');
            var countEl = document.getElementById('sg-selection-count');
            var totalEl = document.getElementById('sg-selection-total');
            var btnValider = document.getElementById('sg-btn-valider');

            function recalculer() {
                var nb = baseCount;
                var total = baseTotal;
                var nbCoches = 0;

                checkboxes.forEach(function (cb) {
                    if (cb.checked) {
                        nb += 1;
                        total += parseFloat(cb.dataset.prix) || 0;
                        nbCoches += 1;
                    }
                });

                countEl.textContent = nb;
                totalEl.textContent = new Intl.NumberFormat('fr-FR').format(total);
                btnValider.disabled = nbCoches === 0;
            }

            checkboxes.forEach(function (cb) {
                cb.addEventListener('change', recalculer);
            });
        })();
    </script>
@endif

@endsection
