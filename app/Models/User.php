<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Exception;
use Database\Factories\UserFactory;
use Filament\Panel;
use Devaslanphp\FilamentAvatar\Core\HasAvatarUrl;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasAvatarUrl;

    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function perfil(): BelongsTo {
        return $this->belongsTo(Perfil::class);
    }

    /**
     * @throws Exception
     */
    public function canAccessPanel(Panel $panel): bool {
        $isPanelAdmin = $panel->getId() === 'admin';

        if ($isPanelAdmin) {
            return $this->hasRole('Administrador');
        }

        return true;
    }

    public function isAdmin(): bool {
        return $this->hasRole('Administrador');
    }

    public function isSupervisor(): bool {
        return $this->hasRole('Supervisor');
    }
}
