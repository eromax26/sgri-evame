@extends('layouts.app')

@section('titre', 'Attribuer des roles')

@section('fil')
    Administration / Roles / <strong>Attribuer des roles</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-1">Roles de {{ $collaborateur->prenom }} {{ $collaborateur->nom }}</h5>
<p class="text-muted small mb-4">Matricule : {{ $collaborateur->matricule }}</p>

<div class="card sg-card-form shadow-sm border-0 mb-4">
    <div class="card-body">
        <h6 class="sg-card-header mb-3">Attribuer un nouveau role</h6>
        <form method="POST" action="{{ route('acces.attribuer', $collaborateur) }}" class="d-flex gap-2">
            @csrf
            <select name="role_id" class="form-select">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->libelle }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn sg-btn-primary text-nowrap">Attribuer</button>
        </form>
    </div>
</div>

<h6 class="sg-card-header">Roles actuels</h6>
<table class="table table-striped bg-white sg-table" style="max-width: 600px;">
    <tbody>
        @forelse ($collaborateur->acces as $acces)
            <tr>
                <td>{{ $acces->role->libelle }}</td>
                <td class="text-muted small">Depuis le {{ $acces->date_attribution?->format('d/m/Y') }}</td>
                <td class="text-end">
                    <form method="POST" action="{{ route('acces.retirer', $acces) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm sg-btn-outline-danger">Retirer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td class="text-muted">Aucun role attribue pour l'instant.</td></tr>
        @endforelse
    </tbody>
</table>

<a href="{{ route('collaborateurs.index') }}" class="btn sg-btn-outline btn-sm mt-3">Retour a la liste</a>

@endsection