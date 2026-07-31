<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                Inventario Animal
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Monitorea y gestiona los animales registrados en tu fundo.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->tienePermiso('animal', 'exportar'))
                <button wire:click="$set('showExportModal', true)"
                    class="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-300 text-sm font-semibold transition duration-200 flex items-center gap-2">
                <span>&#x1F4E5;</span> Exportar
                </button>
            @endif
            @if(auth()->user()->tienePermiso('animal', 'crear'))
                <a href="{{ route('animal.create') }}" class="agro-button">
                    <span>&#x2795;</span> Nuevo Animal
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
        <div class="border-t border-zinc-850/60 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 items-end">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500 text-sm">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Buscar código o nombre...">
                </div>
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Especie</label>
                <x-filter-select model="especieId" :options="['' => 'Todas'] + collect($especies)->pluck('nombre', 'id')->all()" tone="emerald" live compact />
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Raza</label>
                <x-filter-select model="razaId" :options="['' => 'Todas'] + collect($razas)->pluck('nombre', 'id')->all()" tone="emerald" live compact />
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

            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Acceso rápido</label>
                <x-filter-select model="periodo" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Año</label>
                <x-filter-select model="anio" :options="['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all()" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Mes</label>
                <x-filter-select model="mes" :options="['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre']" tone="emerald" live :disabled="$anio === ''" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <input type="date" wire:model.live="fechaDesde"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="fechaHasta"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
            </div>
        </div>
    </x-collapsible-filters>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                        <th class="p-4 cursor-pointer hover:text-zinc-200 transition whitespace-nowrap" wire:click="sort('arete')">
                            Código {!! $sortBy === 'arete' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : '' !!}
                        </th>
                        <th class="p-4 whitespace-nowrap">Foto</th>
                        <th class="p-4 cursor-pointer hover:text-zinc-200 transition whitespace-nowrap" wire:click="sort('nombre')">
                            Nombre {!! $sortBy === 'nombre' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : '' !!}
                        </th>
                        <th class="p-4 whitespace-nowrap">Tipo / raza</th>
                        <th class="p-4 whitespace-nowrap">Sexo</th>
                        <th class="p-4 whitespace-nowrap">Edad</th>
                        <th class="p-4 whitespace-nowrap">Estado del inventario</th>
                        <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                    @forelse($animales as $animal)
                        @php
                            $isRecent = $this->isRecentRecord('animal.animales', $animal->id);
                        @endphp
                        <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20' }}">
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
                                <span class="block font-semibold text-zinc-200">{{ $animal->especie->nombre ?? '-' }}</span>
                                <span class="mt-0.5 block text-[10px] text-zinc-500">{{ $animal->raza->nombre ?? '-' }}</span>
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
                                                class="inline-flex h-8 shrink-0 items-center rounded-lg border px-2.5 text-[10px] font-black uppercase tracking-wide transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 {{ $animal->activo ? 'border-amber-500/30 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20' : 'border-sky-500/30 bg-sky-500/10 text-sky-300 hover:bg-sky-500/20' }}">
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
            <x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="emerald" live compact />
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
                            <select wire:model.live="statusReason"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                <option value="">Selecciona un motivo...</option>
                                @foreach(\App\Models\Animal::INACTIVE_REASONS as $reason => $label)
                                    <option value="{{ $reason }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('statusReason') <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Fecha de baja <span class="text-rose-500">*</span></label>
                            <input type="date" wire:model="statusDate" max="{{ today()->format('Y-m-d') }}"
                                   class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            @error('statusDate') <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        @if($statusReason === 'venta')
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <p class="text-sm font-black text-emerald-900 dark:text-emerald-100">Venta enlazada con Finanzas</p>
                                <p class="mt-1 text-xs leading-5 text-emerald-700 dark:text-emerald-300">Continuarás al formulario de ingreso con este animal y “Venta de Animales” preseleccionados. La baja se aplicará únicamente cuando el movimiento se guarde correctamente.</p>
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

    <!-- Export Modal -->
    @if($showExportModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ format: @js($exportFormat), columns: @js($selectedColumns) }"
                role="dialog" aria-modal="true" aria-label="Exportar inventario animal"
                class="agro-dialog agro-dialog--md agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Exportar Inventario Animal</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">PDF horizontal o Excel. Ambos respetan filtros, orden y fundo activo.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Formato</span>
                        <div class="grid grid-cols-1 gap-3 min-[400px]:grid-cols-2">
                            <label :class="format === 'xlsx' ? 'border-sky-500 bg-sky-100 text-sky-950 shadow-sm shadow-sky-500/20 dark:border-sky-400 dark:bg-sky-400/20 dark:text-sky-50' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-sky-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'" class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 text-sm font-semibold transition">
                                <input type="radio" x-model="format" value="xlsx" class="sr-only">
                                <span :class="format === 'xlsx' ? 'border-sky-600 bg-sky-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2">
                                    <svg x-cloak x-show="format === 'xlsx'" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>Excel (.xlsx)</span>
                            </label>
                            <label :class="format === 'pdf' ? 'border-rose-500 bg-rose-100 text-rose-950 shadow-sm shadow-rose-500/20 dark:border-rose-400 dark:bg-rose-400/20 dark:text-rose-50' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-rose-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'" class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 text-sm font-semibold transition">
                                <input type="radio" x-model="format" value="pdf" class="sr-only">
                                <span :class="format === 'pdf' ? 'border-rose-600 bg-rose-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2">
                                    <svg x-cloak x-show="format === 'pdf'" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>PDF (.pdf)</span>
                            </label>
                        </div>
                        @error('exportFormat') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Columnas</span>
                            <button type="button" x-on:click="columns = columns.length === {{ count($availableColumns) }} ? [] : @js(array_keys($availableColumns))" class="text-xs font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">Seleccionar todas</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($availableColumns as $key => $label)
                                <label :class="columns.includes('{{ $key }}') ? 'border-violet-500 bg-violet-100 text-violet-950 shadow-sm ring-1 ring-violet-500/20 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-slate-200 bg-white text-slate-600 hover:border-violet-300 hover:bg-violet-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-violet-400/10'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition">
                                    <input type="checkbox" x-model="columns" value="{{ $key }}" class="sr-only">
                                    <span :class="columns.includes('{{ $key }}') ? 'border-violet-700 bg-violet-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition">
                                        <svg x-cloak x-show="columns.includes('{{ $key }}')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                    </span>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">Estado reproductivo y precio de compra son opcionales. Precio solo aplica a animales comprados.</p>
                        @error('selectedColumns') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                        @error('selectedColumns.*') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-end">
                    <button wire:click="$set('showExportModal', false)"
                            class="px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:border-rose-300 hover:bg-rose-100 text-rose-700 dark:border-rose-400/30 dark:bg-rose-400/10 dark:hover:bg-rose-400/20 dark:text-rose-300 text-sm font-semibold transition">
                        Cancelar
                    </button>
                    <button type="button" x-on:click="$wire.exportar(format, columns)" wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-wait" wire:target="exportar"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/20 transition disabled:cursor-wait">
                        Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-recent-record-host>
