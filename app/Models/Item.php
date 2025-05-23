<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'valores',
        'categoria'
    ];

    protected function casts(): array {
        return [
            'valores' => 'json'
        ];
    }

    public function hojaChequeo(): BelongsTo {
        return $this->belongsTo(HojaChequeo::class);
    }

    public function itemChequeoDiarios(): HasMany {
        return $this->hasMany(ItemChequeoDiario::class);
    }

    public function alerta(): HasOne {
        return $this->hasOne(Alerta::class);
    }
}
