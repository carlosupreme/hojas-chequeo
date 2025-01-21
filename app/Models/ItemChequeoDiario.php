<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemChequeoDiario extends Model
{
    protected $fillable = [
        'chequeo_diario_id',
        'item_id',
        'simbologia_id',
        'valor'
    ];

    public function chequeoDiario(): BelongsTo {
        return $this->belongsTo(ChequeoDiario::class);
    }

    public function item(): BelongsTo {
        return $this->belongsTo(Item::class);
    }

    public function simbologia(): BelongsTo {
        return $this->belongsTo(Simbologia::class);
    }
}
