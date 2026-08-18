@extends('layouts.app')

@section('titre', 'Societes')

@section('fil')
    Administration / <strong>Sociétés</strong>
@endsection

@section('contenu')

<h5 class="mb-3">Sociétés</h5>

<a href="{{ route('societes.create') }}" class="btn btn-success btn-sm mb-3">+ Nouvelle societe</a>

<table class="table table-striped bg-white">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Sigle</th>
            <th>Départements</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($societes as $societe)
            <tr>
                <td>{{ $societe->nom }}</td>
                <td>{{ $societe->sigle ?? '—' }}</td>
                <td><span class="badge bg-primary">{{ $societe->departements_count }}</span></td>
                <td>
                    <a href="{{ route('societes.edit', $societe) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                    <form method="POST" action="{{ route('societes.destroy', $societe) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Aucune societé.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection