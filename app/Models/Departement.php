<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = [
        'societe_id',
        'nom',
    ];

    public function societe(): BelongsTo
    {
        return $this->belongsTo(Societe::class);
    }

    public function collaborateurs(): HasMany
    {
        return $this->hasMany(Collaborateur::class);
    }
}