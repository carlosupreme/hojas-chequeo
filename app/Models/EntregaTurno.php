<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntregaTurno extends Model
{
    protected $fillable = [
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
}
