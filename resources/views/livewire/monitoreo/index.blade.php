<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <h1 class="bg-gradient-to-r from-emerald-700 to-teal-500 bg-clip-text text-2xl font-extrabold tracking-tight text-transparent sm:text-3xl dark:from-emerald-300 dark:to-teal-300">
                Salud y Reproducción Animal
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Eventos de salud, planes de dosis, partos y alertas del ganado.</p>
        </div>

        <div class="flex w-full flex-col-reverse gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
            @if(auth()->user()->tienePermiso('monitoreo', 'exportar'))
                <button type="button" wire:click="openMonitoreoPdfModal"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-bold text-zinc-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 sm:w-auto dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-emerald-500/50 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-200">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Exportar PDF
                </button>
            @endif

            @if(auth()->user()->tienePermiso('monitoreo', 'crear'))
            @if($tab === 'sanidad')
                <a href="{{ route('monitoreo.sanidad.create') }}" wire:navigate class="agro-button w-full whitespace-nowrap sm:w-auto">
                    <span>+</span> Registrar evento
                </a>
            @elseif($tab === 'partos')
                <a wire:navigate href="{{ route('monitoreo.parto.create') }}" class="agro-button w-full sm:w-auto">
                    <span>&#x1F37C;</span> Registrar Parto
                </a>
            @endif
            @endif
        </div>
    </div>

    <x-monitoreo-dashboard :data="$dashboardData" />

    <div class="no-scrollbar flex overflow-x-auto border-b border-zinc-200 dark:border-zinc-800">
        <button wire:click="$set('tab', 'sanidad')" class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none {{ $tab === 'sanidad' ? 'border-emerald-600 font-bold text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">Salud</button>
        <button wire:click="$set('tab', 'partos')" class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none {{ $tab === 'partos' ? 'border-emerald-600 font-bold text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">&#x1F37C; Reproducción</button>
        <button wire:click="$set('tab', 'alertas')" class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none {{ $tab === 'alertas' ? 'border-emerald-600 font-bold text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">&#x1F514; Alertas</button>
    </div>

    {{-- TAB SANIDAD --}}
    @if($tab === 'sanidad')
        @include('livewire.monitoreo.partials.salud-tab')
    @endif

    {{-- TAB PARTOS --}}
    @if($tab === 'partos')
    @php $partoAnyFilter = $searchParto || $partoTipo || $partoCondicionMadre || $partoCriaEstado || $partoCriaSexo || $partoFechaDesde || $partoFechaHasta; @endphp
    <x-collapsible-filters :active="$partoAnyFilter" title="Filtros de partos" description="Busca por madre, cría, tipo, condición o fecha." id="partos-filter-content" reset="resetPartoFilters">
        <div class="grid w-full grid-cols-1 gap-3 border-t border-zinc-200 pt-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 items-end dark:border-zinc-800">
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500 text-xs">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="searchParto" class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20" placeholder="Madre o cría...">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo de parto</label>
                <x-filter-select model="partoTipo" :options="['' => 'Todos los tipos', 'normal' => 'Normal', 'asistido' => 'Asistido', 'cesarea' => 'Cesárea', 'aborto_prematuro' => 'Aborto / prematuro']" tone="violet" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Condición Madre</label>
                <x-filter-select model="partoCondicionMadre" :options="['' => 'Todas las condiciones', 'optima' => 'Óptima', 'retencion_placenta' => 'Retención de placenta', 'fiebre_leche' => 'Fiebre de leche', 'desgarro' => 'Desgarro']" tone="violet" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Estado Cría</label>
                <x-filter-select model="partoCriaEstado" :options="['' => 'Todos los estados', 'vivo_vigoroso' => 'Vivo y vigoroso', 'debil' => 'Débil', 'muerto_al_nacer' => 'Muerto al nacer']" tone="violet" live compact />
            </div>

            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Sexo Cría</label>
                <x-filter-select model="partoCriaSexo" :options="['' => 'Ambos sexos', 'macho' => 'Macho', 'hembra' => 'Hembra']" tone="violet" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <x-date-picker model="partoFechaDesde" placeholder="dd/mm/aaaa" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <x-date-picker model="partoFechaHasta" placeholder="dd/mm/aaaa" compact />
            </div>
        </div>
    </x-collapsible-filters>

    <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                    <th class="p-4 whitespace-nowrap">Fecha Parto</th>
                    <th class="p-4 whitespace-nowrap">Madre</th>
                    <th class="p-4 whitespace-nowrap">Condición Madre</th>
                    <th class="p-4 whitespace-nowrap">Tipo Parto</th>
                    <th class="p-4 whitespace-nowrap">Cría Nacida</th>
                    <th class="p-4 whitespace-nowrap">Peso Cría</th>
                    <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                @forelse($partos as $part)
                                        @php
                        $isRecent = $this->isRecentRecord('monitoreo.partos', $part->id);
                    @endphp
                    <tr wire:key="parto-{{ $part->id }}" class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/20' }}">
                        <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span>{{ $part->fecha_parto->format('d/m/Y') }}</span>
                                <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                    <x-table-photo :path="$part->madre?->foto_ruta" :frame="$part->madre?->foto_encuadre" alt="Madre" />
                                <div class="min-w-0">
                                    @if($part->madre)
                                    <a href="{{ route('animal.show', $part->madre->id) }}" class="font-bold text-zinc-100 hover:text-emerald-400 transition block truncate max-w-[160px]">
                                        {{ $part->madre->arete }}
                                    </a>
                                    <span class="text-xs text-zinc-500 block truncate max-w-[160px]">{{ $part->madre->nombre ?? 'Sin Nombre' }}</span>
                                    @else
                                    <span class="text-zinc-500 font-semibold">Animal archivado</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap"><x-status-badge :value="$part->condicion_madre" /></td>
                        <td class="p-4 text-zinc-400 whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $part->tipo_parto)) }}</td>
                        <td class="p-4 text-zinc-300 whitespace-nowrap">
                            @if($part->cria)
                                <div class="flex items-center gap-3">
                                    <x-table-photo :path="$part->cria->foto_ruta" :frame="$part->cria->foto_encuadre" alt="Cría" />
                                    <div class="min-w-0">
                                        <a href="{{ route('animal.show', $part->cria->id) }}" class="block max-w-[140px] truncate font-semibold text-zinc-100 hover:text-teal-400">
                                            &#x1F476; {{ $part->cria->arete }}
                                        </a>
                                        <div class="max-w-[140px] truncate text-[10px] font-semibold text-zinc-400">{{ $part->cria->nombre ?: 'Sin nombre' }}</div>
                                        <div class="text-[10px] text-zinc-500">{{ ucfirst($part->cria->genero) }} | {{ ucfirst(str_replace('_', ' ', $part->cria_estado)) }}</div>
                                    </div>
                                </div>
                            @else
                                <x-status-badge value="baja" label="Aborto / Muerto" tone="rose" />
                            @endif
                        </td>
                        <td class="p-4 text-zinc-400 whitespace-nowrap">{{ $part->cria_peso_nacer ? $part->cria_peso_nacer . ' Kg' : '-' }}</td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                            @if(auth()->user()->tienePermiso('monitoreo', 'actualizar'))
                                <x-table-action type="edit" :href="route('monitoreo.parto.edit', $part->id)" />
                            @endif
                            @if(auth()->user()->tienePermiso('monitoreo', 'eliminar'))
                                <x-table-action type="delete" wire:click="solicitarEliminacionParto({{ $part->id }})" />
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-12 text-center text-zinc-500"><div class="text-3xl">&#x1F4ED;</div><div class="mt-2 font-bold text-sm">No se encontraron registros de partos</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="agro-table-footer">
        <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageOptions()" tone="rose" live compact /></div>
        <div class="min-w-0">{{ $partos->links('components.pagination') }}</div>
    </div>
    @endif

    {{-- TAB ALERTAS --}}
    @if($tab === 'alertas')
    @php $alertaAnyFilter = $searchAlerta || $alertaTipo || $alertaFiltroLeida !== '0' || $alertaFechaDesde || $alertaFechaHasta; @endphp
    <x-collapsible-filters :active="$alertaAnyFilter" title="Filtros de alertas" description="Busca por animal, tipo, mensaje o fecha programada." id="alertas-filter-content" reset="resetAlertaFilters">
        <div class="grid w-full grid-cols-1 gap-3 border-t border-zinc-200 pt-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end dark:border-zinc-800">
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500 text-xs">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="searchAlerta" class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" placeholder="Animal o mensaje...">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo de alerta</label>
                <x-filter-select model="alertaTipo" :options="['' => 'Todos los tipos', 'cuarentena' => 'Cuarentena', 'proxima_dosis' => 'Próxima dosis', 'preventivo' => 'Preventivo', 'parto' => 'Parto', 'sanidad' => 'Sanidad']" tone="amber" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Estado de lectura</label>
                <x-filter-select model="alertaFiltroLeida" :options="['' => 'Todas las alertas', '0' => 'Pendientes (No leídas)', '1' => 'Leídas (Archivadas)']" tone="amber" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <x-date-picker model="alertaFechaDesde" placeholder="dd/mm/aaaa" compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <x-date-picker model="alertaFechaHasta" placeholder="dd/mm/aaaa" compact />
            </div>
        </div>
    </x-collapsible-filters>

    {{-- Barra de acciones masivas de alertas (solo admin) --}}
    @if($puedeBorrarAlertas)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                @if(count($selectedAlertas) > 0)
                    <span class="inline-flex items-center gap-2 rounded-full border border-rose-500/25 bg-rose-500/10 px-3 py-1.5 text-xs font-bold text-rose-700 dark:border-rose-400/25 dark:bg-rose-400/10 dark:text-rose-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" /></svg>
                        {{ count($selectedAlertas) }} {{ count($selectedAlertas) === 1 ? 'seleccionada' : 'seleccionadas' }}
                    </span>
                    <button type="button" wire:click="clearAlertasSeleccion"
                            class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-900 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                        Limpiar selección
                    </button>
                    <button type="button" wire:click="openDeleteAlertasMasivoModal('seleccion')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-rose-500 dark:bg-rose-500 dark:hover:bg-rose-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                        Eliminar seleccionadas
                    </button>
                @endif
                @if($alertaAnyFilter && $alertas->total() > 0)
                    <button type="button" wire:click="openDeleteAlertasMasivoModal('filtradas')"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-500/25 bg-rose-500/10 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:border-rose-500/50 hover:bg-rose-500/15 dark:border-rose-400/25 dark:bg-rose-400/10 dark:text-rose-300 dark:hover:border-rose-400/50 dark:hover:bg-rose-400/15">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Eliminar todas las filtradas ({{ $alertas->total() }})
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                    @if($puedeBorrarAlertas)
                        <th class="p-4 w-10">
                            <label class="flex cursor-pointer items-center justify-center" title="{{ count($selectedAlertas) === $alertas->count() && $alertas->isNotEmpty() ? 'Quitar selección de esta página' : 'Seleccionar todas las de esta página' }}">
                                <input type="checkbox" wire:click="toggleSelectAllAlertas" class="agro-checkbox h-4 w-4 rounded border-zinc-700 bg-zinc-800 text-rose-500 focus:ring-rose-500/30" @checked(count($selectedAlertas) > 0 && count($selectedAlertas) === $alertas->count())>
                            </label>
                        </th>
                    @endif
                    <th class="p-4 whitespace-nowrap">Fecha Programada</th>
                    <th class="p-4 whitespace-nowrap">Animal</th>
                    <th class="p-4 whitespace-nowrap">Tipo</th>
                    <th class="p-4 whitespace-nowrap">Mensaje</th>
                    <th class="p-4 whitespace-nowrap">Estado</th>
                    <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                @forelse($alertas as $al)
                    <tr wire:key="alerta-{{ $al->id }}" class="hover:bg-zinc-800/20 transition duration-200 {{ in_array($al->id, $selectedAlertas, true) ? 'bg-rose-500/[.06]' : '' }}">
                        @if($puedeBorrarAlertas)
                            <td class="p-4 w-10">
                                <label class="flex cursor-pointer items-center justify-center">
                                    <input type="checkbox" wire:click="toggleAlertaSeleccion({{ $al->id }})" class="agro-checkbox h-4 w-4 rounded border-zinc-700 bg-zinc-800 text-rose-500 focus:ring-rose-500/30" @checked(in_array($al->id, $selectedAlertas, true))>
                                </label>
                            </td>
                        @endif
                        <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">{{ $al->fecha_alerta->format('d/m/Y') }}</td>
                        <td class="p-4 whitespace-nowrap">
                            @if($al->animal)
                                <div class="flex min-w-0 items-center gap-3">
                                    <x-table-photo :path="$al->animal->foto_ruta" :frame="$al->animal->foto_encuadre" alt="Animal" />
                                    <a href="{{ route('animal.show', $al->animal->id) }}" class="block max-w-[140px] truncate font-bold text-zinc-100 transition hover:text-emerald-400">
                                        {{ $al->animal->arete }}
                                    </a>
                                </div>
                            @else
                                <span class="text-zinc-500 font-semibold whitespace-nowrap">-</span>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap"><x-status-badge :value="$al->tipo" tone="sky" /></td>
                        <td class="p-4 text-zinc-300 truncate max-w-xs" title="{{ $al->mensaje }}">{{ $al->mensaje }}</td>
                        <td class="p-4 whitespace-nowrap">
                            <x-status-badge :value="$al->leida ? 'leida' : 'pendiente'" :label="$al->leida ? 'Leída' : 'Pendiente'" />
                        </td>
                        <td class="p-4 text-right whitespace-nowrap">
                            @php
                                $canMark = !$al->leida && auth()->user()->tienePermiso('monitoreo', 'actualizar');
                            @endphp
                            @if($canMark || $puedeBorrarAlertas)
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($canMark)
                                        <x-table-action type="complete" wire:click="marcarAlertaLeida({{ $al->id }})" label="Marcar como leída" />
                                    @endif
                                    @if($puedeBorrarAlertas)
                                        <x-table-action type="delete" wire:click="openDeleteAlertaModal({{ $al->id }})" label="Eliminar alerta" />
                                    @endif
                                </div>
                            @else
                                <span class="text-zinc-600 text-xs italic">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $puedeBorrarAlertas ? 7 : 6 }}" class="p-12 text-center text-zinc-500"><div class="text-3xl">&#x1F4ED;</div><div class="mt-2 font-bold text-sm">No se encontraron alertas</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="agro-table-footer">
        <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageOptions()" tone="rose" live compact /></div>
        <div class="min-w-0">{{ $alertas->links('components.pagination') }}</div>
    </div>
    @endif

    {{-- PDF Modal --}}
    @if($showMonitoreoPdfModal)
        @php
            $pdfSections = \App\Livewire\Monitoreo\Index::pdfSectionOptions();
            $pdfLabels = \App\Livewire\Monitoreo\Index::pdfColumnLabels();
            $pdfThemes = [
                'sanidad' => [
                    'selected' => 'border-rose-300 bg-rose-50 text-rose-950 ring-1 ring-rose-200 dark:border-rose-500/60 dark:bg-rose-500/10 dark:text-rose-50',
                    'panel' => 'border-rose-200 bg-rose-50/55 dark:border-rose-500/30 dark:bg-rose-500/[.07]',
                    'title' => 'text-rose-900 dark:text-rose-100',
                    'action' => 'text-rose-700 hover:text-rose-600 dark:text-rose-300 dark:hover:text-rose-200',
                    'field' => 'border-rose-300 bg-white/90 text-rose-950 dark:border-rose-500/55 dark:bg-rose-500/10 dark:text-rose-50',
                    'check' => 'border-rose-600 bg-rose-600 dark:border-rose-400 dark:bg-rose-400',
                    'icon' => 'text-white dark:text-rose-950',
                ],
                'partos' => [
                    'selected' => 'border-amber-300 bg-amber-50 text-amber-950 ring-1 ring-amber-200 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-50',
                    'panel' => 'border-amber-200 bg-amber-50/55 dark:border-amber-500/30 dark:bg-amber-500/[.07]',
                    'title' => 'text-amber-900 dark:text-amber-100',
                    'action' => 'text-amber-700 hover:text-amber-600 dark:text-amber-300 dark:hover:text-amber-200',
                    'field' => 'border-amber-300 bg-white/90 text-amber-950 dark:border-amber-500/55 dark:bg-amber-500/10 dark:text-amber-50',
                    'check' => 'border-amber-500 bg-amber-500 dark:border-amber-400 dark:bg-amber-400',
                    'icon' => 'text-amber-950',
                ],
                'alertas' => [
                    'selected' => 'border-violet-300 bg-violet-50 text-violet-950 ring-1 ring-violet-200 dark:border-violet-500/60 dark:bg-violet-500/10 dark:text-violet-50',
                    'panel' => 'border-violet-200 bg-violet-50/55 dark:border-violet-500/30 dark:bg-violet-500/[.07]',
                    'title' => 'text-violet-900 dark:text-violet-100',
                    'action' => 'text-violet-700 hover:text-violet-600 dark:text-violet-300 dark:hover:text-violet-200',
                    'field' => 'border-violet-300 bg-white/90 text-violet-950 dark:border-violet-500/55 dark:bg-violet-500/10 dark:text-violet-50',
                    'check' => 'border-violet-600 bg-violet-600 dark:border-violet-400 dark:bg-violet-400',
                    'icon' => 'text-white dark:text-violet-950',
                ],
            ];
        @endphp
        <div x-data="{
                sections: $wire.entangle('monitoreoPdfSections').live,
                columns: $wire.entangle('monitoreoPdfColumns').live,
                init() { document.body.classList.add('overflow-hidden') },
                destroy() { document.body.classList.remove('overflow-hidden') },
            }"
             x-on:keydown.escape.window="$wire.set('showMonitoreoPdfModal', false)"
             x-on:click.self="$wire.set('showMonitoreoPdfModal', false)"
             class="agro-dialog-overlay agro-dialog-overlay--full">
            <div role="dialog" aria-modal="true" aria-labelledby="monitoreo-report-title"
                 class="agro-dialog agro-dialog--full h-[calc(100dvh-0.5rem)] sm:h-[calc(100dvh-1.5rem)] sm:w-[calc(100vw-1.5rem)]">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-700">
                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Exportación integral</span>
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <span x-text="sections.length"></span> de {{ count($pdfSections) }} secciones
                            </span>
                        </div>
                        <h3 id="monitoreo-report-title" class="text-lg font-bold text-zinc-900 dark:text-white sm:text-xl">Generar reporte PDF de Monitoreo</h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Combina secciones y campos en un único PDF A4 horizontal.</p>
                    </div>
                    <button type="button" wire:click="$set('showMonitoreoPdfModal', false)"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                            aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto xl:grid xl:grid-cols-[21rem_minmax(0,1fr)] xl:overflow-hidden">
                    <aside class="border-b border-zinc-200 bg-zinc-50/70 p-4 xl:overflow-y-auto xl:border-b-0 xl:border-r dark:border-zinc-700 dark:bg-zinc-950/25">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-300">Secciones</span>
                            <button type="button"
                                    x-on:click="sections = sections.length === {{ count($pdfSections) }} ? [] : @js(array_keys($pdfSections))"
                                    class="text-xs font-bold text-emerald-700 transition hover:text-emerald-600 focus:outline-none focus:underline dark:text-emerald-400 dark:hover:text-emerald-300">
                                Seleccionar todas
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-1">
                        @foreach($pdfSections as $key => $option)
                            @php $theme = $pdfThemes[$key]; @endphp
                            <label :class="sections.includes('{{ $key }}')
                                    ? @js($theme['selected'])
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-300 dark:hover:border-zinc-600'"
                                   class="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 transition focus-within:ring-2 focus-within:ring-emerald-500/50">
                                <input type="checkbox" x-model="sections" value="{{ $key }}" class="sr-only">
                                <span :class="sections.includes('{{ $key }}') ? @js($theme['check']) : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900'"
                                      class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 transition">
                                    <svg x-cloak x-show="sections.includes('{{ $key }}')" class="h-3 w-3 {{ $theme['icon'] }}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span class="min-w-0 leading-tight">
                                    <strong class="block text-sm">{{ $option['label'] }}</strong>
                                    <small class="mt-0.5 block text-[11px] font-normal opacity-65">{{ $option['description'] }}</small>
                                </span>
                            </label>
                        @endforeach
                        </div>
                        @error('monitoreoPdfSections') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        @error('monitoreoPdfSections.*') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </aside>

                    <div class="p-4 sm:p-5 xl:overflow-y-auto 2xl:p-6">
                        <div x-cloak x-show="sections.length === 0" class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 text-center dark:border-zinc-700 dark:bg-zinc-950/30">
                            <div class="max-w-sm">
                                <strong class="block text-sm text-zinc-700 dark:text-zinc-200">Sin secciones seleccionadas</strong>
                                <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Activa una sección para configurar campos y filtros del reporte.</span>
                            </div>
                        </div>
                        <div class="grid content-start gap-4 xl:grid-cols-2 2xl:gap-5">
                            @foreach($pdfSections as $sectionKey => $sectionOption)
                                @php
                                    $fields = $pdfLabels[$sectionKey] ?? [];
                                    $theme = $pdfThemes[$sectionKey];
                                @endphp
                                <section x-cloak x-show="sections.includes('{{ $sectionKey }}')"
                                         class="rounded-2xl border p-4 2xl:p-5 {{ $theme['panel'] }}">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <strong class="block text-sm {{ $theme['title'] }}">Campos: {{ $sectionOption['label'] }}</strong>
                                            <small class="mt-0.5 block text-[11px] text-zinc-500 dark:text-zinc-400">Elige información incluida en esta sección.</small>
                                        </div>
                                        <button type="button"
                                                x-on:click="columns['{{ $sectionKey }}'] = columns['{{ $sectionKey }}'].length === {{ count($fields) }} ? [] : @js(array_keys($fields))"
                                                class="shrink-0 text-[11px] font-bold transition focus:outline-none focus:underline {{ $theme['action'] }}">
                                            Seleccionar todos
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        @foreach($fields as $fieldKey => $fieldLabel)
                                            <label :class="columns['{{ $sectionKey }}'].includes('{{ $fieldKey }}')
                                                    ? @js($theme['field'])
                                                    : 'border-zinc-200 bg-white/75 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                                   class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border px-2.5 py-2 text-xs font-semibold leading-tight transition focus-within:ring-2 focus-within:ring-current/30">
                                                <input type="checkbox" x-model="columns['{{ $sectionKey }}']" value="{{ $fieldKey }}" class="sr-only">
                                                <span :class="columns['{{ $sectionKey }}'].includes('{{ $fieldKey }}') ? @js($theme['check']) : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-950'"
                                                      class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition">
                                                    <svg x-cloak x-show="columns['{{ $sectionKey }}'].includes('{{ $fieldKey }}')" class="h-3 w-3 {{ $theme['icon'] }}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                                </span>
                                                <span>{{ $fieldLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error("monitoreoPdfColumns.{$sectionKey}") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                    @error("monitoreoPdfColumns.{$sectionKey}.*") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="hidden text-xs text-zinc-500 sm:block dark:text-zinc-400">Un PDF · Secciones combinadas · Filtros activos</p>
                    <div class="flex gap-2 sm:justify-end">
                        <button type="button" wire:click="$set('showMonitoreoPdfModal', false)"
                                class="h-11 flex-1 rounded-xl border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 sm:flex-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            Cancelar
                        </button>
                        <button type="button" wire:click="downloadMonitoreoReport" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70" wire:target="downloadMonitoreoReport"
                                class="h-11 flex-1 rounded-xl bg-emerald-700 px-6 text-sm font-bold text-white shadow-md shadow-emerald-700/15 transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait sm:flex-none dark:bg-emerald-500 dark:text-emerald-950 dark:hover:bg-emerald-400 dark:focus:ring-offset-zinc-900">
                            <span wire:loading.remove wire:target="downloadMonitoreoReport" class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ver Vista Previa PDF</span>
                            </span>
                            <span wire:loading wire:target="downloadMonitoreoReport">Generando vista previa...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: Eliminar alerta programada (solo admin) --}}
    <div
        x-cloak
        x-show="$wire.showDeleteAlertaModal"
        x-init="$watch('$wire.showDeleteAlertaModal', val => val ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
        x-on:keydown.escape.window="$wire.closeDeleteAlertaModal()"
        x-on:click.self="$wire.closeDeleteAlertaModal()"
        class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-md transition-all dark:bg-black/70"
        role="dialog" aria-modal="true" aria-labelledby="delete-alerta-title"
    >
        <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-zinc-200 bg-white p-5 shadow-2xl transition-all dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
            <!-- Header -->
            <header class="flex items-start justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <svg class="h-5.5 w-5.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-widest text-rose-600 dark:text-rose-400">Monitoreo · Alertas</p>
                        <h3 id="delete-alerta-title" class="mt-0.5 text-lg font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">Eliminar alerta programada</h3>
                        <p class="mt-1 text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-300">Esta alerta se borrará de forma definitiva. No afecta al animal ni a sus dosis programadas.</p>
                    </div>
                </div>
                <button type="button" wire:click="closeDeleteAlertaModal" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200" aria-label="Cerrar">&times;</button>
            </header>

            <div class="mt-4 space-y-4">
                @if(!empty($deleteAlertaData))
                    <!-- Resumen de la alerta -->
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
                            <div>
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha programada</dt>
                                <dd class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $deleteAlertaData['fecha'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Animal</dt>
                                <dd class="mt-1 truncate text-sm font-bold text-zinc-900 dark:text-zinc-100" title="{{ $deleteAlertaData['animal'] }}">{{ $deleteAlertaData['animal'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tipo</dt>
                                <dd class="mt-1"><x-status-badge :value="$deleteAlertaData['tipo']" tone="sky" /></dd>
                            </div>
                            <div class="col-span-2 sm:col-span-3">
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Mensaje</dt>
                                <dd class="mt-1 text-sm leading-relaxed text-zinc-700 dark:text-zinc-200">{{ $deleteAlertaData['mensaje'] }}</dd>
                            </div>
                            <div class="col-span-2 sm:col-span-3">
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</dt>
                                <dd class="mt-1">
                                    <x-status-badge :value="$deleteAlertaData['leida'] ? 'leida' : 'pendiente'" :label="$deleteAlertaData['leida'] ? 'Leída' : 'Pendiente'" />
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-[11px] leading-relaxed text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                    <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong class="block font-bold text-amber-950 dark:text-amber-200">Acción permanente e irreversible:</strong>
                        <span>Se eliminará solo esta alerta. El animal y sus dosis programadas permanecen intactos.</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-5 flex flex-col-reverse gap-2.5 border-t border-zinc-100 pt-4 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" wire:click="closeDeleteAlertaModal" wire:loading.attr="disabled" wire:target="deleteAlerta"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-900 disabled:opacity-60 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="deleteAlerta"
                    wire:loading.attr="disabled"
                    wire:target="deleteAlerta"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-rose-500 disabled:opacity-60 dark:bg-rose-500 dark:hover:bg-rose-400 dark:text-white"
                >
                    <svg wire:loading.remove wire:target="deleteAlerta" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    <span wire:loading.remove wire:target="deleteAlerta">Eliminar alerta</span>
                    <span wire:loading wire:target="deleteAlerta">Eliminando...</span>
                </button>
            </footer>
        </div>
    </div>

    {{-- MODAL: Eliminar alertas en masa (solo admin) --}}
    <div
        x-cloak
        x-show="$wire.showDeleteAlertasMasivoModal"
        x-init="$watch('$wire.showDeleteAlertasMasivoModal', val => val ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
        x-on:keydown.escape.window="$wire.closeDeleteAlertasMasivoModal()"
        x-on:click.self="$wire.closeDeleteAlertasMasivoModal()"
        class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-md transition-all dark:bg-black/70"
        role="dialog" aria-modal="true" aria-labelledby="delete-alertas-masivo-title"
    >
        <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-zinc-200 bg-white p-5 shadow-2xl transition-all dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
            <!-- Header -->
            <header class="flex items-start justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <svg class="h-5.5 w-5.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-widest text-rose-600 dark:text-rose-400">Monitoreo · Alertas</p>
                        <h3 id="delete-alertas-masivo-title" class="mt-0.5 text-lg font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                            {{ $deleteAlertasMasivoMode === 'filtradas' ? 'Eliminar alertas filtradas' : 'Eliminar alertas seleccionadas' }}
                        </h3>
                        <p class="mt-1 text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-300">
                            {{ $deleteAlertasMasivoMode === 'filtradas'
                                ? 'Se borrarán todas las alertas que coinciden con los filtros activos.'
                                : 'Se borrarán de forma definitiva las alertas marcadas.' }}
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="closeDeleteAlertasMasivoModal" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200" aria-label="Cerrar">&times;</button>
            </header>

            <div class="mt-4 space-y-4">
                <!-- Conteo grande -->
                <div class="flex items-center gap-4 rounded-2xl border border-rose-200 bg-rose-50/70 p-4 dark:border-rose-500/25 dark:bg-rose-500/10">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-rose-600 text-white shadow-md shadow-rose-600/20 dark:bg-rose-500">
                        <span class="text-2xl font-black">{{ $deleteAlertasMasivoCount ?? 0 }}</span>
                    </div>
                    <div>
                        <strong class="block text-sm font-extrabold text-rose-950 dark:text-rose-100">
                            {{ $deleteAlertasMasivoCount === 1 ? 'alerta será eliminada' : 'alertas serán eliminadas' }}
                        </strong>
                        <p class="mt-0.5 text-xs text-rose-800/80 dark:text-rose-300/70">
                            {{ $deleteAlertasMasivoMode === 'filtradas' ? 'Según los filtros activos del listado.' : 'De las seleccionadas en el listado.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-[11px] leading-relaxed text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                    <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong class="block font-bold text-amber-950 dark:text-amber-200">Acción permanente e irreversible:</strong>
                        <span>Se eliminarán solo estas alertas. Los animales y sus dosis programadas permanecen intactos.</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-5 flex flex-col-reverse gap-2.5 border-t border-zinc-100 pt-4 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" wire:click="closeDeleteAlertasMasivoModal" wire:loading.attr="disabled" wire:target="deleteAlertasMasivo"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-900 disabled:opacity-60 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="deleteAlertasMasivo"
                    wire:loading.attr="disabled"
                    wire:target="deleteAlertasMasivo"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-rose-500 disabled:opacity-60 dark:bg-rose-500 dark:hover:bg-rose-400 dark:text-white"
                >
                    <svg wire:loading.remove wire:target="deleteAlertasMasivo" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    <span wire:loading.remove wire:target="deleteAlertasMasivo">Eliminar {{ $deleteAlertasMasivoCount ?? 0 }}</span>
                    <span wire:loading wire:target="deleteAlertasMasivo">Eliminando...</span>
                </button>
            </footer>
        </div>
    </div>

    @include('livewire.monitoreo.partials.salud-modals')

    {{-- Modal anterior desactivado durante compatibilidad de datos --}}
    <div
        x-cloak
        x-show="false"
        x-init="$watch('$wire.showVerCasoModal', val => val ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
        x-on:keydown.escape.window="$wire.closeVerCasoModal()"
        x-on:click.self="$wire.closeVerCasoModal()"
        class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-md transition-all dark:bg-black/70"
        role="dialog" aria-modal="true" aria-labelledby="ver-caso-title"
    >
        <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl border border-zinc-200 bg-white p-5 shadow-2xl transition-all dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
            <!-- Header -->
            <header class="flex items-start justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400">
                        <svg class="h-5.5 w-5.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-widest text-sky-600 dark:text-sky-400">Monitoreo · Detalle</p>
                        <h3 id="ver-caso-title" class="mt-0.5 text-lg font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                            {{ !empty($verCasoData) && ($verCasoData['tipo_evento'] ?? null) === 'preventivo' ? 'Intervención preventiva' : 'Caso clínico' }}
                        </h3>
                        <p class="mt-1 text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-300">
                            @if(!empty($verCasoData['animal_url']))
                                <a href="{{ $verCasoData['animal_url'] }}" class="font-semibold text-sky-700 hover:underline dark:text-sky-300">
                                    {{ $verCasoData['arete'] }} · {{ $verCasoData['nombre'] }}
                                </a>
                            @else
                                {{ $verCasoData['arete'] ?? '-' }} · {{ $verCasoData['nombre'] ?? '-' }}
                            @endif
                            &nbsp;·&nbsp; {{ $verCasoData['fecha_evento'] ?? '-' }}
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="closeVerCasoModal" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200" aria-label="Cerrar">&times;</button>
            </header>

            <div class="mt-4 space-y-4">
                @if(!empty($verCasoData))
                    @if(($verCasoData['tipo_evento'] ?? null) === 'preventivo')
                        {{-- Vista preventiva --}}
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tipo de aplicación</dt>
                                    <dd class="mt-1"><x-status-badge :value="$verCasoData['tipo_intervencion'] ?? 'preventivo'" tone="sky" /></dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Alcance</dt>
                                    <dd class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ ucfirst($verCasoData['alcance'] ?? 'individual') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Producto / Marca</dt>
                                    <dd class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $verCasoData['producto_marca'] ?? '-' }}</dd>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Propósito</dt>
                                    <dd class="mt-1 text-sm leading-relaxed text-zinc-700 dark:text-zinc-200">{{ $verCasoData['proposito'] ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Responsable</dt>
                                    <dd class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $verCasoData['responsable'] ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Próxima dosis</dt>
                                    <dd class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $verCasoData['proxima_dosis'] ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @else
                        {{-- Vista clínica --}}
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Clasificación</dt>
                                    <dd class="mt-1"><x-status-badge :value="$verCasoData['clasificacion'] ?? 'enfermedad_infecciosa'" tone="violet" /></dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</dt>
                                    <dd class="mt-1"><x-status-badge :value="$verCasoData['estado'] ?? 'en_tratamiento'" /></dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Medicamento</dt>
                                    <dd class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $verCasoData['medicamento'] ?? '-' }}{{ $verCasoData['dosis_via'] && $verCasoData['dosis_via'] !== '-' ? ' · '.$verCasoData['dosis_via'] : '' }}</dd>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Síntomas / Diagnóstico</dt>
                                    <dd class="mt-1 text-sm leading-relaxed text-zinc-700 dark:text-zinc-200">{{ $verCasoData['sintomas'] ?? '-' }}</dd>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tratamiento</dt>
                                    <dd class="mt-1 text-sm leading-relaxed text-zinc-700 dark:text-zinc-200">{{ $verCasoData['tratamiento'] ?? '-' }}</dd>
                                </div>
                                @if($verCasoData['fecha_cierre'])
                                <div>
                                    <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Alta</dt>
                                    <dd class="mt-1 text-sm font-semibold text-emerald-700 dark:text-emerald-300">✔ {{ $verCasoData['fecha_cierre'] }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    {{-- Plan de dosis --}}
                    @if(!empty($verCasoData['dosisPlan']))
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                            <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Plan de aplicaciones</dt>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($verCasoData['dosisPlan'] as $d)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[11px] font-bold {{ $d['aplicada'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-300' }}" title="{{ $d['nombre'] }} · {{ $d['dosis'] ?: 's/dosis' }} · {{ $d['via'] ?: 's/vía' }}">
                                        {{ $d['aplicada'] ? '✔' : '⏳' }} Dosis {{ $d['numero'] }}: {{ $d['fecha_programada'] }} · {{ $d['nombre'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Fotos --}}
                    @if(!empty($verCasoData['fotos']))
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                            <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Evidencia ({{ count($verCasoData['fotos']) }})</dt>
                            <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                                @foreach($verCasoData['fotos'] as $foto)
                                    <a href="{{ $foto['url'] }}" target="_blank" class="block overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                        <img src="{{ $foto['url'] }}" alt="Evidencia" class="h-20 w-full object-cover transition hover:opacity-80" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Footer -->
            <footer class="mt-5 flex flex-col-reverse gap-2.5 border-t border-zinc-100 pt-4 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" wire:click="closeVerCasoModal"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-900 disabled:opacity-60 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                    Cerrar
                </button>
                @if(auth()->user()->tienePermiso('monitoreo', 'actualizar') && $verCasoId)
                    <a href="{{ route('monitoreo.sanidad.edit', $verCasoId) }}"
                       class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-emerald-500 disabled:opacity-60 dark:bg-emerald-500 dark:hover:bg-emerald-400 dark:text-white">
                        Editar caso
                    </a>
                @endif
            </footer>
        </div>
    </div>

    {{-- MODAL: Marcar caso clínico como recuperado --}}
    <div
        x-cloak
        x-show="false"
        x-init="$watch('$wire.showRecuperarCasoModal', val => val ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
        x-on:keydown.escape.window="$wire.closeRecuperarCasoModal()"
        x-on:click.self="$wire.closeRecuperarCasoModal()"
        class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-md transition-all dark:bg-black/70"
        role="dialog" aria-modal="true" aria-labelledby="recuperar-caso-title"
    >
        <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-zinc-200 bg-white p-5 shadow-2xl transition-all dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
            <!-- Header -->
            <header class="flex items-start justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <svg class="h-5.5 w-5.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25c-2.485 0-4.5 2.015-4.5 4.5s2.015 4.5 4.5 4.5 4.5-2.015 4.5-4.5-2.015-4.5-4.5-4.5ZM12 5.25c4.142 0 7.5 3.358 7.5 7.5 0 4.142-3.358 7.5-7.5 7.5-4.142 0-7.5-3.358-7.5-7.5 0-4.142 3.358-7.5 7.5-7.5Zm7.778 12.5 1.591 1.591M4.222 5.25l-1.591 1.591M21 12h-1.5M4.5 12H3M12 3v1.5M12 19.5V21" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Monitoreo · Seguimiento</p>
                        <h3 id="recuperar-caso-title" class="mt-0.5 text-lg font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">Marcar como recuperado</h3>
                        <p class="mt-1 text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-300">Registra el alta del caso clínico. Se cierran las alertas de cuarentena activas.</p>
                    </div>
                </div>
                <button type="button" wire:click="closeRecuperarCasoModal" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200" aria-label="Cerrar">&times;</button>
            </header>

            <div class="mt-4 space-y-4">
                @if(!empty($recuperarCasoData))
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/50">
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
                            <div>
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Animal</dt>
                                <dd class="mt-1 truncate text-sm font-bold text-zinc-900 dark:text-zinc-100" title="{{ $recuperarCasoData['arete'] }} · {{ $recuperarCasoData['nombre'] }}">{{ $recuperarCasoData['arete'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Diagnóstico</dt>
                                <dd class="mt-1 truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200" title="{{ $recuperarCasoData['diagnostico'] }}">{{ $recuperarCasoData['diagnostico'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Inicio</dt>
                                <dd class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $recuperarCasoData['fecha'] }}</dd>
                            </div>
                            <div class="col-span-2 sm:col-span-3">
                                <dt class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Plan de aplicaciones</dt>
                                <dd class="mt-1 flex items-center gap-3 text-sm font-semibold">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300">
                                        ✔ {{ $recuperarCasoData['aplicadas'] }} aplicadas
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-400/10 dark:text-amber-300">
                                        â³ {{ $recuperarCasoData['pendientes'] }} pendientes
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Fecha de recuperación</span>
                        <x-date-picker model="recuperarCasoFecha" :max="now()->toDateString()" placeholder="dd/mm/aaaa" />
                        @error('recuperarCasoFecha') <p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Observaciones de cierre <span class="font-normal text-zinc-400">(opcional)</span></span>
                    <textarea wire:model="recuperarCasoObservaciones" rows="3" maxlength="1000"
                              class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-800 outline-none transition placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                              placeholder="Ej: Evolución favorable, animal activo y sin fiebre."></textarea>
                    @error('recuperarCasoObservaciones') <p class="mt-1.5 text-xs font-bold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </label>

                <div class="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 text-[11px] leading-relaxed text-emerald-900 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                    <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <div>
                        <strong class="block font-bold text-emerald-950 dark:text-emerald-200">Alta del caso:</strong>
                        <span>El animal queda como recuperado en su historial y las alertas de cuarentena pendientes se cierran automáticamente.</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-5 flex flex-col-reverse gap-2.5 border-t border-zinc-100 pt-4 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" wire:click="closeRecuperarCasoModal" wire:loading.attr="disabled" wire:target="confirmarRecuperacion"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-900 disabled:opacity-60 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="confirmarRecuperacion"
                    wire:loading.attr="disabled"
                    wire:target="confirmarRecuperacion"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-emerald-500 disabled:opacity-60 dark:bg-emerald-500 dark:hover:bg-emerald-400 dark:text-white"
                >
                    <svg wire:loading.remove wire:target="confirmarRecuperacion" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 4.5 4.5 10.5-10.5" /></svg>
                    <span wire:loading.remove wire:target="confirmarRecuperacion">Confirmar recuperación</span>
                    <span wire:loading wire:target="confirmarRecuperacion">Guardando...</span>
                </button>
            </footer>
        </div>
    </div>

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
        :back-action="'$set(\'showMonitoreoPdfModal\', true); $set(\'showExportModal\', false)'"
    >
    </x-pdf-preview-modal>
</x-recent-record-host>


