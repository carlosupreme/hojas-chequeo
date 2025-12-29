<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $fillable = [
        'nombre',
        'tag',
        'foto',
        'area',
        'numeroControl',
        'revision',
    ];
}
