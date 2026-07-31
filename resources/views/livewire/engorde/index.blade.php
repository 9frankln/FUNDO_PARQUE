<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <!-- Breadcrumbs / Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                Lotes de Engorde
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Administra lotes de cualquier especie y controla evolución de peso.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->tienePermiso('engorde', 'exportar'))
                <button wire:click="$set('showExportModal', true)"
                        class="flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-zinc-700 hover:bg-zinc-800">
                    <span>&#x1F4E5;</span> Resumen PDF
                </button>
                <button wire:click="openDetailedReportModal"
                        class="flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300">
                    Reporte detallado
                </button>
            @endif
            @if(auth()->user()->tienePermiso('engorde', 'crear'))
                <a href="{{ route('engorde.lote.create') }}" class="agro-button">
                    <span>➕</span> Crear Lote de Engorde
                </a>
            @endif
        </div>
    </div>

    <!-- Engorde Dashboard -->
    <x-engorde-dashboard :data="$dashboardData" />

    <!-- Filters and Search Bar -->
    <x-collapsible-filters :active="$hasActiveFilters"
                           title="Filtros de lotes"
                           description="Consulta lotes por periodo de inicio, código, nombre o estado."
                           id="engorde-filter-content"
                           reset="resetFilters"
                           loading-target="periodo,anio,mes,fechaDesde,fechaHasta,search,estado,perPage">
        <div class="border-t border-zinc-850/60 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500 text-sm">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Buscar por código o nombre...">
                </div>
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Estado</label>
                <x-filter-select model="estado" :options="['' => 'Todos los estados', 'activo' => 'Activos', 'cerrado' => 'Cerrados']" tone="amber" live compact />
            </div>
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Acceso rápido</label>
                <x-filter-select model="periodo" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']" tone="amber" live compact />
            </div>

            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Año</label>
                <x-filter-select model="anio" :options="['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all()" tone="amber" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Mes</label>
                <x-filter-select model="mes" :options="['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre']" tone="amber" live :disabled="$anio === ''" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <input type="date" wire:model.live="fechaDesde"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-amber-500/50 focus:ring-2 focus:ring-amber-500/20">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="fechaHasta"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-amber-500/50 focus:ring-2 focus:ring-amber-500/20">
            </div>
        </div>
    </x-collapsible-filters>

    <!-- Lotes Table -->
    <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                    <th class="p-4 whitespace-nowrap">Código</th>
                    <th class="p-4 whitespace-nowrap">Foto</th>
                    <th class="p-4 whitespace-nowrap">Nombre del Lote</th>
                    <th class="p-4 whitespace-nowrap">Fecha Inicio</th>
                    <th class="p-4 whitespace-nowrap">Fecha Fin</th>
                    <th class="p-4 whitespace-nowrap">Animales</th>
                    <th class="p-4 whitespace-nowrap">Estado</th>
                    <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                @forelse($lotes as $lote)
                    @php
                        $isRecent = $this->isRecentRecord('engorde.lotes', $lote->id);
                    @endphp
                    <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20' }}">
                        <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                            {{ $lote->codigo }}
                            <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <x-table-photo :path="$lote->foto_ruta" :frame="$lote->foto_encuadre" :alt="'Foto del lote '.$lote->codigo" />
                        </td>
                        <td class="p-4 whitespace-nowrap">{{ $lote->nombre ?? '-' }}</td>
                        <td class="p-4 whitespace-nowrap">{{ $lote->fecha_inicio->format('d/m/Y') }}</td>
                        <td class="p-4 whitespace-nowrap">{{ $lote->fecha_fin ? $lote->fecha_fin->format('d/m/Y') : 'En Curso' }}</td>
                        <td class="p-4 font-semibold text-emerald-400 whitespace-nowrap">{{ $lote->animales_count }} animales</td>
                        <td class="p-4 whitespace-nowrap">
                            <x-status-badge :value="$lote->estado" />
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                            <x-table-action type="view" :href="route('engorde.lote.show', $lote->id)" label="Ver lote" />
                            @if(auth()->user()->tienePermiso('engorde', 'actualizar'))
                                <x-table-action type="edit" :href="route('engorde.lote.edit', $lote->id)" />
                            @endif
                            @if(auth()->user()->tienePermiso('engorde', 'eliminar'))
                                <x-table-action type="delete" wire:click="solicitarEliminacion({{ $lote->id }})" />
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-zinc-550">
                            <div class="text-3xl">📭</div>
                            <div class="mt-2 font-bold text-sm">No se encontraron lotes de engorde</div>
                            <div class="text-xs text-zinc-500 mt-1">Crea un nuevo lote para comenzar el registro.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="amber" live compact />
        </div>
        <div class="min-w-0">
            {{ $lotes->links('components.pagination') }}
        </div>
    </div>

    @if($showExportModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ columns: @js($selectedColumns) }"
                 role="dialog" aria-modal="true" aria-label="Exportar lotes de engorde"
                 class="agro-dialog agro-dialog--md agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Exportar lotes de engorde</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">PDF respetará filtros y orden actuales.</p>
                    </div>
                    <button wire:click="$set('showExportModal', false)" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div>
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Formato</span>
                    <div class="flex items-center gap-3 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm font-semibold text-rose-900 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-600 text-xs font-black text-white">PDF</span>
                        <span>Documento PDF (.pdf)</span>
                    </div>
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
                    <button wire:click="$set('showExportModal', false)" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300">Cancelar</button>
                    <button type="button" x-on:click="$wire.exportar(columns)" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-wait" wire:target="exportar"
                            class="rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:from-indigo-500 hover:to-blue-500 disabled:cursor-wait disabled:opacity-60">
                        Generar PDF
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showDetailedReportModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ scope: @js($detailedReportScope), lots: @js(collect($detailedReportLotIds)->map(fn ($id) => (string) $id)->values()), columns: @js($detailedReportColumns) }"
                 role="dialog" aria-modal="true" aria-label="Reporte general detallado"
                 class="agro-dialog agro-dialog--lg agro-dialog--scroll space-y-5 p-4 sm:p-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Reporte general detallado</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Agrupa lotes elegidos y muestra datos completos de cada animal en PDF horizontal.</p>
                </div>

                <div>
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Lotes a incluir</span>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label :class="scope === 'filtered' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 dark:bg-emerald-400/15 dark:text-emerald-100' : 'border-slate-200 dark:border-slate-700'" class="cursor-pointer rounded-xl border p-3 text-sm font-semibold">
                            <input type="radio" x-model="scope" value="filtered" class="sr-only"> Todos los resultados filtrados
                            <span class="mt-1 block text-xs font-normal opacity-65">{{ $detailedReportLots->count() }} lotes disponibles</span>
                        </label>
                        <label :class="scope === 'selected' ? 'border-rose-500 bg-rose-50 text-rose-900 dark:bg-rose-400/15 dark:text-rose-100' : 'border-slate-200 dark:border-slate-700'" class="cursor-pointer rounded-xl border p-3 text-sm font-semibold">
                            <input type="radio" x-model="scope" value="selected" class="sr-only"> Escoger lotes
                            <span class="mt-1 block text-xs font-normal opacity-65" x-text="`${lots.length} seleccionados`"></span>
                        </label>
                    </div>
                </div>

                <div x-cloak x-show="scope === 'selected'" class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Selección de lotes</span>
                        <button type="button" x-on:click="lots = lots.length === {{ $detailedReportLots->count() }} ? [] : @js($detailedReportLots->pluck('id')->map(fn ($id) => (string) $id)->values())" class="text-xs font-semibold text-rose-600 dark:text-rose-400">Seleccionar todos / limpiar</button>
                    </div>
                    <div class="grid max-h-52 gap-2 overflow-y-auto sm:grid-cols-2">
                        @foreach($detailedReportLots as $reportLot)
                            <label :class="lots.includes('{{ $reportLot->id }}') ? 'border-rose-400 bg-rose-50 dark:bg-rose-400/10' : 'border-slate-200 dark:border-slate-700'" class="flex cursor-pointer items-center gap-3 rounded-lg border p-3">
                                <input type="checkbox" x-model="lots" value="{{ $reportLot->id }}" class="agro-checkbox h-4 w-4 rounded">
                                <span class="min-w-0"><strong class="block text-sm text-slate-900 dark:text-white">{{ $reportLot->codigo }}</strong><small class="block truncate text-slate-500">{{ $reportLot->nombre ?: 'Sin nombre' }} · {{ $reportLot->animales_count }} animales</small></span>
                            </label>
                        @endforeach
                    </div>
                    @error('detailedReportLotIds') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Datos por animal</span>
                        <button type="button" x-on:click="columns = columns.length === {{ count($detailedReportAvailableColumns) }} ? [] : @js(array_keys($detailedReportAvailableColumns))" class="text-xs font-semibold text-violet-600 dark:text-violet-400">Seleccionar todas</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach($detailedReportAvailableColumns as $key => $label)
                            <label :class="columns.includes('{{ $key }}') ? 'border-violet-500 bg-violet-100 text-violet-950 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium">
                                <input type="checkbox" x-model="columns" value="{{ $key }}" class="sr-only">
                                <span :class="columns.includes('{{ $key }}') ? 'border-violet-700 bg-violet-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2">
                                    <svg x-cloak x-show="columns.includes('{{ $key }}')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('detailedReportColumns') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    @error('detailedReportColumns.*') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button wire:click="$set('showDetailedReportModal', false)" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300">Cancelar</button>
                    <button type="button" x-on:click="$wire.exportDetailedReport(scope, lots, columns)" wire:loading.attr="disabled" wire:target="exportDetailedReport" class="rounded-xl bg-rose-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-500 disabled:opacity-60">Generar PDF detallado</button>
                </div>
            </div>
        </div>
    @endif
</x-recent-record-host>
