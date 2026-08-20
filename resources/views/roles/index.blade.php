@extends('layouts.app')

@section('titre', 'Roles')

@section('fil')
    Administration / <strong>Roles</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4">Roles et habilitations</h5>

<a href="{{ route('roles.create') }}" class="btn sg-btn-primary btn-sm mb-3">+ Nouveau role</a>

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
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm sg-btn-outline">Modifier</a>
                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm sg-btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection