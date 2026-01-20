<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perfil extends Model
{
    protected $fillable = [
        'nombre',
        'acceso_total',
        'hoja_ids',
    ];

    protected function casts(): array
    {
        return [
            'acceso_total' => 'boolean',
            'hoja_ids' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($model) {
            if ($model->acceso_total) {
                $model->hoja_ids = [];
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tieneAccesoAHoja(int $hojaId): bool
    {
        if ($this->acceso_total) {
            return true;
        }

        return in_array($hojaId, $this->hoja_ids ?? [], true);
    }
}
