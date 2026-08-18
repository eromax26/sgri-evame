<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acces extends Model
{
    use HasFactory;

    protected $table = 'acces';

    protected $fillable = [
        'collaborateur_id',
        'role_id',
        'date_attribution',
    ];

    protected function casts(): array
    {
        return [
            'date_attribution' => 'date',
        ];
    }

    public function collaborateur(): BelongsTo
    {
        return $this->belongsTo(Collaborateur::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}