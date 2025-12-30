<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaTurno extends Model
{
    protected $fillable = [
        'log_recorrido_id',
        'fecha',
        'hora',
        'entrega_equipos',
        'entrega_observaciones_equipos',
        'entrega_servicios',
        'entrega_observaciones_servicios',
        'recepcion_equipos',
        'recepcion_observaciones_equipos',
        'recepcion_servicios',
        'recepcion_observaciones_servicios',
    ];

    public function logRecorrido(): BelongsTo
    {
        return $this->belongsTo(LogRecorrido::class);
    }
}
