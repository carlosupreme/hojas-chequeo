<?php

namespace App\Models;

use App\Observers\ReporteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ReporteObserver::class)]
class Reporte extends Model
{
    protected $fillable = [
        'equipo_id',
        'user_id',
        'hoja_chequeo_id',
        'fecha',
        'nombre',
        'area',
        'prioridad',
        'observaciones',
        'falla',
        'foto',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hojaChequeo(): BelongsTo
    {
        return $this->belongsTo(HojaChequeo::class);
    }
}
