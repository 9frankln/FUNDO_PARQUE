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
                <button wire:click="openExportModal"
                    class="px-3.5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-300 text-sm font-semibold transition duration-200 flex items-center gap-2 shadow-xs">
                    <span>&#x1F4E5;</span> Exportar
                </button>
            @endif
            @if(auth()->user()->tienePermiso('leche', 'crear'))
                <a wire:navigate href="{{ route('leche.create') }}" class="agro-button">
                    <span class="text-lg leading-none">+</span> Nuevo registro
                </a>
            @endif
        </div>
    </div>

    <x-leche-dashboard :data="$dashboardData" />

    <x-collapsible-filters :active="$hasActiveFilters"
                           title="Filtros de producción"
                           description="Refina el historial por fecha, turno, volumen o evidencia."
                           id="milk-filter-content"
                           reset="resetFilters"
                           loading-target="periodo,anio,mes,fechaDesde,fechaHasta,turno,tipoRegistro,litrosMin,litrosMax,observacion,conFoto,perPage,sort">
        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-3.5 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 items-end">
            <div class="sm:col-span-2 md:col-span-3 lg:col-span-2 xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-zinc-400">&#x1F50D;</span>
                    <input type="search" wire:model.live.debounce.500ms="observacion"
                           aria-label="Buscar en observaciones"
                           placeholder="Buscar observaciones..."
                           class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Turno</label>
                <x-filter-select model="turno" :options="['' => 'Todos los turnos', 'manana' => 'Mañana', 'tarde' => 'Tarde', 'noche' => 'Noche']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo de registro</label>
                <x-filter-select model="tipoRegistro" :options="['' => 'Todos los tipos', 'individual' => 'Individual', 'lote' => 'Lote']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Evidencia foto</label>
                <x-filter-select model="conFoto" :options="['' => 'Todos', '1' => 'Con foto', '0' => 'Sin foto']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Acceso rápido</label>
                <x-filter-select model="periodo" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'ÃÅ¡ltimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']" tone="emerald" live compact />
            </div>

            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Año</label>
                <x-filter-select model="anio" :options="['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all()" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Mes</label>
                <x-filter-select model="mes" :options="['' => 'Todos los meses'] + \App\Support\PaginationOptions::months()" tone="emerald" live :disabled="$anio === ''" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <x-date-picker model="fechaDesde" placeholder="dd/mm/aaaa" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <x-date-picker model="fechaHasta" placeholder="dd/mm/aaaa" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Litros mín.</label>
                <input type="number" min="0" step="0.01" wire:model.live.debounce.350ms="litrosMin" placeholder="0.00"
                       class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 px-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Litros máx.</label>
                <input type="number" min="0" step="0.01" wire:model.live.debounce.350ms="litrosMax" placeholder="Sin límite"
                       class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 px-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
            </div>
        </div>
    </x-collapsible-filters>

    <div class="agro-record-surface overflow-hidden rounded-2xl border">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead class="agro-record-header text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4 whitespace-nowrap">
                            <x-table-sort-header field="fecha" :sort-by="$sortBy" :sort-dir="$sortDir" wire:click="sort('fecha')">
                                Fecha
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 whitespace-nowrap">Foto</th>
                        <th class="p-4 whitespace-nowrap">
                            <x-table-sort-header field="turno" :sort-by="$sortBy" :sort-dir="$sortDir" wire:click="sort('turno')">
                                Turno
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 whitespace-nowrap">
                            <x-table-sort-header field="tipo_registro" :sort-by="$sortBy" :sort-dir="$sortDir" wire:click="sort('tipo_registro')">
                                Tipo
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 text-right whitespace-nowrap">
                            <x-table-sort-header field="cantidad_vacas" :sort-by="$sortBy" :sort-dir="$sortDir" align="right" wire:click="sort('cantidad_vacas')">
                                Vacas
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 text-right whitespace-nowrap">
                            <x-table-sort-header field="litros_total" :sort-by="$sortBy" :sort-dir="$sortDir" align="right" wire:click="sort('litros_total')">
                                Total
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 min-w-52">Observaciones</th>
                        <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="agro-record-list text-sm">
                    @forelse($ordenos as $ordeno)
                        @php
                            $isRecent = $this->isRecentRecord('leche.ordenos', $ordeno->id);
                        @endphp
                        <tr wire:key="ordeno-{{ $ordeno->id }}" class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : '' }}">
                            <td class="p-4 whitespace-nowrap">
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">
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
                            <td class="p-4 text-right font-semibold text-zinc-800 dark:text-zinc-200 whitespace-nowrap">{{ number_format($ordeno->cantidad_vacas) }}</td>
                            <td class="p-4 text-right whitespace-nowrap"><span class="font-extrabold text-emerald-700 dark:text-emerald-400">{{ number_format((float) $ordeno->litros_total, 2) }}</span> <span class="text-xs text-zinc-500">L</span></td>
                            <td class="max-w-xs p-4 text-zinc-600 dark:text-zinc-400">
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
                                <x-empty-state
                                    type="search"
                                    :title="$hasActiveFilters ? 'No hay resultados para estos filtros' : 'Aún no hay registros de ordeño'"
                                    :description="$hasActiveFilters ? 'Ajusta o limpia los filtros para ampliar la búsqueda.' : 'Crea el primer registro para comenzar el control de producción.'"
                                />
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
            <x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageOptions()" tone="emerald" live compact />
        </div>
        <div class="min-w-0">
            {{ $ordenos->links('components.pagination') }}
        </div>
    </div>

    <x-pdf-preview-modal
        :show-export-modal="$showExportModal"
        :export-step="$exportStep"
        :pdf-preview-data="$pdfPreviewData"
        :pdf-preview-token="$pdfPreviewToken"
        :pdf-preview-filename="$pdfPreviewFilename"
        :pdf-preview-title="$pdfPreviewTitle"
        :pdf-preview-row-count="$pdfPreviewRowCount"
        :pdf-preview-page-count="$pdfPreviewPageCount"
        :pdf-include-signatures="$pdfIncludeSignatures"
        :pdf-scale="$pdfScale"
        :has-pdf-customization="true"
    >
        <div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Exportar producción de leche</h3>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Se respetarán los filtros y el orden actuales.</p>
                </div>
                <button type="button" wire:click="closeExportModal" class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-white" aria-label="Cerrar modal">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="mt-4">
                <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Formato</span>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-semibold transition {{ $exportFormat === 'xlsx' ? 'border-sky-500 bg-sky-100 text-sky-950 shadow-sm shadow-sky-500/20 dark:border-sky-400 dark:bg-sky-400/20 dark:text-sky-50' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-sky-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                        <input type="radio" wire:model.live="exportFormat" value="xlsx" class="sr-only">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full border-2 {{ $exportFormat === 'xlsx' ? 'border-sky-600 bg-sky-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900' }}">
                            @if($exportFormat === 'xlsx')
                                <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                            @endif
                        </span>
                        Excel (.xlsx)
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-semibold transition {{ $exportFormat === 'pdf' ? 'border-emerald-500 bg-emerald-100 text-emerald-950 shadow-sm shadow-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-400/20 dark:text-emerald-50' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-emerald-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                        <input type="radio" wire:model.live="exportFormat" value="pdf" class="sr-only">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full border-2 {{ $exportFormat === 'pdf' ? 'border-emerald-600 bg-emerald-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900' }}">
                            @if($exportFormat === 'pdf')
                                <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                            @endif
                        </span>
                        PDF (.pdf)
                    </label>
                </div>
                @error("exportFormat") <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
            </div>

            @if($exportFormat === 'pdf')
                <div class="mt-4 rounded-xl border border-emerald-500/30 bg-emerald-50/40 p-3 dark:border-emerald-500/20 dark:bg-emerald-950/20 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <span class="block text-xs font-bold text-emerald-900 dark:text-emerald-200">Compactación de texto y celdas</span>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Reduce el tamaño de fuente y padding para mostrar más registros por página.</p>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-1 w-full sm:w-auto rounded-lg border border-emerald-300 bg-white p-1 text-xs font-bold dark:border-emerald-700 dark:bg-zinc-800 shadow-2xs">
                            <button type="button" wire:click="$set('pdfScale', '45')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ in_array($pdfScale, ['40', '45'], true) ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-emerald-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                45% <span class="block text-[9px] font-medium opacity-80 sm:inline">Mín</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '55')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ in_array($pdfScale, ['50', '55'], true) ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-emerald-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                55% <span class="block text-[9px] font-medium opacity-80 sm:inline">Hiper</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '65')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '65' ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-emerald-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                65% <span class="block text-[9px] font-medium opacity-80 sm:inline">S-Ultra</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '75')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '75' ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-emerald-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                75% <span class="block text-[9px] font-medium opacity-80 sm:inline">Ultra</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '85')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '85' ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-emerald-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                85% <span class="block text-[9px] font-medium opacity-80 sm:inline">Comp</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '100')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '100' ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-emerald-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                100% <span class="block text-[9px] font-medium opacity-80 sm:inline">Est</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-emerald-200/60 pt-2.5 dark:border-emerald-800/60">
                        <div>
                            <span class="block text-xs font-bold text-emerald-900 dark:text-emerald-200">Bloque de firmas oficiales</span>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Incluye sellos y firmas digitales configuradas al final del documento.</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" wire:model.live="pdfIncludeSignatures" class="sr-only peer">
                            <div class="w-9 h-5 bg-zinc-300 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>
            @endif

            <div class="mt-4">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Columnas</span>
                    <button type="button" wire:click="$set('selectedColumns', {{ count($selectedColumns) === count($availableColumns) ? '[]' : json_encode(array_keys($availableColumns)) }})" class="text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">
                        {{ count($selectedColumns) === count($availableColumns) ? 'Deseleccionar todas' : 'Seleccionar todas' }}
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach($availableColumns as $key => $label)
                        @php $isChecked = in_array($key, $selectedColumns, true); @endphp
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition {{ $isChecked ? 'border-emerald-500 bg-emerald-100 text-emerald-950 shadow-sm ring-1 ring-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-400/20 dark:text-emerald-50' : 'border-zinc-200 bg-white text-zinc-600 hover:border-emerald-300 hover:bg-emerald-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-emerald-400/10' }}">
                            <input type="checkbox" wire:model.live="selectedColumns" value="{{ $key }}" class="sr-only">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition {{ $isChecked ? 'border-emerald-700 bg-emerald-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900' }}">
                                @if($isChecked)
                                    <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                @endif
                            </span>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error("selectedColumns") <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                @error("selectedColumns.*") <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-4 mt-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" wire:click="closeExportModal" class="inline-flex h-10 items-center justify-center px-4 rounded-xl border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 text-xs sm:text-sm font-bold transition active:scale-95 cursor-pointer">Cancelar</button>
                <button type="button" wire:click="exportar" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-wait" wire:target="exportar"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-xs sm:text-sm font-extrabold text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-500 active:scale-95 disabled:cursor-wait disabled:opacity-60 cursor-pointer">
                    <span wire:loading.remove wire:target="exportar">{{ $exportFormat === 'pdf' ? 'Ver Vista Previa PDF' : 'Descargar Excel (.xlsx)' }}</span>
                    <span wire:loading wire:target="exportar">Generando vista previa...</span>
                </button>
            </div>
        </div>
    </x-pdf-preview-modal>
</x-recent-record-host>

