@extends('layouts.app')

@section('titre', 'Societes')

@section('fil')
    Administration / <strong>Sociétés</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-building"></i>Sociétés</h5>

<a href="{{ route('societes.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouvelle societé</a>

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
                    <div class="sg-actions">
                        <a href="{{ route('societes.edit', $societe) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('societes.destroy', $societe) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sg-btn-icon sg-btn-icon--danger" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted"><i class="bi bi-building me-2"></i>Aucune societé.</td></tr>
        @endforelse
    </tbody>
</table>

@endsection