<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffDay extends Model
{
    protected $fillable = [
        'turno_id',
        'fecha',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }
}
