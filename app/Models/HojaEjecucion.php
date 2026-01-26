<?php

namespace App\Models;

use App\Observers\HojaEjecucionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(HojaEjecucionObserver::class)]
class HojaEjecucion extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'user_id',
        'turno_id',
        'nombre_operador',
        'firma_operador',
        'firma_supervisor',
        'observaciones',
        'finalizado_en',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'finalizado_en' => 'datetime',
        ];
    }

    public function hojaChequeo(): BelongsTo
    {
        return $this->belongsTo(HojaChequeo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(HojaFilaRespuesta::class);
    }

    /**
     * Chequeos (ejecuciones) en proceso (pendientes de finalizar).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('finalizado_en');
    }

    /**
     * Chequeos (ejecuciones) finalizados.
     */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->whereNotNull('finalizado_en');
    }

    /**
     * Restringe las ejecuciones al usuario indicado.
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }
}
