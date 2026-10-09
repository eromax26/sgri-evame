<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LigneCommande extends Model
{
    use HasFactory;

    protected $table = 'ligne_commandes';

    protected $fillable = [
        'commande_id',
        'article_id',
        'quantite_demandee',
        'prix_estime_unitaire',
        'quantite_livree',
        'prix_achat_reel',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'quantite_demandee' => 'decimal:2',
            'prix_estime_unitaire' => 'decimal:2',
            'quantite_livree' => 'decimal:2',
            'prix_achat_reel' => 'decimal:2',
        ];
    }

    /* ==================== RELATIONS ==================== */

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /* ==================== SCOPES ==================== */

    public function scopeNonLivree(Builder $query): Builder
    {
        return $query->whereColumn('quantite_livree', '<', 'quantite_demandee');
    }

    public function scopeLivree(Builder $query): Builder
    {
        return $query->whereColumn('quantite_livree', '>=', 'quantite_demandee');
    }

    /* ==================== ACCESSEURS ==================== */

    public function getResteALivrerAttribute(): float
    {
        return max(0, (float) $this->quantite_demandee - (float) $this->quantite_livree);
    }

    public function getEstTotalementLivreeAttribute(): bool
    {
        return (float) $this->quantite_livree >= (float) $this->quantite_demandee;
    }

    public function getEstPartiellementLivreeAttribute(): bool
    {
        return (float) $this->quantite_livree > 0 && !$this->est_totalement_livree;
    }

    public function getTotalEstimeAttribute(): float
    {
        return (float) $this->quantite_demandee * (float) ($this->prix_estime_unitaire ?? 0);
    }

    public function getTotalReelAttribute(): float
    {
        return (float) $this->quantite_livree * (float) ($this->prix_achat_reel ?? 0);
    }

    // Aligné sur la règle de Commande (modifiable tant que rien n'est acheté)
    public function getPeutEtreModifieeAttribute(): bool
    {
        return $this->commande->peut_etre_modifiee;
    }

    public function getPeutEtreSupprimeeAttribute(): bool
    {
        return $this->commande->peut_etre_modifiee;
    }

    public function getPeutEtreLivreeAttribute(): bool
    {
        return in_array($this->commande->statut, ['validee', 'livree_partielle'])
            && !$this->est_totalement_livree;
    }

    /* ==================== MÉTHODES MÉTIER ==================== */

    /**
     * Enregistre l'achat d'une quantité pour cette ligne.
     * Entrée en stock + mise à jour de la ligne + recalcul du statut, en une transaction.
     */
    public function livrer(float $quantite, float $prixReel, ?string $date = null): bool
    {
        if (!$this->peut_etre_livree) {
            return false;
        }

        if ($quantite <= 0 || $quantite > $this->reste_a_livrer) {
            return false;
        }

        return DB::transaction(function () use ($quantite, $prixReel, $date) {
            $dateMouvement = $date ?? now()->toDateString();

            // 1. Mouvement de stock (entrée)
            //    Hors requête HTTP (commande artisan, file d'attente), on rattache
            //    le mouvement au créateur de la commande plutôt qu'à personne.
            MouvStock::create([
                'article_id' => $this->article_id,
                'collaborateur_id' => Auth::id() ?? $this->commande->collaborateur_id,
                'type_mouvement' => 'entree',
                'quantite' => $quantite,
                'date_mouvement' => $dateMouvement,
                'prix_achat' => $prixReel,
                'commande_id' => $this->commande_id,
                'ligne_commande_id' => $this->id,
            ]);

            // 2. Stock de l'article
            $this->article->increment('quantite_stock', $quantite);

            // 3. Ligne : quantité cumulée et prix moyen pondéré
            //    (évite d'écraser le prix si plusieurs achats sur la même ligne)
            $dejaPaye = (float) $this->quantite_livree * (float) ($this->prix_achat_reel ?? 0);
            $nouvelleQuantite = (float) $this->quantite_livree + $quantite;
            $prixMoyen = ($dejaPaye + $quantite * $prixReel) / $nouvelleQuantite;

            $this->update([
                'quantite_livree' => $nouvelleQuantite,
                'prix_achat_reel' => round($prixMoyen, 2),
            ]);

            // 4. Date d'achat de la commande (première réception)
            if (!$this->commande->date_achat) {
                $this->commande->update(['date_achat' => $dateMouvement]);
            }

            // 5. Statut de la commande
            $this->commande->recalculerStatut();

            return true;
        });
    }
}