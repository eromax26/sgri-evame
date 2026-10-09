<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\LigneCommande;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'unite_mesure',
        'quantite_stock',
        'seuil_minimum',
    ];

    protected function casts(): array
    {
        return [
            'quantite_stock' => 'decimal:2',
            'seuil_minimum' => 'decimal:2',
        ];
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvStock::class);
    }

    public function lignesCommande(): HasMany
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function estConfigure(): bool
    {
        return $this->seuil_minimum > 0;
    }

    public function estEpuise(): bool
    {
        return $this->quantite_stock <= 0;
    }

    public function seuilAtteint(): bool
    {
        return $this->estConfigure() && $this->quantite_stock <= $this->seuil_minimum;
    }

    public function scopeEnAlerte($query)
    {
        return $query->where(function ($q) {
            $q->where('quantite_stock', '<=', 0)
                ->orWhere(function ($q2) {
                    $q2->where('seuil_minimum', '>', 0)->whereColumn('quantite_stock', '<=', 'seuil_minimum');
                });
        });
    }

    /**
     * Quantité totale commandée mais pas encore livrée (commandes validées ou partiellement livrées)
     */
    public function quantiteCommandeeNonLivree(): float
    {
        return $this->lignesCommande()
            ->whereHas('commande', fn($q) => $q->whereIn('statut', ['validee', 'livree_partielle']))
            ->get()
            ->sum(fn($l) => max(0, $l->quantite_demandee - $l->quantite_livree));
    }

    /**
     * Valeur estimée des quantités restant à livrer
     */
    public function totalEstimeCommandesEnCours(): float
    {
        return $this->lignesCommande()
            ->whereHas('commande', fn($q) => $q->whereIn('statut', ['validee', 'livree_partielle']))
            ->get()
            ->sum(fn($l) => max(0, $l->quantite_demandee - $l->quantite_livree) * ($l->prix_estime_unitaire ?? 0));
    }

    /* ==================== MÉTHODES MÉTIER ==================== */

    /**
     * Enregistre une sortie de stock : mouvement + décrément du stock, en une transaction.
     *
     * RG11 : la sortie est refusée si la quantité dépasse le stock disponible.
     * Le verrou pessimiste empêche deux sorties simultanées de rendre le stock négatif.
     *
     * @return bool false si la sortie est refusée (stock insuffisant ou quantité invalide)
     */
    public function enregistrerSortie(float $quantite, string $motif, ?string $date = null, ?int $collaborateurId = null): bool
    {
        if ($quantite <= 0) {
            return false;
        }

        return DB::transaction(function () use ($quantite, $motif, $date, $collaborateurId) {
            $article = Article::where('id', $this->id)->lockForUpdate()->firstOrFail();

            if ($quantite > (float) $article->quantite_stock) {
                // Stock relu sous verrou : sert à afficher un message d'erreur exact
                $this->quantite_stock = $article->quantite_stock;

                return false;
            }

            MouvStock::create([
                'article_id' => $article->id,
                'collaborateur_id' => $collaborateurId,
                'type_mouvement' => 'sortie',
                'quantite' => $quantite,
                'date_mouvement' => $date ?? now()->toDateString(),
                'motif_sortie' => $motif,
            ]);

            $article->decrement('quantite_stock', $quantite);

            // Stock à jour sur l'instance appelante (message d'alerte seuil)
            $this->quantite_stock = $article->refresh()->quantite_stock;

            return true;
        });
    }
}