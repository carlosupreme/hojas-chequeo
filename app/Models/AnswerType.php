<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnswerType extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'label',
        'behavior',
        'aggregable',
    ];

    protected function casts(): array
    {
        return [
            'aggregable' => 'boolean',
        ];
    }

    public function hojaFilas(): HasMany
    {
        return $this->hasMany(HojaFila::class);
    }

    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }
}
