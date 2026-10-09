{{-- Lignes du tableau des commandes, isolees pour la recherche dynamique. --}}
@forelse ($commandes as $cmd)
    <tr>
        <td>#{{ $cmd->id }}</td>
        <td>{{ $cmd->date_commande->format('d/m/Y') }}</td>
        <td>{{ $cmd->createur->nom }} {{ $cmd->createur->prenom }}</td>
        <td>{{ $cmd->lignes_count }}</td>
        <td>{{ number_format($cmd->lignes_sum_quantite_demandee ?? 0, 2, ',', ' ') }}</td>
        <td>
            @if ($cmd->prix_estime_total)
                {{ number_format($cmd->prix_estime_total, 0, ',', ' ') }} F
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @include('commandes.partials._statut', ['commande' => $cmd])
        </td>
        <td>
            <div class="sg-actions">
                <a href="{{ route('commandes.show', $cmd) }}" class="sg-btn-icon" title="Voir / Imprimer la liste" aria-label="Voir"><i class="bi bi-eye"></i></a>

                @if ($cmd->peut_etre_modifiee)
                    <a href="{{ route('commandes.edit', $cmd) }}" class="sg-btn-icon text-primary" title="Modifier la liste" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                @endif

                @if ($cmd->peut_etre_validee)
                    <form method="POST" action="{{ route('commandes.valider', $cmd) }}" class="d-inline" onsubmit="return confirm('Valider cette commande ? Elle ne sera plus modifiable.');">
                        @csrf
                        <button type="submit" class="sg-btn-icon text-success" title="Valider" aria-label="Valider"><i class="bi bi-check-circle"></i></button>
                    </form>
                @endif

                @if ($cmd->peut_etre_annulee)
                    <form method="POST" action="{{ route('commandes.annuler', $cmd) }}" class="d-inline" onsubmit="return confirm('Annuler cette commande ?');">
                        @csrf
                        <button type="submit" class="sg-btn-icon text-danger" title="Annuler" aria-label="Annuler"><i class="bi bi-x-circle"></i></button>
                    </form>
                @endif

                @if ($cmd->est_brouillon)
                    <form method="POST" action="{{ route('commandes.destroy', $cmd) }}" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce brouillon ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="sg-btn-icon text-danger" title="Supprimer le brouillon" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="text-center text-muted"><i class="bi bi-truck me-2"></i>Aucune commande trouvée.</td></tr>
@endforelse