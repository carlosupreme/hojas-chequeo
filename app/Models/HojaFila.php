<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaFila extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'answer_type_id',
        'order',
        'categoria',
    ];

    public function hojaChequeo(): BelongsTo
    {
        return $this->belongsTo(HojaChequeo::class);
    }

    public function answerType(): BelongsTo
    {
        return $this->belongsTo(AnswerType::class);
    }

    public function valores(): HasMany
    {
        return $this->hasMany(HojaFilaValor::class);
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(HojaFilaRespuesta::class);
    }
}
