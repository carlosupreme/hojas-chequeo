<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perfil extends Model
{
    protected $fillable = [
        "name",
        "hoja_ids"
    ];

    protected function casts(): array {
        return [
            'hoja_ids' => 'array'
        ];
    }

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }
}
