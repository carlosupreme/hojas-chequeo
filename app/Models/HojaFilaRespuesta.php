<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaFilaRespuesta extends Model
{
    protected $fillable = [
        'hoja_ejecucion_id',
        'hoja_fila_id',
        'answer_option_id',
        'numeric_value',
        'text_value',
        'boolean_value',
    ];
}
