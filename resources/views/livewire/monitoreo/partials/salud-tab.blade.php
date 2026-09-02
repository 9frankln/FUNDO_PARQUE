@php
    $sanidadAnyFilter = $searchSanidad || $sanidadCategoria || $sanidadEstado || $sanidadFechaDesde || $sanidadFechaHasta;
    $categoryTone = fn (string $value) => match($value) {
        'lesion' => 'rose', 'enfermedad' => 'amber', 'parasitos' => 'violet',
        'vacunacion' => 'sky', 'suplementacion' => 'lime', 'procedimiento' => 'orange',
        'control' => 'teal', default => 'zinc',
    };
    $stateTone = fn (string $value) => match($value) {
        'completado' => 'emerald', 'critico' => 'rose', 'cuarentena' => 'amber', default => 'sky',
    };
@endphp

<x-collapsible-filters :active="$sanidadAnyFilter" title="Filtros de salud" description="Busca por animal, evento, hallazgo, producto, seguimiento o fecha." id="salud-filter-content" reset="resetSanidadFilters">
    <div class="grid w-full grid-cols-1 items-end gap-3 border-t border-zinc-200 pt-4 sm:grid-cols-2 lg:grid-cols-5 dark:border-zinc-800">
        <div class="lg:col-span-2">
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
            <input type="search" wire:model.live.debounce.300ms="searchSanidad" placeholder="Código, animal, hallazgo, producto..."
                   class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
        </div>
        <div>
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Evento</label>
            <x-filter-select model="sanidadCategoria" :options="['' => 'Todos'] + \App\Models\SanidadRegistro::CATEGORIAS" tone="emerald" live compact />
        </div>
        <div>
            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Seguimiento</label>
            <x-filter-select model="sanidadEstado" :options="['' => 'Todos'] + \App\Models\SanidadRegistro::ESTADOS_SEGUIMIENTO" tone="emerald" live compact />
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div><label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label><x-date-picker model="sanidadFechaDesde" placeholder="dd/mm/aaaa" compact /></div>
            <div><label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label><x-date-picker model="sanidadFechaHasta" placeholder="dd/mm/aaaa" compact /></div>
        </div>
    </div>
</x-collapsible-filters>

