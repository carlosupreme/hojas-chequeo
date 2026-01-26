<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemRecorrido extends Model
{
    protected $fillable = ['categoria_recorrido_id', 'nombre', 'tipo_entrada', 'orden'];

    const TIPO_ESTADO = 'estado';

    const TIPO_NUMERO = 'numero';

    const TIPO_TEXTO = 'texto';

    public function categoriaRecorrido(): BelongsTo
    {
        return $this->belongsTo(CategoriaRecorrido::class);
    }

    public function valores(): HasMany
    {
        return $this->hasMany(ValorRecorrido::class);
    }

    public function isTipoEstado(): bool
    {
        return $this->tipo_entrada === self::TIPO_ESTADO;
    }

    public function isTipoTexto(): bool
    {
        return $this->tipo_entrada === self::TIPO_TEXTO;
    }

    public function isTipoNumero(): bool
    {
        return $this->tipo_entrada === self::TIPO_NUMERO;
    }
}
