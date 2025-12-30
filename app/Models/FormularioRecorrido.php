<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormularioRecorrido extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function categorias()
    {
        return $this->hasMany(CategoriaRecorrido::class)->orderBy('orden');
    }
}
