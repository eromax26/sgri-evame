@extends('layouts.app')

@section('titre', 'Attribuer des roles')

@section('fil')
    Administration / Roles / <strong>Attribuer des roles</strong>
@endsection

@section('contenu')

<h5 class="mb-1">Roles de {{ $collaborateur->prenom }} {{ $collaborateur->nom }}</h5>
<p class="text-muted small">Matricule : {{ $collaborateur->matricule }}</p>

<div class="card mb-4" style="max-width: 500px;">
    <div class="card-body">
        <h6 class="mb-3">Attribuer un nouveau role</h6>
        <form method="POST" action="{{ route('acces.attribuer', $collaborateur) }}" class="d-flex gap-2">
            @csrf
            <select name="role_id" class="form-select">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->libelle }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary text-nowrap">Attribuer</button>
        </form>
    </div>
</div>

<h6>Roles actuels</h6>
<table class="table table-striped bg-white" style="max-width: 600px;">
    <tbody>
        @forelse ($collaborateur->acces as $acces)
            <tr>
                <td>{{ $acces->role->libelle }}</td>
                <td class="text-muted small">Depuis le {{ $acces->date_attribution?->format('d/m/Y') }}</td>
                <td class="text-end">
                    <form method="POST" action="{{ route('acces.retirer', $acces) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Retirer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td class="text-muted">Aucun role attribue pour l'instant.</td></tr>
        @endforelse
    </tbody>
</table>

<a href="{{ route('collaborateurs.index') }}" class="btn btn-outline-secondary btn-sm">Retour a la liste</a>

@endsection