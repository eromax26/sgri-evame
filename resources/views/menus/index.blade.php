@extends('layouts.app')

@section('titre', 'Menus')

@section('fil')
    Restauration / <strong>Planification des menus</strong>
@endsection

@section('contenu')

<h5 class="sg-page-title mb-4"><i class="bi bi-calendar3"></i>Planification des menus</h5>

<a href="{{ route('menus.create') }}" class="btn sg-btn-primary btn-sm mb-3"><i class="bi bi-plus-lg"></i> Nouveau menu de la semaine</a>

<div class="table-responsive">
<table class="table table-striped bg-white sg-table">
    <thead>
        <tr>
            <th>Semaine</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($menus as $menu)
            <tr>
                <td>Du {{ $menu->date_debut_semaine->format('d/m/Y') }} au {{ $menu->date_fin_semaine->format('d/m/Y') }}</td>
                <td>
                    <span class="sg-pill {{ $menu->estPublie() ? 'sg-pill--ok' : 'sg-pill--warn' }}">
                        {{ $menu->estPublie() ? 'Publie' : 'Brouillon' }}
                    </span>
                </td>
                <td>
                    <div class="sg-actions">
                        @if ($menu->estPublie())
                            <a href="{{ route('menus.edit', $menu) }}" class="sg-btn-icon" title="Consulter" aria-label="Consulter"><i class="bi bi-eye"></i></a>
                        @else
                            <a href="{{ route('menus.edit', $menu) }}" class="sg-btn-icon" title="Gerer" aria-label="Gerer"><i class="bi bi-pencil"></i></a>
                        @endif
                        @unless ($menu->estPublie())
                            <form method="POST" action="{{ route('menus.destroy', $menu) }}" onsubmit="return confirm('Supprimer ce menu brouillon ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sg-btn-icon sg-btn-icon--danger" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted"><i class="bi bi-calendar3 me-2"></i>Aucun menu crée.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

{{ $menus->links() }}

@endsection