<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaRecorrido extends Model
{
    protected $fillable = ['formulario_recorrido_id', 'nombre', 'orden'];

    public function formulario()
    {
        return $this->belongsTo(FormularioRecorrido::class);
    }

    public function items()
    {
        return $this->hasMany(ItemRecorrido::class)->orderBy('orden');
    }
}
