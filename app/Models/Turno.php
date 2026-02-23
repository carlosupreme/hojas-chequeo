<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    protected $fillable = [
        'centro_costo_id',
        'nombre',
        'dias',
        'hora_inicio',
        'hora_final',
        'activo',
    ];

    protected $casts = [
        'dias' => 'array',
        'activo' => 'boolean',
    ];

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function ejecuciones(): HasMany
    {
        return $this->hasMany(HojaEjecucion::class);
    }

    public function offDays(): HasMany
    {
        return $this->hasMany(OffDay::class)->orderBy('fecha');
    }
}
