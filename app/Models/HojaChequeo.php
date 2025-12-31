<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaChequeo extends Model
{
    protected $fillable = [
        'equipo_id',
        'observaciones',
        'encendido',
        'version',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function chequeos(): HasMany
    {
        return $this->hasMany(HojaEjecucion::class);
    }

    public function columnas(): HasMany
    {
        return $this->hasMany(HojaColumna::class);
    }

    public function filas(): HasMany
    {
        return $this->hasMany(HojaFila::class);
    }
}
