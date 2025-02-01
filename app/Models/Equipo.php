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

    public function tarjetones(): HasMany {
        return $this->hasMany(Tarjeton::class);
    }

    public function hojasChequeo(): HasMany {
        return $this->hasMany(HojaChequeo::class);
    }

    public function reportes(): HasMany {
        return $this->hasMany(Reporte::class);
    }
}
