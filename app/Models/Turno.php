<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    protected $fillable = ['nombre', 'dias', 'hora_inicio', 'hora_final', 'activo'];

    protected $casts = [
        'dias' => 'array',
        'activo' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function worksOn($date): bool
    {
        $dayName = Carbon::parse($date)->format('l');

        return in_array($dayName, $this->working_days);
    }
}
