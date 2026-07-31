<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'dni', 'name', 'username', 'email', 'password', 'estado', 'max_active_sessions', 'session_idle_timeout_minutes',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'name' => Uppercase::class,
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
            'max_active_sessions' => 'integer',
            'session_idle_timeout_minutes' => 'integer',
        ];
    }

    public function fundos()
    {
        return $this->belongsToMany(Fundo::class, 'fundo_user')
            ->withPivot('es_administrador')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'rol_id');
    }

    public function sesiones()
    {
        return $this->hasMany(UserSession::class);
    }

    public function tienePermiso(string $modulo, string $accion): bool
    {
        if (! $this->estaActivo()) {
            return false;
        }

        $fundoId = session('fundo_id');

        if (! $fundoId) {
            return false;
        }

        $this->loadMissing(['fundos', 'roles.permisos']);

        $membership = $this->fundos->firstWhere('id', (int) $fundoId);
        if (! $membership || ! $membership->activo) {
            return false;
        }

        if ($membership->pivot->es_administrador) {
            return true;
        }

        return $this->roles
            ->filter(fn ($role) => $role->fundo_id === null || $role->fundo_id === (int) $fundoId)
            ->contains(fn ($role) => $role->permisos->contains(
                fn ($permiso) => $permiso->modulo === $modulo && $permiso->accion === $accion
            ));
    }

    public function estaActivo(): bool
    {
        // Freshly created Eloquent instances do not receive database defaults until reloaded.
        return $this->estado === null || $this->estado === 'activo';
    }

    public function fundoActivo()
    {
        $fundoId = session('fundo_id');

        if (! $fundoId) {
            return null;
        }

        $this->loadMissing('fundos');

        return $this->fundos
            ->where('activo', true)
            ->firstWhere('id', (int) $fundoId);
    }

    public function requestedDatabaseBackups()
    {
        return $this->hasMany(DatabaseBackup::class, 'requested_by');
    }
}
