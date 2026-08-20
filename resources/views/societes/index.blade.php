@extends('layouts.app')

@section('titre', 'Societes')

@section('fil')
    Administration / <strong>Sociétés</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Sociétés</h5>

<a href="{{ route('societes.create') }}" class="btn sg-btn-primary btn-sm mb-3">+ Nouvelle societe</a>

<table class="table table-striped bg-white sg-table">
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
                <td><span class="sg-badge-count">{{ $societe->departements_count }}</span></td>
                <td>
                    <a href="{{ route('societes.edit', $societe) }}" class="btn btn-sm sg-btn-outline">Modifier</a>
                    <form method="POST" action="{{ route('societes.destroy', $societe) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm sg-btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Aucune societé.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection