<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (! array_key_exists('email_verified_at', $user->getAttributes())) {
                $user->email_verified_at = now();
            }
        });
    }

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

        return $this->roles()
            ->with('permisos')
            ->get()
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

    public function esAdministrador(): bool
    {
        $this->loadMissing(['fundos', 'roles']);

        if ($this->fundos->contains(fn ($fundo) => (bool) ($fundo->pivot->es_administrador ?? false))) {
            return true;
        }

        return $this->roles->contains(fn ($role) => strtolower($role->nombre ?? '') === 'administrador');
    }

    public function requestedDatabaseBackups()
    {
        return $this->hasMany(DatabaseBackup::class, 'requested_by');
    }
}
