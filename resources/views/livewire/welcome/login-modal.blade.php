<?php

use App\Livewire\Forms\LoginForm;
use App\Services\AuditLogger;
use App\Services\Security\UserSessionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public LoginForm $form;

    public bool $sessionLimitReached = false;

    public function login(): void
    {
        $this->form->email = strtolower(trim($this->form->email));
        $this->form->password = trim($this->form->password);

        $this->validate();
        $this->form->authenticate();

        $user = Auth::user();
        if (! app(UserSessionService::class)->claim($user, session()->getId(), request())) {
            app(AuditLogger::class)->record(
                'sesion.limite_alcanzado',
                'seguridad',
                'Inicio rechazado por límite de sesiones activas.',
                $user,
                ['limite' => $user->max_active_sessions],
                result: 'rechazado',
                actor: $user,
            );
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->sessionLimitReached = true;
            $this->form->password = '';
            $this->addError('form.email', "Esta cuenta ya alcanzó su límite de {$user->max_active_sessions} sesiones activas.");

            return;
        }

        app(AuditLogger::class)->record(
            'sesion.iniciada',
            'seguridad',
            'Inicio de sesión aceptado.',
            $user,
            ['limite' => $user->max_active_sessions],
            actor: $user,
        );

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }

    public function unlockSessions(): void
    {
        $this->form->email = strtolower(trim($this->form->email));
        $this->form->password = trim($this->form->password);
        $this->validate();
        $this->form->authenticate();

        $user = Auth::user();
        $closedSessions = app(UserSessionService::class)->revokeAll($user, $user, 'self_unblock');
        if (! app(UserSessionService::class)->claim($user, session()->getId(), request())) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->addError('form.email', 'No se pudo desbloquear la sesión. Intenta nuevamente.');

            return;
        }

        app(AuditLogger::class)->record(
            'sesion.autodesbloqueada',
            'seguridad',
            'Cerró sus sesiones activas para recuperar acceso.',
            $user,
            ['sesiones_revocadas' => $closedSessions],
            result: 'exitoso',
            actor: $user,
        );

        $this->sessionLimitReached = false;
        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5" x-data="{ showPassword: false }">
        <div>
            <label for="modal-email" class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-emerald-800 dark:text-emerald-200">
                Usuario o Correo electrónico
            </label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-emerald-600/60 dark:text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <input wire:model="form.email" id="modal-email" type="text" required autocomplete="username"
                       class="w-full rounded-2xl border @error('form.email') border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-500/20 @else border-emerald-300 bg-white focus:border-emerald-500 focus:ring-emerald-500/15 dark:border-emerald-200/10 dark:bg-emerald-950/50 @enderror py-3.5 pl-12 pr-4 text-sm text-emerald-950 outline-none transition placeholder:text-emerald-600/50 dark:placeholder:text-emerald-300/70 focus:ring-1 dark:text-emerald-50 shadow-sm"
                       placeholder="admin o nombre@correo.com">
            </div>
            @error('form.email')
                <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
            @if($sessionLimitReached)
                <div class="mt-3 rounded-xl border border-amber-300 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-100">
                    <p>Vuelve a escribir contraseña y cierra sesiones activas para entrar desde este equipo.</p>
                    <button type="button" wire:click="unlockSessions" wire:loading.attr="disabled" wire:target="unlockSessions" class="mt-2 text-xs font-extrabold underline underline-offset-2">Cerrar sesiones activas y entrar</button>
                </div>
            @endif
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-3">
                <label for="modal-password" class="block text-xs font-bold uppercase tracking-[0.14em] text-emerald-800 dark:text-emerald-200">
                    Contraseña
                </label>
            </div>
            <div class="relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-emerald-600/60 dark:text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <input wire:model="form.password" id="modal-password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password"
                       class="w-full rounded-2xl border @error('form.password') border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-500/20 @else border-emerald-300 bg-white focus:border-emerald-500 focus:ring-emerald-500/15 dark:border-emerald-200/10 dark:bg-emerald-950/50 @enderror py-3.5 pl-12 pr-11 text-sm text-emerald-950 outline-none transition placeholder:text-emerald-600/50 dark:placeholder:text-emerald-300/70 focus:ring-1 dark:text-emerald-50 shadow-sm"
                       placeholder="Tu contraseña">
                <button type="button" @click="showPassword = !showPassword" aria-label="Mostrar u ocultar contraseña"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-emerald-700/60 hover:bg-emerald-100/50 hover:text-emerald-900 dark:text-emerald-300/60 dark:hover:bg-emerald-900/50 dark:hover:text-emerald-100 transition">
                    <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.326 16.17 7.26 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.738 0 8.674 3.327 10.064 7.5-.41 1.233-1.077 2.348-1.937 3.284M3 3l18 18" />
                    </svg>
                </button>
            </div>
            @error('form.password')
                <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="login"
                class="agro-button w-full !min-h-12 !py-3.5 text-sm font-extrabold shadow-xl disabled:cursor-wait">
            <span>Entrar a {{ $branding->name() }}</span>
            <svg wire:loading.remove wire:target="login" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
            </svg>
            <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
            </svg>
        </button>
    </form>
</div>
