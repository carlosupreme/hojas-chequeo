<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarjeton extends Model
{
    protected $fillable = [
        'equipo_id',
        'hora_encendido',
        'hora_apagado',
        'encendido_por',
        'apagado_por',
        'tiempo_operacion_minutos',
        'observaciones',
        'estado',
        'falla_vapor',
        'falla_vapor_descripcion',
    ];

    /**
     * Derive fecha from hora_encendido for backward compatibility.
     */
    public function getFechaAttribute(): ?\Carbon\Carbon
    {
        return $this->hora_encendido?->copy()->startOfDay();
    }

    protected $casts = [
        'hora_encendido' => 'datetime',
        'hora_apagado' => 'datetime',
        'falla_vapor' => 'boolean',
    ];

    // Relación con equipos
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    // Calcular tiempo de operación automáticamente
    protected static function booted()
    {
        static::saving(function ($tarjeton) {
            $tarjeton->calcularTiempoOperacion();
        });
    }

    public function calcularTiempoOperacion()
    {
        if ($this->hora_encendido && $this->hora_apagado) {
            try {
                $inicio = Carbon::parse($this->hora_encendido);
                $fin = Carbon::parse($this->hora_apagado);

                $this->tiempo_operacion_minutos = (int) $inicio->diffInMinutes($fin);
            } catch (Exception $e) {
                $this->tiempo_operacion_minutos = null;
            }
        }
    }

    // Accessor para tiempo formateado
    public function getTiempoOperacionFormateadoAttribute(): string
    {
        if (! $this->hora_encendido || ! $this->hora_apagado) {
            return 'N/A';
        }

        try {
            $inicio = Carbon::parse($this->hora_encendido);
            $fin = Carbon::parse($this->hora_apagado);

            $totalMinutos = (int) $inicio->diffInMinutes($fin);
            $horas = intdiv($totalMinutos, 60);
            $minutos = $totalMinutos % 60;

            return "{$horas}h {$minutos}m";
        } catch (Exception $e) {
            return 'Error formato';
        }
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('hora_encendido', Carbon::today());
    }

    public function scopeEncendidos($query)
    {
        return $query->where('estado', 'encendido');
    }

    public function scopePorEquipo($query, $equipoId)
    {
        return $query->where('equipo_id', $equipoId);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('hora_encendido', [$fechaInicio, $fechaFin]);
    }

    public function scopeUltimaSemana($query)
    {
        return $query->whereDate('hora_encendido', '>=', now()->subWeek());
    }
}
