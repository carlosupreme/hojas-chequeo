<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function reporte(): HasOne {
        return $this->hasOne(Reporte::class);
    }
}
