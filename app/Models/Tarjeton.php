<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarjeton extends Model
{
    protected $fillable = [
        'equipo_id',
        'fecha',
        'hora_encendido',
        'hora_apagado'
    ];

    public function equipo(): BelongsTo {
        return $this->belongsTo(Equipo::class);
    }
}
