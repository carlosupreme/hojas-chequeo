<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaFilaValor extends Model
{
    protected $fillable = [
        'hoja_fila_id',
        'hoja_columna_id',
        'valor',
    ];
}
