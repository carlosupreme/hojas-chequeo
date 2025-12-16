<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HojaChequeo extends Model
{
    protected $fillable = [
        'equipo_id',
        'version',
        'observaciones',
        'area',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function chequeosDiarios(): HasMany
    {
        return $this->hasMany(ChequeoDiario::class);
    }

    public function latestChequeoDiario(): HasOne
    {
        return $this->hasOne(ChequeoDiario::class)
            ->orderByDesc('created_at')
            ->limit(1);
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }

    public function scopeActive($query): void
    {
        $query->where('active', true);
    }
}
