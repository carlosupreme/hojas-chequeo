<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaChequeo extends Model
{
    protected $fillable = [
        'equipo_id',
        'observaciones',
        'encendido',
    ];
}
