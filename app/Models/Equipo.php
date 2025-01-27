<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{

    protected $fillable = [
        'nombre',
        'tag',
        'area',
        'foto',
        'revision',
        'numeroControl'
    ];

    public function hojasChequeo(): HasMany {
        return $this->hasMany(HojaChequeo::class);
    }
}
