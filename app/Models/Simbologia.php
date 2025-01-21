<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
