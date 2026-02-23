<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CentroCosto extends Model
{
    protected $fillable = ['nombre'];

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function offDays(): HasMany
    {
        return $this->hasMany(OffDay::class)->orderBy('fecha');
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Turno::class);
    }
}
