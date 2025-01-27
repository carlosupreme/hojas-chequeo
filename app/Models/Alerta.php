<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alerta extends Model
{
    protected $fillable = [
        'item_id', 'simbologia_id', 'valor', 'contador', 'operador'
    ];

    public function item(): BelongsTo {
        return $this->belongsTo(Item::class);
    }

    public function simbologia(): BelongsTo {
        return $this->belongsTo(Simbologia::class);
    }
}
