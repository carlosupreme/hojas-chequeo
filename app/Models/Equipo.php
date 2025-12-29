<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    protected $fillable = [
        'nombre',
        'tag',
        'foto',
        'area',
        'numeroControl',
        'revision',
    ];

    public function specs(): HasMany
    {
        return $this->hasMany(EquipoSpec::class);
    }
}
