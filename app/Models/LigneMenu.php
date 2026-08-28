<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LigneMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'plat_id',
        'date_repas',
    ];

    protected function casts(): array
    {
        return [
            'date_repas' => 'date',
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

    public function selections(): HasMany
    {
        return $this->hasMany(SelectionRepas::class);
    }
}