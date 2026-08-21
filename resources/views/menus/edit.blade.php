@extends('layouts.app')

@section('titre', 'Planifier le menu')

@section('fil')
    Restauration / Planification des menus / <strong>Gérer le menu</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-2">Menu du {{ $menu->date_debut_semaine->format('d/m/Y') }} au {{ $menu->date_fin_semaine->format('d/m/Y') }}</h5>
<span class="sg-pill {{ $menu->estPublie() ? 'sg-pill--ok' : 'sg-pill--warn' }} mb-3">
    {{ $menu->estPublie() ? 'Publié' : 'Brouillon' }}
</span>

<form method="POST" action="{{ route('menus.updateLignes', $menu) }}" id="form-menu-lignes">
    @csrf
    @method('PUT')
    <table class="table table-bordered bg-white sg-table">
        <thead>
            <tr>
                <th style="width: 180px;">Jour</th>
                <th>Plat prevu</th>
                <th style="width: 120px;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignesMenu as $ligne)
                <tr>
                    <td>
                        <strong>{{ ucfirst($ligne->date_repas->translatedFormat('l')) }}</strong>
                        <div class="text-muted small">{{ $ligne->date_repas->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <select name="plats[{{ $ligne->id }}]" class="form-select" @disabled($menu->estPublie())>
                            @if (! $ligne->plat_id)
                                <option value="">-- Choisir un plat --</option>
                            @endif
                            @foreach ($plats as $plat)
                                <option value="{{ $plat->id }}" @selected($ligne->plat_id == $plat->id)>{{ $plat->libelle }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <span class="sg-pill sg-pill--off">{{ $ligne->statut }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</form>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
    <a href="{{ route('menus.index') }}" class="btn sg-btn-outline"><i class="bi bi-arrow-left"></i> Retour à la liste</a>

    @unless ($menu->estPublie())
        <div class="d-flex gap-2">
            <button type="submit" form="form-menu-lignes" class="btn sg-btn-ok"><i class="bi bi-save"></i> Enregistrer le menu</button>

            <form method="POST" action="{{ route('menus.publier', $menu) }}">
                @csrf
                <button type="submit" class="btn sg-btn-navy"><i class="bi bi-send-check"></i> Publier le menu</button>
            </form>

            <form method="POST" action="{{ route('menus.destroy', $menu) }}" onsubmit="return confirm('Supprimer ce menu brouillon ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn sg-btn-danger"><i class="bi bi-trash"></i> Supprimer le brouillon</button>
            </form>
        </div>
    @endunless
</div>

@endsection