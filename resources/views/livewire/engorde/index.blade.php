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
                <button wire:click="openExportModal"
                        class="flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-zinc-700 hover:bg-zinc-800 cursor-pointer">
                    <span>&#x1F4E5;</span> Resumen PDF
                </button>
                <button wire:click="openDetailedReportModal"
                        class="flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300 cursor-pointer">
                    Reporte detallado
                </button>
            @endif
            @if(auth()->user()->tienePermiso('engorde', 'crear'))
                <a href="{{ route('engorde.lote.create') }}" class="agro-button">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Crear Lote de Engorde</span>
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
        <div class="border-t border-zinc-800/60 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
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
                <x-date-picker model="fechaDesde" placeholder="dd/mm/aaaa" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <x-date-picker model="fechaHasta" placeholder="dd/mm/aaaa" compact />
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
            <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                @forelse($lotes as $lote)
                    @php
                        $isRecent = $this->isRecentRecord('engorde.lotes', $lote->id);
                    @endphp
                    <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/20' }}">
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
                        <td colspan="8" class="p-12 text-center text-zinc-500">
                            <div class="text-3xl">📄</div>
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

    <!-- Export / Live PDF Preview Modal -->
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
        :pdf-signature-scale="$pdfSignatureScale"
        :pdf-table-color-mode="$pdfTableColorMode"
        :pdf-table-radius="$pdfTableRadius"
        :pdf-table-preset="$pdfTablePreset"
        :has-pdf-customization="true"
    >
        {{-- OPTIONS STEP --}}
        <div>
            <div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Exportar Resumen de Lotes de Engorde</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">PDF horizontal resumen. Respeta filtros, orden y fundo activo.</p>
            </div>
            <div class="space-y-4 mt-4">
                <div>
                    <span class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2">Formato</span>
                    <div class="flex items-center gap-3 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm font-semibold text-rose-900 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-600 text-xs font-black text-white">PDF</span>
                        <span>Documento PDF (.pdf)</span>
                    </div>
                </div>

                {{-- AJUSTES EXCLUSIVOS DE PDF --}}
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-50/40 p-3 dark:border-emerald-500/20 dark:bg-emerald-950/20 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <span class="block text-xs font-bold text-emerald-900 dark:text-emerald-200">Compactación de texto y celdas</span>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Reduce el tamaño de fuente y padding para mostrar más lotes por página.</p>
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

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Columnas</span>
                        <button type="button" wire:click="$set('selectedColumns', {{ count($selectedColumns) === count($availableColumns) ? '[]' : json_encode(array_keys($availableColumns)) }})" class="text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">
                            {{ count($selectedColumns) === count($availableColumns) ? 'Deseleccionar todas' : 'Seleccionar todas' }}
                        </button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($availableColumns as $key => $label)
                            @php $isChecked = in_array($key, $selectedColumns, true); @endphp
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition {{ $isChecked ? 'border-emerald-500 bg-emerald-100 text-emerald-950 shadow-sm ring-1 ring-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-400/20 dark:text-emerald-50' : 'border-zinc-200 bg-white text-zinc-600 hover:border-emerald-300 hover:bg-emerald-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-emerald-400/10' }}">
                                <input type="checkbox" wire:model.live="selectedColumns" value="{{ $key }}" class="sr-only">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition {{ $isChecked ? 'border-emerald-700 bg-emerald-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900' }}">
                                    @if($isChecked)
                                        <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                    @endif
                                </span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedColumns') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    @error('selectedColumns.*') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-4 mt-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" wire:click="closeExportModal"
                        class="inline-flex h-10 items-center justify-center px-4 rounded-xl border border-zinc-300 bg-white hover:bg-zinc-100 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 text-xs sm:text-sm font-bold transition active:scale-95 cursor-pointer">
                    Cancelar
                </button>
                <button type="button" wire:click="exportar" wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-wait" wire:target="exportar"
                        class="inline-flex h-10 items-center justify-center gap-2 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition active:scale-95 cursor-pointer disabled:cursor-wait">
                    <span wire:loading.remove wire:target="exportar">Ver Vista Previa PDF</span>
                    <span wire:loading wire:target="exportar">Generando vista previa...</span>
                </button>
            </div>
        </div>
    </x-pdf-preview-modal>

    @if($showDetailedReportModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ scope: @js($detailedReportScope), lots: @js(collect($detailedReportLotIds)->map(fn ($id) => (string) $id)->values()), columns: @js($detailedReportColumns) }"
                 role="dialog" aria-modal="true" aria-label="Reporte general detallado"
                 class="agro-dialog agro-dialog--lg agro-dialog--scroll space-y-5 p-4 sm:p-6">
                <div>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Reporte general detallado de engorde</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Agrupa lotes elegidos y muestra datos completos de cada animal con diseño curvado y temas cromáticos rotativos en PDF horizontal.</p>
                </div>

                {{-- AJUSTES EXCLUSIVOS DE PDF DETALLADO --}}
                <div class="rounded-xl border border-rose-500/30 bg-rose-50/40 p-3 dark:border-rose-500/20 dark:bg-rose-950/20 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <span class="block text-xs font-bold text-rose-900 dark:text-rose-200">Compactación de texto y celdas</span>
                            <p class="text-[11px] text-rose-700 dark:text-rose-400">Reduce el tamaño de fuente para mostrar más animales y lotes por página.</p>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-1 w-full sm:w-auto rounded-lg border border-rose-300 bg-white p-1 text-xs font-bold dark:border-rose-700 dark:bg-zinc-800 shadow-2xs">
                            <button type="button" wire:click="$set('pdfScale', '45')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ in_array($pdfScale, ['40', '45'], true) ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                45% <span class="block text-[9px] font-medium opacity-80 sm:inline">Mín</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '55')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ in_array($pdfScale, ['50', '55'], true) ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                55% <span class="block text-[9px] font-medium opacity-80 sm:inline">Hiper</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '65')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '65' ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                65% <span class="block text-[9px] font-medium opacity-80 sm:inline">S-Ultra</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '75')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '75' ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                75% <span class="block text-[9px] font-medium opacity-80 sm:inline">Ultra</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '85')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '85' ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                85% <span class="block text-[9px] font-medium opacity-80 sm:inline">Comp</span>
                            </button>
                            <button type="button" wire:click="$set('pdfScale', '100')"
                                    class="px-2 py-1.5 rounded-md transition cursor-pointer text-center text-[11px] font-black active:scale-95 {{ $pdfScale === '100' ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:bg-zinc-700' }}">
                                100% <span class="block text-[9px] font-medium opacity-80 sm:inline">Est</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-rose-200/60 pt-2.5 dark:border-rose-800/60">
                        <div>
                            <span class="block text-xs font-bold text-rose-900 dark:text-rose-200">Bloque de firmas oficiales</span>
                            <p class="text-[11px] text-rose-700 dark:text-rose-400">Incluye sellos y firmas digitales configuradas al final del documento.</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" wire:model.live="pdfIncludeSignatures" class="sr-only peer">
                            <div class="w-9 h-5 bg-zinc-300 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-rose-600"></div>
                        </label>
                    </div>
                </div>

                <div>
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Lotes a incluir</span>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label :class="scope === 'filtered' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 dark:bg-emerald-400/15 dark:text-emerald-100' : 'border-zinc-200 dark:border-zinc-700'" class="cursor-pointer rounded-xl border p-3 text-sm font-semibold">
                            <input type="radio" x-model="scope" value="filtered" class="sr-only"> Todos los resultados filtrados
                            <span class="mt-1 block text-xs font-normal opacity-65">{{ $detailedReportLots->count() }} lotes disponibles</span>
                        </label>
                        <label :class="scope === 'selected' ? 'border-rose-500 bg-rose-50 text-rose-900 dark:bg-rose-400/15 dark:text-rose-100' : 'border-zinc-200 dark:border-zinc-700'" class="cursor-pointer rounded-xl border p-3 text-sm font-semibold">
                            <input type="radio" x-model="scope" value="selected" class="sr-only"> Escoger lotes
                            <span class="mt-1 block text-xs font-normal opacity-65" x-text="`${lots.length} seleccionados`"></span>
                        </label>
                    </div>
                </div>

                <div x-cloak x-show="scope === 'selected'" class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Selección de lotes</span>
                        <button type="button" x-on:click="lots = lots.length === {{ $detailedReportLots->count() }} ? [] : @js($detailedReportLots->pluck('id')->map(fn ($id) => (string) $id)->values())" class="text-xs font-semibold text-rose-600 dark:text-rose-400">Seleccionar todos / limpiar</button>
                    </div>
                    <div class="grid max-h-52 gap-2 overflow-y-auto sm:grid-cols-2">
                        @foreach($detailedReportLots as $reportLot)
                            <label :class="lots.includes('{{ $reportLot->id }}') ? 'border-rose-400 bg-rose-50 dark:bg-rose-400/10' : 'border-zinc-200 dark:border-zinc-700'" class="flex cursor-pointer items-center gap-3 rounded-lg border p-3">
                                <input type="checkbox" x-model="lots" value="{{ $reportLot->id }}" class="agro-checkbox h-4 w-4 rounded">
                                <span class="min-w-0"><strong class="block text-sm text-zinc-900 dark:text-white">{{ $reportLot->codigo }}</strong><small class="block truncate text-zinc-500">{{ $reportLot->nombre ?: 'Sin nombre' }} · {{ $reportLot->animales_count }} animales</small></span>
                            </label>
                        @endforeach
                    </div>
                    @error('detailedReportLotIds') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Datos por animal</span>
                        <button type="button" x-on:click="columns = columns.length === {{ count($detailedReportAvailableColumns) }} ? [] : @js(array_keys($detailedReportAvailableColumns))" class="text-xs font-semibold text-violet-600 dark:text-violet-400">Seleccionar todas</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach($detailedReportAvailableColumns as $key => $label)
                            <label :class="columns.includes('{{ $key }}') ? 'border-violet-500 bg-violet-100 text-violet-950 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium">
                                <input type="checkbox" x-model="columns" value="{{ $key }}" class="sr-only">
                                <span :class="columns.includes('{{ $key }}') ? 'border-violet-700 bg-violet-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2">
                                    <svg x-cloak x-show="columns.includes('{{ $key }}')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('detailedReportColumns') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    @error('detailedReportColumns.*') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <button wire:click="$set('showDetailedReportModal', false)" class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancelar</button>
                    <button type="button" x-on:click="$wire.exportDetailedReport(scope, lots, columns)" wire:loading.attr="disabled" wire:target="exportDetailedReport" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-500 disabled:opacity-60">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Ver Vista Previa Detallada</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-recent-record-host>


