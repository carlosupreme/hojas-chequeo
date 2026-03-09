<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Tarjeton extends Model
{
    protected $fillable = [
        'equipo_id',
        'fecha',
        'hora_encendido',
        'hora_apagado',
        'encendido_por',
        'apagado_por',
        'tiempo_operacion_minutos',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_encendido' => 'datetime',
        'hora_apagado' => 'datetime',
    ];

    // Relación con equipos
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    // Calcular tiempo de operación y derivar fecha automáticamente
    protected static function booted(): void
    {
        static::saving(function ($tarjeton): void {
            if ($tarjeton->hora_encendido) {
                $tarjeton->fecha = Carbon::parse($tarjeton->hora_encendido)->toDateString();
            }

            $tarjeton->calcularTiempoOperacion();
        });
    }

    public function calcularTiempoOperacion(): void
    {
        if ($this->hora_encendido && $this->hora_apagado) {
            try {
                $inicio = Carbon::parse($this->hora_encendido);
                $fin = Carbon::parse($this->hora_apagado);

                $this->tiempo_operacion_minutos = $inicio->diffInMinutes($fin);
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

            $totalMinutos = $inicio->diffInMinutes($fin);
            $horas = intval($totalMinutos / 60);
            $minutos = $totalMinutos % 60;

            if ($fin->toDateString() !== $inicio->toDateString()) {
                return "{$horas}h {$minutos}m (nocturno)";
            }

            return "{$horas}h {$minutos}m";
        } catch (Exception $e) {
            return 'Error formato';
        }
    }

    // Scopes útiles
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha', today());
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
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    public function scopeUltimaSemana($query)
    {
        return $query->whereDate('fecha', '>=', now()->subWeek());
    }
}