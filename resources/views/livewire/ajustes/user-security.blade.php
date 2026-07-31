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
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div><h4 class="text-sm font-extrabold text-sky-950 dark:text-sky-100">Política de sesión</h4><p class="mt-1 text-xs leading-5 text-sky-800 dark:text-sky-200">Cada navegador cuenta como una sesión. Cierre automático protege equipos sin uso.@if($securityUserCanUseUnlimitedSessions) Usa 0 para sesiones sin límite.@endif</p></div>
                    <div class="grid w-full grid-cols-2 gap-2 sm:w-auto"><label class="block"><span class="mb-1 block text-[10px] font-bold uppercase tracking-wider">Sesiones</span><input type="number" min="{{ $securityUserCanUseUnlimitedSessions ? 0 : 1 }}" max="10" wire:model="securitySessionLimit" class="w-full rounded-xl border border-sky-300 bg-white px-3 py-2 text-sm font-bold dark:border-sky-400/30 dark:bg-zinc-950"></label><label class="block"><span class="mb-1 block text-[10px] font-bold uppercase tracking-wider">Inactividad</span><div class="relative"><input type="number" min="5" max="{{ config('session.lifetime', 30) }}" wire:model="securityIdleTimeoutMinutes" class="w-full rounded-xl border border-sky-300 bg-white px-3 py-2 pr-10 text-sm font-bold dark:border-sky-400/30 dark:bg-zinc-950"><span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[10px] font-bold text-zinc-500">min</span></div></label><button type="button" wire:click="saveUserSessionLimit" wire:loading.attr="disabled" wire:target="saveUserSessionLimit" class="agro-button col-span-2 !min-h-10 !px-4 text-xs">Guardar política</button></div>
                </div>
                @error('securitySessionLimit')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                @error('securityIdleTimeoutMinutes')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
            </section>

            <section>
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div><h4 class="text-sm font-extrabold">Sesiones por equipo</h4><p class="mt-1 text-xs text-zinc-500">Restablecer cierra todos los dispositivos y deja sesiones activas en cero.</p></div><button type="button" x-on:click="confirmDelete('¿Restablecer sesiones?', 'Se cerrarán todos los equipos de {{ addslashes($securityUserName) }}. La cuenta deberá iniciar sesión nuevamente.').then((res) => { if (res.isConfirmed) $wire.revokeAllUserSessions() })" class="agro-button-secondary !border-rose-200 !text-rose-700 dark:!border-rose-400/30 dark:!text-rose-300">Restablecer sesiones</button></div>
                <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    <div class="hidden overflow-x-auto sm:block"><table class="w-full min-w-[700px] text-left"><thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:bg-zinc-900"><tr><th class="p-3">Equipo</th><th class="p-3">IP</th><th class="p-3">Última actividad</th><th class="p-3">Estado</th><th class="p-3 text-right">Acción</th></tr></thead><tbody class="divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
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
            </section>
        </div>

        <footer class="flex justify-end border-t border-zinc-200 p-4 dark:border-zinc-800 sm:px-6"><button type="button" wire:click="closeUserSecurityModal" class="agro-button-secondary">Cerrar</button></footer>
    </section>
</div>
