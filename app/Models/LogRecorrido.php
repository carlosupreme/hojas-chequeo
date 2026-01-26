<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LogRecorrido extends Model
{
    protected $fillable = [
        'formulario_recorrido_id',
        'user_id',
        'supervisor_id',
        'turno_id',
        'fecha',
        'firma_operador',
        'firma_supervisor',
        'firmado_operador_at',
        'firmado_supervisor_at',
    ];

    protected $casts = [
        'fecha' => 'date',
        'firmado_operador_at' => 'datetime',
        'firmado_supervisor_at' => 'datetime',
    ];

    public function valores(): HasMany
    {
        return $this->hasMany(ValorRecorrido::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function formularioRecorrido(): BelongsTo
    {
        return $this->belongsTo(FormularioRecorrido::class);
    }
}
