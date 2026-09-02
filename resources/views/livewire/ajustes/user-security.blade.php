<div x-data
     x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
     class="agro-dialog-overlay">
    <section role="dialog" aria-modal="true" aria-label="Seguridad de cuenta" class="agro-dialog agro-dialog--xl w-full max-w-5xl">
        <header class="flex items-start justify-between border-b border-zinc-200 p-4 dark:border-zinc-800 sm:px-6">
            <div><p class="agro-kicker">Seguridad de cuenta</p><h3 class="mt-1 text-xl font-extrabold">{{ $securityUserName }}</h3><p class="mt-1 text-xs text-zinc-500">{{ $securityUserEmail }}</p></div>
            <button wire:click="closeUserSecurityModal" class="agro-icon-button !h-9 !w-9" aria-label="Cerrar">&times;</button>
        </header>

        <div class="agro-dialog__scroll space-y-5 p-4 sm:p-6">
            <section class="rounded-2xl border border-sky-200 bg-sky-50/70 p-4 dark:border-sky-400/20 dark:bg-sky-400/10">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-md"><h4 class="text-sm font-extrabold text-sky-950 dark:text-sky-100">Política de sesión</h4><p class="mt-1 text-xs leading-5 text-sky-800 dark:text-sky-200">Cada navegador cuenta como una sesión. Cierre automático protege equipos sin uso.@if($securityUserCanUseUnlimitedSessions) Marca «Sin límite de sesión» para que nunca cierre solo, o programa los minutos de inactividad. Usa 0 en Sesiones para dispositivos sin límite.@endif</p></div>
                    <div x-data="{ unlimited: @js($securitySessionUnlimited), async savePolicy() { await $wire.set('securitySessionUnlimited', this.unlimited); $wire.saveUserSessionLimit(); } }" class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:w-auto xl:grid-cols-4">
                        <label class="block">
                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider">Sesiones</span>
                            <div class="relative">
                                <input type="number" min="{{ $securityUserCanUseUnlimitedSessions ? 0 : 1 }}" max="10" wire:model="securitySessionLimit" class="w-full rounded-xl border border-sky-300 bg-white px-3 py-2 pr-10 text-sm font-bold dark:border-sky-400/30 dark:bg-zinc-950">
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[10px] font-bold text-zinc-500">disp.</span>
                            </div>
                        </label>
                        @if($securityUserCanUseUnlimitedSessions)
                        <label class="block">
                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider">Sin límite de sesión:</span>
                            <button type="button" @click="unlimited = !unlimited" role="switch" :aria-checked="unlimited ? 'true' : 'false'" class="inline-flex h-9 w-full items-center gap-2 rounded-xl border border-sky-300 bg-white px-3 transition dark:border-sky-400/30 dark:bg-zinc-950">
                                <span class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition" :class="unlimited ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-600'">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition" :class="unlimited ? 'translate-x-4' : 'translate-x-0.5'"></span>
                                </span>
                                <span class="text-xs font-extrabold" :class="unlimited ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-500'"><span x-text="unlimited ? 'Activo' : 'Inactivo'"></span></span>
                            </button>
                        </label>
                        @endif
                        <label class="block" x-show="!unlimited" x-cloak>
                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider">Inactividad</span>
                            <div class="relative">
                                <input type="number" min="5" max="{{ $securityUserCanUseUnlimitedSessions ? 525600 : config('session.lifetime', 30) }}" wire:model="securityIdleTimeoutMinutes" :disabled="unlimited" class="w-full rounded-xl border border-sky-300 bg-white px-3 py-2 pr-10 text-sm font-bold dark:border-sky-400/30 dark:bg-zinc-950">
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[10px] font-bold text-zinc-500">min</span>
                            </div>
                        </label>
                        <button type="button" @click="savePolicy()" wire:loading.attr="disabled" wire:target="saveUserSessionLimit" class="agro-button self-end !min-h-9 !px-4 text-xs">Guardar política</button>
                    </div>
                </div>
                @error('securitySessionLimit')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                @error('securityIdleTimeoutMinutes')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
            </section>

            <section>
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div><h4 class="text-sm font-extrabold">Sesiones por equipo</h4><p class="mt-1 text-xs text-zinc-500">Restablecer cierra todos los dispositivos al instante; programar lo ejecuta solo en el momento elegido.</p></div><div class="flex flex-wrap items-center gap-2"><button type="button" wire:click="openScheduledTaskModal" class="agro-button-secondary !border-sky-200 !text-sky-700 dark:!border-sky-400/30 dark:!text-sky-300"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>Programar</button><button type="button" x-on:click="confirmDelete('¿Restablecer sesiones?', 'Se cerrarán todos los equipos de {{ addslashes($securityUserName) }}. La cuenta deberá iniciar sesión nuevamente.').then((res) => { if (res.isConfirmed) $wire.revokeAllUserSessions() })" class="agro-button-secondary !border-rose-200 !text-rose-700 dark:!border-rose-400/30 dark:!text-rose-300">Restablecer sesiones</button><button type="button" wire:click="openUserSecurityDeleteModal" title="Eliminar historial de sesiones" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-300 bg-white text-zinc-600 shadow-xs transition hover:border-rose-400 hover:bg-rose-50 hover:text-rose-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button></div></div>
                <div class="agro-record-surface overflow-hidden rounded-2xl border">
                    <div class="hidden overflow-x-auto sm:block"><table class="w-full min-w-[700px] text-left"><thead class="agro-record-header border-b text-[10px] font-bold uppercase tracking-wider"><tr><th class="p-3">Equipo</th><th class="p-3">IP</th><th class="p-3">Última actividad</th><th class="p-3">Estado</th><th class="p-3 text-right">Acción</th></tr></thead><tbody class="agro-record-list text-sm">
                        @forelse($securitySessions as $session)
                            @php
                                $active = ! $session->revoked_at && $session->last_activity_at?->gte(now()->subMinutes((int) config('session.lifetime', 30)));
                            @endphp
                            <tr wire:key="security-session-{{ $session->id }}"><td class="p-3"><strong class="block">{{ $session->device_label ?: 'Equipo sin identificar' }}</strong><span class="block max-w-64 truncate text-[10px] text-zinc-500" title="{{ $session->user_agent }}">{{ $session->user_agent ?: 'Sin agente registrado' }}</span></td><td class="p-3 font-mono text-xs">{{ $session->ip_address ?: 'Sin IP' }}</td><td class="p-3"><strong class="block text-xs">{{ $session->last_activity_at?->format('d/m/Y') ?: 'Sin fecha' }}</strong><span class="text-[10px] text-zinc-500">{{ $session->last_activity_at?->format('H:i:s') }}</span></td><td class="p-3"><span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ $active ? ($session->session_hash === $currentSessionHash ? 'Actual' : 'Activa') : ($session->revocation_reason === 'expired' ? 'Expirada' : 'Revocada') }}</span></td><td class="p-3 text-right">@if($active)<button type="button" wire:click="revokeUserSession({{ $session->id }})" class="rounded-lg border border-rose-200 px-3 py-2 text-[10px] font-bold text-rose-700 dark:border-rose-400/30 dark:text-rose-300">Revocar</button>@else<span class="text-[10px] text-zinc-400">{{ $session->revoked_at?->format('d/m H:i') }}</span>@endif</td></tr>
                        @empty<tr><td colspan="5" class="p-8 text-center text-sm text-zinc-500">Sin sesiones registradas.</td></tr>@endforelse
                    </tbody></table></div>
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800 sm:hidden">
                        @forelse($securitySessions as $session)
                            @php
                                $active = ! $session->revoked_at && $session->last_activity_at?->gte(now()->subMinutes((int) config('session.lifetime', 30)));
                            @endphp
                            <article wire:key="security-session-mobile-{{ $session->id }}" class="space-y-2 p-3"><div class="flex items-start justify-between gap-3"><div><strong class="block text-sm">{{ $session->device_label ?: 'Equipo sin identificar' }}</strong><span class="text-[10px] text-zinc-500">{{ $session->ip_address ?: 'Sin IP' }} · {{ $session->last_activity_at?->format('d/m H:i') }}</span></div><span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ $active ? 'Activa' : 'Revocada' }}</span></div><p class="truncate text-[10px] text-zinc-500">{{ $session->user_agent }}</p>@if($active)<button type="button" wire:click="revokeUserSession({{ $session->id }})" class="text-xs font-bold text-rose-700 dark:text-rose-300">Revocar sesión</button>@endif</article>
                        @empty
                            <div class="p-6 text-center text-sm text-zinc-500">Sin sesiones registradas.</div>
                        @endforelse
                    </div>
                </div>
                @if($securitySessions->total() > 0)
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 dark:border-zinc-700/80 dark:bg-zinc-800/40">
                        <div class="flex items-center gap-2 text-xs font-bold text-zinc-600 dark:text-zinc-300"><span>Mostrar</span><x-filter-select model="securitySessionsPerPage" :options="$perPageOptions" tone="emerald" live compact /><span class="whitespace-nowrap">{{ $securitySessions->firstItem() ?? 0 }} - {{ $securitySessions->lastItem() ?? 0 }} de {{ $securitySessions->total() }}</span></div>
                        <div class="min-w-0">{{ $securitySessions->links('components.pagination') }}</div>
                    </div>
                @endif
            </section>
        </div>

        <footer class="flex justify-end border-t border-zinc-200 p-4 dark:border-zinc-800 sm:px-6"><button type="button" wire:click="closeUserSecurityModal" class="agro-button-secondary">Cerrar</button></footer>
    </section>

    @if($showUserSecurityDeleteModal)
    <div x-data="{ mode: 'revoked' }" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-md dark:bg-black/70">
        <div class="relative w-full max-w-xl overflow-hidden rounded-3xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <header class="flex items-start justify-between border-b border-zinc-100 pb-5 dark:border-zinc-800">
                <div class="flex items-center gap-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></span>
                    <div><p class="agro-kicker">Historial de sesiones</p><h3 class="text-lg font-extrabold">Eliminar historial de sesiones</h3><p class="mt-1 text-xs text-zinc-500">Selecciona el alcance de sesiones que deseas purgar de la base de datos.</p></div>
                </div>
                <button wire:click="closeUserSecurityDeleteModal" class="agro-icon-button" aria-label="Cerrar">&times;</button>
            </header>

            <div class="space-y-4 pt-5">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-zinc-500">Selecciona el alcance del borrado</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button type="button" @click="mode = 'revoked'" :class="mode === 'revoked' ? 'border-indigo-500 bg-indigo-50/90 ring-2 ring-indigo-500/20 dark:border-indigo-400 dark:bg-indigo-950/60 dark:ring-indigo-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800'" class="flex min-h-14 w-full items-center justify-between rounded-2xl border px-4 py-3 text-left transition-all duration-150 shadow-xs">
                        <div class="flex items-center gap-3">
                            <span :class="mode === 'revoked' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></span>
                            <span class="text-xs font-extrabold">Solo revocadas</span>
                        </div>
                        <template x-if="mode === 'revoked'"><svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg></template>
                    </button>
                    <button type="button" @click="mode = 'all'" :class="mode === 'all' ? 'border-rose-500 bg-rose-50/90 ring-2 ring-rose-500/20 dark:border-rose-400 dark:bg-rose-950/60 dark:ring-rose-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-rose-300 hover:bg-rose-50/40 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-rose-800 dark:hover:bg-rose-950/20'" class="flex min-h-14 w-full items-center justify-between rounded-2xl border px-4 py-3 text-left transition-all duration-150 shadow-xs">
                        <div class="flex items-center gap-3">
                            <span :class="mode === 'all' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' : 'bg-rose-50 text-rose-500 dark:bg-rose-950/50 dark:text-rose-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                            <span class="text-xs font-extrabold">Todas (activas y revocadas)</span>
                        </div>
                        <template x-if="mode === 'all'"><svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg></template>
                    </button>
                </div>

                <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-xs leading-relaxed text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                    <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div><strong class="block font-bold text-amber-950 dark:text-amber-200">Acción permanente:</strong><span>Las sesiones que coincidan con el alcance seleccionado se eliminarán definitivamente. {{ $securityUserName }} deberá iniciar sesión nuevamente en esos equipos.</span></div>
                </div>
            </div>

            <footer class="mt-7 flex flex-col-reverse gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" @click="$wire.closeUserSecurityDeleteModal()" class="agro-button-secondary">Cancelar</button>
                <button type="button" x-on:click.prevent="confirmDelete('¿Eliminar sesiones del historial?', '¡Esta acción no se podrá revertir! Las sesiones del alcance seleccionado se eliminarán definitivamente.').then((res) => { if (res.isConfirmed) $wire.deleteUserSessions(mode) })" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-rose-500"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>Eliminar Sesiones</button>
            </footer>
        </div>
    </div>
    @endif

    @if($showScheduledTaskModal)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-md dark:bg-black/70">
        <div class="relative w-full max-w-xl overflow-hidden rounded-3xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <header class="flex items-start justify-between border-b border-zinc-100 pb-5 dark:border-zinc-800">
                <div class="flex items-center gap-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></span>
                    <div><p class="agro-kicker">Sesiones programadas</p><h3 class="text-lg font-extrabold">Programar tarea de sesiones</h3><p class="mt-1 text-xs text-zinc-500">Se ejecutará sola en el momento elegido, incluso sin que estés conectado.</p></div>
                </div>
                <button wire:click="closeScheduledTaskModal" class="agro-icon-button" aria-label="Cerrar">&times;</button>
            </header>

            <div class="space-y-4 pt-5">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-zinc-500">Tipo de tarea</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button type="button" wire:click="$set('scheduledTaskType', 'reset')" class="flex min-h-16 w-full items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-left transition-all duration-150 shadow-xs {{ $scheduledTaskType === 'reset' ? 'border-sky-500 bg-sky-50/90 ring-2 ring-sky-500/20 dark:border-sky-400 dark:bg-sky-950/60 dark:ring-sky-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800' }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $scheduledTaskType === 'reset' ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-400' }}"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg></span>
                            <span class="text-xs font-extrabold">Restablecer sesiones<span class="mt-0.5 block font-normal text-[10px] text-zinc-500">Cierra todas las sesiones activas del usuario.</span></span>
                        </div>
                        @if($scheduledTaskType === 'reset')<svg class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>@endif
                    </button>
                    <button type="button" wire:click="$set('scheduledTaskType', 'purge')" class="flex min-h-16 w-full items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-left transition-all duration-150 shadow-xs {{ $scheduledTaskType === 'purge' ? 'border-rose-500 bg-rose-50/90 ring-2 ring-rose-500/20 dark:border-rose-400 dark:bg-rose-950/60 dark:ring-rose-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-rose-300 hover:bg-rose-50/40 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-rose-800 dark:hover:bg-rose-950/20' }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $scheduledTaskType === 'purge' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' : 'bg-rose-50 text-rose-500 dark:bg-rose-950/50 dark:text-rose-400' }}"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></span>
                            <span class="text-xs font-extrabold">Limpiar historial<span class="mt-0.5 block font-normal text-[10px] text-zinc-500">Borra sesiones revocadas/expiradas automáticamente.</span></span>
                        </div>
                        @if($scheduledTaskType === 'purge')<svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>@endif
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_10rem]">
                    <label class="block"><span class="mb-1 block text-[10px] font-bold uppercase tracking-wider">Ejecutar dentro de</span><input type="number" min="1" max="525600" wire:model="scheduledTaskValue" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm font-bold dark:border-zinc-700 dark:bg-zinc-950"></label>
                    <div class="block"><span class="mb-1 block text-[10px] font-bold uppercase tracking-wider">Unidad</span><x-filter-select model="scheduledTaskUnit" :options="['minutos' => 'Minutos', 'horas' => 'Horas', 'dias' => 'Días']" tone="sky" compact /></div>
                </div>

                <div class="flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50/70 p-4 text-xs leading-relaxed text-sky-900 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200">
                    <svg class="h-5 w-5 shrink-0 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    <div><strong class="block font-bold text-sky-950 dark:text-sky-100">Se ejecutará sola:</strong><span>La tarea se procesa por cron y, como respaldo, en cada visita al sistema. Puedes cancelarla antes de que se ejecute.</span></div>
                </div>
            </div>

            <footer class="mt-7 flex flex-col-reverse gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" wire:click="closeScheduledTaskModal" class="agro-button-secondary">Cancelar</button>
                <button type="button" wire:click="scheduleSessionTask" wire:loading.attr="disabled" wire:target="scheduleSessionTask" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-sky-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-sky-500"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>Programar tarea</button>
            </footer>

            @if($scheduledTasks->isNotEmpty())
            <div class="mt-6 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/40">
                <p class="mb-2 text-[10px] font-extrabold uppercase tracking-[0.18em] text-zinc-500">Tareas pendientes</p>
                <div class="space-y-2">
                    @foreach($scheduledTasks as $task)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs dark:border-zinc-700 dark:bg-zinc-900">
                        <div><strong class="block text-zinc-800 dark:text-zinc-100">{{ $task->tipo === 'reset' ? 'Restablecer sesiones' : 'Limpiar historial' }}</strong><span class="text-[10px] text-zinc-500">{{ $task->execute_at->format('d/m/Y H:i') }} · {{ $task->execute_at->diffForHumans() }}</span></div>
                        <button type="button" wire:click="cancelScheduledSessionTask({{ $task->id }})" class="rounded-lg border border-zinc-300 px-2.5 py-1.5 text-[10px] font-bold text-zinc-600 transition hover:border-rose-400 hover:bg-rose-50 hover:text-rose-600 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-rose-950/40">Cancelar</button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
