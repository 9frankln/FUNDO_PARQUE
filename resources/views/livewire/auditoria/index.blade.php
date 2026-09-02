<div x-data="{ showModal: @entangle('showDeleteModal'), mode: @entangle('deleteMode') }" class="mx-auto max-w-7xl space-y-5">
    <header class="agro-card overflow-hidden p-4 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 4.5 6v5.5c0 4.6 3.2 7.9 7.5 9.5 4.3-1.6 7.5-4.9 7.5-9.5V6L12 3Z"/><path stroke-linecap="round" stroke-width="1.8" d="M8.5 12h7m-7 3h4m-4-6h7"/></svg>
                </span>
                <div>
                    <p class="agro-kicker">Administración</p>
                    <h1 class="agro-title mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">Auditoría</h1>
                    <p class="mt-1 text-sm text-zinc-500">Historial completo de accesos, acciones, cambios, sesiones, IP y equipos.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->tienePermiso('auditoria', 'exportar'))
                    <button type="button" wire:click="exportar" wire:loading.attr="disabled" wire:target="exportar" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-70 dark:bg-emerald-600 dark:hover:bg-emerald-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 18.5A2.5 2.5 0 0 0 6.5 21h11A2.5 2.5 0 0 0 20 18.5"/></svg>
                        <span wire:loading.remove wire:target="exportar">Exportar CSV</span>
                        <span wire:loading wire:target="exportar">Generando...</span>
                    </button>
                @endif
                <button type="button" @click="showModal = true; $wire.openDeleteModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-300 bg-white text-zinc-600 shadow-xs transition hover:border-rose-400 hover:bg-rose-50 hover:text-rose-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Borrar registros de auditoría">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <section class="agro-card p-4 sm:p-5 space-y-4">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Usuario, actividad, IP..." class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Módulo</label>
                <x-filter-select model="module" :options="collect(['all' => 'Todos los módulos'])->merge($modules->mapWithKeys(fn ($module) => [$module => ucfirst($module)]))->all()" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Usuario</label>
                <x-filter-select model="user" :options="collect(['all' => 'Todos los usuarios'])->merge($users->mapWithKeys(fn ($user) => [$user->id => $user->name.' · '.$user->username]))->all()" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Actividad</label>
                <x-filter-select model="event" :options="collect(['all' => 'Todas las actividades'])->merge($events->mapWithKeys(fn ($event) => [$event => str($event)->replace(['.', '_'], ' ')->headline()]))->all()" tone="emerald" live compact />
            </div>
        </div>
        <div class="flex flex-col gap-3 border-t border-zinc-200 pt-3 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Periodo:</span>
                <div class="flex items-center gap-2">
                    <div class="w-32">
                        <x-date-picker model="from" placeholder="Desde" compact />
                    </div>
                    <span class="text-xs text-zinc-400">-</span>
                    <div class="w-32">
                        <x-date-picker model="to" placeholder="Hasta" compact />
                    </div>
                </div>
            </div>
            <button type="button" wire:click="clearFilters" class="self-start rounded-lg px-3 py-1.5 text-xs font-bold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 sm:self-auto">Limpiar filtros</button>
        </div>
    </section>

    <section class="agro-record-surface overflow-hidden rounded-2xl border">
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[1100px] text-left">
                <thead class="agro-record-header text-[10px] font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Fecha / hora</th>
                        <th class="p-4">Actor</th>
                        <th class="p-4">Usuario afectado</th>
                        <th class="p-4">Actividad</th>
                        <th class="p-4">Origen</th>
                        <th class="p-4">Detalle</th>
                    </tr>
                </thead>
                <tbody class="agro-record-list text-sm">
                    @forelse($logs as $log)
                        <tr wire:key="log-{{ $log->id }}">
                            <td class="p-4">
                                <strong class="block text-zinc-900 dark:text-zinc-100">{{ $log->created_at?->format('d/m/Y') }}</strong>
                                <span class="text-xs text-zinc-500">{{ $log->created_at?->format('H:i:s') }}</span>
                            </td>
                            <td class="p-4">
                                <strong class="block text-zinc-900 dark:text-zinc-100">{{ $log->usuario?->name ?? 'Sistema' }}</strong>
                                <span class="text-xs text-zinc-500">{{ $log->usuario?->username ?? $log->usuario?->email ?? 'Sin cuenta' }}</span>
                            </td>
                            <td class="p-4">
                                <strong class="block text-zinc-900 dark:text-zinc-100">{{ $log->usuarioObjetivo?->name ?? 'No aplica' }}</strong>
                                <span class="text-xs text-zinc-500">{{ $log->usuarioObjetivo?->username ?? '' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="inline-block rounded-full bg-zinc-100 px-2.5 py-0.5 text-[10px] font-bold text-zinc-800 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700">
                                    {{ str($log->event ?: $log->accion)->replace(['.', '_'], ' ')->headline() }}
                                </span>
                                <span class="ml-1 text-[10px] font-bold text-zinc-500">{{ ucfirst($log->modulo ?: 'Sistema') }}</span>
                                <span class="mt-1 block text-[10px] font-semibold {{ $log->result === 'exitoso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ ucfirst($log->result) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="block font-mono text-xs text-zinc-800 dark:text-zinc-200">{{ $log->ip_address ?: 'Sin IP' }}</span>
                                <span class="block max-w-44 truncate text-[10px] text-zinc-500" title="{{ $log->user_agent }}">{{ $log->user_agent ?: 'Sin agente' }}</span>
                            </td>
                            <td class="max-w-md p-4">
                                <p class="text-xs leading-5 text-zinc-800 dark:text-zinc-200">{{ $log->detalle ?: 'Sin detalle' }}</p>
                                <p class="mt-1 truncate text-[10px] text-zinc-500">{{ trim(($log->method ?: '').' '.($log->url ?: '')) }}</p>
                                @if($log->metadata)
                                    <details class="mt-1">
                                        <summary class="cursor-pointer text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Ver detalles</summary>
                                        <pre class="mt-1 max-w-md overflow-auto rounded-lg bg-zinc-900 p-2 text-[10px] text-zinc-100">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <x-empty-state icon="clipboard" title="Sin registros de auditoría" description="No se encontraron registros con los filtros aplicados." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800 lg:hidden">
            @forelse($logs as $log)
                <article wire:key="log-card-{{ $log->id }}" class="space-y-2 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <strong class="block text-sm">{{ str($log->event ?: $log->accion)->replace(['.', '_'], ' ')->headline() }}</strong>
                            <span class="text-[10px] text-zinc-500">{{ $log->created_at?->format('d/m/Y H:i:s') }} · {{ ucfirst($log->modulo ?: 'Sistema') }}</span>
                        </div>
                        <span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $log->result === 'exitoso' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-400/10 dark:text-rose-200' }}">
                            {{ ucfirst($log->result) }}
                        </span>
                    </div>
                    <p class="text-xs">{{ $log->detalle ?: 'Sin detalle' }}</p>
                    <p class="text-[10px] text-zinc-500">Actor: {{ $log->usuario?->name ?? 'Sistema' }} · Afectado: {{ $log->usuarioObjetivo?->name ?? 'No aplica' }}</p>
                    <p class="truncate font-mono text-[10px] text-zinc-500">{{ $log->ip_address }} · {{ $log->user_agent }}</p>
                    @if($log->metadata)
                        <details>
                            <summary class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300">Ver detalles</summary>
                            <pre class="mt-1 overflow-auto rounded-lg bg-zinc-950 p-2 text-[10px] text-zinc-100">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    @endif
                </article>
            @empty
                <div class="p-0">
                    <x-empty-state icon="clipboard" title="Sin registros de auditoría" description="No se encontraron registros con los filtros aplicados." />
                </div>
            @endforelse
        </div>
    </section>

    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <x-filter-select model="perPage" :options="$perPageOptions" tone="emerald" live compact />
            <span class="hidden whitespace-nowrap text-zinc-500 sm:inline">{{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} de {{ $logs->total() }}</span>
        </div>
        <div class="min-w-0">{{ $logs->links('components.pagination') }}</div>
    </div>

    <!-- Modal Elegante con Legibilidad Máxima y Colores Armónicos del Sistema -->
    <div
        x-cloak
        x-show="showModal"
        x-init="$watch('showModal', val => val ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
        class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-md transition-all dark:bg-black/70"
    >
        <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-zinc-200 bg-white p-5 shadow-2xl transition-all dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
            <!-- Modal Header -->
            <header class="flex items-start justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-widest text-rose-600 dark:text-rose-400">Módulo de Seguridad</p>
                        <h3 class="mt-0.5 text-lg font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">Borrar Registros de Auditoría</h3>
                        <p class="mt-1 text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-300">Selecciona el rango de historial que deseas purgar de la base de datos.</p>
                    </div>
                </div>
                <button type="button" @click="showModal = false; $wire.closeDeleteModal()" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200" aria-label="Cerrar">&times;</button>
            </header>

            <div class="mt-4 space-y-4">
                <!-- Grid de Opciones con Contraste Garantizado -->
                <div>
                    <label class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Selecciona el alcance del borrado</label>
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                        <!-- Hoy -->
                        <button
                            type="button"
                            @click="mode = 'today'"
                            :class="mode === 'today' ? 'border-emerald-500 bg-emerald-50/90 ring-2 ring-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-950/60 dark:ring-emerald-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800'"
                            class="flex min-h-12 w-full items-center justify-between rounded-2xl border px-3.5 py-2.5 text-left transition-all duration-150 shadow-xs"
                        >
                            <div class="flex items-center gap-3">
                                <span :class="mode === 'today' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                                </span>
                                <span :class="mode === 'today' ? 'text-emerald-950 dark:text-emerald-100 font-extrabold' : 'text-zinc-800 dark:text-zinc-200 font-bold'" class="text-xs">Registros de hoy</span>
                            </div>
                            <template x-if="mode === 'today'">
                                <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                            </template>
                        </button>

                        <!-- Esta semana -->
                        <button
                            type="button"
                            @click="mode = 'week'"
                            :class="mode === 'week' ? 'border-emerald-500 bg-emerald-50/90 ring-2 ring-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-950/60 dark:ring-emerald-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800'"
                            class="flex min-h-12 w-full items-center justify-between rounded-2xl border px-3.5 py-2.5 text-left transition-all duration-150 shadow-xs"
                        >
                            <div class="flex items-center gap-3">
                                <span :class="mode === 'week' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 5v14M12 5v14M18 5v14"/></svg>
                                </span>
                                <span :class="mode === 'week' ? 'text-emerald-950 dark:text-emerald-100 font-extrabold' : 'text-zinc-800 dark:text-zinc-200 font-bold'" class="text-xs">Esta semana</span>
                            </div>
                            <template x-if="mode === 'week'">
                                <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                            </template>
                        </button>

                        <!-- Entre fechas -->
                        <button
                            type="button"
                            @click="mode = 'period'"
                            :class="mode === 'period' ? 'border-emerald-500 bg-emerald-50/90 ring-2 ring-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-950/60 dark:ring-emerald-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800'"
                            class="flex min-h-12 w-full items-center justify-between rounded-2xl border px-3.5 py-2.5 text-left transition-all duration-150 shadow-xs"
                        >
                            <div class="flex items-center gap-3">
                                <span :class="mode === 'period' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M3 20h18M8 4v16M16 4v16"/></svg>
                                </span>
                                <span :class="mode === 'period' ? 'text-emerald-950 dark:text-emerald-100 font-extrabold' : 'text-zinc-800 dark:text-zinc-200 font-bold'" class="text-xs">Entre fechas</span>
                            </div>
                            <template x-if="mode === 'period'">
                                <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                            </template>
                        </button>

                        <!-- Últimos N días -->
                        <button
                            type="button"
                            @click="mode = 'days'"
                            :class="mode === 'days' ? 'border-emerald-500 bg-emerald-50/90 ring-2 ring-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-950/60 dark:ring-emerald-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800'"
                            class="flex min-h-12 w-full items-center justify-between rounded-2xl border px-3.5 py-2.5 text-left transition-all duration-150 shadow-xs"
                        >
                            <div class="flex items-center gap-3">
                                <span :class="mode === 'days' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </span>
                                <span :class="mode === 'days' ? 'text-emerald-950 dark:text-emerald-100 font-extrabold' : 'text-zinc-800 dark:text-zinc-200 font-bold'" class="text-xs">Últimos N días</span>
                            </div>
                            <template x-if="mode === 'days'">
                                <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                            </template>
                        </button>

                        <!-- Por usuario -->
                        <button
                            type="button"
                            @click="mode = 'user'"
                            :class="mode === 'user' ? 'border-emerald-500 bg-emerald-50/90 ring-2 ring-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-950/60 dark:ring-emerald-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800'"
                            class="flex min-h-12 w-full items-center justify-between rounded-2xl border px-3.5 py-2.5 text-left transition-all duration-150 shadow-xs"
                        >
                            <div class="flex items-center gap-3">
                                <span :class="mode === 'user' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                                </span>
                                <span :class="mode === 'user' ? 'text-emerald-950 dark:text-emerald-100 font-extrabold' : 'text-zinc-800 dark:text-zinc-200 font-bold'" class="text-xs">Por usuario</span>
                            </div>
                            <template x-if="mode === 'user'">
                                <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                            </template>
                        </button>

                        <!-- Eliminar todo -->
                        <button
                            type="button"
                            @click="mode = 'all'"
                            :class="mode === 'all' ? 'border-rose-500 bg-rose-50/90 ring-2 ring-rose-500/20 dark:border-rose-400 dark:bg-rose-950/60 dark:ring-rose-400/30' : 'border-zinc-200 bg-white text-zinc-700 hover:border-rose-300 hover:bg-rose-50/40 dark:border-zinc-700/80 dark:bg-zinc-800/50 dark:text-zinc-200 dark:hover:border-rose-800 dark:hover:bg-rose-950/20'"
                            class="flex min-h-12 w-full items-center justify-between rounded-2xl border px-3.5 py-2.5 text-left transition-all duration-150 shadow-xs"
                        >
                            <div class="flex items-center gap-3">
                                <span :class="mode === 'all' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' : 'bg-rose-50 text-rose-500 dark:bg-rose-950/50 dark:text-rose-400'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </span>
                                <span :class="mode === 'all' ? 'text-rose-950 dark:text-rose-100 font-extrabold' : 'text-rose-700 dark:text-rose-300 font-bold'" class="text-xs">Eliminar todo</span>
                            </div>
                            <template x-if="mode === 'all'">
                                <svg class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Paneles Dinámicos con Textos Claros -->
                <div x-show="mode === 'user'" x-cloak class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                    <label class="block">
                        <span class="mb-2.5 block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Selecciona el Usuario a eliminar</span>
                        <x-filter-select
                            model="deleteUserId"
                            :options="['' => 'Selecciona un usuario de la lista...'] + $users->mapWithKeys(fn ($u) => [$u->id => $u->name.' ('.$u->username.')'])->all()"
                            tone="emerald"
                            live
                        />
                        @error('deleteUserId')<p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </label>
                </div>

                <div x-show="mode === 'period'" x-cloak class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2.5 block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Fecha Inicio (Desde)</span>
                            <x-date-picker model="deleteFrom" placeholder="dd/mm/aaaa" />
                            @error('deleteFrom')<p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </label>
                        <label class="block">
                            <span class="mb-2.5 block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Fecha Fin (Hasta)</span>
                            <x-date-picker model="deleteTo" placeholder="dd/mm/aaaa" />
                            @error('deleteTo')<p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </label>
                    </div>
                </div>

                <div x-show="mode === 'days'" x-cloak class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                    <label class="block">
                        <span class="mb-2.5 block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Número de Días hacia atrás</span>
                        <input type="number" wire:model.live="deleteDays" min="1" max="3650" class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400/20 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="30">
                        @error('deleteDays')<p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </label>
                </div>

                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-[11px] leading-relaxed text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                    <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong class="block font-bold text-amber-950 dark:text-amber-200">Acción permanente:</strong>
                        <span>Los registros de auditoría que coincidan con el alcance seleccionado serán eliminados definitivamente del fundo activo.</span>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons con Confirmación propia (agro-alert) -->
            <footer class="mt-5 flex flex-col-reverse gap-2.5 border-t border-zinc-100 pt-4 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" @click="showModal = false; $wire.closeDeleteModal()" wire:loading.attr="disabled" wire:target="deleteLogs" class="agro-button-secondary">Cancelar</button>
                <button
                    type="button"
                    x-on:click.prevent="confirmDelete(
                        '¿Eliminar registros de auditoría?',
                        '¡Esta acción no se podrá revertir! Los registros que coincidan con el alcance seleccionado serán eliminados definitivamente del fundo activo.'
                    ).then((res) => {
                        if (res.isConfirmed) {
                            showModal = false;
                            $wire.deleteLogs();
                        }
                    })"
                    wire:loading.attr="disabled"
                    wire:target="deleteLogs"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-rose-500 disabled:opacity-60"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    <span wire:loading.remove wire:target="deleteLogs">Eliminar Registros</span>
                    <span wire:loading wire:target="deleteLogs">Eliminando...</span>
                </button>
            </footer>
        </div>
    </div>
</div>
