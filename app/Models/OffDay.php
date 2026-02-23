<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffDay extends Model
{
    protected $fillable = [
        'centro_costo_id',
        'fecha',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class);
    }
}
