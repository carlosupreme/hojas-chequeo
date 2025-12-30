<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemRecorrido extends Model
{
    protected $fillable = ['categoria_recorrido_id', 'nombre', 'tipo_entrada', 'orden'];

    // Tipos de entrada constantes para evitar errores
    const TIPO_ESTADO = 'estado';

    const TIPO_NUMERO = 'numero';

    const TIPO_TEXTO = 'texto';

    public function categoriaRecorrido()
    {
        return $this->belongsTo(CategoriaRecorrido::class);
    }
}
