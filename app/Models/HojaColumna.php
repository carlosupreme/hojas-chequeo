<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaColumna extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'key',
        'label',
        'is_fixed',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_fixed' => 'boolean',
        ];
    }
}
