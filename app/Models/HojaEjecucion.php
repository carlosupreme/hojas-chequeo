<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaEjecucion extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'user_id',
        'nombre_operador',
        'firma_operador',
        'firma_supervisor',
        'observaciones',
        'finalizado_en',
    ];
}
