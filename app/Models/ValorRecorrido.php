<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValorRecorrido extends Model
{
    protected $fillable = [
        'log_recorrido_id',
        'item_recorrido_id',
        'estado',
        'valor_numerico',
        'observaciones',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemRecorrido::class);
    }
}
