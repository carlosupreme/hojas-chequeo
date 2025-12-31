<?php

namespace App\Models;

use App\Observers\HojaFilaRespuestaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([HojaFilaRespuestaObserver::class])]
class HojaFilaRespuesta extends Model
{
    protected $fillable = [
        'hoja_ejecucion_id',
        'hoja_fila_id',
        'answer_option_id',
        'numeric_value',
        'text_value',
        'boolean_value',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:2',
            'boolean_value' => 'boolean',
        ];
    }

    public function hojaEjecucion(): BelongsTo
    {
        return $this->belongsTo(HojaEjecucion::class);
    }

    public function hojaFila(): BelongsTo
    {
        return $this->belongsTo(HojaFila::class);
    }

    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class);
    }
}
