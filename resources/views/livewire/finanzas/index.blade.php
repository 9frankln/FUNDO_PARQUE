<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">Finanzas y contabilidad</h1>
            <p class="mt-1 text-sm text-zinc-400">Control unificado de ingresos, egresos, compras de insumos y asignaciones familiares.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto sm:justify-end">
            @if(auth()->user()->tienePermiso('finanzas', 'exportar'))
                <button type="button" wire:click="openReportModal"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-zinc-700 bg-zinc-900 px-4 text-sm font-bold text-zinc-200 shadow-sm transition hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-300 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Exportar PDF
                </button>
            @endif
            @if(auth()->user()->tienePermiso('finanzas', 'crear'))
                <a href="{{ route('finanzas.movimiento.create') }}" class="agro-button w-full sm:w-auto">
                    <span aria-hidden="true">+</span> Nuevo movimiento
                </a>
            @endif
        </div>
    </div>

    <x-finance-dashboard :data="$dashboardData" />

    <x-collapsible-filters :active="$hasMovementFilters"
                           title="Filtros de movimientos"
                           description="Búsqueda precisa por periodo, categoría, monto o comprobante."
                           id="movimientos-filter-content"
                           reset="resetMovimientoFilters"
                           loading-target="searchMovimiento,tipoMovimiento,categoriaMovimiento,periodoMovimiento,fechaDesdeMovimiento,fechaHastaMovimiento,montoMinMovimiento,montoMaxMovimiento,conComprobante">
        <div class="border-t border-zinc-800 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500" aria-hidden="true">&#x1F50D;</span>
                    <input type="search" wire:model.live.debounce.300ms="searchMovimiento"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                           placeholder="Buscar descripción o categoría...">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo</label>
                <x-filter-select model="tipoMovimiento" :options="['' => 'Todos los tipos', 'ingreso' => 'Ingreso (+)', 'egreso' => 'Egreso (-)']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Categoría</label>
                <x-filter-select model="categoriaMovimiento" :options="['' => 'Todas las categorías'] + $categorias->pluck('nombre', 'id')->toArray()" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Periodo</label>
                <x-filter-select model="periodoMovimiento" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'anio_actual' => 'Año actual']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <x-date-picker model="fechaDesdeMovimiento" placeholder="dd/mm/aaaa" compact />
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <x-date-picker model="fechaHastaMovimiento" placeholder="dd/mm/aaaa" compact />
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto mínimo</label>
                <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMinMovimiento" placeholder="0.00" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto máximo</label>
                <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMaxMovimiento" placeholder="Sin límite" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Comprobante</label>
                <x-filter-select model="conComprobante" :options="['' => 'Con y sin comprobante', '1' => 'Con comprobante / foto', '0' => 'Sin comprobante']" tone="emerald" live compact />
            </div>
        </div>
    </x-collapsible-filters>

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950 text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                        <th class="p-4 whitespace-nowrap">Fecha</th>
                        <th class="p-4 whitespace-nowrap">Categoría</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4 whitespace-nowrap">Comprobante</th>
                        <th class="p-4 whitespace-nowrap">Monto</th>
                        <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/70 text-sm text-zinc-300">
                    @forelse($movimientos as $movimiento)
                        @php
                            $isRecent = $this->isRecentRecord('finanzas.movimientos', $movimiento->id);
                            $receiptUrl = $movimiento->comprobante_ruta
                                ? route('movimiento.comprobante', $movimiento).'?v='.sha1($movimiento->comprobante_ruta)
                                : null;
                            $sharedPhotoUrl = $movimiento->fotoCompartidaUrl();
                            $sharedPhotoLink = $movimiento->fotoCompartidaEnlace();
                            $sharedPhotoFrame = $movimiento->fotoCompartidaEncuadre();
                            $sharedPhotoTitle = $movimiento->fotoCompartidaTitulo();
                            $animalesVendidos = $movimiento->animalesVendidos;
                            $compraMedicamento = $movimiento->compraMedicamento;
                            $compraInsumo = $movimiento->compraInsumo;
                            $compradorVenta = $movimiento->compradorVenta();
                        @endphp
                        <tr class="transition {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/25' }}">
                            <td class="p-4 font-semibold text-zinc-100 whitespace-nowrap">
                                {{ $movimiento->fecha->format('d/m/Y') }}
                                <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="font-medium text-zinc-300">{{ $movimiento->categoria?->nombre ?? 'Sin categoría' }}</span>
                                <span class="mt-0.5 block text-[10px] uppercase tracking-wider {{ $movimiento->tipo === 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">{{ $movimiento->tipo }}</span>
                            </td>
                            <td class="max-w-md p-4 space-y-1">
                                @if($movimiento->esAsignacionFamiliar())
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-violet-400">
                                            <span>👤</span> Para: {{ $movimiento->beneficiario }}
                                        </span>
                                        @if($movimiento->proposito)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-violet-950/60 text-violet-300 border border-violet-800/40">
                                                {{ ucfirst(str_replace('_', ' ', $movimiento->proposito)) }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($compraMedicamento)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('medicamentos.show', $compraMedicamento->medicamento_id) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-bold text-cyan-400 hover:text-cyan-300">
                                            <span>💊</span> {{ $compraMedicamento->medicamento?->nombre ?? 'Medicamento' }} · {{ $compraMedicamento->numero_lote }}
                                        </a>
                                        <span class="text-[11px] text-zinc-400">({{ $compraMedicamento->cantidad_inicial + 0 }} {{ $compraMedicamento->medicamento?->unidad_stock ?? 'unid' }})</span>
                                        @if($compraMedicamento->proveedor)
                                            <span class="text-[11px] font-semibold text-zinc-400">Prov: {{ $compraMedicamento->proveedor }}</span>
                                        @endif
                                    </div>
                                @elseif($compraInsumo)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('insumos.show', $compraInsumo->insumo_id) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-bold text-emerald-400 hover:text-emerald-300">
                                            <span>📦</span> {{ $compraInsumo->insumo?->nombre ?? 'Insumo' }} · {{ $compraInsumo->numero_lote }}
                                        </a>
                                        <span class="text-[11px] text-zinc-400">({{ $compraInsumo->cantidad_inicial + 0 }} {{ $compraInsumo->insumo?->unidad_stock ?? 'unid' }})</span>
                                        @if($compraInsumo->proveedor)
                                            <span class="text-[11px] font-semibold text-zinc-400">Prov: {{ $compraInsumo->proveedor }}</span>
                                        @endif
                                    </div>
                                @elseif($animalesVendidos->isNotEmpty())
                                    <div class="flex items-center gap-1 flex-wrap">
                                        @foreach($animalesVendidos as $animalVendido)
                                            <a href="{{ route('animal.show', $animalVendido->id) }}" wire:navigate class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-bold bg-amber-950/60 text-amber-300 border border-amber-800/40 hover:bg-amber-900/60 transition">
                                                <span>🐄</span> {{ $animalVendido->arete }}
                                            </a>
                                        @endforeach
                                        @if($compradorVenta)
                                            <span class="text-[11px] font-semibold text-zinc-400">Comprador: {{ $compradorVenta }}</span>
                                        @endif
                                    </div>
                                @endif
                                <span class="text-xs text-zinc-400">{{ $movimiento->descripcionLegible() ?: ($movimiento->animalesVendidos->isNotEmpty() ? 'Venta registrada.' : 'Sin descripción') }}</span>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @if($sharedPhotoUrl)
                                    <a href="{{ $sharedPhotoLink }}" wire:navigate title="Abrir ficha" class="group inline-flex overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50">
                                        <img src="{{ $sharedPhotoUrl }}"
                                             alt="{{ $sharedPhotoTitle }}"
                                             width="48" height="48" loading="lazy" decoding="async"
                                             class="h-12 w-12 rounded-xl border border-emerald-500/30 object-cover shadow-sm transition group-hover:border-emerald-400"
                                             style="object-position: {{ $sharedPhotoFrame['x'] }}% {{ $sharedPhotoFrame['y'] }}%; transform: scale({{ $sharedPhotoFrame['zoom'] }}); transform-origin: {{ $sharedPhotoFrame['x'] }}% {{ $sharedPhotoFrame['y'] }}%;">
                                    </a>
                                @elseif($movimiento->comprobante_ruta && $movimiento->comprobanteEsImagen())
                                    @php
                                        $receiptTableFrame = \App\Support\ImageFrame::normalize($movimiento->comprobante_encuadre);
                                    @endphp
                                    <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" title="Abrir comprobante" class="group inline-flex overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50">
                                        <img src="{{ $receiptUrl }}"
                                             alt="Comprobante del movimiento {{ $movimiento->id }}"
                                             width="48" height="48" loading="lazy" decoding="async"
                                             class="h-12 w-12 rounded-xl border border-zinc-700 object-cover shadow-sm transition group-hover:border-emerald-400"
                                             style="object-position: {{ $receiptTableFrame['x'] }}% {{ $receiptTableFrame['y'] }}%; transform: scale({{ $receiptTableFrame['zoom'] }}); transform-origin: {{ $receiptTableFrame['x'] }}% {{ $receiptTableFrame['y'] }}%;">
                                    </a>
                                @elseif($movimiento->comprobante_ruta)
                                    <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" title="Abrir comprobante PDF" aria-label="Abrir comprobante PDF" class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-rose-500/30 bg-rose-100 text-rose-600 transition hover:border-rose-400 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:border-rose-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M9 15h6M9 11h2" /></svg>
                                    </a>
                                @else
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-zinc-700 text-zinc-600" title="Sin comprobante">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" d="m5 19 14-14" /></svg>
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-base font-extrabold whitespace-nowrap {{ $movimiento->tipo === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $movimiento->tipo === 'ingreso' ? '+' : '-' }} S/. {{ number_format((float) $movimiento->monto, 2) }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <x-table-action type="view" :href="route('finanzas.movimiento.show', $movimiento->id)" label="Ver movimiento" />
                                    @if(auth()->user()->tienePermiso('finanzas', 'actualizar'))
                                        <x-table-action type="edit" :href="route('finanzas.movimiento.edit', $movimiento->id)" />
                                    @endif
                                    @if(auth()->user()->tienePermiso('finanzas', 'eliminar'))
                                        <x-table-action type="delete" wire:click="solicitarEliminacionMovimiento({{ $movimiento->id }})" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-12 text-center text-sm text-zinc-500">No hay movimientos registrados con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="agro-table-footer">
        <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageOptions()" tone="emerald" live compact /></div>
        <div class="min-w-0">{{ $movimientos->links('components.pagination') }}</div>
    </div>

    @if($showReportModal)
        <x-finance-report-modal
            title="Exportar reporte de caja"
            description="Selecciona solo la información esencial. Se respetan los filtros activos."
            :section-options="$reportSectionOptions"
            :column-options="$reportColumnOptions"
            tone="emerald" />
    @endif

    {{-- MODAL LIVE PDF PREVIEW --}}
    <x-pdf-preview-modal
        :show-export-modal="$showExportModal && $exportStep === 'preview'"
        export-step="preview"
        :pdf-preview-data="$pdfPreviewData"
        :pdf-preview-token="$pdfPreviewToken"
        :pdf-preview-filename="$pdfPreviewFilename"
        :pdf-preview-title="$pdfPreviewTitle"
        :pdf-preview-row-count="$pdfPreviewRowCount"
        :pdf-preview-page-count="$pdfPreviewPageCount"
        :pdf-include-signatures="$pdfIncludeSignatures"
        :pdf-scale="$pdfScale"
        :has-pdf-customization="true"
        :back-action="'$set(\'showReportModal\', true); $set(\'showExportModal\', false)'"
    >
        {{-- No options slot: finanzas uses finance-report-modal for options --}}
    </x-pdf-preview-modal>
</x-recent-record-host>
