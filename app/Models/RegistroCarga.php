<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroCarga extends Model
{
    protected $fillable = [
        'equipo_id',
        'user_id',
        'turno_id',
        'centro_costo_id',
        'registrado_en',
    ];

    protected $casts = [
        'registrado_en' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class);
    }

    // -------------------------------------------------------------------------
    // Scopes — used for stats queries
    // -------------------------------------------------------------------------

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('registrado_en', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('registrado_en', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeForEquipo(Builder $query, int $equipoId): Builder
    {
        return $query->where('equipo_id', $equipoId);
    }

    public function scopeForTurno(Builder $query, int $turnoId): Builder
    {
        return $query->where('turno_id', $turnoId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCentroCosto(Builder $query, int $id): Builder
    {
        return $query->where('centro_costo_id', $id);
    }
}
