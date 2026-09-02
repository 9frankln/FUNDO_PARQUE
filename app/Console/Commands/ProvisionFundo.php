<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FundoProvisioner;
use Database\Seeders\CoreDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Throwable;

class ProvisionFundo extends Command
{
    protected $signature = 'fundo:provision
        {--fundo= : Nombre del fundo}
        {--name=Administrador : Nombre del administrador}
        {--email= : Correo del administrador}
        {--username=admin : Nombre de usuario del administrador}';

    protected $description = 'Provision an initial fundo and its administrator without fixed credentials';

    public function handle(FundoProvisioner $provisioner): int
    {
        $fundoName = $this->optionValue('fundo', 'Nombre del fundo');
        $adminName = $this->optionValue('name', 'Nombre del administrador');
        $email = strtolower($this->optionValue('email', 'Correo del administrador'));
        $username = strtolower($this->optionValue('username', 'Nombre de usuario'));
        $existingUser = $email !== ''
            ? User::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->first()
            : null;
        $password = null;

        if (! $existingUser) {
            $password = trim((string) env('INITIAL_ADMIN_PASSWORD'));
            if ($password === '' && $this->input->isInteractive()) {
                $password = (string) $this->secret('Contrasena inicial');
                $confirmation = (string) $this->secret('Confirme la contrasena');
                if (! hash_equals($password, $confirmation)) {
                    $this->error('Las contrasenas no coinciden.');

                    return self::FAILURE;
                }
            }
        }

        $usernameRules = ['required', 'string', 'max:100', 'alpha_dash'];
        if (! $existingUser) {
            $usernameRules[] = Rule::unique('users', 'username');
        }
        $validator = Validator::make([
            'fundo' => $fundoName,
            'name' => $adminName,
            'email' => $email,
            'username' => $username,
            'password' => $password,
        ], [
            'fundo' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'username' => $usernameRules,
            'password' => [$existingUser ? 'nullable' : 'required', 'string', Password::min(8)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        try {
            $this->callSilent('db:seed', [
                '--class' => CoreDataSeeder::class,
                '--force' => true,
            ]);
            $result = $provisioner->provision(
                $fundoName,
                $adminName,
                $email,
                $username,
                $password,
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Fundo {$result['fundo']->nombre} provisionado para {$result['user']->email}.");

        return self::SUCCESS;
    }

    private function optionValue(string $option, string $question): string
    {
        $value = trim((string) $this->option($option));

        if ($value === '' && $this->input->isInteractive()) {
            $value = trim((string) $this->ask($question));
        }

        return $value;
    }
}
