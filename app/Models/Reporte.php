<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    protected $fillable = [
        'equipo_id',
        'hoja_chequeo_id',
        'fecha'
    ];

    public function equipo(): BelongsTo {
        return $this->belongsTo(Equipo::class);
    }

    public function hojaChequeo(): BelongsTo {
        return $this->belongsTo(HojaChequeo::class);
    }
}
