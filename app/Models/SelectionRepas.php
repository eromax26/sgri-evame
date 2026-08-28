<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class SelectionRepas extends Model
{
    use HasFactory;

    protected $table = 'selections_repas';

    protected $fillable = [
        'ligne_menu_id',
        'collaborateur_id',
        'agent_securite_id',
        'date_selection',
        'date_impression',
        'date_retrait',
        'prix',
        'statut',
        'numero_ticket',
        'periode_facturation',
        'statut_facturation',
    ];

    protected function casts(): array
    {
        return [
            'date_selection' => 'datetime',
            'date_impression' => 'datetime',
            'date_retrait' => 'datetime',
            'prix' => 'decimal:2',
        ];
    }

    public function ligneMenu(): BelongsTo
    {
        return $this->belongsTo(LigneMenu::class);
    }

    public function collaborateur(): BelongsTo
    {
        return $this->belongsTo(Collaborateur::class, 'collaborateur_id');
    }

    public function agentSecurite(): BelongsTo
    {
        return $this->belongsTo(Collaborateur::class, 'agent_securite_id');
    }

    public function plat(): HasOneThrough
    {
        return $this->hasOneThrough(
            Plat::class,
            LigneMenu::class,
            'id',
            'id',
            'ligne_menu_id',
            'plat_id'
        );
    }

    public function getDateRepasAttribute()
    {
        return $this->ligneMenu->date_repas;
    }
}
