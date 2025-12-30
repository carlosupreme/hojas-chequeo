<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perfil extends Model
{
    const TODAS_LAS_HOJAS = '*';

    protected $fillable = [
        'nombre',
        'hoja_ids',
    ];

    protected function casts(): array
    {
        return [
            'hoja_ids' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tieneAccesoAHoja(int $hojaId): bool
    {
        return in_array(self::TODAS_LAS_HOJAS, $this->hoja_ids) || in_array($hojaId, $this->hoja_ids);
    }
}
