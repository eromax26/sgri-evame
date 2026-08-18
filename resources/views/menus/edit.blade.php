@extends('layouts.app')

@section('titre', 'Planifier le menu')

@section('contenu')

<h5 class="mb-1">Menu du {{ $menu->date_debut_semaine->format('d/m/Y') }} au {{ $menu->date_fin_semaine->format('d/m/Y') }}</h5>
<span class="badge {{ $menu->estPublie() ? 'bg-success' : 'bg-warning text-dark' }} mb-3">
    {{ $menu->estPublie() ? 'Publié' : 'Brouillon' }}
</span>

<table class="table table-bordered bg-white">
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
                    <form method="POST" action="{{ route('ligne-menus.update', $ligne) }}" class="d-flex gap-2">
                        @csrf
                        @method('PUT')
                        <select name="plat_id" class="form-select" @disabled($menu->estPublie())>
                            @foreach ($plats as $plat)
                                <option value="{{ $plat->id }}" @selected($ligne->plat_id == $plat->id)>{{ $plat->libelle }}</option>
                            @endforeach
                        </select>
                        @unless ($menu->estPublie())
                            <button type="submit" class="btn btn-outline-primary text-nowrap">Modifier</button>
                        @endunless
                    </form>
                </td>
                <td>
                    <span class="badge bg-light text-dark">{{ $ligne->statut }}</span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@unless ($menu->estPublie())
    <form method="POST" action="{{ route('menus.publier', $menu) }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary">Publier le menu</button>
    </form>
@endunless

<a href="{{ route('menus.index') }}" class="btn btn-outline-secondary">Retour à la liste</a>

@endsection