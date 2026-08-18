@extends('layouts.app')

@section('titre', 'Roles')

@section('fil')
    Administration / <strong>Roles</strong>
@endsection

@section('contenu')

<h5 class="mb-3">Roles et habilitations</h5>

<a href="{{ route('roles.create') }}" class="btn btn-success btn-sm mb-3">+ Nouveau role</a>

<table class="table table-striped bg-white">
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
                <td><span class="badge bg-primary">{{ $role->acces_count }}</span></td>
                <td>
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection