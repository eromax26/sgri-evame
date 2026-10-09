{{-- Lignes du tableau des articles, isolees pour la recherche dynamique. --}}
@forelse ($articles as $article)
    <tr>
        <td>{{ $article->libelle }}</td>
        <td>{{ $article->unite_mesure }}</td>
        <td>{{ number_format($article->quantite_stock, 2, ',', ' ') }}</td>
        <td>{{ number_format($article->seuil_minimum, 2, ',', ' ') }}</td>
        <td>
            @php
                $aRecevoir = $article->quantiteCommandeeNonLivree();
            @endphp
            @if ($aRecevoir > 0)
                <span class="text-primary fw-bold">{{ number_format($aRecevoir, 2, ',', ' ') }} {{ $article->unite_mesure }}</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @php
                $valeurEstimee = $article->totalEstimeCommandesEnCours();
            @endphp
            @if ($valeurEstimee > 0)
                <span class="text-info">{{ number_format($valeurEstimee, 0, ',', ' ') }} F</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if ($article->estEpuise())
                <span class="sg-pill sg-pill--critique">Stock épuisé !</span>
            @elseif ($article->seuilAtteint())
                <span class="sg-pill sg-pill--danger">Stock faible</span>
            @else
                <span class="sg-pill sg-pill--ok">Normal</span>
            @endif
        </td>
        <td>
            <div class="sg-actions">
                <a href="{{ route('articles.show', $article) }}" class="sg-btn-icon" title="Historique des mouvements" aria-label="Historique des mouvements"><i class="bi bi-clock-history"></i></a>
                <a href="{{ route('articles.edit', $article) }}" class="sg-btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="text-center text-muted"><i class="bi bi-box-seam me-2"></i>Aucun article enregistré.</td></tr>
@endforelse