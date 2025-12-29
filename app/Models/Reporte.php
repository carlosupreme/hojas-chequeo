<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    protected $fillable = [
        'equipo_id',
        'hoja_chequeo_id',
        'fecha',
        'name',
        'area',
        'priority',
        'observations',
        'failure',
        'photo',
        'user_id',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}
