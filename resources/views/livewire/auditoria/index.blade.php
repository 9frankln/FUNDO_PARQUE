<div class="mx-auto max-w-7xl space-y-5">
    <header class="agro-card overflow-hidden p-4 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-400/15 dark:text-indigo-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 4.5 6v5.5c0 4.6 3.2 7.9 7.5 9.5 4.3-1.6 7.5-4.9 7.5-9.5V6L12 3Z"/><path stroke-linecap="round" stroke-width="1.8" d="M8.5 12h7m-7 3h4m-4-6h7"/></svg>
                </span>
                <div><p class="agro-kicker">Administración</p><h1 class="agro-title mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">Auditoría</h1><p class="mt-1 text-sm text-zinc-500">Accesos, vistas, cambios, sesiones, IP y equipos.</p></div>
            </div>
            @if(auth()->user()->tienePermiso('auditoria', 'exportar'))<button type="button" wire:click="exportar" wire:loading.attr="disabled" wire:target="exportar" class="agro-button w-full sm:w-auto"><span wire:loading.remove wire:target="exportar">Exportar CSV</span><span wire:loading wire:target="exportar">Generando...</span></button>@endif
        </div>
    </header>

    <section class="agro-card p-3 sm:p-4">
        <div class="grid gap-2.5 lg:grid-cols-[minmax(16rem,1.7fr)_11rem_14rem_13rem]">
            <label class="relative"><span class="sr-only">Buscar actividad</span><svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg><input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar usuario, actividad, IP o ruta..." class="w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-10 pr-4 text-sm dark:border-zinc-700 dark:bg-zinc-950"></label>
            <x-filter-select model="module" :options="collect(['all' => 'Módulos'])->merge($modules->mapWithKeys(fn ($module) => [$module => ucfirst($module)]))->all()" tone="indigo" live compact />
            <x-filter-select model="user" :options="collect(['all' => 'Usuarios'])->merge($users->mapWithKeys(fn ($user) => [$user->id => $user->name.' · '.$user->username]))->all()" tone="indigo" live compact />
            <x-filter-select model="event" :options="collect(['all' => 'Actividades'])->merge($events->mapWithKeys(fn ($event) => [$event => str($event)->replace(['.', '_'], ' ')->headline()]))->all()" tone="indigo" live compact />
        </div>
        <div class="mt-2.5 flex flex-col gap-2 border-t border-zinc-200 pt-2.5 dark:border-zinc-800 sm:flex-row sm:items-center">
            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Periodo</span>
            <div class="grid flex-1 grid-cols-2 gap-2 sm:max-w-sm"><input type="date" wire:model.live="from" aria-label="Desde" class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-2 text-xs dark:border-zinc-700 dark:bg-zinc-950"><input type="date" wire:model.live="to" aria-label="Hasta" class="w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-2 text-xs dark:border-zinc-700 dark:bg-zinc-950"></div>
            <button type="button" wire:click="clearFilters" class="rounded-lg px-3 py-2 text-xs font-bold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-zinc-100">Limpiar filtros</button>
        </div>
    </section>

    <section class="agro-record-surface overflow-hidden rounded-2xl border">
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[1100px] text-left"><thead class="agro-record-header text-[10px] font-bold uppercase tracking-wider"><tr><th class="p-4">Fecha / hora</th><th class="p-4">Actor</th><th class="p-4">Usuario afectado</th><th class="p-4">Actividad</th><th class="p-4">Origen</th><th class="p-4">Detalle</th></tr></thead><tbody class="agro-record-list text-sm">
                @forelse($logs as $log)
                    <tr wire:key="audit-{{ $log->id }}"><td class="p-4"><strong class="block">{{ $log->created_at?->format('d/m/Y') }}</strong><span class="text-xs text-zinc-500">{{ $log->created_at?->format('H:i:s') }}</span></td><td class="p-4"><strong class="block">{{ $log->usuario?->name ?? 'Sistema' }}</strong><span class="text-xs text-zinc-500">{{ $log->usuario?->username ?? $log->usuario?->email ?? 'Sin cuenta' }}</span></td><td class="p-4"><strong class="block">{{ $log->usuarioObjetivo?->name ?? 'No aplica' }}</strong><span class="text-xs text-zinc-500">{{ $log->usuarioObjetivo?->username ?? '' }}</span></td><td class="p-4"><span class="rounded-full bg-indigo-100 px-2 py-1 text-[10px] font-bold text-indigo-800 dark:bg-indigo-400/10 dark:text-indigo-200">{{ str($log->event ?: $log->accion)->replace(['.', '_'], ' ')->headline() }}</span><span class="ml-1 text-[10px] font-bold text-zinc-500">{{ ucfirst($log->modulo ?: 'Sistema') }}</span><span class="mt-1 block text-[10px] {{ $log->result === 'exitoso' ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">{{ ucfirst($log->result) }}</span></td><td class="p-4"><span class="block font-mono text-xs">{{ $log->ip_address ?: 'Sin IP' }}</span><span class="block max-w-44 truncate text-[10px] text-zinc-500" title="{{ $log->user_agent }}">{{ $log->user_agent ?: 'Sin agente' }}</span></td><td class="max-w-md p-4"><p class="text-xs leading-5 text-zinc-700 dark:text-zinc-200">{{ $log->detalle ?: 'Sin detalle' }}</p><p class="mt-1 truncate text-[10px] text-zinc-500">{{ trim(($log->method ?: '').' '.($log->url ?: '')) }}</p>@if($log->metadata)<details class="mt-1"><summary class="cursor-pointer text-[10px] font-bold text-indigo-700 dark:text-indigo-300">Ver cambios</summary><pre class="mt-1 max-w-md overflow-auto rounded-lg bg-zinc-950 p-2 text-[10px] text-zinc-100">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre></details>@endif</td></tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center text-sm text-zinc-500">Sin actividad para filtros actuales.</td></tr>
                @endforelse
            </tbody></table>
        </div>
        <div class="divide-y divide-zinc-200 dark:divide-zinc-800 lg:hidden">
            @forelse($logs as $log)
                <article wire:key="audit-mobile-{{ $log->id }}" class="space-y-2 p-4"><div class="flex items-start justify-between gap-3"><div><strong class="block text-sm">{{ str($log->event ?: $log->accion)->replace(['.', '_'], ' ')->headline() }}</strong><span class="text-[10px] text-zinc-500">{{ $log->created_at?->format('d/m/Y H:i:s') }} · {{ ucfirst($log->modulo ?: 'Sistema') }}</span></div><span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $log->result === 'exitoso' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-400/10 dark:text-rose-200' }}">{{ ucfirst($log->result) }}</span></div><p class="text-xs">{{ $log->detalle ?: 'Sin detalle' }}</p><p class="text-[10px] text-zinc-500">Actor: {{ $log->usuario?->name ?? 'Sistema' }} · Afectado: {{ $log->usuarioObjetivo?->name ?? 'No aplica' }}</p><p class="truncate font-mono text-[10px] text-zinc-500">{{ $log->ip_address }} · {{ $log->user_agent }}</p>@if($log->metadata)<details><summary class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300">Ver cambios</summary><pre class="mt-1 overflow-auto rounded-lg bg-zinc-950 p-2 text-[10px] text-zinc-100">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre></details>@endif</article>
            @empty
                <div class="p-8 text-center text-sm text-zinc-500">Sin actividad para filtros actuales.</div>
            @endforelse
        </div>
    </section>

    <div class="agro-table-footer"><div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="$perPageOptions" tone="indigo" live compact /><span class="hidden text-zinc-500 sm:inline">{{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} de {{ $logs->total() }}</span></div><div class="min-w-0">{{ $logs->links('components.pagination') }}</div></div>
</div>
