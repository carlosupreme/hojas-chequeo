<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaChequeo extends Model
{
    protected $fillable = [
        'equipo_id',
        'version',
        'observaciones',
        'area'
    ];

    public function equipo(): BelongsTo {
        return $this->belongsTo(Equipo::class);
    }

    public function items(): HasMany {
        return $this->hasMany(Item::class);
    }

    public function chequeosDiarios(): HasMany {
        return $this->hasMany(ChequeoDiario::class);
    }

    public function reportes(): HasMany {
        return $this->hasMany(Reporte::class);
    }
}
