<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tag',
        'foto',
        'area',
        'numeroControl',
        'revision',
    ];

    public static function getAreas()
    {
        return Equipo::query()
            ->selectRaw('UPPER(area) as area')
            ->whereNotNull('area')
            ->distinct()
            ->pluck('area')
            ->toArray();
    }

    public function registroCargas(): HasMany
    {
        return $this->hasMany(RegistroCarga::class);
    }

    public function capacidad(): string
    {
        $capacidad = $this->specs()->where('tipo', 'like', '%Capacidad%')->first();

        if (! $capacidad) {
            return '';
        }

        return $capacidad->optimo.''.$capacidad->unidad;

    }

    public function scopeCalderas($query)
    {
        return $query->where('tag', 'CM-CAL-01')->orWhere('tag', 'CM-CAL-02');
    }

    public function scopeTombolas($query)
    {
        return $query->where('tag', 'like', '%TOM-%');
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

    public function turnos(): BelongsToMany
    {
        return $this->belongsToMany(Turno::class);
    }
}
