<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FundoProvisioner
{
    /**
     * @return array{fundo: Fundo, user: User}
     */
    public function provision(
        string $fundoName,
        string $adminName,
        string $email,
        string $username,
        ?string $password = null,
    ): array {
        $fundoName = Str::upper(trim($fundoName));
        $email = Str::lower(trim($email));
        $username = Str::lower(trim($username));

        return DB::transaction(function () use ($fundoName, $adminName, $email, $username, $password): array {
            $user = User::withTrashed()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if ($user?->trashed()) {
                throw ValidationException::withMessages([
                    'email' => 'El correo pertenece a un usuario archivado. Restaure esa cuenta primero.',
                ]);
            }

            if (! $user) {
                if (! filled($password)) {
                    throw ValidationException::withMessages([
                        'password' => 'La contrasena inicial es obligatoria para un usuario nuevo.',
                    ]);
                }

                $usernameInUse = User::withTrashed()
                    ->whereRaw('LOWER(username) = ?', [$username])
                    ->exists();
                if ($usernameInUse) {
                    throw ValidationException::withMessages([
                        'username' => 'El nombre de usuario ya esta registrado.',
                    ]);
                }

                $user = new User([
                    'name' => trim($adminName),
                    'email' => $email,
                    'username' => $username,
                    'password' => $password,
                    'estado' => 'activo',
                ]);
                $user->email_verified_at = now();
                $user->save();
            }

            $fundo = Fundo::firstOrCreate(
                ['nombre' => $fundoName],
                ['activo' => true]
            );

            $adminRole = Role::query()
                ->whereNull('fundo_id')
                ->where('nombre', 'Administrador General')
                ->first();
            if (! $adminRole) {
                throw new RuntimeException('Falta el rol Administrador General. Ejecute php artisan db:seed.');
            }

            $user->roles()->syncWithoutDetaching([$adminRole->getKey()]);
            $fundo->usuarios()->syncWithoutDetaching([
                $user->getKey() => ['es_administrador' => true],
            ]);

            foreach (['moneda' => 'PEN', 'alerta_dias' => '7'] as $key => $value) {
                ConfiguracionSistema::withoutGlobalScopes()->updateOrCreate(
                    ['fundo_id' => $fundo->getKey(), 'clave' => $key],
                    ['valor' => $value]
                );
            }

            return ['fundo' => $fundo, 'user' => $user];
        });
    }
}
