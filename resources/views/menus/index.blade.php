@extends('layouts.app')

@section('titre', 'Menus')

@section('fil')
    Restauration / <strong>Planification des menus</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Planification des menus</h5>

<a href="{{ route('menus.create') }}" class="btn sg-btn-primary btn-sm mb-3">+ Nouveau menu de la semaine</a>

<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Semaine</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($menus as $menu)
            <tr>
                <td>Du {{ $menu->date_debut_semaine->format('d/m/Y') }} au {{ $menu->date_fin_semaine->format('d/m/Y') }}</td>
                <td>
                    <span class="sg-pill {{ $menu->estPublie() ? 'sg-pill--ok' : 'sg-pill--warn' }}">
                        {{ $menu->estPublie() ? 'Publie' : 'Brouillon' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm sg-btn-outline">Gerer</a>
                    @unless ($menu->estPublie())
                        <form method="POST" action="{{ route('menus.destroy', $menu) }}" class="d-inline" onsubmit="return confirm('Supprimer ce menu brouillon ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm sg-btn-outline-danger">Supprimer</button>
                        </form>
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted">Aucun menu crée.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $menus->links() }}

@endsection