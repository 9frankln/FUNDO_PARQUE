<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">Finanzas y contabilidad</h1>
            <p class="mt-1 text-sm text-zinc-400">Movimientos de caja y asignaciones familiares, cada uno en su flujo.</p>
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
                @if($tab === 'movimientos')
                    <a href="{{ route('finanzas.movimiento.create') }}" class="agro-button w-full sm:w-auto">
                        <span aria-hidden="true">+</span> Nuevo movimiento
                    </a>
                @else
                    <a href="{{ route('finanzas.asignacion.create') }}" class="agro-button w-full sm:w-auto">
                        <span aria-hidden="true">+</span> Nueva asignación
                    </a>
                @endif
            @endif
        </div>
    </div>

    <x-finance-dashboard :data="$dashboardData" />

    <div class="flex gap-1 overflow-x-auto border-b border-zinc-800" role="tablist" aria-label="Secciones de finanzas">
        <button type="button" wire:click="$set('tab', 'movimientos')" role="tab" aria-selected="{{ $tab === 'movimientos' ? 'true' : 'false' }}"
                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold transition {{ $tab === 'movimientos' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}">
            Movimientos de caja
        </button>
        <button type="button" wire:click="$set('tab', 'asignaciones')" role="tab" aria-selected="{{ $tab === 'asignaciones' ? 'true' : 'false' }}"
                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold transition {{ $tab === 'asignaciones' ? 'border-violet-500 text-violet-400' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}">
            Asignación familiar
        </button>
    </div>

    @if($tab === 'movimientos')
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
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Periodo</label>
                    <x-filter-select model="periodoMovimiento" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'anio_actual' => 'Año actual']" tone="emerald" live compact />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                    <input type="date" wire:model.live="fechaDesdeMovimiento" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                    <input type="date" wire:model.live="fechaHastaMovimiento" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo</label>
                    <x-filter-select model="tipoMovimiento" :options="['' => 'Ingresos y egresos', 'ingreso' => 'Ingresos', 'egreso' => 'Egresos']" tone="emerald" live compact />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Categoría</label>
                    <x-filter-select model="categoriaMovimiento" :options="['' => 'Todas las categorías'] + $categorias->mapWithKeys(fn ($category) => [(string) $category->id => $category->nombre])->all()" tone="emerald" live compact />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Comprobante</label>
                    <x-filter-select model="conComprobante" :options="['' => 'Todos', '1' => 'Con comprobante', '0' => 'Sin comprobante']" tone="emerald" live compact />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto mínimo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMinMovimiento" placeholder="0.00" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto máximo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMaxMovimiento" placeholder="Sin límite" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
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
                                <td class="max-w-sm p-4 text-zinc-400">{{ $movimiento->descripcion ?: 'Sin descripción' }}</td>
                                <td class="p-4 whitespace-nowrap">
                                    @if($movimiento->comprobante_ruta && $movimiento->comprobanteEsImagen())
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
                                        <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" title="Abrir comprobante PDF" aria-label="Abrir comprobante PDF" class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-300 transition hover:border-rose-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M9 15h6M9 11h2" /></svg>
                                        </a>
                                    @else
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-zinc-700 text-zinc-600" title="Sin comprobante">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" d="m5 19 14-14" /></svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-base font-extrabold whitespace-nowrap {{ $movimiento->tipo === 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">
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
                            <tr><td colspan="6" class="p-12 text-center text-sm text-zinc-500">No hay movimientos con estos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="agro-table-footer">
            <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="emerald" live compact /></div>
            <div class="min-w-0">{{ $movimientos->links('components.pagination') }}</div>
        </div>
    @else
        <x-collapsible-filters :active="$hasAssignmentFilters"
                               title="Filtros de asignaciones"
                               description="Encuentra una entrega por beneficiario, fecha, propósito, foto o monto."
                               id="asignaciones-filter-content"
                               reset="resetAsignacionFilters"
                               loading-target="searchAsignacion,propositoAsignacion,periodoAsignacion,fechaDesdeAsignacion,fechaHastaAsignacion,montoMinAsignacion,montoMaxAsignacion,conFoto">
            <div class="border-t border-zinc-800 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                    <div class="relative w-full">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500" aria-hidden="true">&#x1F50D;</span>
                        <input type="search" wire:model.live.debounce.300ms="searchAsignacion"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20"
                               placeholder="Buscar beneficiario o detalle...">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Periodo</label>
                    <x-filter-select model="periodoAsignacion" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'anio_actual' => 'Año actual']" tone="violet" live compact />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                    <input type="date" wire:model.live="fechaDesdeAsignacion" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                    <input type="date" wire:model.live="fechaHastaAsignacion" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>

                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Propósito</label>
                    <x-filter-select model="propositoAsignacion" :options="['' => 'Todos los propósitos', 'estudio' => 'Estudios', 'salud' => 'Salud', 'alimentacion' => 'Alimentación', 'vivienda' => 'Vivienda', 'transporte' => 'Transporte', 'ropa' => 'Ropa', 'gastos_personales' => 'Gastos personales', 'emergencia' => 'Emergencia', 'otros' => 'Otros']" tone="violet" live compact />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Foto</label>
                    <x-filter-select model="conFoto" :options="['' => 'Con y sin foto', '1' => 'Con foto', '0' => 'Sin foto']" tone="violet" live compact />
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto mínimo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMinAsignacion" placeholder="0.00" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto máximo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMaxAsignacion" placeholder="Sin límite" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>
            </div>
        </x-collapsible-filters>

        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950 text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                            <th class="p-4 whitespace-nowrap">Fecha</th>
                            <th class="p-4 whitespace-nowrap">Beneficiario</th>
                            <th class="p-4 whitespace-nowrap">Propósito</th>
                            <th class="p-4 whitespace-nowrap">Foto</th>
                            <th class="p-4 whitespace-nowrap">Monto</th>
                            <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/70 text-sm text-zinc-300">
                        @forelse($asignaciones as $asignacion)
                            @php
                                $isRecent = $this->isRecentRecord('finanzas.asignaciones', $asignacion->id);
                            @endphp
                            <tr class="transition {{ $isRecent ? 'bg-violet-500/10' : 'hover:bg-zinc-800/25' }}">
                                <td class="p-4 font-semibold text-zinc-100 whitespace-nowrap">
                                    {{ $asignacion->fecha->format('d/m/Y') }}
                                    <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <a href="{{ route('finanzas.asignacion.show', $asignacion->id) }}" class="font-bold text-zinc-200 transition hover:text-violet-300">{{ $asignacion->beneficiario }}</a>
                                </td>
                                <td class="p-4 whitespace-nowrap"><x-status-badge :value="$asignacion->proposito" :label="ucfirst(str_replace('_', ' ', $asignacion->proposito))" tone="violet" /></td>
                                <td class="p-4 whitespace-nowrap">
                                    @if($asignacion->foto_ruta)
                                        @php
                                            $assignmentTableFrame = \App\Support\ImageFrame::normalize($asignacion->foto_encuadre);
                                        @endphp
                                        <a href="{{ route('asignacion.foto', $asignacion) }}" target="_blank" rel="noopener" title="Abrir foto" class="group inline-flex overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/50">
                                            <img src="{{ route('asignacion.foto', $asignacion) }}" alt="Foto de {{ $asignacion->beneficiario }}" width="48" height="48" loading="lazy" decoding="async" class="h-12 w-12 rounded-xl border border-violet-500/20 bg-zinc-950 object-cover transition group-hover:border-violet-400" style="object-position: {{ $assignmentTableFrame['x'] }}% {{ $assignmentTableFrame['y'] }}%; transform: scale({{ $assignmentTableFrame['zoom'] }}); transform-origin: {{ $assignmentTableFrame['x'] }}% {{ $assignmentTableFrame['y'] }}%;">
                                        </a>
                                    @else
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-zinc-700 bg-zinc-950 text-zinc-600" title="Sin foto">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" d="m5 19 14-14" /></svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-base font-extrabold text-violet-300 whitespace-nowrap">S/. {{ number_format((float) $asignacion->monto, 2) }}</td>
                                <td class="p-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-table-action type="view" :href="route('finanzas.asignacion.show', $asignacion->id)" label="Ver asignación" />
                                        @if(auth()->user()->tienePermiso('finanzas', 'actualizar'))
                                            <x-table-action type="edit" :href="route('finanzas.asignacion.edit', $asignacion->id)" />
                                        @endif
                                        @if(auth()->user()->tienePermiso('finanzas', 'eliminar'))
                                            <x-table-action type="delete" wire:click="solicitarEliminacionAsignacion({{ $asignacion->id }})" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-12 text-center text-sm text-zinc-500">No hay asignaciones con estos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="agro-table-footer">
            <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="violet" live compact /></div>
            <div class="min-w-0">{{ $asignaciones->links('components.pagination') }}</div>
        </div>
    @endif

    @if($showReportModal)
        <x-finance-report-modal
            :title="$reportType === 'movimientos' ? 'Exportar movimientos de caja' : 'Exportar asignación familiar'"
            description="Selecciona solo la información esencial. Se respetan los filtros activos."
            :section-options="$reportSectionOptions"
            :column-options="$reportColumnOptions"
            :tone="$reportType === 'movimientos' ? 'emerald' : 'violet'" />
    @endif
</x-recent-record-host>
