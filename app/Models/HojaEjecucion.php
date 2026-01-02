<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaEjecucion extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'user_id',
        'turno_id',
        'nombre_operador',
        'firma_operador',
        'firma_supervisor',
        'observaciones',
        'finalizado_en',
    ];

    protected function casts(): array
    {
        return [
            'finalizado_en' => 'datetime',
        ];
    }

    public function hojaChequeo(): BelongsTo
    {
        return $this->belongsTo(HojaChequeo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(HojaFilaRespuesta::class);
    }
}
