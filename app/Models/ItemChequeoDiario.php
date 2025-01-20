<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemChequeoDiario extends Model
{
    protected $fillable = [
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

    public function simbologia(): HasOne {
        return $this->hasOne(Simbologia::class);
    }
}
