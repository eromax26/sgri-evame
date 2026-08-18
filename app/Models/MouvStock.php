<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvStock extends Model
{
    use HasFactory;

    protected $table = 'mouv_stocks';

    protected $fillable = [
        'article_id',
        'collaborateur_id',
        'type_mouvement',
        'quantite',
        'date_mouvement',
        'prix_achat',
        'motif_sortie',
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
}