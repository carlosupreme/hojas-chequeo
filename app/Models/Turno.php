<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * Find the active Turno that matches a given timestamp and HojaChequeo.
     *
     * Resolves the equipo from the HojaChequeo, then finds a turno that:
     *  - has that equipo attached
     *  - the timestamp's time falls between hora_inicio and hora_final
     *  - the corresponding day is in the turno's dias array
     *
     * Handles midnight-crossing shifts (e.g. 22:00→06:00):
     *  - If time >= hora_inicio → shift started today, check today's day
     *  - If time < hora_final  → shift started yesterday, check yesterday's day
     */
    public static function findForTimestamp(Carbon $timestamp, int $hojaChequeoId): ?static
    {
        $equipoId = HojaChequeo::where('id', $hojaChequeoId)->value('equipo_id');

        if (! $equipoId) {
            return null;
        }

        $time = $timestamp->format('H:i:s');
        $today = strtolower($timestamp->englishDayOfWeek);
        $yesterday = strtolower($timestamp->copy()->subDay()->englishDayOfWeek);

        return static::query()
            ->where('activo', true)
            ->whereHas('equipos', fn (Builder $q) => $q->where('equipos.id', $equipoId))
            ->get()
            ->first(function (self $turno) use ($time, $today, $yesterday) {
                $inicio = $turno->hora_inicio;
                $final = $turno->hora_final;

                if ($inicio < $final) {
                    // Normal shift (e.g. 06:00 → 14:00)
                    return $time >= $inicio
                        && $time < $final
                        && in_array($today, $turno->dias);
                }

                // Midnight-crossing shift (e.g. 22:00 → 06:00)
                if ($time >= $inicio) {
                    // After start, before midnight → shift started today
                    return in_array($today, $turno->dias);
                }

                if ($time < $final) {
                    // After midnight, before end → shift started yesterday
                    return in_array($yesterday, $turno->dias);
                }

                return false;
            });
    }

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

    public function equipos(): BelongsToMany
    {
        return $this->belongsToMany(Equipo::class);
    }
}
