@extends('layouts.app')

@section('titre', 'Roles')

@section('fil')
    Administration / <strong>Roles</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-shield-lock"></i>Roles et habilitations</h5>

<a href="{{ route('roles.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouveau role</a>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Libelle</th>
            <th>Description</th>
            <th>Collaborateurs concernes</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($roles as $role)
            <tr>
                <td>{{ $role->libelle }}</td>
                <td>{{ $role->description ?? '—' }}</td>
                <td><span class="sg-badge-count">{{ $role->acces_count }}</span></td>
                <td>
                    <div class="sg-actions">
                        <a href="{{ route('roles.edit', $role) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('roles.destroy', $role) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sg-btn-icon sg-btn-icon--danger" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

@endsection