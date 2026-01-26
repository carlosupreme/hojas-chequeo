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

    public function capacidad(): string
    {
        $capacidad = $this->specs()->where('tipo', 'like', '%Capacidad%')->first();

        if (! $capacidad) {
            return '';
        }

        return $capacidad->optimo.''.$capacidad->unidad;

    }

    public function specs(): HasMany
    {
        return $this->hasMany(EquipoSpec::class);
    }

    public function hojaChequeos(): HasMany
    {
        return $this->hasMany(HojaChequeo::class);
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }
}
