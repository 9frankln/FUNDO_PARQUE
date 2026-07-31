<div class="mx-auto max-w-7xl space-y-5">
    <header class="agro-card overflow-hidden p-4 sm:p-6">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.5 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.5a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15.5 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.1.38.32.73.6 1 .3.27.68.4 1.1.4H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15Z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="agro-kicker">Administración</p>
                <h1 class="agro-title mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">Ajustes del sistema</h1>
                <p class="mt-1 text-sm text-zinc-500">Equipo, permisos, identidad visual y continuidad de datos.</p>
            </div>
        </div>
    </header>

    @php
        $settingsTabs = array_filter([
            'colaboradores' => ['Equipo', 'Usuarios y accesos'],
            'roles' => ['Roles y permisos', 'Matriz de seguridad'],
            'general' => ['Preferencias', 'Marca y fundo'],
            'backup' => ['Backups', 'Protección e historial'],
        ], fn ($tab) => $settingsTabAccess[$tab] ?? false, ARRAY_FILTER_USE_KEY);
    @endphp

    <nav aria-label="Secciones de ajustes">
        <div x-data="{ open: false }" class="relative sm:hidden">
            <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open" class="flex w-full items-center gap-3 rounded-2xl border border-emerald-950/10 bg-white p-3 text-left shadow-sm dark:border-emerald-200/10 dark:bg-emerald-950/30">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-700 text-white dark:bg-emerald-400 dark:text-emerald-950">
                    @switch($activeTab)
                        @case('colaboradores')<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m19 0v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7.87a4 4 0 0 1 0 7.75"/></svg>@break
                        @case('roles')<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 4.5 6v5.5c0 4.6 3.2 7.9 7.5 9.5 4.3-1.6 7.5-4.9 7.5-9.5V6L12 3Z"/><path stroke-linecap="round" stroke-width="1.8" d="m9 12 2 2 4-4"/></svg>@break
                        @case('general')<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z"/><path stroke-linecap="round" stroke-width="1.8" d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4L14.4 21h-4l-.6-1.6a1.7 1.7 0 0 0-1.91.31l-.06.06L5 16.94l.06-.06A1.7 1.7 0 0 0 4.6 15L3 14.4v-4L4.6 9.8a1.7 1.7 0 0 0-.31-1.91l-.06-.06L7.06 5l.06.06A1.7 1.7 0 0 0 9 4.6L9.6 3h4l.6 1.6a1.7 1.7 0 0 0 1.91-.31l.06-.06L19 7.06l-.06.06A1.7 1.7 0 0 0 19.4 9l1.6.6v4l-1.6.6Z"/></svg>@break
                        @default<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2ZM7 5v5h10V5M8 15h8"/></svg>
                    @endswitch
                </span>
                <span class="min-w-0 flex-1"><strong class="block truncate text-sm text-zinc-900 dark:text-zinc-100">{{ $settingsTabs[$activeTab][0] }}</strong><small class="mt-0.5 block truncate text-xs text-zinc-500">{{ $settingsTabs[$activeTab][1] }}</small></span>
                <svg class="h-5 w-5 shrink-0 text-zinc-400 transition" x-bind:class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
            </button>

            <div x-cloak x-show="open" x-transition.origin.top x-on:click.outside="open = false" class="absolute inset-x-0 z-40 mt-2 overflow-hidden rounded-2xl border border-emerald-950/10 bg-white p-1.5 shadow-xl shadow-emerald-950/10 dark:border-emerald-200/10 dark:bg-emerald-950">
                @foreach($settingsTabs as $tab => [$label, $description])
                    <button type="button" wire:click="$set('activeTab', '{{ $tab }}')" x-on:click="open = false" class="flex w-full items-center justify-between rounded-xl px-3 py-3 text-left transition {{ $activeTab === $tab ? 'bg-emerald-700 text-white dark:bg-emerald-400 dark:text-emerald-950' : 'text-zinc-700 hover:bg-emerald-50 dark:text-zinc-200 dark:hover:bg-emerald-400/10' }}">
                        <span><strong class="block text-sm">{{ $label }}</strong><small class="mt-0.5 block text-[10px] opacity-70">{{ $description }}</small></span>
                        @if($activeTab === $tab)<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 12 4 4L19 6"/></svg>@endif
                    </button>
                @endforeach
            </div>
        </div>

        <div class="hidden grid-cols-2 gap-1.5 rounded-2xl border border-emerald-950/10 bg-white p-1.5 dark:border-emerald-200/10 dark:bg-emerald-950/25 sm:grid lg:grid-cols-4">
            @foreach($settingsTabs as $tab => [$label, $description])
                <button type="button" wire:click="$set('activeTab', '{{ $tab }}')" class="min-w-0 rounded-xl px-3 py-3 text-left transition {{ $activeTab === $tab ? 'bg-emerald-700 text-white shadow-md dark:bg-emerald-400 dark:text-emerald-950' : 'text-zinc-500 hover:bg-emerald-50 hover:text-emerald-800 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-200' }}">
                    <strong class="block truncate text-sm">{{ $label }}</strong><small class="mt-0.5 block truncate text-[10px] opacity-70">{{ $description }}</small>
                </button>
            @endforeach
        </div>
    </nav>

    @if($activeTab === 'colaboradores')
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div><h2 class="text-xl font-extrabold text-zinc-900 dark:text-zinc-100">Equipo del fundo</h2><p class="text-xs text-zinc-500">CRUD de integrantes, estado y acceso específico.</p></div>
                @if(auth()->user()->tienePermiso('ajustes', 'crear'))
                    <button type="button" wire:click="openUserFormModal" class="agro-button w-full sm:w-auto">Nuevo usuario</button>
                @endif
            </div>

            <div class="agro-card grid gap-3 p-3 sm:grid-cols-[minmax(0,1fr)_12rem] sm:p-4">
                <label class="relative block">
                    <span class="sr-only">Buscar integrante</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                    <input type="search" wire:model.live.debounce.300ms="userSearch" placeholder="Nombre, correo, usuario o DNI..." class="w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-10 pr-4 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                </label>
                <x-filter-select model="userStatus" :options="['all' => 'Todos los estados', 'activo' => 'Activos', 'suspendido' => 'Suspendidos', 'inactivo' => 'Inactivos']" tone="emerald" live compact />
            </div>

            <div class="agro-record-surface overflow-hidden rounded-2xl border">
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[960px] text-left">
                        <thead class="agro-record-header text-[10px] font-bold uppercase tracking-wider"><tr><th class="p-4">Integrante</th><th class="p-4">Usuario / DNI</th><th class="p-4">Roles</th><th class="p-4">Acceso</th><th class="p-4">Sesiones</th><th class="p-4">Inactividad</th><th class="p-4 text-right">Acciones</th></tr></thead>
                        <tbody class="agro-record-list text-sm">
                            @forelse($usuariosFundo as $usr)
                                @php
                                    $membership = $usr->fundos->firstWhere('id', (int) session('fundo_id'));
                                    $isAdmin = (bool) $membership?->pivot?->es_administrador;
                                @endphp
                                <tr wire:key="team-{{ $usr->id }}">
                                    <td class="p-4"><strong class="block text-zinc-900 dark:text-zinc-100">{{ $usr->name }}</strong><span class="text-xs text-zinc-500">{{ $usr->email }}</span></td>
                                    <td class="p-4"><span class="block font-semibold">{{ $usr->username }}</span><span class="text-xs text-zinc-500">{{ $usr->dni ? 'DNI '.$usr->dni : 'Sin DNI' }}</span></td>
                                    <td class="p-4"><div class="flex max-w-72 flex-wrap gap-1">@forelse($usr->roles as $role)<span class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-1 text-[10px] font-bold dark:border-zinc-700 dark:bg-zinc-800">{{ $role->nombre }}</span>@empty<span class="text-xs text-zinc-500">Sin roles</span>@endforelse</div></td>
                                    <td class="p-4"><div class="flex flex-wrap items-center gap-2"><x-status-badge :value="$usr->estado" :tone="$usr->estado === 'activo' ? 'emerald' : ($usr->estado === 'suspendido' ? 'amber' : 'slate')" /><span class="text-[10px] font-bold {{ $isAdmin ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-500' }}">{{ $isAdmin ? 'Administrador' : 'Estándar' }}</span></div></td>
                                    <td class="p-4"><span class="font-bold text-emerald-700 dark:text-emerald-300">{{ $usr->sesiones_activas_count }}</span><span class="text-xs text-zinc-500"> / {{ $usr->max_active_sessions === 0 ? 'Sin límite' : $usr->max_active_sessions }} activas</span></td>
                                    <td class="p-4"><span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-[10px] font-bold text-sky-800 dark:bg-sky-400/10 dark:text-sky-300">{{ $usr->session_idle_timeout_minutes }} min</span></td>
                                    <td class="p-4"><div class="flex justify-end gap-1.5">
                                        @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))
                                            @if($canManageFundoAdmins)<x-table-action type="edit" wire:click="openUserFormModal({{ $usr->id }})" label="Editar datos" />@endif
                                            @if($usr->id !== auth()->id())<x-table-action type="view" wire:click="openUserAccessModal({{ $usr->id }})" label="Gestionar acceso" />@endif
                                            @if($canManageFundoAdmins)
                                                <x-table-action type="verify" wire:click="openUserSecurityModal({{ $usr->id }})" label="Gestionar seguridad de sesiones" />
                                                <x-table-action type="key" wire:click="openPasswordResetModal({{ $usr->id }})" label="Asignar nueva contraseña" />
                                                @if($usr->id !== auth()->id())<button type="button" wire:click="toggleUserStatus({{ $usr->id }})" class="h-9 rounded-lg border border-amber-300 px-2.5 text-[10px] font-bold text-amber-700 dark:border-amber-500/30 dark:text-amber-300">{{ $usr->estado === 'activo' ? 'Suspender' : 'Activar' }}</button>@endif
                                            @endif
                                        @endif
                                        @if($usr->id !== auth()->id() && $canManageFundoAdmins)
                                            <x-table-action type="delete" x-on:click.prevent="confirmDelete('¿Retirar integrante del fundo?', 'El usuario perderá sus accesos a este fundo.').then((res) => { if (res.isConfirmed) $wire.removeUserFromFundo({{ $usr->id }}) })" label="Retirar del fundo" />
                                        @endif
                                    </div></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-10 text-center text-sm text-zinc-500">Sin integrantes para filtros actuales.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-800 md:hidden">
                    @forelse($usuariosFundo as $usr)
                        @php
                            $membership = $usr->fundos->firstWhere('id', (int) session('fundo_id'));
                        @endphp
                        <article wire:key="team-mobile-{{ $usr->id }}" class="space-y-3 p-4">
                            <div class="flex items-start justify-between gap-3"><div class="min-w-0"><strong class="block truncate text-sm text-zinc-900 dark:text-zinc-100">{{ $usr->name }}</strong><span class="block truncate text-xs text-zinc-500">{{ $usr->email }}</span></div><x-status-badge :value="$usr->estado" :tone="$usr->estado === 'activo' ? 'emerald' : ($usr->estado === 'suspendido' ? 'amber' : 'slate')" /></div>
                            <p class="text-xs text-zinc-500">{{ $usr->username }} · {{ $usr->dni ?: 'Sin DNI' }} · {{ $membership?->pivot?->es_administrador ? 'Administrador' : 'Acceso estándar' }} · {{ $usr->sesiones_activas_count }}/{{ $usr->max_active_sessions === 0 ? 'Sin límite' : $usr->max_active_sessions }} sesiones · cierre {{ $usr->session_idle_timeout_minutes }} min</p>
                            <div class="flex flex-wrap gap-1">@forelse($usr->roles as $role)<span class="rounded-md border border-zinc-200 bg-zinc-50 px-2 py-1 text-[10px] font-bold dark:border-zinc-700 dark:bg-zinc-800">{{ $role->nombre }}</span>@empty<span class="text-xs text-zinc-500">Sin roles</span>@endforelse</div>
                            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                                @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))
                                    @if($canManageFundoAdmins)<x-table-action type="edit" wire:click="openUserFormModal({{ $usr->id }})" label="Editar datos" />@endif
                                    @if($usr->id !== auth()->id())<x-table-action type="view" wire:click="openUserAccessModal({{ $usr->id }})" label="Gestionar acceso" />@endif
                                    @if($canManageFundoAdmins)
                                        <x-table-action type="verify" wire:click="openUserSecurityModal({{ $usr->id }})" label="Gestionar seguridad de sesiones" />
                                        <x-table-action type="key" wire:click="openPasswordResetModal({{ $usr->id }})" label="Asignar nueva contraseña" />
                                        @if($usr->id !== auth()->id())<button type="button" wire:click="toggleUserStatus({{ $usr->id }})" class="h-9 rounded-xl border border-amber-300 px-3 text-xs font-bold text-amber-700 dark:border-amber-500/30 dark:text-amber-300">{{ $usr->estado === 'activo' ? 'Suspender' : 'Activar' }}</button>@endif
                                    @endif
                                @endif
                                @if($usr->id !== auth()->id() && $canManageFundoAdmins)
                                    <x-table-action type="delete" x-on:click.prevent="confirmDelete('¿Retirar integrante del fundo?', 'El usuario perderá sus accesos a este fundo.').then((res) => { if (res.isConfirmed) $wire.removeUserFromFundo({{ $usr->id }}) })" label="Retirar del fundo" />
                                @endif
                            </div>
                        </article>
                    @empty<div class="p-8 text-center text-sm text-zinc-500">Sin resultados.</div>@endforelse
                </div>
            </div>

            <div class="agro-table-footer"><div class="agro-table-size"><span>Mostrar</span><x-filter-select model="usersPerPage" :options="$perPageOptions" tone="emerald" live compact /></div><div class="min-w-0">{{ $usuariosFundo->links('components.pagination') }}</div></div>
        </section>
    @endif

    @if($activeTab === 'roles')
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><h2 class="text-xl font-extrabold">Roles y permisos</h2><p class="text-xs text-zinc-500">Plantillas globales protegidas; roles del fundo con CRUD completo.</p></div>@if(auth()->user()->tienePermiso('ajustes', 'crear'))<button wire:click="openRoleModal" class="agro-button w-full sm:w-auto">Nuevo rol</button>@endif</div>
            <div class="agro-card grid gap-3 p-3 sm:grid-cols-[minmax(0,1fr)_12rem] sm:p-4">
                <input type="search" wire:model.live.debounce.300ms="roleSearch" placeholder="Buscar nombre o descripción..." class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <x-filter-select model="roleScope" :options="['all' => 'Todos los alcances', 'fundo' => 'Solo del fundo', 'global' => 'Solo globales']" tone="emerald" live compact />
            </div>
            <div class="agro-record-surface overflow-hidden rounded-2xl border">
                <div class="overflow-x-auto"><table class="w-full min-w-[680px] text-left"><thead class="agro-record-header text-[10px] font-bold uppercase tracking-wider"><tr><th class="p-4">Rol</th><th class="p-4">Alcance</th><th class="p-4">Descripción</th><th class="p-4">Permisos</th><th class="p-4 text-right">Acciones</th></tr></thead><tbody class="agro-record-list text-sm">
                    @forelse($rolesFundo as $role)
                        @php
                            $isProtected = $role->es_protegido;
                        @endphp
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="p-4 font-bold">{{ $role->nombre }}</td>
                            <td class="p-4">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $isProtected ? 'bg-violet-100 text-violet-800 dark:bg-violet-400/10 dark:text-violet-300' : ($role->fundo_id ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-sky-100 text-sky-800 dark:bg-sky-400/10 dark:text-sky-300') }}">
                                    {{ $isProtected ? ($role->fundo_id ? 'Fundo protegido' : 'Global protegido') : 'Fundo' }}
                                </span>
                            </td>
                            <td class="max-w-sm p-4 text-xs text-zinc-500">{{ $role->descripcion ?: 'Sin descripción' }}</td>
                            <td class="p-4">
                                <strong class="text-emerald-700 dark:text-emerald-300">{{ $role->permisos_count }}</strong> <span class="text-xs text-zinc-500">permisos</span>
                            </td>
                            <td class="p-4">
                                <div class="flex justify-end gap-2">
                                    <x-table-action type="view" wire:click="openViewRoleModal({{ $role->id }})" label="Ver detalles del rol" />
                                    @if(! $isProtected && auth()->user()->tienePermiso('ajustes', 'actualizar'))
                                        <x-table-action type="edit" wire:click="openRoleModal({{ $role->id }})" label="Editar rol" />
                                    @endif
                                    @if(! $isProtected && auth()->user()->tienePermiso('ajustes', 'eliminar'))
                                        <x-table-action type="delete" x-on:click.prevent="confirmDelete('¿Eliminar este rol?', 'Se eliminará {{ $role->nombre }}. ¡Esta acción no se podrá revertir!').then((res) => { if (res.isConfirmed) $wire.deleteRole({{ $role->id }}) })" label="Eliminar rol" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-sm text-zinc-500">Sin roles para filtros actuales.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
            <div class="agro-table-footer"><div class="agro-table-size"><span>Mostrar</span><x-filter-select model="rolesPerPage" :options="$perPageOptions" tone="emerald" live compact /></div><div class="min-w-0">{{ $rolesFundo->links('components.pagination') }}</div></div>
        </section>
    @endif

    @if($activeTab === 'general')
        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,.65fr)]">
            <form wire:submit="saveBranding" x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="agro-card overflow-hidden"
                  x-data="{
                      previewName: $wire.entangle('brandName'),
                      previewTagline: $wire.entangle('brandTagline'),
                      previewColor: $wire.entangle('brandColor'),
                      previewMode: $wire.entangle('brandColorMode'),
                      previewCustomColor: $wire.entangle('brandCustomColor'),
                      palettes: @js($brandPaletteRgb),
                      parseHex(value) {
                          const match = String(value || '').trim().match(/^#([0-9a-f]{6})$/i);
                          return match ? [0, 2, 4].map((offset) => parseInt(match[1].slice(offset, offset + 2), 16)) : null;
                      },
                      mix(source, target, weight) {
                          return source.map((value, index) => Math.round(value + ((target[index] - value) * weight)));
                      },
                      contrastWithWhite(rgb) {
                          const channels = rgb.map((channel) => { const value = channel / 255; return value <= .04045 ? value / 12.92 : Math.pow((value + .055) / 1.055, 2.4) });
                          return 1.05 / ((.2126 * channels[0]) + (.7152 * channels[1]) + (.0722 * channels[2]) + .05);
                      },
                      customPalette() {
                          const base = this.parseHex(this.previewCustomColor);
                          if (!base) return null;
                          const mixes = { 50: ['white', .94], 100: ['white', .86], 200: ['white', .70], 300: ['white', .50], 400: ['white', .25], 500: ['base', 0], 600: ['black', .18], 700: ['black', .36], 800: ['black', .52], 900: ['black', .66], 950: ['black', .80] };
                          return Object.fromEntries(Object.entries(mixes).map(([shade, [target, weight]]) => {
                              let rgb = target === 'white' ? this.mix(base, [255, 255, 255], weight) : (target === 'black' ? this.mix(base, [0, 0, 0], weight) : base);
                              if (['600', '700'].includes(shade)) while (this.contrastWithWhite(rgb) < 4.5) rgb = this.mix(rgb, [0, 0, 0], .08);
                              return [shade, rgb.join(' ')];
                          }));
                      },
                      applyColor() {
                          const palette = this.previewMode === 'custom' ? this.customPalette() : this.palettes[this.previewColor];
                          if (palette) Object.entries(palette).forEach(([shade, rgb]) => document.documentElement.style.setProperty(`--brand-${shade}`, rgb));
                      }
                  }"
                  x-init="$nextTick(() => applyColor())">
                <div class="border-b border-zinc-200 p-4 dark:border-zinc-800 sm:p-6"><p class="agro-kicker">Identidad global</p><h2 class="mt-1 text-xl font-extrabold">Nombre, lema, color y logo</h2><p class="mt-1 text-xs text-zinc-500">Cambios aplicados en navegación, acceso, reportes PDF y exportaciones.</p></div>
                <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
                    @php
                        $brandingLogoFrame = \App\Support\ImageFrame::normalize($brandLogoFrame);
                    @endphp
                    <div class="space-y-3" x-data="optimizedImageUpload('brandLogo', 512, 262144)">
                        <div class="relative flex aspect-square items-center justify-center overflow-hidden rounded-3xl border border-dashed border-emerald-300 bg-emerald-50 p-5 dark:border-emerald-400/25 dark:bg-emerald-950/30" x-bind:aria-busy="busy">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" alt="Vista previa inmediata del logo" class="absolute inset-0 h-full w-full object-contain">
                            </template>
                            <div x-show="!previewUrl" class="h-full w-full">
                                @if($brandLogo)
                                    <img src="{{ $brandLogo->temporaryUrl() }}" alt="Vista previa" class="h-full w-full object-cover" style="object-position: {{ $brandingLogoFrame['x'] }}% {{ $brandingLogoFrame['y'] }}%; transform: scale({{ $brandingLogoFrame['zoom'] }}); transform-origin: {{ $brandingLogoFrame['x'] }}% {{ $brandingLogoFrame['y'] }}%;">
                                @elseif($branding->logoUrl())
                                    <img src="{{ $branding->logoUrl() }}" alt="Logo actual" class="h-full w-full object-cover" style="object-position: {{ $brandingLogoFrame['x'] }}% {{ $brandingLogoFrame['y'] }}%; transform: scale({{ $brandingLogoFrame['zoom'] }}); transform-origin: {{ $brandingLogoFrame['x'] }}% {{ $brandingLogoFrame['y'] }}%;">
                                @else
                                    <span class="flex h-full w-full items-center justify-center"><x-brand-logo class="h-24 w-24 text-emerald-700 dark:text-emerald-300" /></span>
                                @endif
                            </div>
                            @if($brandLogo)
                                <x-image-frame-editor id="branding-logo-frame" :src="$brandLogo->temporaryUrl()" x-model="brandLogoFrame.x" y-model="brandLogoFrame.y" zoom-model="brandLogoFrame.zoom" />
                            @elseif($branding->logoUrl())
                                <x-image-frame-editor id="branding-logo-frame" :src="$branding->logoUrl()" x-model="brandLogoFrame.x" y-model="brandLogoFrame.y" zoom-model="brandLogoFrame.zoom" />
                            @endif
                            <div x-cloak x-show="busy" class="absolute inset-x-3 bottom-3 z-30 rounded-xl bg-zinc-950/90 p-3 shadow-lg" role="status" aria-live="polite">
                                <div class="flex items-center justify-between text-[11px] font-semibold text-emerald-300"><span x-text="processing ? 'Optimizando logo...' : 'Subiendo logo...'"></span><span x-show="uploading" x-text="`${progress}%`"></span></div>
                                <progress max="100" x-bind:value="processing ? null : progress" class="mt-2 block h-1 w-full overflow-hidden rounded-full"></progress>
                            </div>
                        </div>
                        <x-image-source-actions input-id="branding-logo-input" gallery-label="Seleccionar logo" />
                        @if($brandLogo)<button type="button" wire:click="cancelBrandLogoChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="w-full text-xs font-bold text-zinc-600 dark:text-zinc-300">Descartar logo nuevo</button>@endif
                        <p class="text-center text-[11px] leading-5 text-zinc-500">JPG, PNG o WebP. Vista previa directa, WebP ≤512 px y 256 KB. Ajusta encuadre antes de guardar.</p>
                        <span x-cloak x-show="clientError" x-text="clientError" class="block text-xs text-rose-500" role="alert"></span>
                        @if($brandLogoPath)<button type="button" wire:click="removeBrandLogo" wire:confirm="¿Usar nuevamente el icono predeterminado?" class="w-full text-xs font-bold text-rose-600">Quitar logo actual</button>@endif
                        @error('brandLogo')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2"><label class="block"><span class="mb-1.5 block text-xs font-bold">Nombre del sistema</span><input x-model="previewName" maxlength="80" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm dark:border-zinc-700"></label><label class="block"><span class="mb-1.5 block text-xs font-bold">Lema</span><input x-model="previewTagline" maxlength="120" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm dark:border-zinc-700"></label></div>
                        <div>
                            <span class="mb-2 block text-xs font-bold">Color principal</span>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($brandPalettes as $color => $palette)
                                    <label class="relative cursor-pointer rounded-xl border-2 p-2.5 transition hover:-translate-y-0.5"
                                           :class="previewMode === 'preset' && previewColor === '{{ $color }}' ? 'bg-zinc-50 dark:bg-zinc-800' : 'border-zinc-200 dark:border-zinc-700'"
                                           :style="previewMode === 'preset' && previewColor === '{{ $color }}' ? 'border-color: {{ $palette[600] }}; box-shadow: 0 0 0 3px {{ $palette[600] }}33' : ''">
                                        <input type="radio" x-model="previewColor" x-on:change="previewMode = 'preset'; applyColor()" value="{{ $color }}" class="sr-only">
                                        <span class="flex items-center gap-2"><span class="h-7 w-7 rounded-lg" style="background: {{ $palette[600] }}"></span><strong class="text-xs">{{ $brandPaletteLabels[$color] ?? ucfirst($color) }}</strong></span>
                                        <span x-cloak x-show="previewMode === 'preset' && previewColor === '{{ $color }}'" class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full text-white" style="background: {{ $palette[600] }}"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m5 12 4 4L19 6" /></svg></span>
                                    </label>
                                @endforeach
                            </div>
                            <label class="mt-3 flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 transition" :class="previewMode === 'custom' ? 'border-emerald-600 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-400/10' : 'border-zinc-200 dark:border-zinc-700'">
                                <input type="radio" x-model="previewMode" value="custom" x-on:change="applyColor()" class="agro-checkbox h-4 w-4">
                                <span class="h-8 w-8 rounded-lg border border-black/10" :style="`background-color: ${parseHex(previewCustomColor) ? previewCustomColor : '#718F6D'}`"></span>
                                <span><strong class="block text-xs">Color personalizado</strong><small class="text-[10px] text-zinc-500">Elige cualquier color sólido.</small></span>
                            </label>
                            <div x-cloak x-show="previewMode === 'custom'" x-transition class="mt-3 grid grid-cols-[3.25rem_minmax(0,1fr)] gap-2 rounded-xl bg-zinc-50 p-3 dark:bg-zinc-900">
                                <input type="color" x-model="previewCustomColor" x-on:input.debounce.80ms="applyColor()" class="h-10 w-full cursor-pointer rounded-lg border-0 p-1">
                                <input type="text" x-model="previewCustomColor" x-on:input.debounce.120ms="applyColor()" maxlength="7" placeholder="#718F6D" class="w-full rounded-lg border border-zinc-300 px-3 py-2 font-mono text-xs uppercase dark:border-zinc-700">
                            </div>
                            @error('brandCustomColor')<p class="mt-2 text-xs text-rose-500">{{ $message }}</p>@enderror
                            <p class="mt-2 text-[11px] text-zinc-500">La vista cambia al instante. Guarda para aplicarlo en todo el sistema, Gestión web y Auditoría.</p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700"><p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Vista previa</p><div class="mt-3 flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-white"><x-brand-logo class="h-6 w-6" /></span><span><strong class="block" x-text="previewName || 'Nombre del sistema'"></strong><small class="text-zinc-500" x-text="previewTagline || 'Lema del sistema'"></small></span></div></div>
                    </div>
                </div>
                @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))<div class="flex justify-end border-t border-zinc-200 p-4 dark:border-zinc-800 sm:px-6"><button type="submit" x-bind:disabled="$store.imageUploads.busy" wire:loading.attr="disabled" wire:target="saveBranding,brandLogo" class="agro-button w-full sm:w-auto">Guardar identidad</button></div>@endif
            </form>

            <form wire:submit="saveSettings" class="agro-card h-fit p-4 sm:p-6">
                <p class="agro-kicker">Preferencias del fundo</p><h2 class="mt-1 text-xl font-extrabold">Operación local</h2>
                <div class="mt-5 space-y-4"><label class="block"><span class="mb-1.5 block text-xs font-bold">Nombre del fundo</span><input wire:model="settings.nombre_fundo" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm dark:border-zinc-700"></label><label class="block"><span class="mb-1.5 block text-xs font-bold">Moneda principal</span><x-filter-select model="settings.moneda" :options="['PEN' => 'Soles (PEN)', 'USD' => 'Dólares (USD)']" tone="emerald" /></label><label class="block"><span class="mb-1.5 block text-xs font-bold">Anticipación de alertas</span><div class="relative"><input type="number" min="1" max="365" wire:model="settings.alerta_dias" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 pr-14 text-sm dark:border-zinc-700"><span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-zinc-500">días</span></div></label></div>
                @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))<button type="submit" class="agro-button mt-6 w-full">Guardar preferencias</button>@endif
            </form>
        </section>
    @endif

    @if($activeTab === 'backup')
        @include('livewire.ajustes.backup')
        @if(false)
        <section class="space-y-5">
            <div class="grid gap-5 lg:grid-cols-[22rem_minmax(0,1fr)]">
                <form wire:submit="saveBackupSettings" class="agro-card h-fit p-4 sm:p-6">
                    <p class="agro-kicker">Automatización</p><h2 class="mt-1 text-xl font-extrabold">Backup programado</h2><p class="mt-1 text-xs leading-5 text-zinc-500">Copia SQL privada por fundo. Multimedia no incluida.</p>
                    <label class="mt-5 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-400/20 dark:bg-emerald-400/10"><span><strong class="block text-sm">Generación automática</strong><small class="text-zinc-500">Scheduler cada 15 minutos</small></span><input type="checkbox" wire:model="backupSettings.enabled" class="agro-checkbox h-5 w-5 rounded"></label>
                    <div class="mt-4 grid grid-cols-[minmax(0,1fr)_9rem] gap-2"><label><span class="mb-1.5 block text-xs font-bold">Cada</span><input type="number" min="1" wire:model="backupSettings.interval_value" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm dark:border-zinc-700"></label><label><span class="mb-1.5 block text-xs font-bold">Unidad</span><x-filter-select model="backupSettings.interval_unit" :options="['hours' => 'Horas', 'days' => 'Días']" tone="emerald" /></label></div>
                    @error('backupSettings.interval_value')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    <label class="mt-4 block"><span class="mb-1.5 block text-xs font-bold">Copias a conservar</span><input type="number" min="2" max="100" wire:model="backupSettings.retention_count" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm dark:border-zinc-700"><small class="mt-1 block text-[10px] text-zinc-500">Entre 2 y 100 por fundo.</small></label>
                    @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))<button type="submit" class="agro-button mt-5 w-full">Guardar programación</button>@endif
                    <div class="mt-4 rounded-xl bg-amber-50 p-3 text-[11px] leading-5 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200">Servidor debe ejecutar <code>php artisan schedule:run</code> cada minuto. En desarrollo: <code>php artisan schedule:work</code>.</div>
                </form>

                <div class="space-y-4">
                    <div class="agro-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6"><div><p class="agro-kicker">Copia inmediata</p><h2 class="mt-1 text-xl font-extrabold">Generar ahora</h2><p class="mt-1 text-xs text-zinc-500">Archivo privado con checksum SHA-256.</p></div>@if(auth()->user()->tienePermiso('ajustes', 'exportar'))<button wire:click="generateBackup" wire:loading.attr="disabled" wire:target="generateBackup" class="agro-button w-full sm:w-auto"><span wire:loading.remove wire:target="generateBackup">Generar backup</span><span wire:loading wire:target="generateBackup">Generando...</span></button>@endif</div>
                    <div class="agro-record-surface overflow-hidden rounded-2xl border"><div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left"><thead class="agro-record-header text-[10px] font-bold uppercase tracking-wider"><tr><th class="p-4">Fecha</th><th class="p-4">Origen</th><th class="p-4">Estado</th><th class="p-4">Archivo</th><th class="p-4">Integridad</th><th class="p-4 text-right">Acciones</th></tr></thead><tbody class="agro-record-list text-sm">
                        @forelse($backups as $backup)<tr wire:key="backup-{{ $backup->id }}"><td class="p-4"><strong class="block">{{ $backup->created_at->format('d/m/Y') }}</strong><span class="text-xs text-zinc-500">{{ $backup->created_at->format('H:i:s') }}</span></td><td class="p-4 text-xs"><span class="font-bold">{{ $backup->trigger === 'scheduled' ? 'Programado' : 'Manual' }}</span><span class="block text-zinc-500">{{ $backup->requester?->name ?? 'Sistema' }}</span></td><td class="p-4"><x-status-badge :value="$backup->status" :label="match($backup->status) { 'completed' => 'Completado', 'failed' => 'Fallido', 'running' => 'Generando', default => 'Pendiente' }" :tone="match($backup->status) { 'completed' => 'emerald', 'failed' => 'rose', 'running' => 'amber', default => 'slate' }" /></td><td class="max-w-52 p-4 text-xs"><span class="block truncate font-semibold" title="{{ $backup->filename }}">{{ $backup->filename ?? 'Sin archivo' }}</span><span class="text-zinc-500">{{ $backup->size_bytes ? number_format($backup->size_bytes / 1024, 1).' KB' : $backup->database_driver }}</span></td><td class="p-4 font-mono text-[10px] text-zinc-500">{{ $backup->checksum_sha256 ? substr($backup->checksum_sha256, 0, 12).'…' : ($backup->error_message ? 'Revisar logs' : 'Pendiente') }}</td><td class="p-4"><div class="flex justify-end gap-2"><x-table-action type="view" wire:click="openBackupDetails({{ $backup->id }})" label="Ver detalles del backup" />@if($backup->status === 'completed' && auth()->user()->tienePermiso('ajustes', 'exportar'))<x-table-action type="verify" wire:click="verifyBackup({{ $backup->id }})" label="Verificar integridad SHA-256" />@if($backup->path)<x-table-action type="download" href="{{ route('ajustes.backups.download', $backup) }}" label="Descargar backup SQL" />@endif @endif @if(auth()->user()->tienePermiso('ajustes', 'eliminar'))<x-table-action type="delete" x-on:click.prevent="confirmDelete('¿Eliminar este backup privado?', 'No se podrá recuperar este archivo de respaldo.').then((res) => { if (res.isConfirmed) $wire.deleteBackup({{ $backup->id }}) })" label="Eliminar backup" />@endif</div></td></tr>@empty<tr><td colspan="6" class="p-10 text-center text-sm text-zinc-500">Todavía no existen backups.</td></tr>@endforelse
                    </tbody></table></div></div>
                    <div class="agro-table-footer"><div class="agro-table-size"><span>Mostrar</span><x-filter-select model="backupsPerPage" :options="$perPageOptions" tone="emerald" live compact /></div><div class="min-w-0">{{ $backups->links('components.pagination') }}</div></div>
                </div>
            </div>
        </section>
    @endif

        @endif

    @if($showRoleModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <section role="dialog" aria-modal="true" aria-label="Gestionar rol" class="agro-dialog agro-dialog--xl w-full max-w-7xl">
                <header class="flex items-center justify-between border-b border-zinc-200 px-4 py-3.5 dark:border-zinc-800">
                    <div>
                        <p class="agro-kicker">Seguridad</p>
                        <h3 class="mt-0.5 text-lg font-extrabold text-zinc-900 dark:text-zinc-100">{{ $roleId ? 'Editar rol' : 'Nuevo rol' }}</h3>
                    </div>
                    <button wire:click="closeRoleModal" class="agro-icon-button !h-9 !w-9" aria-label="Cerrar">&times;</button>
                </header>
                <form wire:submit="saveRole" class="agro-dialog__scroll space-y-4 p-4 sm:p-5">
                    <div class="grid gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-5">
                            <label class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Nombre del rol</label>
                            <input wire:model="roleNombre" maxlength="100" placeholder="Ej: Veterinario, Operario de Ordeño, Contador..." class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-medium text-zinc-900 placeholder-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500">
                            @error('roleNombre')<span class="mt-1 block text-xs font-medium text-rose-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="sm:col-span-7">
                            <label class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Descripción <span class="font-normal text-zinc-400 dark:text-zinc-500">(opcional)</span></label>
                            <input wire:model="roleDescripcion" maxlength="255" placeholder="Responsabilidad o alcance del rol..." class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-medium text-zinc-900 placeholder-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500">
                            @error('roleDescripcion')<span class="mt-1 block text-xs font-medium text-rose-500">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between border-b border-zinc-200/80 pb-2 dark:border-zinc-800/80">
                            <span class="text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Permisos por Módulo</span>
                            <small class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Selecciona los permisos habilitados</small>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($permisosEstructurados as $module => $permissions)
                                @php
                                    $moduleKey = strtolower($module);
                                @endphp
                                <fieldset class="flex flex-col justify-between rounded-xl border p-2.5 transition-colors {{ $moduleTones[$moduleKey] ?? 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60' }}">
                                    <div class="mb-2 flex items-center justify-between">
                                        <legend class="text-[11px] font-black uppercase tracking-wider text-zinc-900 dark:text-zinc-100">{{ $moduleLabels[$moduleKey] ?? ucfirst($module) }}</legend>
                                        <button type="button" wire:click="toggleModuloAll('{{ $module }}')" class="rounded-md px-1.5 py-0.5 text-[10px] font-extrabold text-emerald-700 hover:bg-emerald-200/60 dark:text-emerald-300 dark:hover:bg-emerald-400/20 transition">
                                            Todos
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        @foreach($permissions as $permission)
                                            @php
                                                $isSelected = in_array((string) $permission->id, array_map('strval', $selectedPermisos), true);
                                            @endphp
                                            <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2 py-1.5 text-[10px] transition {{ $isSelected ? 'border-emerald-500/40 bg-emerald-500/15 font-bold text-emerald-950 dark:border-emerald-400/30 dark:bg-emerald-400/20 dark:text-emerald-200 shadow-xs' : 'border-zinc-200/60 bg-white/80 font-medium text-zinc-700 hover:bg-white dark:border-zinc-700/60 dark:bg-zinc-900/60 dark:text-zinc-300 dark:hover:bg-zinc-900' }}">
                                                <input type="checkbox" wire:model="selectedPermisos" value="{{ $permission->id }}" class="agro-checkbox h-3.5 w-3.5 rounded text-emerald-600 focus:ring-emerald-500">
                                                <span class="capitalize truncate">{{ match($permission->accion) { 'leer' => 'Ver', 'crear' => 'Crear', 'actualizar' => 'Editar', 'eliminar' => 'Eliminar', 'exportar' => 'Exportar', 'restaurar' => 'Restaurar', default => ucfirst($permission->accion) } }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>
                    </div>
                </form>
                <footer class="flex flex-col-reverse gap-2 border-t border-zinc-200 p-3.5 dark:border-zinc-800 sm:flex-row sm:justify-end">
                    <button wire:click="closeRoleModal" class="agro-button-secondary !min-h-9 !px-4 !py-2 text-xs">Cancelar</button>
                    <button wire:click="saveRole" class="agro-button !min-h-9 !px-4 !py-2 text-xs">Guardar rol</button>
                </footer>
            </section>
        </div>
    @endif


    @if($showUserFormModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <section role="dialog" aria-modal="true" aria-label="Gestionar integrante" class="agro-dialog agro-dialog--compact agro-dialog--scroll p-4 sm:p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="agro-kicker">Equipo</p>
                        <h3 class="mt-1 text-xl font-extrabold">{{ $editingUserId ? 'Editar usuario' : 'Nuevo usuario' }}</h3>
                    </div>
                    <button wire:click="closeUserFormModal" class="agro-icon-button" aria-label="Cerrar">&times;</button>
                </div>
                <form wire:submit="saveUser" class="mt-5 space-y-4">
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Nombre completo</span>
                        <input wire:model="userName" placeholder="Ej: Juan Pérez Morales" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs text-zinc-900 placeholder-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                        @error('userName')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Usuario</span>
                            <input wire:model="userUsername" placeholder="Ej: juan.perez" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs text-zinc-900 placeholder-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                            @error('userUsername')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">DNI</span>
                            <input wire:model="userDni" placeholder="Ej: 71234567" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs text-zinc-900 placeholder-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                            @error('userDni')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <label class="block">
                        <span class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Correo electrónico</span>
                        <input type="email" wire:model="userEmail" placeholder="Ej: usuario@agrofundo.com" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs text-zinc-900 placeholder-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                        @error('userEmail')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                    </label>

                    @if(! $editingUserId)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block" x-data="{ show: false }">
                            <span class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Contraseña {{ $editingUserId ? '(opcional)' : '' }}</span>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" wire:model="userPassword" placeholder="••••••••" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 pr-10 text-xs text-zinc-900 placeholder-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.043 10.043 0 013.682-.863c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('userPassword')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                        </label>

                        <label class="block" x-data="{ show: false }">
                            <span class="mb-1 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Repetir contraseña</span>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" wire:model="userPasswordConfirmation" placeholder="Repita la contraseña" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 pr-10 text-xs text-zinc-900 placeholder-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.043 10.043 0 013.682-.863c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </label>
                    </div>
                    @else
                        <p class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-xs text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200">La contraseña se cambia desde Seguridad de cuenta. Todas las sesiones se cerrarán al hacerlo.</p>
                    @endif

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeUserFormModal" class="agro-button-secondary">Cancelar</button>
                        <button type="submit" class="agro-button">{{ $editingUserId ? 'Actualizar' : 'Agregar' }}</button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    @if($showPasswordResetModal)
        @include('livewire.ajustes.password-reset')
    @endif

    @if($showUserSecurityModal)
        @include('livewire.ajustes.user-security')
    @endif

    @if($showUserAccessModal)
        @include('livewire.ajustes.user-access')
        @if(false)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay"><section role="dialog" aria-modal="true" aria-label="Gestionar accesos" class="agro-dialog agro-dialog--compact agro-dialog--scroll p-4 sm:p-6"><div class="flex items-start justify-between"><div><p class="agro-kicker">Acceso al fundo</p><h3 class="mt-1 text-xl font-extrabold">{{ $selectedUserName }}</h3><p class="text-xs text-zinc-500">{{ $selectedUserEmail }}</p></div><button wire:click="closeUserAccessModal" class="agro-icon-button" aria-label="Cerrar">&times;</button></div><form wire:submit="saveUserAccess" class="mt-5 space-y-4"><label class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-400/20 dark:bg-emerald-400/10"><span><strong class="block text-sm">Administrador del fundo</strong><small class="text-zinc-500">Acceso completo solo aquí.</small></span><input type="checkbox" wire:model="userEsAdmin" class="agro-checkbox h-5 w-5 rounded"></label><div><p class="mb-2 text-xs font-bold">Roles personalizados</p><div class="max-h-72 space-y-2 overflow-y-auto">@forelse($rolesDisponibles as $role)<label class="flex items-center gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-700"><input type="checkbox" wire:model="userRoleIds" value="{{ $role->id }}" class="agro-checkbox h-4 w-4 rounded"><span><strong class="block text-sm">{{ $role->nombre }}</strong><small class="text-zinc-500">{{ $role->descripcion ?: 'Sin descripción' }}</small></span></label>@empty<p class="rounded-xl bg-zinc-50 p-4 text-xs text-zinc-500 dark:bg-zinc-800">Crea primero un rol personalizado. Roles globales heredados están protegidos.</p>@endforelse</div></div><div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" wire:click="closeUserAccessModal" class="agro-button-secondary">Cancelar</button><button type="submit" class="agro-button">Guardar accesos</button></div></form></section></div>
    @endif

        @endif

    @if($showViewRoleModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <section role="dialog" aria-modal="true" aria-label="Detalles del rol" class="agro-dialog agro-dialog--xl w-full max-w-7xl">
                <header class="flex items-center justify-between border-b border-zinc-200 px-4 py-3.5 dark:border-zinc-800">
                    <div>
                        <p class="agro-kicker">Detalles del Rol</p>
                        <h3 class="mt-0.5 text-lg font-extrabold text-zinc-900 dark:text-zinc-100">{{ $viewRoleNombre }}</h3>
                        <p class="text-xs text-zinc-500">{{ $viewRoleDescripcion ?: 'Sin descripción registrada' }}</p>
                    </div>
                    <button wire:click="closeViewRoleModal" class="agro-icon-button !h-9 !w-9" aria-label="Cerrar">&times;</button>
                </header>
                <div class="agro-dialog__scroll space-y-4 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200/80 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-900/70">
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Alcance</span>
                            <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">
                                {{ $viewRoleAlcance }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Permisos activos</span>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">{{ count($viewRolePermisoIds) }} permisos</span>
                        </div>
                    </div>

                    @if(!empty($viewRoleUsuarios))
                        <div>
                            <span class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-zinc-500">Integrantes asignados</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($viewRoleUsuarios as $u)
                                    <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300">
                                        {{ $u['name'] }} ({{ '@'.$u['username'] }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <span class="mb-2 block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Matriz de Permisos por Módulo</span>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($permisosEstructurados as $module => $permissions)
                                @php
                                    $moduleKey = strtolower($module);
                                    $moduleIds = collect($permissions)->pluck('id')->all();
                                    $activeCount = count(array_intersect($moduleIds, $viewRolePermisoIds));
                                @endphp
                                <fieldset class="flex flex-col justify-between rounded-xl border p-2.5 transition-colors {{ $moduleTones[$moduleKey] ?? 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60' }}">
                                    <div class="mb-2 flex items-center justify-between">
                                        <legend class="text-[11px] font-black uppercase tracking-wider text-zinc-900 dark:text-zinc-100">{{ $module }}</legend>
                                        <span class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400">{{ $activeCount }}/{{ count($permissions) }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        @foreach($permissions as $permission)
                                            @php
                                                $hasPerm = in_array((int) $permission->id, $viewRolePermisoIds, true);
                                            @endphp
                                            <div class="flex items-center gap-1.5 rounded-lg border px-2 py-1.5 text-[10px] transition {{ $hasPerm ? 'border-emerald-500/40 bg-emerald-500/15 font-bold text-emerald-950 dark:border-emerald-400/30 dark:bg-emerald-400/20 dark:text-emerald-200 shadow-xs' : 'border-zinc-200/50 bg-zinc-100/50 font-medium text-zinc-400 opacity-60 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-zinc-500' }}">
                                                <svg class="h-3 w-3 shrink-0 {{ $hasPerm ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-300 dark:text-zinc-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($hasPerm)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m4.5 12.75 4.5 4.5 10.5-10.5" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                                                    @endif
                                                </svg>
                                                <span class="truncate">{{ match($permission->accion) { 'leer' => 'Ver', 'crear' => 'Crear', 'actualizar' => 'Editar', 'eliminar' => 'Eliminar', 'exportar' => 'Exportar', 'restaurar' => 'Restaurar', default => ucfirst($permission->accion) } }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>
                    </div>
                </div>
                <footer class="flex justify-end border-t border-zinc-200 p-3.5 dark:border-zinc-800">
                    <button wire:click="closeViewRoleModal" class="agro-button-secondary !min-h-9 !px-4 !py-2 text-xs">Cerrar</button>
                </footer>
            </section>
        </div>
    @endif
</div>
