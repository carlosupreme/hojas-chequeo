<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alerta extends Model
{
    protected $fillable = [
        'item_id', 'simbologia_id', 'valor', 'contador'
    ];

    public function item(): BelongsTo {
        return $this->belongsTo(Item::class);
    }

    public function simbologia(): HasOne {
        return $this->hasOne(Simbologia::class);
    }
}
