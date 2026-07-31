<div x-data="{ selected: $wire.entangle('userRoleIds'), all: @js($rolesDisponibles->pluck('id')->map(fn ($id) => (string) $id)->values()->all()) }"
     x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
     class="agro-dialog-overlay">
    <section role="dialog" aria-modal="true" aria-label="Gestionar accesos" class="agro-dialog agro-dialog--lg">
        <header class="flex items-start justify-between border-b border-zinc-200 p-4 dark:border-zinc-800 sm:px-6">
            <div><p class="agro-kicker">Acceso al fundo</p><h3 class="mt-1 text-xl font-extrabold">{{ $selectedUserName }}</h3><p class="mt-1 text-xs text-zinc-500">{{ $selectedUserEmail }}</p></div>
            <button wire:click="closeUserAccessModal" class="agro-icon-button !h-9 !w-9" aria-label="Cerrar">&times;</button>
        </header>
        <div class="agro-dialog__scroll space-y-5 p-4 sm:p-6">
            @if($canManageFundoAdmins)
                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-400/20 dark:bg-amber-400/10"><span><strong class="block text-sm text-amber-900 dark:text-amber-100">Administrador del fundo</strong><small class="mt-1 block text-amber-700 dark:text-amber-200">Acceso total. Solo otro administrador puede concederlo o retirarlo.</small></span><input type="checkbox" wire:model="userEsAdmin" class="agro-checkbox h-5 w-5 rounded"></label>
            @else
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-xs text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">Puedes asignar roles permitidos. Solo administrador actual puede conceder acceso total.</div>
            @endif

            <div>
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"><div><h4 class="text-sm font-extrabold">Roles disponibles</h4><p class="mt-1 text-xs text-zinc-500">Permisos se acumulan. Plantillas globales requieren administrador del fundo.</p></div><div class="flex gap-2"><button type="button" x-on:click="selected = [...all]" class="rounded-lg bg-emerald-50 px-3 py-2 text-[10px] font-bold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">Seleccionar todos</button><button type="button" x-on:click="selected = []" class="rounded-lg bg-zinc-100 px-3 py-2 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">Limpiar</button></div></div>
                <div class="grid max-h-[50vh] gap-3 overflow-y-auto sm:grid-cols-2">
                    @forelse($rolesDisponibles as $role)
                        @php
                            $hasSensitiveAccess = $role->permisos->contains(fn ($permission) => $permission->modulo === 'ajustes' && in_array($permission->accion, ['eliminar', 'exportar', 'restaurar'], true));
                        @endphp
                        <label class="cursor-pointer rounded-2xl border border-zinc-200 bg-white p-4 transition hover:border-emerald-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-emerald-400/40">
                            <span class="flex items-start gap-3"><input type="checkbox" x-model="selected" value="{{ (string) $role->id }}" class="agro-checkbox mt-0.5 h-4 w-4 rounded"><span class="min-w-0 flex-1"><strong class="block text-sm">{{ $role->nombre }}</strong><small class="mt-1 block text-zinc-500">{{ $role->descripcion ?: 'Sin descripción' }}</small><span class="mt-2 flex flex-wrap gap-1"><span class="rounded-full bg-zinc-100 px-2 py-1 text-[9px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $role->permisos->count() }} permisos</span><span class="rounded-full bg-sky-100 px-2 py-1 text-[9px] font-bold text-sky-800 dark:bg-sky-400/10 dark:text-sky-200">{{ $role->fundo_id ? 'Fundo' : 'Global' }}</span>@if($hasSensitiveAccess)<span class="rounded-full bg-amber-100 px-2 py-1 text-[9px] font-bold text-amber-800 dark:bg-amber-400/10 dark:text-amber-200">Acceso sensible</span>@endif</span></span></span>
                        </label>
                    @empty
                        <p class="rounded-2xl bg-zinc-50 p-5 text-xs text-zinc-500 dark:bg-zinc-900 sm:col-span-2">Crea primero un rol personalizado. Roles globales permanecen protegidos.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <footer class="flex flex-col-reverse gap-2 border-t border-zinc-200 p-4 dark:border-zinc-800 sm:flex-row sm:justify-end sm:px-6"><button wire:click="closeUserAccessModal" class="agro-button-secondary">Cancelar</button><button wire:click="saveUserAccess" wire:loading.attr="disabled" wire:target="saveUserAccess" class="agro-button">Guardar accesos</button></footer>
    </section>
</div>
