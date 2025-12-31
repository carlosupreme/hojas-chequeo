<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function hojaChequeo(): BelongsTo
    {
        return $this->belongsTo(HojaChequeo::class);
    }

    public function valores(): HasMany
    {
        return $this->hasMany(HojaFilaValor::class);
    }
}
