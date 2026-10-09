<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commande extends Model
{
    use HasFactory;

    protected $table = 'commandes';

    protected $fillable = [
        'collaborateur_id',
        'date_commande',
        'date_achat',
        'statut',
        'prix_estime_total',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'date_commande' => 'date',
            'date_achat' => 'date',
            'prix_estime_total' => 'decimal:2',
        ];
    }

    /* ==================== RELATIONS ==================== */

    public function createur(): BelongsTo
    {
        return $this->belongsTo(Collaborateur::class, 'collaborateur_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'ligne_commandes')
            ->withPivot('quantite_demandee', 'prix_estime_unitaire', 'quantite_livree', 'prix_achat_reel')
            ->withTimestamps();
    }

    /* ==================== SCOPES ==================== */

    public function scopeBrouillons(Builder $query): Builder
    {
        return $query->where('statut', 'brouillon');
    }

    public function scopeValidees(Builder $query): Builder
    {
        return $query->where('statut', 'validee');
    }

    public function scopeEnCours(Builder $query): Builder
    {
        return $query->whereIn('statut', ['validee', 'livree_partielle']);
    }

    public function scopeTerminees(Builder $query): Builder
    {
        return $query->whereIn('statut', ['livree', 'annulee', 'cloturee']);
    }

    /* ==================== ACCESSEURS ==================== */

    public function getEstBrouillonAttribute(): bool
    {
        return $this->statut === 'brouillon';
    }

    public function getEstValideeAttribute(): bool
    {
        return $this->statut === 'validee';
    }

    public function getEstLivreePartielleAttribute(): bool
    {
        return $this->statut === 'livree_partielle';
    }

    public function getEstLivreeAttribute(): bool
    {
        return $this->statut === 'livree';
    }

    // RG16 : courses terminees sans avoir tout recu, le reste n'est plus attendu
    public function getEstClotureeAttribute(): bool
    {
        return $this->statut === 'cloturee';
    }

    public function getEstAnnuleeAttribute(): bool
    {
        return $this->statut === 'annulee';
    }

    // Modifiable tant que rien n'a été acheté (brouillon ou liste validée)
    public function getPeutEtreModifieeAttribute(): bool
    {
        return in_array($this->statut, ['brouillon', 'validee'])
            && $this->lignes()->where('quantite_livree', '>', 0)->doesntExist();
    }

    public function getPeutEtreValideeAttribute(): bool
    {
        return $this->est_brouillon && $this->lignes()->count() > 0;
    }

    public function getPeutEtreAnnuleeAttribute(): bool
    {
        return in_array($this->statut, ['brouillon', 'validee'])
            && $this->lignes()->where('quantite_livree', '>', 0)->doesntExist();
    }

    // RG16 : seul un reste deja partiellement achete peut etre cloture
    public function getPeutEtreClotureeAttribute(): bool
    {
        return $this->est_livree_partielle;
    }

    public function getNbArticlesAttribute(): int
    {
        return $this->lignes()->count();
    }

    public function getQuantiteTotaleDemandeeAttribute(): float
    {
        return (float) $this->lignes()->sum('quantite_demandee');
    }

    public function getQuantiteTotaleLivreeAttribute(): float
    {
        return (float) $this->lignes()->sum('quantite_livree');
    }

    /* ==================== MÉTHODES MÉTIER ==================== */

    public function valider(): bool
    {
        if (!$this->peut_etre_validee) {
            return false;
        }

        $this->update([
            'statut' => 'validee',
            'prix_estime_total' => $this->calculerTotalEstime(),
        ]);

        return true;
    }

    public function annuler(): bool
    {
        if (!$this->peut_etre_annulee) {
            return false;
        }

        $this->update(['statut' => 'annulee']);

        return true;
    }

    /**
     * RG16 : clôture une liste partiellement livrée.
     * Le reste à recevoir cesse d'être compté dans les commandes en cours et dans
     * les quantités "à recevoir" des articles.
     */
    public function cloturer(): bool
    {
        if (!$this->peut_etre_cloturee) {
            return false;
        }

        $this->update(['statut' => 'cloturee']);

        return true;
    }

    /**
     * Recalcule le statut après l'enregistrement des achats.
     * Vérification ligne par ligne (et non sur les totaux globaux).
     */
    public function recalculerStatut(): void
    {
        // Ne s'applique qu'à une commande validée ou partiellement livrée
        if (!in_array($this->statut, ['validee', 'livree_partielle'])) {
            return;
        }

        $achete = $this->lignes()->where('quantite_livree', '>', 0)->exists();

        $incomplet = $this->lignes()
            ->whereColumn('quantite_livree', '<', 'quantite_demandee')
            ->exists();

        $this->update([
            'statut' => match (true) {
                !$achete => 'validee',
                $incomplet => 'livree_partielle',
                default => 'livree',
            },
        ]);
    }

    public function calculerTotalEstime(): float
    {
        return (float) $this->lignes()->get()
            ->sum(fn ($l) => $l->quantite_demandee * ($l->prix_estime_unitaire ?? 0));
    }

    // À appeler après modification des lignes d'une liste déjà validée
    public function recalculerTotalEstime(): void
    {
        $this->update(['prix_estime_total' => $this->calculerTotalEstime()]);
    }
}