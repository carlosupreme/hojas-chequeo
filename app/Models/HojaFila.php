<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaFila extends Model
{
    protected $fillable = [
        'hoja_chequeo_id',
        'answer_type_id',
        'order',
    ];
}
