<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogRecorrido extends Model
{
    protected $fillable = ['formulario_recorrido_id', 'user_id', 'turno_id', 'fecha'];

    protected $casts = ['fecha' => 'date'];

    public function valores()
    {
        return $this->hasMany(ValorRecorrido::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }
}
