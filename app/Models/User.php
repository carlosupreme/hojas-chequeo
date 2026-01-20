<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public static string $canEditDatesPermission = 'chequeos.edit.date';

    protected $with = ['turno', 'perfil', 'roles'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil_id',
        'turno_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function chequeos(): HasMany
    {
        return $this->hasMany(HojaEjecucion::class);
    }

    public function chequeosPendientes(): HasMany
    {
        return $this->hasMany(HojaEjecucion::class)
            ->whereNull('finalizado_en');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function perfil(): BelongsTo
    {
        return $this->belongsTo(Perfil::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $isPanelAdmin = $panel->getId() === 'admin';

        if ($isPanelAdmin) {
            return $this->hasRole('Administrador');
        }

        return true;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('Supervisor');
    }

    public function tieneAccesoAHoja($hojaId): bool
    {
        return $this->perfil->tieneAccesoAHoja($hojaId);
    }
}
