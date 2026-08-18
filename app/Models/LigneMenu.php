<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'plat_id',
        'collaborateur_id',
        'agent_securite_id',
        'date_repas',
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
            'date_repas' => 'date',
            'date_selection' => 'datetime',
            'date_impression' => 'datetime',
            'date_retrait' => 'datetime',
            'prix' => 'decimal:2',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function plat(): BelongsTo
    {
        return $this->belongsTo(Plat::class);
    }

    public function collaborateur(): BelongsTo
    {
        return $this->belongsTo(Collaborateur::class, 'collaborateur_id');
    }

    public function agentSecurite(): BelongsTo
    {
        return $this->belongsTo(Collaborateur::class, 'agent_securite_id');
    }
}