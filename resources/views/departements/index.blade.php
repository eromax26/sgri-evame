@extends('layouts.app')

@section('titre', 'Departements')

@section('fil')
    Administration / <strong>Départements</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-diagram-3"></i>Départements</h5>

<a href="{{ route('departements.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouveau departement</a>

<table class="table table-striped bg-white sg-table">
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
                <td><span class="sg-badge-count">{{ $dep->collaborateurs_count }}</span></td>
                <td>
                    <div class="sg-actions">
                        <a href="{{ route('departements.edit', $dep) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('departements.destroy', $dep) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sg-btn-icon sg-btn-icon--danger" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted"><i class="bi bi-diagram-3 me-2"></i>Aucun département.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection