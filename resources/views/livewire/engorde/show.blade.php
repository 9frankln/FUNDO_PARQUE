<div class="mx-auto max-w-7xl space-y-6">
    @if($recentEngordeAnimalIds)
        <div x-data x-init="setTimeout(() => $wire.clearRecentEngordeRows(), 15000)" class="hidden"></div>
    @endif
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('engorde.index') }}" 
               class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-100 transition duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                    Lote: {{ $lote->codigo }}
                </h1>
                <p class="text-zinc-400 text-sm mt-1">{{ $lote->nombre ?? 'Detalles del Lote de Engorde' }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            @if(auth()->user()->tienePermiso('engorde', 'exportar'))
                <button wire:click="openReportModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-300 bg-rose-50 px-5 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300 dark:hover:bg-rose-400/20">
                    Reporte detallado
                </button>
            @endif
            @if(auth()->user()->tienePermiso('engorde', 'actualizar'))
                <a href="{{ route('engorde.lote.edit', $lote->id) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-5 py-2.5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-500/15 dark:text-emerald-300">
                    Editar lote
                </a>
            @endif
            @if($lote->estado === 'activo' && auth()->user()->tienePermiso('engorde', 'actualizar'))
                <button wire:click="openAddAnimalModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-2.5 font-bold text-zinc-950 shadow-lg shadow-emerald-500/10 transition hover:-translate-y-0.5 hover:from-emerald-400 hover:to-teal-400">
                    Agregar animal
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(24rem,5fr)_minmax(0,7fr)]">
        <div class="overflow-hidden rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900">
            @if($lote->foto_ruta)
                @php
                    $lotDetailFrame = \App\Support\ImageFrame::normalize($lote->foto_encuadre);
                @endphp
                <a href="{{ '/storage/'.ltrim($lote->foto_ruta, '/') }}" target="_blank" rel="noopener" class="group block aspect-[4/3] overflow-hidden rounded-xl bg-emerald-50 dark:bg-zinc-950">
                    <img src="{{ '/storage/'.ltrim($lote->foto_ruta, '/') }}" alt="Foto del lote {{ $lote->codigo }}" class="h-full w-full object-cover transition" decoding="async" style="object-position: {{ $lotDetailFrame['x'] }}% {{ $lotDetailFrame['y'] }}%; transform: scale({{ $lotDetailFrame['zoom'] }}); transform-origin: {{ $lotDetailFrame['x'] }}% {{ $lotDetailFrame['y'] }}%;">
                </a>
            @else
                <div class="flex aspect-[4/3] flex-col items-center justify-center rounded-xl border-2 border-dashed border-emerald-200 bg-emerald-50 text-center dark:border-zinc-800 dark:bg-zinc-950">
                    <svg class="h-10 w-10 text-emerald-700/40 dark:text-zinc-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"></path>
                    </svg>
                    <span class="mt-2 text-xs font-semibold text-zinc-500">Lote sin fotografía</span>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-5 rounded-2xl border border-emerald-950/10 bg-white p-6 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 xl:grid-cols-3">
            <div>
                <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Fecha de Inicio</span>
                <span class="text-base font-bold text-zinc-800 dark:text-zinc-200 mt-1 block">{{ $lote->fecha_inicio->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Fecha de Cierre</span>
                <span class="text-base font-bold text-zinc-800 dark:text-zinc-200 mt-1 block">{{ $lote->fecha_fin ? $lote->fecha_fin->format('d/m/Y') : 'En Proceso' }}</span>
            </div>
            <div>
                <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Duración</span>
                <span class="mt-1 block text-base font-bold text-zinc-800 dark:text-zinc-200">{{ $lote->fecha_inicio->diffInDays($lote->fecha_fin ?? now()) }} días</span>
            </div>
            <div>
                <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Estado</span>
                <x-status-badge :value="$lote->estado" class="mt-2.5" />
            </div>
            <div>
                <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Total de animales</span>
                <span class="text-xl font-black text-emerald-700 dark:text-emerald-400 mt-0.5 block">{{ $lote->animales->count() }} animales</span>
            </div>
            <div>
                <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Ganancia acumulada</span>
                <span class="mt-0.5 block text-xl font-black {{ $loteSummary['gain_kg'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $loteSummary['gain_kg'] > 0 ? '+' : '' }}{{ number_format($loteSummary['gain_kg'], 2) }} kg</span>
            </div>
            @if($lote->observaciones)
                <div class="col-span-2 border-t border-emerald-950/10 pt-4 dark:border-zinc-800 xl:col-span-3">
                    <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Observaciones</span>
                    <p class="mt-2 whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-300">{{ $lote->observaciones }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Animals List inside Lote -->
    <div class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-zinc-200">Animales en este lote</h3>
                <p class="mt-1 text-xs text-zinc-500">Último control real, evolución porcentual y ganancia media diaria.</p>
            </div>
            <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300">{{ $lote->animales->count() }} animales</span>
        </div>

        @php
            $showActions = $lote->estado === 'activo' && auth()->user()->tienePermiso('engorde', 'actualizar');
        @endphp
        <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
            <table class="w-full min-w-[1120px] text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                        <th class="p-4">Foto</th>
                        <th class="p-4">Animal</th>
                        <th class="p-4">Clasificación</th>
                        <th class="p-4">Ingreso</th>
                        <th class="p-4 text-right">Peso inicial</th>
                        <th class="p-4 text-right">Último control</th>
                        <th class="p-4 text-right">Evolución</th>
                        <th class="p-4 text-center">Controles</th>
                        @if($showActions)
                            <th class="p-4 text-right">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                    @forelse($lote->animales as $ea)
                        @php
                            $animal = $ea->animal;
                            $metrics = $ea->reportMetrics();
                            $lastWeight = $metrics['last_weight'];
                            $gainTone = $metrics['gain_kg'] > 0 ? 'text-emerald-700 dark:text-emerald-400' : ($metrics['gain_kg'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-500');
                            $isRecent = in_array($ea->id, $recentEngordeAnimalIds, true);
                        @endphp
                        <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20' }}">
                            <td class="p-4 whitespace-nowrap">
                                <x-table-photo :path="$animal?->foto_ruta" :frame="$animal?->foto_encuadre" :alt="'Foto de '.($animal?->nombre ?: $animal?->arete ?: 'animal archivado')" />
                            </td>
                            <td class="p-4">
                                @if($animal)
                                    <a href="{{ route('animal.show', $animal->id) }}" class="font-mono font-black text-emerald-700 transition hover:text-emerald-500 dark:text-emerald-300">{{ $animal->arete }}</a>
                                    <span class="mt-1 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $animal->nombre ?: 'Sin nombre' }}</span>
                                @else
                                    <span class="font-semibold text-rose-600">Animal no disponible</span>
                                @endif
                                @if($isRecent)
                                    <span class="mt-1 inline-block rounded-full bg-emerald-500 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-950">{{ $recentEngordeAction === 'updated' ? 'Actualizado' : 'Agregado' }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="block font-semibold text-zinc-800 dark:text-zinc-200">{{ $animal?->especie?->nombre ?? '-' }} / {{ $animal?->raza?->nombre ?? '-' }}</span>
                                <span class="mt-1 block text-[10px] text-zinc-500">{{ ucfirst($animal?->genero ?? '-') }} · {{ $animal?->clasificacion_edad ?? '-' }}</span>
                                <span class="block text-[10px] text-zinc-500">{{ $animal?->edad_texto ?? '-' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="block font-semibold text-zinc-800 dark:text-zinc-200">{{ $ea->fecha_ingreso?->format('d/m/Y') ?? '-' }}</span>
                                <span class="mt-1 block text-[10px] text-zinc-500">{{ $metrics['days_in_fattening'] }} días</span>
                            </td>
                            <td class="p-4 text-right font-semibold whitespace-nowrap">{{ number_format($metrics['initial_weight'], 2) }} kg</td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <span class="block font-black text-zinc-900 dark:text-zinc-100">{{ number_format($metrics['reference_weight'], 2) }} kg</span>
                                <span class="mt-1 block text-[10px] {{ (int) $recentPesajeId === (int) data_get($lastWeight, 'id') ? 'font-bold text-emerald-600' : 'text-zinc-500' }}">{{ $lastWeight ? $lastWeight->fecha->format('d/m/Y') : 'Peso de ingreso' }}</span>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <span class="block font-black {{ $gainTone }}">{{ $metrics['gain_kg'] > 0 ? '+' : '' }}{{ number_format($metrics['gain_kg'], 2) }} kg</span>
                                <span class="mt-1 block text-[10px] {{ $gainTone }}">{{ $metrics['gain_percentage'] !== null ? number_format($metrics['gain_percentage'], 2).' %' : '-' }}</span>
                                <span class="block text-[10px] text-zinc-500">GMD: {{ $metrics['average_daily_gain'] !== null ? number_format($metrics['average_daily_gain'], 3).' kg/día' : '-' }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-violet-500/10 px-2.5 py-1 text-xs font-bold text-violet-700 dark:text-violet-300">{{ $ea->pesajes_count }}</span>
                            </td>
                            @if($showActions)
                                <td class="p-4 shrink-0">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-table-action type="weight" wire:click="openLogWeightModal({{ $ea->id }})" />
                                        <x-table-action type="delete" wire:click="quitarAnimal({{ $ea->id }})" label="Retirar del lote" />
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showActions ? 9 : 8 }}" class="p-12 text-center text-zinc-550">
                                <div class="text-3xl">📭</div>
                                <div class="mt-2 font-bold text-sm">No hay animales en este lote</div>
                                <div class="text-xs text-zinc-500 mt-1">Haz clic en "Agregar Animal" para incorporarlos.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showReportModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ columns: @js($reportColumns) }" role="dialog" aria-modal="true" aria-label="Reporte detallado del lote" class="agro-dialog agro-dialog--md agro-dialog--scroll space-y-5 p-4 sm:p-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Reporte detallado del lote</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $lote->codigo }} · {{ $lote->nombre ?: 'Sin nombre' }}. PDF horizontal, sin recortar datos.</p>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm font-semibold text-rose-800 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">
                    <span class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-black text-white">PDF</span>
                    Foto registrada se exporta como Sí/No; imágenes reales permanecen en sistema.
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Datos por animal</span>
                        <button type="button" x-on:click="columns = columns.length === {{ count($reportAvailableColumns) }} ? [] : @js(array_keys($reportAvailableColumns))" class="text-xs font-semibold text-violet-600 dark:text-violet-400">Seleccionar todas</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach($reportAvailableColumns as $key => $label)
                            <label :class="columns.includes('{{ $key }}') ? 'border-violet-500 bg-violet-100 text-violet-950 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition">
                                <input type="checkbox" x-model="columns" value="{{ $key }}" class="sr-only">
                                <span :class="columns.includes('{{ $key }}') ? 'border-violet-700 bg-violet-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2">
                                    <svg x-cloak x-show="columns.includes('{{ $key }}')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('reportColumns') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    @error('reportColumns.*') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button wire:click="$set('showReportModal', false)" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300">Cancelar</button>
                    <button type="button" x-on:click="$wire.exportDetailedReport(columns)" wire:loading.attr="disabled" wire:target="exportDetailedReport" class="rounded-xl bg-rose-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-500 disabled:opacity-60">Generar PDF</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal 1: Agregar Animal -->
    @if($showAddAnimalModal && auth()->user()->tienePermiso('engorde', 'actualizar'))
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div role="dialog" aria-modal="true" aria-label="Agregar animales al lote" class="agro-dialog agro-dialog--lg">
                <div class="flex items-start justify-between gap-4 border-b border-emerald-950/10 p-5 dark:border-zinc-800 sm:p-6">
                    <div>
                        <h3 class="text-xl font-bold text-emerald-950 dark:text-zinc-100">Agregar animales al lote</h3>
                        <p class="mt-1 text-xs text-zinc-500">Primero elige especie. Luego selecciona uno o varios animales.</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                        {{ collect($selectedAnimals)->filter()->count() }} seleccionados
                    </span>
                </div>

                <form wire:submit="agregarAnimales" class="flex min-h-0 flex-1 flex-col">
                    <div class="grid grid-cols-1 gap-3 border-b border-emerald-950/10 p-4 dark:border-zinc-800 sm:grid-cols-2 sm:p-5">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">1. Tipo de animal</label>
                            <x-filter-select
                                model="engordeEspecieId"
                                :options="['' => 'Selecciona una especie...'] + $especiesDisponibles->pluck('nombre', 'id')->all()"
                                tone="emerald"
                                live
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">2. Buscar dentro de especie</label>
                            <input type="search" wire:model.live.debounce.250ms="engordeSearch" @disabled(!$engordeEspecieId)
                                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:disabled:bg-slate-800"
                                   placeholder="Código o nombre...">
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5">
                        @if(!$engordeEspecieId)
                            <div class="flex min-h-64 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/60 px-6 text-center dark:border-zinc-800 dark:bg-zinc-950/50">
                                <svg class="h-10 w-10 text-emerald-600/50" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Z"></path>
                                </svg>
                                <p class="mt-3 text-sm font-bold text-emerald-900 dark:text-emerald-100">Selecciona tipo de animal</p>
                                <p class="mt-1 text-xs text-zinc-500">Solo entonces aparecerán animales correspondientes.</p>
                            </div>
                        @else
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-xs text-zinc-500">Hasta 100 resultados disponibles.</p>
                                <button type="button" wire:click="selectAllVisible" class="text-xs font-bold text-emerald-700 hover:underline dark:text-emerald-400">
                                    Seleccionar todos visibles
                                </button>
                            </div>

                            <div class="space-y-2">
                                @forelse($animalesDisponibles as $animal)
                                    @php
                                        $isSelected = (bool) ($selectedAnimals[$animal->id] ?? false);
                                    @endphp
                                    <div wire:key="engorde-select-{{ $animal->id }}" class="rounded-xl border p-3 transition {{ $isSelected ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10' : 'border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-950/60' }}">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-mono text-sm font-black text-emerald-800 dark:text-emerald-300">{{ $animal->arete }}</span>
                                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $animal->especie?->nombre }}</span>
                                                </div>
                                                <p class="mt-1 truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $animal->nombre ?: 'Sin nombre' }}</p>
                                                <p class="mt-0.5 text-[11px] text-zinc-500">{{ $animal->clasificacion_edad }} · {{ $animal->edad_texto }} · Peso actual: {{ $animal->peso ?: 'Sin registrar' }} kg</p>
                                            </div>
                                            <button type="button" wire:click="toggleAnimalSelection({{ $animal->id }})"
                                                    class="shrink-0 rounded-xl px-4 py-2 text-xs font-bold transition {{ $isSelected ? 'bg-rose-500/10 text-rose-700 hover:bg-rose-500/20 dark:text-rose-300' : 'bg-emerald-500 text-emerald-950 hover:bg-emerald-400' }}">
                                                {{ $isSelected ? 'Quitar' : 'Agregar' }}
                                            </button>
                                        </div>

                                        @if($isSelected)
                                            <div class="mt-3 border-t border-emerald-500/20 pt-3 sm:max-w-xs">
                                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Peso inicial de engorde (kg)</label>
                                                <input type="number" min="0.01" max="999999.99" step="0.01" wire:model="pesosIniciales.{{ $animal->id }}"
                                                       class="w-full rounded-xl border border-emerald-500/30 bg-white px-3.5 py-2 text-sm font-semibold text-zinc-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:bg-zinc-950 dark:text-zinc-100">
                                                @error("pesosIniciales.{$animal->id}") <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-slate-200 p-8 text-center text-sm text-zinc-500 dark:border-zinc-800">No hay animales disponibles para este filtro.</div>
                                @endforelse
                            </div>
                        @endif
                        @error('selectedAnimals') <span class="mt-3 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-emerald-950/10 bg-emerald-50/40 p-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <button type="button" wire:click="clearAnimalSelection" @disabled(!collect($selectedAnimals)->filter()->count())
                                class="text-xs font-semibold text-zinc-500 transition hover:text-rose-600 disabled:opacity-40">Limpiar selección</button>
                        <div class="flex flex-col-reverse gap-2 sm:flex-row">
                            <button type="button" wire:click="$set('showAddAnimalModal', false)" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">Cancelar</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="agregarAnimales" @disabled(!collect($selectedAnimals)->filter()->count())
                                    class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-emerald-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-50">
                                <span wire:loading.remove wire:target="agregarAnimales">Incorporar {{ collect($selectedAnimals)->filter()->count() }} animal(es)</span>
                                <span wire:loading wire:target="agregarAnimales">Incorporando...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal 2: Registrar Pesaje -->
    @if($showLogWeightModal)
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div role="dialog" aria-modal="true" aria-label="Registrar peso" class="agro-dialog agro-dialog--sm agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div>
                    <h3 class="text-lg font-bold text-zinc-100">Registrar Peso (Control)</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Registrando peso para: <strong class="text-emerald-400">{{ $selectedAnimalName }}</strong></p>
                </div>

                <form wire:submit.prevent="registrarPesaje" class="space-y-4">
                    <!-- Nuevo Peso -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Nuevo Peso Registrado (Kg)</label>
                        <input type="number" min="0.01" step="0.01" wire:model="nuevoPeso"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                               placeholder="Ej: 395.20">
                        @error('nuevoPeso') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha del Pesaje -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Fecha del Pesaje</label>
                        <input type="date" wire:model="fechaPesaje" 
                               class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 focus:border-emerald-500/50 focus:ring-emerald-500/20 text-zinc-100 text-sm transition outline-none">
                        @error('fechaPesaje') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Observaciones / Notas (Opcional)</label>
                        <input type="text" wire:model="observacionesPesaje" 
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                               placeholder="Ej: Medido después de alimentación">
                        @error('observacionesPesaje') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-850/80 pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" wire:click="$set('showLogWeightModal', false)" 
                                class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-zinc-850 text-zinc-400 hover:text-zinc-200 text-sm font-semibold transition">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-zinc-950 font-bold text-sm transition">
                            Guardar Control
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
