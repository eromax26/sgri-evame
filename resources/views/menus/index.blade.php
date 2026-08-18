@extends('layouts.app')

@section('titre', 'Menus')

@section('fil')
    Restauration / <strong>Planification des menus</strong>
@endsection

@section('contenu')

<h5 class="mb-3">Planification des menus</h5>

<a href="{{ route('menus.create') }}" class="btn btn-success btn-sm mb-3">+ Nouveau menu de la semaine</a>

<table class="table table-striped bg-white">
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
                    <span class="badge {{ $menu->estPublie() ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $menu->estPublie() ? 'Publie' : 'Brouillon' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary">Gerer</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted">Aucun menu crée.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $menus->links() }}

@endsection