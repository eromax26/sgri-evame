{{-- Lignes du tableau des collaborateurs, isolees pour la recherche dynamique. --}}
@forelse ($collaborateurs as $c)
    <tr>
        <td>{{ $c->matricule }}</td>
        <td>{{ $c->nom }} {{ $c->prenom }}</td>
        <td>{{ $c->departement->nom ?? '—' }}</td>
        <td>{{ $c->fonction ?? '—' }}</td>
        <td>
            @if ($c->statut === 'actif')
                <span class="sg-pill sg-pill--ok">Actif</span>
            @elseif ($c->statut === 'inactif')
                <span class="sg-pill sg-pill--off">Inactif</span>
            @else
                <span class="sg-pill sg-pill--warn">Bloque</span>
            @endif
        </td>
        <td>
            <div class="sg-actions">
                <a href="{{ route('collaborateurs.edit', $c) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                <a href="{{ route('roles.attribuerForm', $c) }}" class="sg-btn-icon" title="Gérer les roles" aria-label="Gérer les roles"><i class="bi bi-shield-check"></i></a>
                @if ($c->statut === 'actif')
                    <form method="POST" action="{{ route('collaborateurs.destroy', $c) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="sg-btn-icon sg-btn-icon--danger" title="Désactiver" aria-label="Désactiver"><i class="bi bi-slash-circle"></i></button>
                    </form>
                @else
                    <form method="POST" action="{{ route('collaborateurs.activer', $c) }}">
                        @csrf
                        <button type="submit" class="sg-btn-icon sg-btn-icon--ok" title="Réactiver" aria-label="Réactiver"><i class="bi bi-arrow-clockwise"></i></button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center text-muted"><i class="bi bi-people me-2"></i>Aucun collaborateur trouvé.</td></tr>
@endforelse