<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                Inventario Animal
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Monitorea y gestiona los animales registrados en tu fundo.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            @if(auth()->user()->tienePermiso('animal', 'exportar'))
                <button wire:click="openExportModal"
                    class="px-3.5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-300 text-sm font-semibold transition duration-200 flex items-center gap-2 shadow-xs">
                <span>&#x1F4E5;</span> Exportar
                </button>
            @endif
            @if(auth()->user()->tienePermiso('animal', 'crear'))
                <button wire:click="openImportModal"
                    class="px-3.5 py-2.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-sm font-semibold transition duration-200 flex items-center gap-2 shadow-xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    <span>Importar</span>
                </button>
                <a wire:navigate href="{{ route('animal.create') }}" class="agro-button">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Nuevo Animal</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Dynamic Animal Dashboard -->
    <x-animal-dashboard :data="$dashboardData" />

    <!-- Filters -->
    <x-collapsible-filters :active="$hasActiveFilters"
                           title="Filtros de inventario"
                           description="Consulta animales por fecha de alta y características."
                           id="animal-filter-content"
                           reset="resetFilters"
                           loading-target="periodo,anio,mes,fechaDesde,fechaHasta,search,especieId,razaId,genero,activo,motivoBaja,perPage,sort">
        <div class="border-t border-zinc-800/60 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 items-end">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500 text-xs">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                           placeholder="Buscar código o nombre...">
                </div>
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Especie</label>
                <x-filter-select model="especieId" :options="['' => 'Todas'] + collect($especies)->pluck('nombre', 'id')->all()" tone="emerald" live compact />
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Raza</label>
                <x-filter-select wire:key="idx-raza-{{ $especieId ?: 'all' }}" model="razaId" :options="['' => 'Todas'] + collect($razas)->pluck('nombre', 'id')->all()" tone="emerald" live compact />
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Género</label>
                <x-filter-select model="genero" :options="['' => 'Todos', 'macho' => 'Macho', 'hembra' => 'Hembra']" tone="sky" live compact />
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Estado</label>
                <x-filter-select model="activo" :options="['' => 'Todo el inventario', '1' => 'En inventario', '0' => 'Dados de baja']" tone="rose" live compact />
            </div>

            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Motivo de baja</label>
                <x-filter-select model="motivoBaja" :options="['' => 'Todos los motivos'] + \App\Models\Animal::INACTIVE_REASONS" tone="amber" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Acceso rápido</label>
                <x-filter-select model="periodo" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']" tone="emerald" live compact />
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
        </div>
    </x-collapsible-filters>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                        <th class="p-4 whitespace-nowrap">
                            <x-table-sort-header field="arete" :sort-by="$sortBy" :sort-dir="$sortDir" wire:click="sort('arete')">
                                Código
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 whitespace-nowrap">Foto</th>
                        <th class="p-4 whitespace-nowrap">
                            <x-table-sort-header field="nombre" :sort-by="$sortBy" :sort-dir="$sortDir" wire:click="sort('nombre')">
                                Nombre
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 whitespace-nowrap">Tipo / raza</th>
                        <th class="p-4 whitespace-nowrap">Sexo</th>
                        <th class="p-4 whitespace-nowrap">
                            <x-table-sort-header field="fecha_nacimiento" :sort-by="$sortBy" :sort-dir="$sortDir" wire:click="sort('fecha_nacimiento')">
                                Edad
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 whitespace-nowrap">
                            <x-table-sort-header field="id" :sort-by="$sortBy" :sort-dir="$sortDir" wire:click="sort('id')">
                                Estado / Registro
                            </x-table-sort-header>
                        </th>
                        <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                    @forelse($animales as $animal)
                        @php
                            $isRecent = $this->isRecentRecord('animal.animales', $animal->id);
                        @endphp
                        <tr wire:key="animal-{{ $animal->id }}" class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/20' }}">
                            <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                                <a href="{{ route('animal.show', $animal->id) }}"
                                   class="rounded font-bold text-zinc-100 transition hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:hover:text-emerald-300"
                                   title="Ver ficha de {{ $animal->arete }}">
                                    {{ $animal->arete }}
                                </a>
                                <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <x-table-photo :path="$animal->foto_ruta" :frame="$animal->foto_encuadre" :alt="'Foto de '.($animal->nombre ?: $animal->arete)" />
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <a href="{{ route('animal.show', $animal->id) }}"
                                   class="rounded font-semibold text-zinc-200 transition hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:hover:text-emerald-300"
                                   title="Ver ficha de {{ $animal->nombre ?: $animal->arete }}">
                                    {{ $animal->nombre ?? '-' }}
                                </a>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="block font-semibold text-zinc-200">{{ $animal->especie?->nombre ?? '-' }}</span>
                                <span class="mt-0.5 block text-[10px] text-zinc-500">{{ $animal->raza?->nombre ?? '-' }}</span>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <x-status-badge :value="$animal->genero" />
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="block font-semibold text-zinc-200">{{ $animal->clasificacion_edad }}</span>
                                <span class="mt-0.5 block text-[10px] text-zinc-500">{{ $animal->edad_texto }}</span>
                                @if($animal->denticion_estimada)
                                    <span class="mt-0.5 block text-[10px] text-amber-600 dark:text-amber-400">{{ $animal->denticion_estimada }}</span>
                                @endif
                            </td>
                            <td class="min-w-56 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <x-status-badge
                                            :value="$animal->activo ? 'activo' : 'inactivo'"
                                            :label="$animal->activo ? 'En inventario' : 'Dado de baja'"
                                        />
                                        @unless($animal->activo)
                                            <p class="mt-1.5 text-xs font-bold text-zinc-300">{{ $animal->motivo_baja_label }}</p>
                                            <p class="mt-0.5 text-[10px] text-zinc-500">
                                                {{ $animal->fecha_baja?->format('d/m/Y') ?? 'Fecha no registrada' }}
                                                @if($animal->comprador_baja) · {{ $animal->comprador_baja }} @endif
                                            </p>
                                        @endunless
                                    </div>
                                    @if(auth()->user()->tienePermiso('animal', 'actualizar'))
                                        <button type="button" wire:click="openStatusModal({{ $animal->id }})"
                                                class="inline-flex h-8 shrink-0 items-center rounded-lg border px-2.5 text-[10px] font-black uppercase tracking-wide transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 {{ $animal->activo ? 'border-amber-500/30 bg-amber-500/10 text-amber-600 hover:bg-amber-500/20 dark:text-amber-300' : 'border-sky-500/30 bg-sky-500/10 text-sky-600 hover:bg-sky-500/20 dark:text-sky-300' }}">
                                            Gestionar
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                <x-table-action type="view" :href="route('animal.show', $animal->id)" label="Ver ficha" />
                                @if(auth()->user()->tienePermiso('animal', 'actualizar'))
                                    <x-table-action type="edit" :href="route('animal.edit', $animal->id)" />
                                @endif
                                @if(auth()->user()->tienePermiso('animal', 'eliminar'))
                                    <x-table-action type="delete" wire:click="solicitarEliminacion({{ $animal->id }})" />
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-zinc-500">
                                <div class="text-3xl">&#x1F4ED;</div>
                                <div class="mt-2 font-bold text-sm">No se encontraron animales</div>
                                <div class="text-xs text-zinc-500 mt-1">Intenta ajustando los filtros o añade uno nuevo.</div>
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
            {{ $animales->links('components.pagination') }}
        </div>
    </div>

    @if($showStatusModal && $statusAnimal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
             class="agro-dialog-overlay">
            <div role="dialog" aria-modal="true" aria-labelledby="animal-status-title"
                 class="agro-dialog agro-dialog--md agro-dialog--scroll">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-400">Control de inventario</p>
                        <h3 id="animal-status-title" class="mt-1 text-xl font-black text-zinc-900 dark:text-white">
                            {{ $statusAnimal->activo ? 'Registrar baja' : 'Revisar baja' }}
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            {{ $statusAnimal->arete }}{{ $statusAnimal->nombre ? ' · '.$statusAnimal->nombre : '' }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeStatusModal" aria-label="Cerrar"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    @if($statusAnimal->activo)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
                            La baja retirará al animal del stock, ordeño y engorde activo. Su historial se conserva.
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Motivo de baja <span class="text-rose-500">*</span></label>
                            <x-filter-select model="statusReason"
                                             :options="['' => 'Selecciona un motivo...'] + \App\Models\Animal::INACTIVE_REASONS"
                                             tone="rose"
                                             live />
                            @error('statusReason') <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Fecha de baja <span class="text-rose-500">*</span></label>
                            <x-date-picker model="statusDate" :max="today()->format('Y-m-d')" placeholder="dd/mm/aaaa" />
                            @error('statusDate') <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        @if($statusReason === 'venta')
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <p class="text-sm font-black text-emerald-900 dark:text-emerald-100">Venta enlazada con Finanzas</p>
                                <p class="mt-1 text-xs leading-5 text-emerald-700 dark:text-emerald-300">Continuarás al formulario de ingreso con este animal y "Venta de Animales" preseleccionados. La baja se aplicará únicamente cuando el movimiento se guarde correctamente.</p>
                            </div>
                        @else
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    Detalle {{ $statusReason === 'otro' ? '*' : '(opcional)' }}
                                </label>
                                <textarea wire:model="statusDetail" rows="3" maxlength="255"
                                          class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                          placeholder="Información breve para la trazabilidad"></textarea>
                                @error('statusDetail') <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @else
                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-zinc-100 p-4 dark:bg-zinc-800/70">
                                <dt class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Motivo</dt>
                                <dd class="mt-1 font-bold text-zinc-900 dark:text-white">{{ $statusAnimal->motivo_baja_label }}</dd>
                            </div>
                            <div class="rounded-xl bg-zinc-100 p-4 dark:bg-zinc-800/70">
                                <dt class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Fecha</dt>
                                <dd class="mt-1 font-bold text-zinc-900 dark:text-white">{{ $statusAnimal->fecha_baja?->format('d/m/Y') ?? 'No registrada' }}</dd>
                            </div>
                        </dl>
                        @if($statusAnimal->detalle_baja)
                            <p class="rounded-xl border border-zinc-200 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">{{ $statusAnimal->detalle_baja }}</p>
                        @endif
                        @if($statusAnimal->motivo_baja === 'venta' && $statusAnimal->movimientoVenta)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <p class="text-sm font-black text-emerald-900 dark:text-emerald-100">Venta financiera vinculada</p>
                                <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">
                                    Movimiento #{{ str_pad((string) $statusAnimal->movimientoVenta->id, 6, '0', STR_PAD_LEFT) }}
                                    · S/. {{ number_format((float) $statusAnimal->movimientoVenta->monto, 2) }}
                                    @if($statusAnimal->comprador_baja) · {{ $statusAnimal->comprador_baja }} @endif
                                </p>
                            </div>
                        @else
                            <p class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-800 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-200">Si la baja fue un error, puedes devolver el animal al inventario activo.</p>
                        @endif
                        @error('statusReason') <p class="text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                    @endif
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:flex-row sm:justify-end sm:px-6">
                    <button type="button" wire:click="closeStatusModal"
                            class="h-11 rounded-xl border border-zinc-300 px-5 text-sm font-bold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Cancelar
                    </button>
                    @if($statusAnimal->activo)
                        <button type="button" wire:click="confirmStatusChange" wire:loading.attr="disabled" wire:target="confirmStatusChange"
                                class="h-11 rounded-xl bg-amber-500 px-6 text-sm font-black text-zinc-950 transition hover:bg-amber-400 disabled:opacity-60">
                            {{ $statusReason === 'venta' ? 'Continuar a Finanzas' : 'Confirmar baja' }}
                        </button>
                    @elseif(!($statusAnimal->motivo_baja === 'venta' && $statusAnimal->movimientoVenta))
                        <button type="button" wire:click="confirmStatusChange" wire:loading.attr="disabled" wire:target="confirmStatusChange"
                                class="h-11 rounded-xl bg-sky-500 px-6 text-sm font-black text-white transition hover:bg-sky-400 disabled:opacity-60">
                            Reactivar animal
                        </button>
                    @else
                        <a href="{{ route('finanzas.movimiento.show', $statusAnimal->movimientoVenta) }}"
                           class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-500 px-6 text-sm font-black text-zinc-950 transition hover:bg-emerald-400">
                            Ver movimiento
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

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
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Exportar Inventario Animal</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">PDF horizontal o Excel. Ambos respetan filtros, orden y fundo activo.</p>
            </div>
            <div class="space-y-4 mt-4">
                <div>
                    <span class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider mb-2">Formato</span>
                    <div class="grid grid-cols-1 gap-3 min-[400px]:grid-cols-2">
                        <label class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 text-sm font-semibold transition {{ $exportFormat === 'xlsx' ? 'border-sky-500 bg-sky-100 text-sky-950 shadow-sm shadow-sky-500/20 dark:border-sky-400 dark:bg-sky-400/20 dark:text-sky-50' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-sky-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="exportFormat" value="xlsx" class="sr-only">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 {{ $exportFormat === 'xlsx' ? 'border-sky-600 bg-sky-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900' }}">
                                @if($exportFormat === 'xlsx')
                                    <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                @endif
                            </span>
                            <span>Excel (.xlsx)</span>
                        </label>
                        <label class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 text-sm font-semibold transition {{ $exportFormat === 'pdf' ? 'border-emerald-500 bg-emerald-100 text-emerald-950 shadow-sm shadow-emerald-500/20 dark:border-emerald-400 dark:bg-emerald-400/20 dark:text-emerald-50' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-emerald-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="exportFormat" value="pdf" class="sr-only">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 {{ $exportFormat === 'pdf' ? 'border-emerald-600 bg-emerald-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900' }}">
                                @if($exportFormat === 'pdf')
                                    <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                @endif
                            </span>
                            <span>PDF (.pdf)</span>
                        </label>
                    </div>
                    @error('exportFormat') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>

                @if($exportFormat === 'pdf')
                    {{-- AJUSTES EXCLUSIVOS DE PDF --}}
                    <div class="rounded-xl border border-emerald-500/30 bg-emerald-50/40 p-3 dark:border-emerald-500/20 dark:bg-emerald-950/20 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                            <div>
                                <span class="block text-xs font-bold text-emerald-900 dark:text-emerald-200">Compactación de texto y celdas</span>
                                <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Reduce el tamaño de fuente y padding para mostrar más animales por página.</p>
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
                    <p class="mt-2 text-[11px] text-zinc-500 dark:text-zinc-400">Estado reproductivo y precio de compra son opcionales. Precio solo aplica a animales comprados.</p>
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
                    <span wire:loading.remove wire:target="exportar">{{ $exportFormat === 'pdf' ? 'Ver Vista Previa PDF' : 'Descargar Excel (.xlsx)' }}</span>
                    <span wire:loading wire:target="exportar">Generando vista previa...</span>
                </button>
            </div>
        </div>
    </x-pdf-preview-modal>



    {{-- MODAL DE IMPORTACIÃÆ’âââ€š¬Åâ€œN MASIVA DE ANIMALES --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/75 backdrop-blur-xs"
             x-data
             x-on:keydown.escape.window="$wire.closeImportModal()">
            <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="flex items-start justify-between border-b border-zinc-200/80 pb-4 dark:border-zinc-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                            </span>
                            <h3 class="text-base font-black text-zinc-950 dark:text-white">Importación Masiva de Animales</h3>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500">Carga múltiples vacas, toros y terneros simultáneamente desde Excel (.xlsx) o CSV.</p>
                    </div>
                    <button wire:click="closeImportModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Paso 1: Descarga de Plantilla --}}
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-50/60 p-4 dark:border-emerald-500/20 dark:bg-emerald-950/20">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold text-emerald-900 dark:text-emerald-300">Paso 1: Descarga la plantilla oficial</p>
                            <p class="mt-0.5 text-[11px] text-emerald-700/80 dark:text-emerald-400/80">Incluye los encabezados obligatorios y ejemplos de llenado para evitar errores.</p>
                        </div>
                        <button type="button" wire:click="downloadImportTemplate"
                                class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-xl border border-emerald-600 bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-emerald-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Descargar Plantilla (.xlsx)
                        </button>
                    </div>
                </div>

                {{-- Paso 2: Subida de archivo --}}
                <div class="space-y-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                        Paso 2: Sube el archivo completado
                    </label>
                    <div class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50/80 p-6 text-center transition hover:border-emerald-500/50 dark:border-zinc-700 dark:bg-zinc-950/60">
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv,.txt"
                               class="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                        <svg class="h-10 w-10 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p class="mt-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            @if($importFile)
                                <span class="text-emerald-600 font-bold dark:text-emerald-400">Archivo seleccionado: {{ $importFile->getClientOriginalName() }}</span>
                            @else
                                Arrastra tu archivo aquí o <span class="text-emerald-600 dark:text-emerald-400 underline">haz clic para examinar</span>
                            @endif
                        </p>
                        <p class="mt-1 text-[10px] text-zinc-400">Formatos permitidos: .xlsx, .xls, .csv (Máx. 10MB)</p>
                    </div>
                    @error('importFile') <p class="text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror

                    <div wire:loading wire:target="importFile" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                        <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></span>
                        Cargando archivo...
                    </div>
                </div>

                {{-- Resultados / Errores de Validación --}}
                @if(!empty($importSummary))
                    <div class="rounded-xl border p-4 space-y-3 {{ ($importSummary['imported'] ?? 0) > 0 ? 'border-emerald-500/30 bg-emerald-50/50 dark:bg-emerald-950/20' : 'border-rose-500/30 bg-rose-50/50 dark:bg-rose-950/20' }}">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="{{ ($importSummary['imported'] ?? 0) > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                Registros válidos importados: {{ $importSummary['imported'] ?? 0 }} de {{ $importSummary['total'] ?? 0 }}
                            </span>
                            @if(!empty($importSummary['invalid']))
                                <span class="text-rose-600 dark:text-rose-400">{{ $importSummary['invalid'] }} filas con observaciones</span>
                            @endif
                        </div>

                        @if(!empty($importSummary['errors']))
                            <div class="max-h-40 overflow-y-auto space-y-1.5 border-t border-zinc-200 pt-2 dark:border-zinc-800 text-[11px]">
                                @foreach($importSummary['errors'] as $err)
                                    <div class="rounded-lg bg-white p-2 border border-rose-200 dark:bg-zinc-950 dark:border-rose-900/50">
                                        <p class="font-bold text-rose-700 dark:text-rose-400">Fila {{ $err['row'] }} [{{ $err['arete'] }}]:</p>
                                        <ul class="list-disc pl-4 text-zinc-600 dark:text-zinc-400">
                                            @foreach($err['messages'] as $msg)
                                                <li>{{ $msg }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Footer botones --}}
                <div class="flex flex-col-reverse gap-2.5 border-t border-zinc-200 pt-4 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" wire:click="closeImportModal"
                            class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $importSuccess ? 'Cerrar' : 'Cancelar' }}
                    </button>
                    @if(!$importSuccess)
                        <button type="button" wire:click="processImport"
                                wire:loading.attr="disabled"
                                wire:target="processImport,importFile"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-bold text-white shadow-md transition hover:bg-emerald-500 disabled:opacity-50">
                            <span wire:loading.remove wire:target="processImport">Procesar e Importar</span>
                            <span wire:loading wire:target="processImport" class="flex items-center gap-1.5">
                                <span class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                Importando...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-recent-record-host>



