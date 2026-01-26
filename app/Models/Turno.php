<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    protected $fillable = ['nombre', 'dias', 'hora_inicio', 'hora_final', 'activo'];

    protected $casts = [
        'dias' => 'array',
        'activo' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
