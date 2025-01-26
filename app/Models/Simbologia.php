<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Simbologia extends Model
{
    protected $fillable = [
        'icono',
        'nombre',
        'descripcion',
        'color'
    ];

    public function alertas(): HasMany {
        return $this->hasMany(Alerta::class);
    }

    public function itemChequeoDiario(): HasMany {
        return $this->hasMany(ItemChequeoDiario::class);
    }
}
