<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormularioRecorrido extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function categorias()
    {
        return $this->hasMany(CategoriaRecorrido::class)->orderBy('orden');
    }

    public function logRecorridos(): HasMany
    {
        return $this->hasMany(LogRecorrido::class);
    }
}
