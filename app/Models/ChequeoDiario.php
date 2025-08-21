<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChequeoDiario extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'nombre_operador',
        'operador_id',
        'firma_operador',
        'firma_supervisor',
        'observaciones',
        'created_at'
    ];

    public function hojaChequeo(): BelongsTo {
        return $this->belongsTo(HojaChequeo::class);
    }

    public function operador(): BelongsTo {
        return $this->belongsTo(User::class, 'operador_id', 'id');
    }

    public function itemsChequeoDiario(): HasMany {
        return $this->hasMany(ItemChequeoDiario::class);
    }

    protected static function booted() {
        static::addGlobalScope('recent_first', function (Builder $builder) {
            $builder->orderByDesc('created_at');
        });
    }
}