<div class="agro-record-surface hidden overflow-hidden rounded-2xl border xl:block">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1080px] border-collapse text-left">
            <thead>
                <tr class="agro-record-header border-b text-[10px] font-bold uppercase tracking-wider">
                    <th class="w-28 whitespace-nowrap px-3 py-2.5">Fecha</th>
                    <th class="w-52 px-3 py-2.5">Animal</th>
                    <th class="w-44 px-3 py-2.5">Evento</th>
                    <th class="min-w-[170px] px-3 py-2.5">Hallazgo / motivo</th>
                    <th class="min-w-[190px] px-3 py-2.5">Atención y dosis</th>
                    <th class="w-36 whitespace-nowrap px-3 py-2.5">Estado</th>
                    <th class="w-48 whitespace-nowrap px-3 py-2.5 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="agro-record-list text-xs text-zinc-300">
                @forelse($sanidades as $san)
                    @php $isRecent = $this->isRecentRecord('monitoreo.sanidad', $san->id); @endphp
                    <tr wire:key="salud-{{ $san->id }}" class="align-top transition {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/20' }}">
                        <td class="whitespace-nowrap px-3 py-3 font-bold text-zinc-100">
                            {{ $san->fecha_evento->format('d/m/Y') }}
                            <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <x-table-photo :path="$san->animal?->foto_ruta" :frame="$san->animal?->foto_encuadre" :alt="$san->animal?->nombre ?: 'Animal'" size="sm" />
                                <div class="min-w-0">
                                    @if($san->animal)
                                        <a href="{{ route('animal.show', $san->animal->id) }}" wire:navigate class="block truncate font-black text-zinc-100 hover:text-emerald-400">{{ $san->animal->arete }}</a>
                                        <span class="block truncate text-[10px] text-zinc-500">{{ $san->animal->nombre ?: 'Sin nombre' }}</span>
                                    @else <span class="text-zinc-500">Animal archivado</span> @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <x-status-badge :value="$san->categoria_salud" :label="$san->categoria_salud_label" :tone="$categoryTone($san->categoria_salud)" class="text-[10px]" />
                            <p class="mt-1 text-[10px] font-semibold text-zinc-500">{{ ucfirst(str_replace('_', ' ', $san->subtipo ?: 'otro')) }}</p>
                            @if($san->severidad)<p class="mt-0.5 text-[10px] text-zinc-500">Severidad: {{ ucfirst($san->severidad) }}</p>@endif
                            @if($san->ubicacion_corporal)<p class="mt-0.5 text-[10px] text-zinc-500">Zona: {{ $san->ubicacion_corporal }}</p>@endif
                        </td>
                        <td class="px-3 py-3">
                            <p class="line-clamp-3 break-words text-[11px] leading-5 text-zinc-300">{{ $san->sintomas_diagnostico ?: '-' }}</p>
                            @if($san->proposito)<p class="mt-1 text-[10px] text-zinc-500">{{ $san->proposito }}</p>@endif
                        </td>
                        <td class="px-3 py-3">
                            <p class="line-clamp-2 break-words text-[11px] leading-5 text-zinc-300">{{ $san->tratamiento ?: $san->producto_marca ?: 'Sin indicaciones adicionales' }}</p>
                            @if($san->dosisPlan->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($san->dosisPlan as $dosis)
                                        <span class="inline-flex items-center gap-1 rounded-lg px-1.5 py-1 text-[9px] font-bold {{ $dosis->aplicada ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-300' }}">
                                            D{{ $dosis->numero }} {{ $dosis->aplicada ? '✔' : $dosis->fecha_programada->format('d/m') }}
                                            @if(! $dosis->aplicada && auth()->user()->tienePermiso('monitoreo', 'actualizar'))
                                                <button type="button" wire:click="marcarDosisAplicada({{ $dosis->id }})" class="rounded bg-emerald-500/20 px-1 hover:bg-emerald-500/35">Aplicar</button>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-3">
                            <x-status-badge :value="$san->estado_seguimiento" :label="$san->estado_seguimiento_label" :tone="$stateTone($san->estado_seguimiento)" class="text-[10px]" />
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                                @if(auth()->user()->tienePermiso('monitoreo', 'leer'))<x-table-action type="view" wire:click="openVerCasoModal({{ $san->id }})" label="Ver detalle" />@endif
                                @if(auth()->user()->tienePermiso('monitoreo', 'actualizar') && $san->estado_seguimiento !== 'completado')
                                    <button type="button" wire:click="openRecuperarCasoModal({{ $san->id }})" title="Marcar como recuperado / curado"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-emerald-600/40 bg-emerald-100 px-2.5 text-xs font-bold text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-200 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        Curado
                                    </button>
                                @endif
                                @if(auth()->user()->tienePermiso('monitoreo', 'actualizar'))<x-table-action type="edit" :href="route('monitoreo.sanidad.edit', $san->id)" />@endif
                                @if(auth()->user()->tienePermiso('monitoreo', 'eliminar'))<x-table-action type="delete" wire:click="solicitarEliminacionSanidad({{ $san->id }})" />@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-12 text-center text-zinc-500"><p class="text-sm font-bold">Aún no hay eventos de salud.</p><p class="mt-1 text-xs">Usa “Registrar evento” para comenzar el historial.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="agro-record-surface overflow-hidden rounded-2xl border xl:hidden">
    <div class="agro-record-list">
        @forelse($sanidades as $san)
            <article wire:key="salud-card-{{ $san->id }}" class="space-y-3 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <x-table-photo :path="$san->animal?->foto_ruta" :frame="$san->animal?->foto_encuadre" :alt="$san->animal?->nombre ?: 'Animal'" />
                        <div class="min-w-0">
                            @if($san->animal)<a href="{{ route('animal.show', $san->animal->id) }}" wire:navigate class="block truncate text-xs font-black text-zinc-100 hover:text-emerald-400">{{ $san->animal->arete }} · {{ $san->animal->nombre ?: 'Sin nombre' }}</a>@endif
                            <p class="mt-1 text-[10px] text-zinc-500">{{ $san->fecha_evento->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <x-status-badge :value="$san->estado_seguimiento" :label="$san->estado_seguimiento_label" :tone="$stateTone($san->estado_seguimiento)" class="shrink-0 text-[10px]" />
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :value="$san->categoria_salud" :label="$san->categoria_salud_label" :tone="$categoryTone($san->categoria_salud)" class="text-[10px]" />
                    <span class="text-[10px] text-zinc-500">{{ ucfirst(str_replace('_', ' ', $san->subtipo ?: 'otro')) }}</span>
                </div>
                <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-zinc-800">
                    <div class="p-3"><span class="text-[9px] font-bold uppercase text-zinc-500">Hallazgo</span><p class="mt-1 text-xs leading-5 text-zinc-300">{{ $san->sintomas_diagnostico ?: '-' }}</p></div>
                    <div class="border-l border-zinc-800 p-3"><span class="text-[9px] font-bold uppercase text-zinc-500">Atención</span><p class="mt-1 text-xs leading-5 text-zinc-300">{{ $san->tratamiento ?: $san->producto_marca ?: '-' }}</p></div>
                </div>
                @if($san->dosisPlan->isNotEmpty())
                    <div class="flex flex-wrap gap-1">@foreach($san->dosisPlan as $dosis)<span class="rounded-lg px-2 py-1 text-[10px] font-bold {{ $dosis->aplicada ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-300' }}">D{{ $dosis->numero }} · {{ $dosis->aplicada ? 'Aplicada' : $dosis->fecha_programada->format('d/m/Y') }}</span>@endforeach</div>
                @endif
                <div class="flex justify-end gap-2">
                    @if(auth()->user()->tienePermiso('monitoreo', 'leer'))<x-table-action type="view" wire:click="openVerCasoModal({{ $san->id }})" label="Ver detalle" />@endif
                    @if(auth()->user()->tienePermiso('monitoreo', 'actualizar') && $san->estado_seguimiento !== 'completado')
                        <button type="button" wire:click="openRecuperarCasoModal({{ $san->id }})" title="Marcar como recuperado / curado"
                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-emerald-600/40 bg-emerald-100 px-2.5 text-xs font-bold text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-200 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Curado
                        </button>
                    @endif
                    @if(auth()->user()->tienePermiso('monitoreo', 'actualizar'))<x-table-action type="edit" :href="route('monitoreo.sanidad.edit', $san->id)" />@endif
                    @if(auth()->user()->tienePermiso('monitoreo', 'eliminar'))<x-table-action type="delete" wire:click="solicitarEliminacionSanidad({{ $san->id }})" />@endif
                </div>
            </article>
        @empty
            <div class="p-10 text-center text-zinc-500"><p class="text-sm font-bold">Aún no hay eventos de salud.</p></div>
        @endforelse
    </div>
</div>
<div class="agro-table-footer">
    <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageOptions()" tone="emerald" live compact /></div>
    <div class="min-w-0">{{ $sanidades->links('components.pagination') }}</div>
</div>


