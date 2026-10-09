{{-- Lignes du tableau des plats, isolees pour la recherche dynamique. --}}
@forelse ($plats as $plat)
    <tr>
        <td>
            @if ($plat->photo)
                <img src="{{ asset('storage/' . $plat->photo) }}" alt="{{ $plat->libelle }}" style="height: 44px; width: 44px; object-fit: cover; border-radius: 6px;">
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>{{ $plat->code_plat }}</td>
        <td>{{ $plat->libelle }}</td>
        <td>{{ $plat->categorie ?? '—' }}</td>
        <td>{{ $plat->prix ? number_format($plat->prix, 0, ',', ' ') . ' F' : 'A definir' }}</td>
        <td>
            <span class="sg-pill {{ $plat->statut === 'actif' ? 'sg-pill--ok' : 'sg-pill--off' }}">
                {{ ucfirst($plat->statut) }}
            </span>
        </td>
        <td>
            <div class="sg-actions">
                <a href="{{ route('plats.edit', $plat) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                @if ($plat->statut === 'actif')
                    <form method="POST" action="{{ route('plats.destroy', $plat) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="sg-btn-icon sg-btn-icon--danger" title="Désactiver" aria-label="Désactiver"><i class="bi bi-slash-circle"></i></button>
                    </form>
                @else
                    <form method="POST" action="{{ route('plats.activer', $plat) }}">
                        @csrf
                        <button type="submit" class="sg-btn-icon sg-btn-icon--ok" title="Réactiver" aria-label="Réactiver"><i class="bi bi-arrow-clockwise"></i></button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center text-muted"><i class="bi bi-egg-fried me-2"></i>Aucun plat trouvé.</td></tr>
@endforelse