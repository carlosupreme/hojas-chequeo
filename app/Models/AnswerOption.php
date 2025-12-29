<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerOption extends Model
{
    protected $fillable = [
        'answer_type_id',
        'key',
        'label',
        'icon',
        'color',
    ];

    public function answerType()
    {
        return $this->belongsTo(AnswerType::class);
    }
}
