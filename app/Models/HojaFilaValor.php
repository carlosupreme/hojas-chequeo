<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HojaFilaValor extends Model
{
    protected $fillable = [
        'hoja_fila_id',
        'hoja_columna_id',
        'valor',
    ];

    public function hojaFila(): BelongsTo
    {
        return $this->belongsTo(HojaFila::class);
    }

    public function hojaColumna(): BelongsTo
    {
        return $this->belongsTo(HojaColumna::class);
    }
}
