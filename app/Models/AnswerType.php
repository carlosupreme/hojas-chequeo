<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerType extends Model
{
    protected $fillable = [
        'key',
        'label',
        'behavior',
        'aggregable',
    ];
}
