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
        'hora_encendido' => 'string',
        'hora_apagado' => 'string',
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
                $inicio = Carbon::createFromFormat('H:i', $this->hora_encendido);
                $fin = Carbon::createFromFormat('H:i', $this->hora_apagado);

                if ($fin->lessThan($inicio)) {
                    $fin->addDay();
                    $this->tiempo_operacion_minutos = $inicio->diffInMinutes($fin);
                } else {
                    $this->tiempo_operacion_minutos = $inicio->diffInMinutes($fin);
                }
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
            $inicio = Carbon::createFromFormat('H:i', $this->hora_encendido);
            $fin = Carbon::createFromFormat('H:i', $this->hora_apagado);

            if ($fin->lessThan($inicio)) {
                $fin->addDay();
            }

            $totalMinutos = $inicio->diffInMinutes($fin);
            $horas = intval($totalMinutos / 60);
            $minutos = $totalMinutos % 60;

            if ($fin->day > $inicio->day) {
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
