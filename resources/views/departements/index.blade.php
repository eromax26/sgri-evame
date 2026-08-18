@extends('layouts.app')

@section('titre', 'Departements')

@section('contenu')

<h5 class="mb-3">Départements</h5>

<a href="{{ route('departements.create') }}" class="btn btn-success btn-sm mb-3">+ Nouveau departement</a>

<table class="table table-striped bg-white">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Societe</th>
            <th>Collaborateurs</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($departements as $dep)
            <tr>
                <td>{{ $dep->nom }}</td>
                <td>{{ $dep->societe->nom }}</td>
                <td><span class="badge bg-primary">{{ $dep->collaborateurs_count }}</span></td>
                <td>
                    <a href="{{ route('departements.edit', $dep) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                    <form method="POST" action="{{ route('departements.destroy', $dep) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Aucun département.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection