<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnswerOption extends Model
{
    protected $fillable = [
        'answer_type_id',
        'key',
        'label',
        'icon',
        'color',
    ];

    public function answerType(): BelongsTo
    {
        return $this->belongsTo(AnswerType::class);
    }

    public function hojaFilaRespuestas(): HasMany
    {
        return $this->hasMany(HojaFilaRespuesta::class);
    }
}
