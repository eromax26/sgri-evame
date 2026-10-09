<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvStock extends Model
{
    use HasFactory;

    protected $table = 'mouv_stocks';

    // Motifs de sortie normalises : evite le texte libre et garantit des statistiques exploitables
    public const MOTIFS_SORTIE = [
        'Utilisation cuisine',
        'Perte',
        'Péremption',
        'Ajustement inventaire',
    ];

    protected $fillable = [
        'article_id',
        'collaborateur_id',
        'type_mouvement',
        'quantite',
        'date_mouvement',
        'prix_achat',
        'motif_sortie',
        'commande_id',
        'ligne_commande_id',
    ];

    protected function casts(): array
    {
        return [
            'date_mouvement' => 'date',
            'quantite' => 'decimal:2',
            'prix_achat' => 'decimal:2',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function collaborateur(): BelongsTo
    {
        return $this->belongsTo(Collaborateur::class);
    }

    // Renseignees uniquement pour une entree issue d'un achat (module Commandes)
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function ligneCommande(): BelongsTo
    {
        return $this->belongsTo(LigneCommande::class);
    }

    /* ==================== SCOPES ==================== */

    public function scopeEntrees(Builder $query): Builder
    {
        return $query->where('type_mouvement', 'entree');
    }

    public function scopeSorties(Builder $query): Builder
    {
        return $query->where('type_mouvement', 'sortie');
    }

    /* ==================== ACCESSEURS ==================== */

    public function getEstEntreeAttribute(): bool
    {
        return $this->type_mouvement === 'entree';
    }

    public function getEstSortieAttribute(): bool
    {
        return $this->type_mouvement === 'sortie';
    }

    // Origine du mouvement : lie a une commande (achat) ou saisi directement (sortie)
    public function getEstLieACommandeAttribute(): bool
    {
        return $this->commande_id !== null;
    }
}