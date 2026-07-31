<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                Control de Leche
            </h1>
            <p class="mt-1 text-sm text-zinc-400">Compara turnos, volumen y rendimiento del ordeño.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->tienePermiso('leche', 'exportar'))
                <button wire:click="$set('showExportModal', true)"
                        class="flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition duration-200 hover:border-zinc-700 hover:bg-zinc-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 12-4-4m4 4 4-4M5 20h14" /></svg>
                    Exportar
                </button>
            @endif
            @if(auth()->user()->tienePermiso('leche', 'crear'))
                <a href="{{ route('leche.create') }}" class="agro-button">
                    <span class="text-lg leading-none">+</span> Nuevo registro
                </a>
            @endif
        </div>
    </div>

    <x-leche-dashboard :data="$dashboardData" />

    <div x-data="{ filtersOpen: @js($hasActiveFilters) }"
         class="space-y-4 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-4 sm:p-6">
        <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="text-sm font-bold text-zinc-100">Filtros de producción</h2>
                <p class="text-xs text-zinc-500">Refina el historial por fecha, turno, volumen o evidencia.</p>
            </div>
            <div class="flex w-full flex-wrap items-center gap-2 md:w-auto md:justify-end">
                <span wire:loading.inline-flex wire:target="periodo,anio,mes,fechaDesde,fechaHasta,turno,tipoRegistro,litrosMin,litrosMax,observacion,conFoto,perPage,sort"
                      class="items-center gap-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                    <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></span>
                    Actualizando
                </span>
                @if($hasActiveFilters)
                    <span class="rounded-full border border-emerald-500/25 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                        Activos
                    </span>
                @endif
                <button wire:click="resetFilters" @disabled(! $hasActiveFilters)
                        class="rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-zinc-400 transition duration-200 hover:border-zinc-700 hover:bg-zinc-900 hover:text-zinc-300 disabled:cursor-not-allowed disabled:opacity-40">
                    Limpiar filtros
                </button>
                <button type="button" x-on:click="filtersOpen = ! filtersOpen"
                        x-bind:aria-expanded="filtersOpen"
                        aria-controls="milk-filter-content"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-emerald-600 bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700 sm:flex-none dark:border-emerald-500 dark:bg-emerald-500 dark:text-emerald-950 dark:hover:bg-emerald-400">
                    <span x-text="filtersOpen ? 'Ocultar filtros' : 'Mostrar filtros'">{{ $hasActiveFilters ? 'Ocultar filtros' : 'Mostrar filtros' }}</span>
                    <svg class="h-4 w-4 transition-transform duration-200" x-bind:class="filtersOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="milk-filter-content" x-cloak x-show="filtersOpen"
             x-transition:enter="transition duration-200 ease-out"
             x-transition:enter-start="-translate-y-1 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-1 opacity-0"
             class="space-y-4">
        <div class="relative w-full md:max-w-md">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm text-zinc-500">&#x1F50D;</span>
            <input type="search" wire:model.live.debounce.500ms="observacion"
                   aria-label="Buscar en observaciones"
                   placeholder="Buscar observaciones (mín. 2 caracteres)"
                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-2.5 pl-10 pr-4 text-sm text-zinc-200 placeholder-zinc-600 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
        </div>

        <div class="space-y-4 border-t border-zinc-800 pt-4">
            <div>
                <h3 class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-400">Periodo histórico</h3>
                <p class="mt-1 text-xs text-zinc-500">Usa un acceso rápido, selecciona año y mes, o define un rango exacto.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Acceso rápido</label>
                    <x-filter-select model="periodo" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']" tone="emerald" live />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Año</label>
                    <x-filter-select model="anio" :options="['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all()" tone="emerald" live />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Mes</label>
                    <x-filter-select model="mes" :options="['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre']" tone="emerald" live :disabled="$anio === ''" />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                    <input type="date" wire:model.live="fechaDesde"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                    <input type="date" wire:model.live="fechaHasta"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
                </div>
            </div>
        </div>

        <div class="space-y-4 border-t border-zinc-800 pt-4">
            <h3 class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-500">Detalles de producción</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Turno</label>
                    <x-filter-select model="turno" :options="['' => 'Todos los turnos', 'manana' => 'Mañana', 'tarde' => 'Tarde', 'noche' => 'Noche']" tone="emerald" live />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo de registro</label>
                    <x-filter-select model="tipoRegistro" :options="['' => 'Todos los tipos', 'individual' => 'Individual', 'lote' => 'Lote']" tone="emerald" live />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Litros mínimos</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.350ms="litrosMin" placeholder="0.00"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Litros máximos</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.350ms="litrosMax" placeholder="Sin límite"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Evidencia fotográfica</label>
                    <x-filter-select model="conFoto" :options="['' => 'Todos', '1' => 'Con foto', '0' => 'Sin foto']" tone="emerald" live />
                </div>
            </div>
        </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950 text-xs font-bold uppercase tracking-wider text-zinc-400">
                        <th class="p-4 whitespace-nowrap">
                            <button wire:click="sort('fecha')" class="transition hover:text-zinc-200">Fecha {!! $sortBy === 'fecha' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : '' !!}</button>
                        </th>
                        <th class="p-4 whitespace-nowrap">Foto</th>
                        <th class="p-4 whitespace-nowrap">
                            <button wire:click="sort('turno')" class="transition hover:text-zinc-200">Turno {!! $sortBy === 'turno' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : '' !!}</button>
                        </th>
                        <th class="p-4 whitespace-nowrap">
                            <button wire:click="sort('tipo_registro')" class="transition hover:text-zinc-200">Tipo {!! $sortBy === 'tipo_registro' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : '' !!}</button>
                        </th>
                        <th class="p-4 text-right whitespace-nowrap">
                            <button wire:click="sort('cantidad_vacas')" class="transition hover:text-zinc-200">Vacas {!! $sortBy === 'cantidad_vacas' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : '' !!}</button>
                        </th>
                        <th class="p-4 text-right whitespace-nowrap">
                            <button wire:click="sort('litros_total')" class="transition hover:text-zinc-200">Total {!! $sortBy === 'litros_total' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : '' !!}</button>
                        </th>
                        <th class="p-4 min-w-52">Observaciones</th>
                        <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                    @forelse($ordenos as $ordeno)
                        @php
                            $isRecent = $this->isRecentRecord('leche.ordenos', $ordeno->id);
                        @endphp
                        <tr wire:key="ordeno-{{ $ordeno->id }}" class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20' }}">
                            <td class="p-4 whitespace-nowrap">
                                <div class="font-bold text-zinc-100">
                                    {{ $ordeno->fecha->format('d/m/Y') }}
                                    <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                                </div>
                                <div class="mt-0.5 text-[10px] uppercase tracking-wide text-zinc-500">{{ $ordeno->fecha->translatedFormat('l') }}</div>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <x-table-photo :path="$ordeno->foto_ruta_diaria" :frame="$ordeno->foto_encuadre_diario" :alt="'Foto del ordeño del '.$ordeno->fecha->format('d/m/Y')" />
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <x-status-badge :value="$ordeno->turno" :label="\App\Models\Ordeno::turnoLabel($ordeno->turno)" />
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <x-status-badge :value="$ordeno->tipo_registro" :label="\App\Models\Ordeno::tipoLabel($ordeno->tipo_registro)" />
                            </td>
                            <td class="p-4 text-right font-semibold text-zinc-200 whitespace-nowrap">{{ number_format($ordeno->cantidad_vacas) }}</td>
                            <td class="p-4 text-right whitespace-nowrap"><span class="font-extrabold text-emerald-700 dark:text-emerald-400">{{ number_format((float) $ordeno->litros_total, 2) }}</span> <span class="text-xs text-zinc-500">L</span></td>
                            <td class="max-w-xs p-4 text-zinc-400">
                                <p class="line-clamp-2" title="{{ $ordeno->observaciones }}">{{ $ordeno->observaciones ?: 'Sin observaciones' }}</p>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()->tienePermiso('leche', 'leer'))
                                        <x-table-action type="view" :href="route('leche.show', $ordeno->id)" label="Ver detalle" />
                                    @endif
                                    @if(auth()->user()->tienePermiso('leche', 'actualizar'))
                                        <x-table-action type="edit" :href="route('leche.edit', $ordeno->id)" />
                                    @endif
                                    @if(auth()->user()->tienePermiso('leche', 'eliminar'))
                                        <x-table-action type="delete" wire:click="solicitarEliminacion({{ $ordeno->id }})" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-zinc-800 bg-zinc-950 text-zinc-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 13h6m-3-3v6m8-4a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" /></svg>
                                </div>
                                <div class="mt-3 font-bold text-zinc-200">{{ $hasActiveFilters ? 'No hay resultados para estos filtros' : 'Aún no hay registros de ordeño' }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ $hasActiveFilters ? 'Ajusta o limpia los filtros para ampliar la búsqueda.' : 'Crea el primer registro para comenzar el control de producción.' }}</div>
                                @if($hasActiveFilters)
                                    <button wire:click="resetFilters" class="mt-4 text-xs font-bold text-emerald-700 hover:text-emerald-600 dark:text-emerald-400 dark:hover:text-emerald-300">Limpiar filtros</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="emerald" live compact />
        </div>
        <div class="min-w-0">
            {{ $ordenos->links('components.pagination') }}
        </div>
    </div>

    @if($showExportModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ format: @js($exportFormat), columns: @js($selectedColumns) }"
                   role="dialog" aria-modal="true" aria-label="Exportar producción de leche"
                   class="agro-dialog agro-dialog--md agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Exportar producción de leche</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Se respetarán los filtros y el orden actuales.</p>
                    </div>
                    <button wire:click="$set('showExportModal', false)" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div>
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Formato</span>
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="format === 'xlsx' ? 'border-sky-500 bg-sky-100 text-sky-950 shadow-sm shadow-sky-500/20 dark:border-sky-400 dark:bg-sky-400/20 dark:text-sky-50' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-sky-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'" class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-semibold transition">
                            <input type="radio" x-model="format" value="xlsx" class="sr-only">
                            <span :class="format === 'xlsx' ? 'border-sky-600 bg-sky-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 items-center justify-center rounded-full border-2">
                                <svg x-cloak x-show="format === 'xlsx'" class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m5 12 4 4L19 6" /></svg>
                            </span>
                            Excel (.xlsx)
                        </label>
                        <label :class="format === 'pdf' ? 'border-rose-500 bg-rose-100 text-rose-950 shadow-sm shadow-rose-500/20 dark:border-rose-400 dark:bg-rose-400/20 dark:text-rose-50' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-rose-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'" class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-semibold transition">
                            <input type="radio" x-model="format" value="pdf" class="sr-only">
                            <span :class="format === 'pdf' ? 'border-rose-600 bg-rose-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 items-center justify-center rounded-full border-2">
                                <svg x-cloak x-show="format === 'pdf'" class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m5 12 4 4L19 6" /></svg>
                            </span>
                            PDF (.pdf)
                        </label>
                    </div>
                    @error('exportFormat') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Columnas</span>
                        <button type="button" x-on:click="columns = columns.length === {{ count($availableColumns) }} ? [] : @js(array_keys($availableColumns))" class="text-xs font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">Seleccionar todas</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach($availableColumns as $key => $label)
                            <label :class="columns.includes('{{ $key }}') ? 'border-violet-500 bg-violet-100 text-violet-950 shadow-sm ring-1 ring-violet-500/20 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-slate-200 bg-white text-slate-600 hover:border-violet-300 hover:bg-violet-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-violet-400/10'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition">
                                <input type="checkbox" x-model="columns" value="{{ $key }}" class="sr-only">
                                <span :class="columns.includes('{{ $key }}') ? 'border-violet-700 bg-violet-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition">
                                    <svg x-cloak x-show="columns.includes('{{ $key }}')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedColumns') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    @error('selectedColumns.*') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-end">
                    <button wire:click="$set('showExportModal', false)" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300 dark:hover:bg-rose-400/20">Cancelar</button>
                    <button type="button" x-on:click="$wire.exportar(format, columns)" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-wait" wire:target="exportar"
                             class="rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:from-indigo-500 hover:to-blue-500 disabled:cursor-wait disabled:opacity-60">
                        Generar reporte
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-recent-record-host>
