<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $loginInput = Str::lower(trim($this->email));
        $password = trim($this->password);

        $this->ensureIsNotRateLimited();

        $user = User::where(function ($query) use ($loginInput) {
            $query->where('email', $loginInput)
                ->orWhere('username', $loginInput);
        })->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey());
            app(AuditLogger::class)->record(
                'sesion.inicio_rechazado',
                'seguridad',
                'Identificador no encontrado.',
                metadata: ['identificador' => $loginInput],
                result: 'rechazado',
            );

            throw ValidationException::withMessages([
                'form.email' => 'El usuario o correo electrónico ingresado no existe.',
            ]);
        }

        if ($user->estado !== 'activo') {
            RateLimiter::hit($this->throttleKey());
            app(AuditLogger::class)->record(
                'sesion.inicio_rechazado',
                'seguridad',
                'Cuenta no activa.',
                $user,
                ['estado' => $user->estado],
                result: 'rechazado',
            );

            throw ValidationException::withMessages([
                'form.email' => 'Esta cuenta se encuentra inactiva. Contacte al administrador.',
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());
            app(AuditLogger::class)->record(
                'sesion.inicio_rechazado',
                'seguridad',
                'Contraseña incorrecta.',
                $user,
                result: 'rechazado',
            );

            throw ValidationException::withMessages([
                'form.password' => 'La contraseña ingresada es incorrecta.',
            ]);
        }

        // Destroy anonymous session before attaching user, so it cannot count as a second device.
        Session::regenerate(true);
        Auth::login($user, $this->remember);

        RateLimiter::clear($this->throttleKey());
        $user->forceFill(['ultimo_acceso' => now()])->saveQuietly();
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
