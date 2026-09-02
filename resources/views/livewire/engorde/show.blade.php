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

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            @if(auth()->user()->tienePermiso('engorde', 'exportar'))
                <button wire:click="openReportModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300 dark:hover:bg-rose-400/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    Reporte detallado
                </button>
            @endif

            @if(auth()->user()->tienePermiso('engorde', 'actualizar'))
                <a href="{{ route('engorde.lote.edit', $lote->id) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-2.5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-500/15 dark:text-emerald-300">
                    Editar lote
                </a>

                @if($lote->estado === 'activo')
                    @php
                        $hasActiveAnimals = $lote->animales->contains(fn ($ea) => $ea->estado === 'engorde_activo');
                    @endphp
                    @if($hasActiveAnimals && auth()->user()->tienePermiso('finanzas', 'crear'))
                        <button wire:click="openVenderLoteModal"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2.5 text-sm font-bold text-zinc-950 shadow-lg shadow-amber-500/10 transition hover:-translate-y-0.5 hover:from-amber-400 hover:to-amber-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            Vender / Liquidar
                        </button>
                    @endif

                    <button wire:click="openCerrarLoteModal"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-700 bg-zinc-800/80 px-4 py-2.5 text-sm font-bold text-zinc-300 transition hover:bg-zinc-700">
                        Cerrar lote
                    </button>

                    <button wire:click="openAddAnimalModal"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 font-bold text-zinc-950 shadow-lg shadow-emerald-500/10 transition hover:-translate-y-0.5 hover:from-emerald-400 hover:to-teal-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Agregar animal
                    </button>
                @else
                    <button wire:click="reabrirLote"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 text-sm font-bold text-emerald-600 transition hover:bg-emerald-500/20 dark:text-emerald-300">
                        Reabrir lote
                    </button>
                @endif
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
                <span class="mt-1 block text-base font-bold text-zinc-800 dark:text-zinc-200">{{ (int) $lote->fecha_inicio->startOfDay()->diffInDays(($lote->fecha_fin ?? now())->startOfDay()) }} días</span>
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
            @php
                $soldAnimals = $lote->animales->filter(fn ($ea) => $ea->estado === 'vendido');
                $totalIngresoVenta = $soldAnimals->map(fn ($ea) => $ea->animal?->movimientoVenta)->filter()->unique('id')->sum('monto');
            @endphp
            @if($soldAnimals->isNotEmpty())
                <div class="col-span-2 rounded-xl border border-amber-500/20 bg-amber-500/5 p-3.5 dark:border-amber-500/20 dark:bg-amber-500/10 xl:col-span-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/20 text-base font-bold text-amber-700 dark:text-amber-300">
                                💰
                            </span>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Liquidación / Venta Registrada</span>
                                <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $soldAnimals->count() }} animal(es) liquidados y dados de baja por venta</span>
                            </div>
                        </div>
                        @if($totalIngresoVenta > 0)
                            <div class="text-right">
                                <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Ingreso en Finanzas</span>
                                <span class="block text-base font-black text-emerald-600 dark:text-emerald-400">S/. {{ number_format($totalIngresoVenta, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
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
                <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                    @forelse($lote->animales as $ea)
                        @php
                            $animal = $ea->animal;
                            $metrics = $ea->reportMetrics();
                            $lastWeight = $metrics['last_weight'];
                            $gainTone = $metrics['gain_kg'] > 0 ? 'text-emerald-700 dark:text-emerald-400' : ($metrics['gain_kg'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-500');
                            $isRecent = in_array($ea->id, $recentEngordeAnimalIds, true);
                        @endphp
                        <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/20' }}">
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
                                @if($ea->estado === 'vendido')
                                    <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                        Vendido{{ $animal?->comprador_baja ? ' · '.$animal->comprador_baja : '' }}
                                    </span>
                                @elseif($isRecent)
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
                                        @if($ea->estado === 'engorde_activo')
                                            <x-table-action type="edit" wire:click="openEditIngresoModal({{ $ea->id }})" label="Editar ingreso" />
                                            <x-table-action type="weight" wire:click="openLogWeightModal({{ $ea->id }})" />
                                            <x-table-action type="delete" wire:click="solicitarQuitarAnimal({{ $ea->id }})" label="Retirar del lote" />
                                        @else
                                            <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider">Completado</span>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showActions ? 9 : 8 }}" class="p-12 text-center text-zinc-500">
                                <div class="text-3xl">📄</div>
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
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Reporte detallado del lote</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $lote->codigo }} · {{ $lote->nombre ?: 'Sin nombre' }}. PDF horizontal, sin recortar datos.</p>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm font-semibold text-rose-800 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">
                    <span class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-black text-white">PDF</span>
                    Foto registrada se exporta como Sí/No; imágenes reales permanecen en sistema.
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Datos por animal</span>
                        <button type="button" x-on:click="columns = columns.length === {{ count($reportAvailableColumns) }} ? [] : @js(array_keys($reportAvailableColumns))" class="text-xs font-semibold text-violet-600 dark:text-violet-400">Seleccionar todas</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach($reportAvailableColumns as $key => $label)
                            <label :class="columns.includes('{{ $key }}') ? 'border-violet-500 bg-violet-100 text-violet-950 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition">
                                <input type="checkbox" x-model="columns" value="{{ $key }}" class="sr-only">
                                <span :class="columns.includes('{{ $key }}') ? 'border-violet-700 bg-violet-600' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2">
                                    <svg x-cloak x-show="columns.includes('{{ $key }}')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('reportColumns') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    @error('reportColumns.*') <p class="mt-2 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <button wire:click="$set('showReportModal', false)" class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancelar</button>
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
                        <p class="mt-1 text-xs text-zinc-500">Primero elige especie. Luego selecciona uno o varios animales e indica su fecha de ingreso.</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                        {{ collect($selectedAnimals)->filter()->count() }} seleccionados
                    </span>
                </div>

                <form wire:submit="agregarAnimales" autocomplete="off" class="flex min-h-0 flex-1 flex-col">
                    <div class="grid grid-cols-1 gap-3 border-b border-emerald-950/10 p-4 dark:border-zinc-800 sm:grid-cols-3 sm:p-5">
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
                                   class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:disabled:bg-zinc-800"
                                   placeholder="Código o nombre...">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">3. Fecha de ingreso base</label>
                            <x-date-picker model="fechaIngresoDefault" placeholder="dd/mm/aaaa" />
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
                                    <div wire:key="engorde-select-{{ $animal->id }}" class="rounded-xl border p-3 transition {{ $isSelected ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950/60' }}">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-mono text-sm font-black text-emerald-800 dark:text-emerald-300">{{ $animal->arete }}</span>
                                                    <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $animal->especie?->nombre }}</span>
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
                                            <div class="mt-3 grid grid-cols-1 gap-3 border-t border-emerald-500/20 pt-3 sm:grid-cols-2">
                                                <div>
                                                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Peso inicial de engorde (kg) *</label>
                                                    <input type="number" min="0.01" max="999999.99" step="0.01" wire:model="pesosIniciales.{{ $animal->id }}"
                                                           class="w-full rounded-xl border border-emerald-500/30 bg-white px-3.5 py-2 text-sm font-semibold text-zinc-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:bg-zinc-950 dark:text-zinc-100"
                                                           placeholder="Ej: 450.00">
                                                    @error("pesosIniciales.{$animal->id}") <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Fecha de ingreso al lote *</label>
                                                    <x-date-picker model="fechasIngreso.{{ $animal->id }}" placeholder="dd/mm/aaaa" />
                                                    @error("fechasIngreso.{$animal->id}") <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-zinc-200 p-8 text-center text-sm text-zinc-500 dark:border-zinc-800">No hay animales disponibles para este filtro.</div>
                                @endforelse
                            </div>
                        @endif
                        @error('selectedAnimals') <span class="mt-3 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-emerald-950/10 bg-emerald-50/40 p-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <button type="button" wire:click="clearAnimalSelection" @disabled(!collect($selectedAnimals)->filter()->count())
                                class="text-xs font-semibold text-zinc-500 transition hover:text-rose-600 disabled:opacity-40">Limpiar selección</button>
                        <div class="flex flex-col-reverse gap-2 sm:flex-row">
                            <button type="button" wire:click="closeAddAnimalModal" class="rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">Cancelar</button>
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
                    <p class="text-xs text-zinc-500 mt-0.5">Registrando peso para: <strong class="text-emerald-600 dark:text-emerald-400">{{ $selectedAnimalName }}</strong></p>
                </div>

                <form wire:submit.prevent="registrarPesaje" autocomplete="off" class="space-y-4">
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
                        <x-date-picker model="fechaPesaje" placeholder="dd/mm/aaaa" />
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

                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-800/80 pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" wire:click="closeLogWeightModal" 
                                class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 text-sm font-semibold transition">
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

    <!-- Modal 3: Editar Ingreso del Animal -->
    @if($showEditIngresoModal && auth()->user()->tienePermiso('engorde', 'actualizar'))
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div role="dialog" aria-modal="true" aria-label="Editar ingreso del animal" class="agro-dialog agro-dialog--sm agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div>
                    <h3 class="text-lg font-bold text-zinc-100">Editar Ingreso al Lote</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Modificando datos para: <strong class="text-emerald-600 dark:text-emerald-400">{{ $editAnimalNombre }}</strong></p>
                </div>

                <form wire:submit.prevent="actualizarIngreso" autocomplete="off" class="space-y-4">
                    <!-- Fecha de Ingreso -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Fecha de Ingreso al Lote *</label>
                        <x-date-picker model="editFechaIngreso" placeholder="dd/mm/aaaa" />
                        <p class="mt-1 text-[11px] text-zinc-500">Debe ser igual o posterior a la fecha de inicio del lote ({{ $lote->fecha_inicio->format('d/m/Y') }}).</p>
                        @error('editFechaIngreso') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Peso Inicial -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Peso Inicial de Engorde (Kg) *</label>
                        <input type="number" min="0.01" step="0.01" wire:model="editPesoInicial"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                               placeholder="Ej: 480.00">
                        @error('editPesoInicial') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Observaciones / Notas (Opcional)</label>
                        <input type="text" wire:model="editObservaciones" 
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                               placeholder="Ej: Ingreso reprogramado o corrección de fecha">
                        @error('editObservaciones') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-800/80 pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" wire:click="closeEditIngresoModal" 
                                class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 text-sm font-semibold transition">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="actualizarIngreso"
                                class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-zinc-950 font-bold text-sm transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="actualizarIngreso">Guardar Cambios</span>
                            <span wire:loading wire:target="actualizarIngreso">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal 4: Liquidar y Vender Lote -->
    @if($showVenderLoteModal && auth()->user()->tienePermiso('engorde', 'actualizar') && auth()->user()->tienePermiso('finanzas', 'crear'))
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div role="dialog" aria-modal="true" aria-label="Liquidar y vender lote" class="agro-dialog agro-dialog--lg agro-dialog--scroll space-y-5 p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4 border-b border-amber-500/20 pb-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 rounded-md bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-300">
                            💰 Finanzas & Inventario
                        </div>
                        <h3 class="mt-1 text-lg font-bold text-zinc-100">Liquidar y Vender Lote {{ $lote->codigo }}</h3>
                        <p class="text-xs text-zinc-400">Registra el precio por animal o monto total, emite el asiento en Finanzas y marca los animales como vendidos.</p>
                    </div>
                </div>

                @php
                    $selectedIds = collect($animalesAVender)->filter()->keys();
                    $activeAnimalRecords = $lote->animales->filter(fn($ea) => $ea->estado === 'engorde_activo');
                    $selectedRecords = $activeAnimalRecords->whereIn('animal_id', $selectedIds);
                    $totalKg = $selectedRecords->sum(fn($ea) => (float)($ea->peso_actual ?: $ea->peso_inicial));
                    $totalVentaNum = is_numeric($montoVenta) ? (float)$montoVenta : 0;
                    $countSel = $selectedRecords->count();
                    $promedioAnimal = $countSel > 0 && $totalVentaNum > 0 ? $totalVentaNum / $countSel : 0;
                    $precioKg = $totalKg > 0 && $totalVentaNum > 0 ? $totalVentaNum / $totalKg : 0;
                @endphp

                <form wire:submit.prevent="liquidarVentaLote" autocomplete="off" class="space-y-5">
                    {{-- 1. Datos Generales --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Fecha de Venta -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Fecha de Venta *</label>
                            <x-date-picker model="fechaVenta" placeholder="dd/mm/aaaa" />
                            @error('fechaVenta') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Comprador -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-400 mb-1.5">
                                Comprador / Razón Social
                                <span class="ml-1 font-normal text-zinc-500 text-[10px]">(Opcional)</span>
                            </label>
                            <input type="text" wire:model="compradorVenta" autocomplete="off"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3.5 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-amber-500/50 focus:ring-amber-500/20"
                                   placeholder="Ej: Frigorífico Central / Juan Pérez">
                            @error('compradorVenta') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- 2. Lista de Animales y Precios Individuales --}}
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-800/80 pb-2">
                            <div>
                                <label class="block text-xs font-bold text-zinc-200">
                                    Animales a Liquidar & Precio Individual (S/.) *
                                </label>
                                <span class="text-[11px] text-zinc-500">
                                    Ingresa el precio de cada animal para auto-sumar el total, o usa la distribución rápida.
                                </span>
                            </div>

                            {{-- Botones de distribución rápida --}}
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="distribuirMontoTotalEquitativo"
                                        title="Divide el monto total en partes iguales entre los seleccionados"
                                        class="inline-flex items-center gap-1 rounded-lg border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-[11px] font-bold text-amber-400 hover:bg-amber-500/20 transition">
                                    ⚡ Promedio
                                </button>
                                <button type="button" wire:click="distribuirMontoPorPeso"
                                        title="Distribuye el monto total según el peso en kg de cada animal"
                                        class="inline-flex items-center gap-1 rounded-lg border border-zinc-700 bg-zinc-800/80 px-2.5 py-1 text-[11px] font-bold text-zinc-300 hover:bg-zinc-700 transition">
                                    ⚖️ Por peso
                                </button>
                                <span class="ml-1 rounded-md bg-amber-500/20 px-2 py-0.5 text-[11px] font-extrabold text-amber-400">
                                    {{ $countSel }} de {{ $activeAnimalRecords->count() }}
                                </span>
                            </div>
                        </div>

                        {{-- Lista de filas de animales con input de precio --}}
                        <div class="max-h-64 overflow-y-auto space-y-2 rounded-xl border border-zinc-800 bg-zinc-950/70 p-2.5">
                            @foreach($activeAnimalRecords as $ea)
                                @php
                                    $isSelected = !empty($animalesAVender[$ea->animal_id]);
                                    $peso = (float)($ea->peso_actual ?: $ea->peso_inicial);
                                @endphp
                                <div wire:key="ea-sale-show-{{ $ea->id }}"
                                     class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 rounded-xl border p-2.5 transition {{ $isSelected ? 'border-amber-500/30 bg-zinc-900/90' : 'border-zinc-800 bg-zinc-950/40 opacity-60' }}">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="checkbox" wire:model.live="animalesAVender.{{ $ea->animal_id }}"
                                               id="ea-check-show-{{ $ea->animal_id }}"
                                               class="h-4 w-4 rounded border-zinc-700 bg-zinc-900 text-amber-500 focus:ring-amber-500/30 cursor-pointer">
                                        <label for="ea-check-show-{{ $ea->animal_id }}" class="min-w-0 cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold text-emerald-400 text-xs">{{ $ea->animal?->arete }}</span>
                                                <span class="text-xs font-semibold text-zinc-200 truncate">{{ $ea->animal?->nombre ?: 'Sin nombre' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[11px] text-zinc-400 mt-0.5">
                                                <span>Peso: <strong class="text-zinc-200">{{ number_format($peso, 2) }} kg</strong></span>
                                                @if($ea->categoria)
                                                    <span class="text-zinc-600">·</span>
                                                    <span class="text-zinc-500">{{ ucfirst(str_replace('_', ' ', $ea->categoria)) }}</span>
                                                @endif
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Input de precio individual --}}
                                    <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                        <span class="text-xs font-bold text-zinc-400">S/.</span>
                                        <input type="number" step="0.01" min="0"
                                               wire:model.live.debounce.300ms="preciosAnimales.{{ $ea->animal_id }}"
                                               placeholder="0.00"
                                               @if(!$isSelected) disabled @endif
                                               class="w-28 sm:w-32 rounded-lg border border-zinc-700 bg-zinc-950 px-2.5 py-1.5 text-right font-mono text-xs font-bold text-amber-300 outline-none transition placeholder:text-zinc-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 disabled:opacity-40 disabled:cursor-not-allowed">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('animalesAVender') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- 3. Tarjeta Resumen / Métricas Calculadas --}}
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 rounded-xl border border-zinc-800 bg-zinc-950/80 p-3 text-center">
                        <div class="border-r border-zinc-800 last:border-0 p-1">
                            <span class="block text-[10px] uppercase tracking-wider text-zinc-500 font-semibold">Animales</span>
                            <span class="text-sm font-bold text-zinc-200">{{ $countSel }} seleccionados</span>
                        </div>
                        <div class="border-r border-zinc-800 last:border-0 p-1">
                            <span class="block text-[10px] uppercase tracking-wider text-zinc-500 font-semibold">Peso Total</span>
                            <span class="text-sm font-bold text-emerald-400 font-mono">{{ number_format($totalKg, 2) }} kg</span>
                        </div>
                        <div class="border-r border-zinc-800 last:border-0 p-1">
                            <span class="block text-[10px] uppercase tracking-wider text-zinc-500 font-semibold">Promedio / Animal</span>
                            <span class="text-sm font-bold text-amber-400 font-mono">
                                {{ $promedioAnimal > 0 ? 'S/ '.number_format($promedioAnimal, 2) : '-' }}
                            </span>
                        </div>
                        <div class="p-1">
                            <span class="block text-[10px] uppercase tracking-wider text-zinc-500 font-semibold">Precio / kg en pie</span>
                            <span class="text-sm font-bold text-sky-400 font-mono">
                                {{ $precioKg > 0 ? 'S/ '.number_format($precioKg, 2).' /kg' : '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- 4. Monto Total & Observaciones --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Monto Total -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-zinc-400">Monto Total de Venta (S/.) *</label>
                                <span class="text-[10px] text-amber-500/80">Suma automática</span>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-zinc-500 text-sm">S/</span>
                                <input type="number" min="0.01" step="0.01" wire:model.live.debounce.300ms="montoVenta" autocomplete="off"
                                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 pl-9 pr-4 py-2.5 font-mono text-sm font-bold text-amber-300 outline-none transition placeholder:text-zinc-500 focus:border-amber-500/50 focus:ring-amber-500/20"
                                       placeholder="0.00">
                            </div>
                            @error('montoVenta') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Notas / Observaciones (Opcional)</label>
                            <input type="text" wire:model="observacionesVenta" autocomplete="off"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3.5 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-amber-500/50 focus:ring-amber-500/20"
                                   placeholder="Ej: Precio pactado puesto en camión">
                            @error('observacionesVenta') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- 5. Comprobante / Foto de Venta ("Modo Queso" Uploader) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-zinc-300">
                                Comprobante / Foto de Venta
                                <span class="ml-1 font-normal text-zinc-500 text-[10px]">(Opcional)</span>
                            </label>
                            <span class="text-[10px] text-zinc-500">JPG, PNG, WebP o PDF · Máx. 25 MB</span>
                        </div>

                        {{-- Contenedor con vista previa o zona de carga --}}
                        <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-700 bg-zinc-950/80 p-3 transition hover:border-amber-500/50">
                            @if($comprobanteVenta)
                                @php
                                    $isImg = false;
                                    try {
                                        $isImg = str_starts_with((string) $comprobanteVenta->getMimeType(), 'image/');
                                    } catch (\Throwable $e) {}
                                @endphp

                                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if($isImg)
                                            <img src="{{ $comprobanteVenta->temporaryUrl() }}"
                                                 alt="Vista previa del comprobante"
                                                 class="h-16 w-24 object-cover rounded-lg border border-amber-500/40 shadow-sm shrink-0">
                                        @else
                                            <div class="flex h-16 w-24 shrink-0 items-center justify-center rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-400">
                                                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-zinc-100">{{ $comprobanteVenta->getClientOriginalName() }}</p>
                                            <p class="text-[10px] text-amber-400 mt-0.5">Listo para guardar en almacenamiento privado</p>
                                        </div>
                                    </div>

                                    <button type="button" wire:click="limpiarComprobanteVenta"
                                            class="inline-flex items-center gap-1 rounded-lg bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 px-3 py-1.5 text-xs font-bold text-rose-400 transition shrink-0">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Quitar archivo
                                    </button>
                                </div>
                            @else
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 py-1">
                                    <div class="flex items-center gap-2.5 text-zinc-400 text-center sm:text-left">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-900 border border-zinc-800 text-amber-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/></svg>
                                        </div>
                                        <div>
                                            <span class="block text-xs font-semibold text-zinc-300">Subir recibo, factura o foto de los animales</span>
                                            <span class="block text-[10px] text-zinc-500">Se optimiza automáticamente en WebP</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        {{-- Botón Cámara --}}
                                        <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-zinc-700 bg-zinc-900 hover:bg-zinc-800 px-3 py-1.5 text-xs font-bold text-zinc-200 transition shadow-sm">
                                            <svg class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/></svg>
                                            Tomar foto
                                            <input type="file" wire:model="comprobanteVenta" accept="image/*" capture="environment" class="sr-only">
                                        </label>

                                        {{-- Botón Galería / PDF --}}
                                        <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-amber-500/40 bg-amber-500/10 hover:bg-amber-500/20 px-3 py-1.5 text-xs font-bold text-amber-400 transition shadow-sm">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5M3.75 15v3.75A2.25 2.25 0 0 0 6 21h12a2.25 2.25 0 0 0 2.25-2.25V15"/></svg>
                                            Galería o PDF
                                            <input type="file" wire:model="comprobanteVenta" accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only">
                                        </label>
                                    </div>
                                </div>
                            @endif

                            {{-- Indicador de subida --}}
                            <div wire:loading wire:target="comprobanteVenta" class="absolute inset-0 bg-zinc-950/90 flex items-center justify-center gap-2 text-xs font-bold text-amber-400">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-amber-400 border-t-transparent"></span>
                                Optimizando y subiendo archivo...
                            </div>
                        </div>
                        @error('comprobanteVenta') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- 6. Botones de Acción --}}
                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-800/80 pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" wire:click="closeVenderLoteModal"
                                class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 text-sm font-semibold transition">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="liquidarVentaLote,comprobanteVenta"
                                class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold text-sm transition disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20">
                            <span wire:loading.remove wire:target="liquidarVentaLote">Registrar Venta en Finanzas (S/ {{ number_format($totalVentaNum, 2) }})</span>
                            <span wire:loading wire:target="liquidarVentaLote" class="flex items-center gap-2">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-950 border-t-transparent"></span>
                                Procesando venta...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal 5: Finalizar / Cerrar Lote -->
    @if($showCerrarLoteModal && auth()->user()->tienePermiso('engorde', 'actualizar'))
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div role="dialog" aria-modal="true" aria-label="Cerrar lote de engorde" class="agro-dialog agro-dialog--sm agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div>
                    <h3 class="text-lg font-bold text-zinc-100">Finalizar Ciclo de Engorde</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">El lote se marcará como cerrado y se congelarán las métricas.</p>
                </div>

                <form wire:submit.prevent="finalizarLote" autocomplete="off" class="space-y-4">
                    <!-- Fecha de Cierre -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Fecha de Cierre del Lote *</label>
                        <x-date-picker model="fechaCierreLote" placeholder="dd/mm/aaaa" />
                        <p class="mt-1 text-[11px] text-zinc-500">Debe ser igual o posterior al inicio ({{ $lote->fecha_inicio->format('d/m/Y') }}).</p>
                        @error('fechaCierreLote') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Observaciones de Cierre -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Motivo / Notas de Finalización (Opcional)</label>
                        <input type="text" wire:model="observacionesCierreLote"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                               placeholder="Ej: Fin del ciclo planificado de 90 días">
                        @error('observacionesCierreLote') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-800/80 pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" wire:click="closeCerrarLoteModal"
                                class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 text-sm font-semibold transition">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="finalizarLote"
                                class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-zinc-950 font-bold text-sm transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="finalizarLote">Cerrar Lote</span>
                            <span wire:loading wire:target="finalizarLote">Cerrando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
    </x-pdf-preview-modal>
</div>


