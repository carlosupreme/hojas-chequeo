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
        'observaciones',
        'encendido',
        'version',
    ];

    public static function getCurrentVersion(int $equipo_id): int
    {
        $latestVersion = HojaChequeo::where('equipo_id', $equipo_id)
            ->orderBy('version', 'desc')
            ->value('version');

        return $latestVersion ? (int) $latestVersion + 1 : 1;
    }

    public function latestChequeoDiario(): HasOne
    {
        return $this->hasOne(HojaEjecucion::class)
            ->orderByDesc('finalizado_en')
            ->limit(1);
    }

    public function scopeEncendidas($query): void
    {
        $query->where('encendido', true);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function chequeos(): HasMany
    {
        return $this->hasMany(HojaEjecucion::class);
    }

    public function columnas(): HasMany
    {
        return $this->hasMany(HojaColumna::class)->orderBy('order');
    }

    public function filas(): HasMany
    {
        return $this->hasMany(HojaFila::class)->orderBy('order');
    }
}
