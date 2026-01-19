<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function scopeAvailableTo(Builder $query, ?array $ids): Builder
    {
        if (empty($ids)) {
            return $query;
        }

        if (in_array('*', $ids)) {
            return $query;
        }

        return $query->whereIn('id', $ids);
    }

    public function scopeInArea(Builder $query, ?string $area): Builder
    {
        return $query->when($area, fn (Builder $q) => $q->whereHas('equipo', fn (Builder $eq) => $eq->where('area', $area)));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->whereHas('equipo', fn (Builder $eq) => $eq->where('nombre', 'ILIKE', "%{$term}%")
            ->orWhere('tag', 'ILIKE', "%{$term}%")
            ->orWhere('area', 'ILIKE', "%{$term}%")
        ));
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

    public function hasItems(): bool
    {
        return $this->filas()->count() > 0;
    }
}
