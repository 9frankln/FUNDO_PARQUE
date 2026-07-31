<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <h1 class="bg-gradient-to-r from-emerald-700 to-teal-500 bg-clip-text text-2xl font-extrabold tracking-tight text-transparent sm:text-3xl dark:from-emerald-300 dark:to-teal-300">
                Monitoreo Sanitario y Reproducción
            </h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Registra eventos clínicos, vacunaciones preventivas, control de partos y alertas.</p>
        </div>

        <div class="flex w-full flex-col-reverse gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
            @if(auth()->user()->tienePermiso('monitoreo', 'exportar'))
                <button type="button" wire:click="openMonitoreoPdfModal"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-emerald-500/50 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-200">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Exportar PDF
                </button>
            @endif

            @if(auth()->user()->tienePermiso('monitoreo', 'crear'))
            @if($tab === 'sanidad')
                <a href="{{ route('monitoreo.sanidad.create') }}" class="agro-button w-full sm:w-auto">
                    <span>&#x1F3E5;</span> Registrar Evento Clínico
                </a>
            @elseif($tab === 'profilaxis')
                <a href="{{ route('monitoreo.profilaxis.create') }}" class="agro-button w-full sm:w-auto">
                    <span>&#x1F6E1;&#xFE0F;</span> Nueva Intervención Profiláctica
                </a>
            @elseif($tab === 'partos')
                <a href="{{ route('monitoreo.parto.create') }}" class="agro-button w-full sm:w-auto">
                    <span>&#x1F37C;</span> Registrar Parto
                </a>
            @endif
            @endif
        </div>
    </div>

    <x-monitoreo-dashboard :data="$dashboardData" />

    <div class="no-scrollbar flex overflow-x-auto border-b border-slate-200 dark:border-slate-800">
        <button wire:click="$set('tab', 'sanidad')" class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none {{ $tab === 'sanidad' ? 'border-emerald-600 font-bold text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">&#x1F3E5; Historial Clínico</button>
        <button wire:click="$set('tab', 'profilaxis')" class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none {{ $tab === 'profilaxis' ? 'border-emerald-600 font-bold text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">&#x1F6E1;&#xFE0F; Profilaxis y Vacunas</button>
        <button wire:click="$set('tab', 'partos')" class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none {{ $tab === 'partos' ? 'border-emerald-600 font-bold text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">&#x1F37C; Partos (Reproducción)</button>
        <button wire:click="$set('tab', 'alertas')" class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none {{ $tab === 'alertas' ? 'border-emerald-600 font-bold text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">&#x1F514; Alertas Programadas</button>
    </div>

    {{-- TAB SANIDAD --}}
    @if($tab === 'sanidad')
    @php $sanidadAnyFilter = $searchSanidad || $sanidadClasificacion || $sanidadEstadoClinico || $sanidadFechaDesde || $sanidadFechaHasta; @endphp
    <x-collapsible-filters :active="$sanidadAnyFilter" title="Filtros del historial clínico" description="Busca por animal, síntomas, clasificación, estado o fecha." id="sanidad-filter-content" reset="resetSanidadFilters">
        <div class="grid w-full grid-cols-1 gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end dark:border-slate-800">
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="searchSanidad" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500" placeholder="Animal, síntomas...">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Clasificación</label>
                <x-filter-select model="sanidadClasificacion" :options="['' => 'Todas las clasificaciones', 'enfermedad_infecciosa' => 'Enfermedad infecciosa', 'trastorno_metabolico' => 'Trastorno metabólico', 'lesion_accidente' => 'Lesión / accidente']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Estado</label>
                <x-filter-select model="sanidadEstadoClinico" :options="['' => 'Todos los estados', 'en_tratamiento' => 'En tratamiento', 'recuperada' => 'Recuperada', 'critico' => 'Crítico', 'cuarentena' => 'Cuarentena', 'baja' => 'Baja']" tone="emerald" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <input type="date" wire:model.live="sanidadFechaDesde" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="sanidadFechaHasta" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            </div>
        </div>
    </x-collapsible-filters>

    <div class="agro-record-surface hidden overflow-hidden rounded-2xl border xl:block">
        <table class="w-full table-fixed border-collapse text-left">
            <colgroup>
                <col class="w-[7%]">
                <col class="w-[14%]">
                <col class="w-[16%]">
                <col class="w-[21%]">
                <col class="w-[23%]">
                <col class="w-[11%]">
                <col class="w-[8%]">
            </colgroup>
            <thead>
                <tr class="agro-record-header border-b text-[10px] font-bold uppercase tracking-wider">
                    <th class="px-3 py-2.5">Fecha</th>
                    <th class="px-3 py-2.5">Animal</th>
                    <th class="px-3 py-2.5">Clasificación</th>
                    <th class="px-3 py-2.5">Síntomas / diagnóstico</th>
                    <th class="border-l border-zinc-800/70 px-3 py-2.5">Tratamiento / medicación</th>
                    <th class="px-3 py-2.5">Estado</th>
                    <th class="px-3 py-2.5 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="agro-record-list text-xs text-zinc-300">
                @forelse($sanidades as $san)
                @php
                    $isRecent = $this->isRecentRecord('monitoreo.sanidad', $san->id);
                @endphp
                    <tr class="align-top transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20' }}">
                        <td class="whitespace-nowrap px-3 py-2.5 text-[11px] font-bold text-zinc-100">
                            {{ $san->fecha_evento->format('d/m/Y') }}
                            <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                        </td>
                        <td class="px-3 py-2.5">
                            <div class="flex min-w-0 items-center gap-2">
                                <x-table-photo :path="$san->animal?->foto_ruta" :frame="$san->animal?->foto_encuadre" :alt="$san->animal?->nombre ?: 'Animal'" size="sm" />
                                <div class="min-w-0">
                                    @if($san->animal)
                                    <a href="{{ route('animal.show', $san->animal->id) }}" class="block truncate font-bold text-zinc-100 transition hover:text-emerald-400">
                                        {{ $san->animal->arete }}
                                    </a>
                                    <span class="block truncate text-[10px] text-zinc-500">{{ $san->animal->nombre ?? 'Sin Nombre' }}</span>
                                    @else
                                    <span class="text-zinc-500 font-semibold">Animal archivado</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-zinc-400">
                            <x-status-badge :value="$san->clasificacion" tone="violet" :dot="false" class="max-w-full !whitespace-normal !px-2 text-center text-[10px] leading-4" />
                        </td>
                        <td class="px-3 py-2.5 text-zinc-400">
                            <p class="break-words text-[11px] leading-[1.45] text-zinc-300">{{ $san->sintomas_diagnostico ?? '-' }}</p>
                        </td>
                        <td class="border-l border-zinc-800/70 px-3 py-2.5 text-zinc-400">
                            <p class="break-words text-[11px] leading-[1.45] text-zinc-300">{{ $san->tratamiento ?? 'Sin tratamiento' }}</p>
                            @if($san->medicamento || $san->medicamento_nombre || $san->dosis_via)
                                <p class="mt-0.5 break-words text-[10px] font-semibold leading-[1.4] text-zinc-500">
                                    {{ $san->medicamento?->nombre ?? $san->medicamento_nombre ?? 'Sin medicamento' }}{{ $san->dosis_via ? ' · '.$san->dosis_via : '' }}
                                </p>
                            @endif
                        </td>
                        <td class="px-3 py-2.5">
                            <x-status-badge :value="$san->estado_clinico" class="text-[10px]" />
                        </td>
                        <td class="px-3 py-2.5">
                            <div class="flex items-center justify-end gap-1.5">
                            @if(auth()->user()->tienePermiso('monitoreo', 'actualizar'))
                                <x-table-action type="edit" :href="route('monitoreo.sanidad.edit', $san->id)" />
                            @endif
                            @if(auth()->user()->tienePermiso('monitoreo', 'eliminar'))
                                <x-table-action type="delete" wire:click="solicitarEliminacionSanidad({{ $san->id }})" />
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-12 text-center text-zinc-500"><div class="text-3xl">&#x1F4ED;</div><div class="mt-2 text-sm font-bold">No se encontraron registros clínicos</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="agro-record-surface overflow-hidden rounded-2xl border xl:hidden">
        <div class="agro-record-list">
            @forelse($sanidades as $san)
                @php
                    $isRecent = $this->isRecentRecord('monitoreo.sanidad', $san->id);
                @endphp
                <article class="space-y-3 p-4 transition {{ $isRecent ? 'bg-emerald-500/10' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <x-table-photo :path="$san->animal?->foto_ruta" :frame="$san->animal?->foto_encuadre" :alt="$san->animal?->nombre ?: 'Animal'" />
                            <div class="min-w-0">
                                @if($san->animal)
                                    <a href="{{ route('animal.show', $san->animal->id) }}" class="block truncate text-xs font-bold text-zinc-100 hover:text-emerald-400">{{ $san->animal->arete }} · {{ $san->animal->nombre ?? 'Sin nombre' }}</a>
                                @else
                                    <span class="text-xs font-semibold text-zinc-500">Animal archivado</span>
                                @endif
                                <span class="mt-0.5 block text-[10px] font-semibold text-zinc-500">{{ $san->fecha_evento->format('d/m/Y') }}</span>
                                <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                            </div>
                        </div>
                        <x-status-badge :value="$san->estado_clinico" class="shrink-0 text-[10px]" />
                    </div>

                    <div><x-status-badge :value="$san->clasificacion" tone="violet" class="text-[10px]" /></div>

                    <div class="agro-record-detail grid grid-cols-2 overflow-hidden rounded-xl border">
                        <div class="min-w-0 p-3">
                            <span class="text-[9px] font-bold uppercase tracking-wide text-zinc-500">Síntomas / diagnóstico</span>
                            <p class="mt-1 break-words text-xs leading-5 text-zinc-300">{{ $san->sintomas_diagnostico ?? '-' }}</p>
                        </div>
                        <div class="min-w-0 border-l border-zinc-800/70 p-3">
                            <span class="text-[9px] font-bold uppercase tracking-wide text-zinc-500">Tratamiento / medicación</span>
                            <p class="mt-1 break-words text-xs leading-5 text-zinc-300">{{ $san->tratamiento ?? 'Sin tratamiento' }}</p>
                            @if($san->medicamento || $san->medicamento_nombre || $san->dosis_via)
                                <p class="mt-1 break-words text-[10px] font-semibold text-zinc-500">{{ $san->medicamento?->nombre ?? $san->medicamento_nombre ?? 'Sin medicamento' }}{{ $san->dosis_via ? ' · '.$san->dosis_via : '' }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        @if(auth()->user()->tienePermiso('monitoreo', 'actualizar'))
                            <x-table-action type="edit" :href="route('monitoreo.sanidad.edit', $san->id)" />
                        @endif
                        @if(auth()->user()->tienePermiso('monitoreo', 'eliminar'))
                            <x-table-action type="delete" wire:click="solicitarEliminacionSanidad({{ $san->id }})" />
                        @endif
                    </div>
                </article>
            @empty
                <div class="p-10 text-center text-zinc-500"><div class="text-3xl">&#x1F4ED;</div><div class="mt-2 text-sm font-bold">No se encontraron registros clínicos</div></div>
            @endforelse
        </div>
    </div>
    <div class="agro-table-footer">
        <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="rose" live compact /></div>
        <div class="min-w-0">{{ $sanidades->links('components.pagination') }}</div>
    </div>
    @endif

    {{-- TAB PROFILAXIS --}}
    @if($tab === 'profilaxis')
    @php $profAnyFilter = $searchProfilaxis || $profilaxisTipo || $profilaxisFechaDesde || $profilaxisFechaHasta; @endphp
    <x-collapsible-filters :active="$profAnyFilter" title="Filtros de profilaxis" description="Busca por producto, propósito, tipo o fecha." id="profilaxis-filter-content" reset="resetProfilaxisFilters">
        <div class="grid w-full grid-cols-1 gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end dark:border-slate-800">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="searchProfilaxis" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500" placeholder="Producto, propósito...">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo de intervención</label>
                <x-filter-select model="profilaxisTipo" :options="['' => 'Todos los tipos', 'vacuna' => 'Vacuna', 'desparasitante_interno' => 'Desparasitante interno', 'desparasitante_externo' => 'Desparasitante externo', 'vitamina' => 'Vitamina']" tone="sky" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <input type="date" wire:model.live="profilaxisFechaDesde" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="profilaxisFechaHasta" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            </div>
        </div>
    </x-collapsible-filters>

    <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                    <th class="p-4 whitespace-nowrap">Fecha Aplicación</th>
                    <th class="p-4 whitespace-nowrap">Tipo Intervención</th>
                    <th class="p-4 whitespace-nowrap">Producto / Marca</th>
                    <th class="p-4 whitespace-nowrap">Dosis</th>
                    <th class="p-4 whitespace-nowrap">Calendario</th>
                    <th class="p-4 whitespace-nowrap">Animales</th>
                    <th class="p-4 whitespace-nowrap">Evidencia</th>
                    <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                @forelse($profilaxis as $prof)
                                        @php
                        $isRecent = $this->isRecentRecord('monitoreo.profilaxis', $prof->id);
                    @endphp
                    <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20' }}">
                        <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                            {{ $prof->fecha_aplicacion->format('d/m/Y') }}
                            <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                        </td>
                        <td class="p-4 whitespace-nowrap"><x-status-badge :value="$prof->tipo_intervencion" tone="sky" /></td>
                        <td class="p-4 text-zinc-200 whitespace-nowrap">{{ $prof->producto_marca }}</td>
                        <td class="p-4 text-zinc-300 whitespace-nowrap">{{ $prof->dosis ?? '-' }}</td>
                        <td class="p-4 text-xs text-zinc-300">
                            @forelse($prof->fechasDosisProgramadas() as $doseIndex => $scheduledDate)
                                <span class="block whitespace-nowrap"><strong class="text-teal-700 dark:text-teal-300">Dosis {{ $doseIndex + 2 }}:</strong> {{ $scheduledDate->format('d/m/Y') }}</span>
                            @empty
                                <span class="font-semibold text-zinc-500">Única dosis</span>
                            @endforelse
                        </td>
                        <td class="p-4 max-w-[240px]">
                            @php
                                $profAnimales = $prof->animales->take(3);
                                $profRestantes = $prof->animales->count() - 3;
                            @endphp
                            @if($profAnimales->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-2">
                                @foreach($profAnimales as $pa)
                                    <div class="flex min-w-0 items-center gap-1.5">
                                        <x-table-photo :path="$pa->foto_ruta" :frame="$pa->foto_encuadre" alt="Animal" size="sm" />
                                        <span class="max-w-20 truncate text-xs font-bold text-zinc-300">{{ $pa->arete }}</span>
                                    </div>
                                @endforeach
                                @if($profRestantes > 0)
                                    <span class="text-xs text-zinc-500 font-semibold">+{{ $profRestantes }} más</span>
                                @endif
                                </div>
                            @else
                                <span class="text-zinc-500 italic">Sin animales vinculados</span>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            @if($prof->fotos->isNotEmpty())
                                <div class="flex items-center gap-2">
                                    <x-table-photo :url="route('record-photo.show', $prof->fotos->first())" :frame="$prof->fotos->first()->encuadre" alt="Evidencia" size="sm" />
                                    <span class="text-xs font-bold text-sky-700 dark:text-sky-300">{{ $prof->fotos->count() }} foto(s)</span>
                                </div>
                            @else
                                <span class="text-xs text-zinc-500">Sin fotos</span>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                            @if(auth()->user()->tienePermiso('monitoreo', 'actualizar'))
                                <x-table-action type="edit" :href="route('monitoreo.profilaxis.edit', $prof->id)" />
                            @endif
                            @if(auth()->user()->tienePermiso('monitoreo', 'eliminar'))
                                <x-table-action type="delete" wire:click="solicitarEliminacionProfilaxis({{ $prof->id }})" />
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-12 text-center text-zinc-500"><div class="text-3xl">&#x1F4ED;</div><div class="mt-2 font-bold text-sm">No se encontraron registros de profilaxis</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="agro-table-footer">
        <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="rose" live compact /></div>
        <div class="min-w-0">{{ $profilaxis->links('components.pagination') }}</div>
    </div>
    @endif

    {{-- TAB PARTOS --}}
    @if($tab === 'partos')
    @php $partoAnyFilter = $searchParto || $partoTipo || $partoCondicionMadre || $partoCriaEstado || $partoCriaSexo || $partoFechaDesde || $partoFechaHasta; @endphp
    <x-collapsible-filters :active="$partoAnyFilter" title="Filtros de partos" description="Busca por madre, cría, tipo, condición o fecha." id="partos-filter-content" reset="resetPartoFilters">
        <div class="grid w-full grid-cols-1 gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 items-end dark:border-slate-800">
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="searchParto" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500" placeholder="Madre o cría...">
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
                <input type="date" wire:model.live="partoFechaDesde" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="partoFechaHasta" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
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
            <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                @forelse($partos as $part)
                                        @php
                        $isRecent = $this->isRecentRecord('monitoreo.partos', $part->id);
                    @endphp
                    <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20' }}">
                        <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                            {{ $part->fecha_parto->format('d/m/Y') }}
                            <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
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
        <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="rose" live compact /></div>
        <div class="min-w-0">{{ $partos->links('components.pagination') }}</div>
    </div>
    @endif

    {{-- TAB ALERTAS --}}
    @if($tab === 'alertas')
    @php $alertaAnyFilter = $searchAlerta || $alertaTipo || $alertaFiltroLeida !== '0' || $alertaFechaDesde || $alertaFechaHasta; @endphp
    <x-collapsible-filters :active="$alertaAnyFilter" title="Filtros de alertas" description="Busca por animal, tipo, mensaje o fecha programada." id="alertas-filter-content" reset="resetAlertaFilters">
        <div class="grid w-full grid-cols-1 gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end dark:border-slate-800">
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="searchAlerta" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500" placeholder="Animal o mensaje...">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo de alerta</label>
                <x-filter-select model="alertaTipo" :options="['' => 'Todos los tipos', 'cuarentena' => 'Cuarentena', 'proxima_dosis' => 'Próxima dosis', 'profilaxis' => 'Profilaxis', 'parto' => 'Parto', 'sanidad' => 'Sanidad']" tone="amber" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Estado de lectura</label>
                <x-filter-select model="alertaFiltroLeida" :options="['' => 'Todas las alertas', '0' => 'Pendientes (No leídas)', '1' => 'Leídas (Archivadas)']" tone="amber" live compact />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <input type="date" wire:model.live="alertaFechaDesde" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="alertaFechaHasta" class="py-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            </div>
        </div>
    </x-collapsible-filters>

    <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                    <th class="p-4 whitespace-nowrap">Fecha Programada</th>
                    <th class="p-4 whitespace-nowrap">Animal</th>
                    <th class="p-4 whitespace-nowrap">Tipo</th>
                    <th class="p-4 whitespace-nowrap">Mensaje</th>
                    <th class="p-4 whitespace-nowrap">Estado</th>
                    <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                @forelse($alertas as $al)
                    <tr class="hover:bg-zinc-850/20 transition duration-200">
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
                            @if(!$al->leida && auth()->user()->tienePermiso('monitoreo', 'actualizar'))
                                <x-table-action type="complete" wire:click="marcarAlertaLeida({{ $al->id }})" label="Marcar como leída" />
                            @else
                                <span class="text-zinc-600 text-xs italic">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-12 text-center text-zinc-500"><div class="text-3xl">&#x1F4ED;</div><div class="mt-2 font-bold text-sm">No se encontraron alertas</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="agro-table-footer">
        <div class="agro-table-size"><span>Mostrar</span><x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="rose" live compact /></div>
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
                'profilaxis' => [
                    'selected' => 'border-sky-300 bg-sky-50 text-sky-950 ring-1 ring-sky-200 dark:border-sky-500/60 dark:bg-sky-500/10 dark:text-sky-50',
                    'panel' => 'border-sky-200 bg-sky-50/55 dark:border-sky-500/30 dark:bg-sky-500/[.07]',
                    'title' => 'text-sky-900 dark:text-sky-100',
                    'action' => 'text-sky-700 hover:text-sky-600 dark:text-sky-300 dark:hover:text-sky-200',
                    'field' => 'border-sky-300 bg-white/90 text-sky-950 dark:border-sky-500/55 dark:bg-sky-500/10 dark:text-sky-50',
                    'check' => 'border-sky-600 bg-sky-600 dark:border-sky-400 dark:bg-sky-400',
                    'icon' => 'text-white dark:text-sky-950',
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
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-700">
                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Exportación integral</span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                <span x-text="sections.length"></span> de {{ count($pdfSections) }} secciones
                            </span>
                        </div>
                        <h3 id="monitoreo-report-title" class="text-lg font-bold text-slate-900 dark:text-white sm:text-xl">Generar reporte PDF de Monitoreo</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Combina secciones y campos en un único PDF A4 horizontal.</p>
                    </div>
                    <button type="button" wire:click="$set('showMonitoreoPdfModal', false)"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                            aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto xl:grid xl:grid-cols-[21rem_minmax(0,1fr)] xl:overflow-hidden">
                    <aside class="border-b border-slate-200 bg-slate-50/70 p-4 xl:overflow-y-auto xl:border-b-0 xl:border-r dark:border-slate-700 dark:bg-slate-950/25">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Secciones</span>
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
                                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:border-slate-600'"
                                   class="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 transition focus-within:ring-2 focus-within:ring-emerald-500/50">
                                <input type="checkbox" x-model="sections" value="{{ $key }}" class="sr-only">
                                <span :class="sections.includes('{{ $key }}') ? @js($theme['check']) : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'"
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
                        <div x-cloak x-show="sections.length === 0" class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center dark:border-slate-700 dark:bg-slate-950/30">
                            <div class="max-w-sm">
                                <strong class="block text-sm text-slate-700 dark:text-slate-200">Sin secciones seleccionadas</strong>
                                <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Activa una sección para configurar campos y filtros del reporte.</span>
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
                                            <small class="mt-0.5 block text-[11px] text-slate-500 dark:text-slate-400">Elige información incluida en esta sección.</small>
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
                                                    : 'border-slate-200 bg-white/75 text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400 dark:hover:border-slate-600'"
                                                   class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border px-2.5 py-2 text-xs font-semibold leading-tight transition focus-within:ring-2 focus-within:ring-current/30">
                                                <input type="checkbox" x-model="columns['{{ $sectionKey }}']" value="{{ $fieldKey }}" class="sr-only">
                                                <span :class="columns['{{ $sectionKey }}'].includes('{{ $fieldKey }}') ? @js($theme['check']) : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-950'"
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

                <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-700 dark:bg-slate-900">
                    <p class="hidden text-xs text-slate-500 sm:block dark:text-slate-400">Un PDF · Secciones combinadas · Filtros activos</p>
                    <div class="flex gap-2 sm:justify-end">
                        <button type="button" wire:click="$set('showMonitoreoPdfModal', false)"
                                class="h-11 flex-1 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 sm:flex-none dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                            Cancelar
                        </button>
                        <button type="button" wire:click="downloadMonitoreoReport" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70" wire:target="downloadMonitoreoReport"
                                class="h-11 flex-1 rounded-xl bg-emerald-700 px-6 text-sm font-bold text-white shadow-md shadow-emerald-700/15 transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait sm:flex-none dark:bg-emerald-500 dark:text-emerald-950 dark:hover:bg-emerald-400 dark:focus:ring-offset-slate-900">
                            <span wire:loading.remove wire:target="downloadMonitoreoReport">Generar reporte</span>
                            <span wire:loading wire:target="downloadMonitoreoReport">Generando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-recent-record-host>
