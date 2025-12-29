<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipoSpec extends Model
{
    protected $fillable = [
        'equipo_id',
        'tipo',
        'unidad',
        'min',
        'optimo',
        'max',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}
