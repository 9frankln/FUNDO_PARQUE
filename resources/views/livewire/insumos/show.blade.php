<div class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('medicamentos.index', ['tab' => 'insumos']) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-white dark:border-zinc-700 dark:hover:bg-zinc-800" aria-label="Volver a botiquín">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-white text-emerald-700 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-emerald-300">
                @if($insumo->foto_ruta)
                    @php $photoFrame = \App\Support\ImageFrame::normalize($insumo->foto_encuadre); @endphp
                    <img src="{{ asset('storage/'.$insumo->foto_ruta) }}" alt="Foto de {{ $insumo->nombre }}" class="h-full w-full object-cover" style="object-position: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%; transform: scale({{ $photoFrame['zoom'] }}); transform-origin: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%;">
                @else
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                @endif
            </span>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">{{ $insumo->nombre }}</h1>
                    <span class="rounded-lg bg-zinc-100 px-2.5 py-0.5 text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $insumo->tipo_label }}
                    </span>
                </div>
                <p class="text-xs text-zinc-500 mt-0.5">
                    {{ $insumo->marca_laboratorio }} {{ $insumo->presentacion ? '· '.$insumo->presentacion : '' }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->tienePermiso('medicamentos', 'crear'))
                <button type="button" wire:click="openLoteModal" class="inline-flex h-10 items-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-emerald-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>+ Reabastecer Stock</span>
                </button>
            @endif

            @if(auth()->user()->tienePermiso('medicamentos', 'actualizar'))
                <a wire:navigate href="{{ route('insumos.edit', $insumo->id) }}" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-4 text-xs font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                    <span>Editar Insumo</span>
                </a>
            @endif

            @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                <button type="button" wire:click="solicitarEliminacionInsumo" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-4 text-xs font-bold text-rose-700 shadow-sm transition hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    <span>Eliminar Insumo</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Tarjetas de Datos Clave --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500">Stock Total Disponible</p>
            <p class="mt-1 text-2xl font-black {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-900 dark:text-white' }}">
                {{ rtrim(rtrim(number_format($stockDisponible, 3, '.', ''), '0'), '.') }}
                <span class="text-xs font-normal text-zinc-500">{{ $insumo->unidad_label }}</span>
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500">Stock Mínimo de Alerta</p>
            <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">
                {{ rtrim(rtrim(number_format((float) $insumo->stock_minimo, 3, '.', ''), '0'), '.') }}
                <span class="text-xs font-normal text-zinc-500">{{ $insumo->unidad_label }}</span>
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500">Entradas / Lotes Activos</p>
            <p class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $lotes->total() }}</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500">Ubicación Física</p>
            <p class="mt-1 text-sm font-bold text-zinc-900 dark:text-white truncate">{{ $insumo->ubicacion_predeterminada ?: 'Sin ubicación' }}</p>
        </div>
    </div>

    {{-- Tabla 1: Lotes / Entradas Físicas --}}
    <section class="agro-record-surface overflow-hidden rounded-2xl border">
        <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50/50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                    Lotes y Entradas Registradas
                </h2>
                <p class="mt-0.5 text-[10px] text-zinc-500 dark:text-zinc-400">Existencias físicas y códigos de lote asignados.</p>
            </div>
            <span class="rounded-full border border-zinc-300 bg-zinc-100 px-2.5 py-1 text-[10px] font-black text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">{{ $lotes->total() }}</span>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-xs">
                <thead class="agro-record-header border-b text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Código Insumo</th>
                        <th class="p-4">Fecha Ingreso</th>
                        <th class="p-4">Vencimiento</th>
                        <th class="p-4">Proveedor / Donante</th>
                        <th class="p-4 text-right">Inicial</th>
                        <th class="p-4 text-right">Disponible</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="agro-record-list text-sm">
                    @forelse($lotes as $lot)
                        <tr wire:key="ins-lot-{{ $lot->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <td class="p-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $lot->numero_lote }}
                            </td>
                            <td class="p-4 font-mono text-zinc-500">{{ $lot->fecha_ingreso?->format('d/m/Y') }}</td>
                            <td class="p-4">
                                @if($lot->fecha_vencimiento)
                                    <span class="font-mono text-xs {{ $lot->fecha_vencimiento->isPast() ? 'font-bold text-rose-600' : 'text-zinc-700 dark:text-zinc-300' }}">
                                        {{ $lot->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 text-[11px]">No perecible</span>
                                @endif
                            </td>
                            <td class="p-4 text-zinc-600 dark:text-zinc-300">{{ $lot->proveedor ?: '—' }}</td>
                            <td class="p-4 text-right font-mono text-zinc-500">
                                {{ rtrim(rtrim(number_format((float) $lot->cantidad_inicial, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td class="p-4 text-right font-mono font-black text-zinc-900 dark:text-white">
                                {{ rtrim(rtrim(number_format((float) $lot->cantidad_disponible, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if($lot->movimiento_id)
                                        <a wire:navigate href="{{ route('finanzas.index') }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Ver egreso en Finanzas">
                                            <svg class="h-3.5 w-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </a>
                                    @endif
                                    @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                        <x-table-action type="delete" wire:click="solicitarEliminacionLote({{ $lot->id }})" label="Eliminar lote" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-xs text-zinc-500">No hay lotes registrados para este insumo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista Móvil Lotes --}}
        <div class="divide-y divide-zinc-200 dark:divide-zinc-800 md:hidden">
            @forelse($lotes as $lot)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $lot->numero_lote }}</span>
                            <span class="block text-[10px] text-zinc-500">{{ $lot->fecha_vencimiento ? 'Vence '.$lot->fecha_vencimiento->format('d/m/Y') : 'No perecible' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono text-sm font-black text-zinc-900 dark:text-white">{{ rtrim(rtrim(number_format((float) $lot->cantidad_disponible, 3, '.', ''), '0'), '.') }} {{ $insumo->unidad_label }}</span>
                            <span class="block text-[10px] text-zinc-500">de {{ rtrim(rtrim(number_format((float) $lot->cantidad_inicial, 3, '.', ''), '0'), '.') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-100 pt-2 text-[10px] text-zinc-500 dark:border-zinc-800">
                        <span>{{ $lot->proveedor ?: 'Sin proveedor' }}</span>
                        @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                            <button type="button" wire:click="solicitarEliminacionLote({{ $lot->id }})" class="font-bold text-rose-500 hover:underline">
                                Eliminar
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-zinc-500">No hay lotes registrados.</div>
            @endforelse
        </div>
    </section>

    <!-- Paginación Lotes Insumo -->
    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageCompactOptions()" tone="emerald" live compact />
        </div>
        <div class="min-w-0">
            {{ $lotes->links('components.pagination') }}
        </div>
    </div>

    {{-- Tabla 2: Historial de Movimientos / Consumos --}}
    <section class="agro-record-surface overflow-hidden rounded-2xl border">
        <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50/50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                    Historial de Movimientos y Consumos
                </h2>
                <p class="mt-0.5 text-[10px] text-zinc-500 dark:text-zinc-400">Descargas, consumos en animales y descartes.</p>
            </div>
            <span class="rounded-full border border-zinc-300 bg-zinc-100 px-2.5 py-1 text-[10px] font-black text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">{{ $movimientos->total() }}</span>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-xs">
                <thead class="agro-record-header border-b text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Fecha / Hora</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Lote</th>
                        <th class="p-4">Animal / Destino</th>
                        <th class="p-4 text-right">Cantidad</th>
                        <th class="p-4 text-right">Saldo Lote</th>
                        <th class="p-4">Detalle</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="agro-record-list text-sm">
                    @forelse($movimientos as $mov)
                        <tr wire:key="ins-mov-{{ $mov->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <td class="p-4 font-mono text-zinc-500">{{ $mov->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td class="p-4">
                                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold {{ $mov->cantidad > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ $mov->tipo }}
                                </span>
                            </td>
                            <td class="p-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $mov->lote?->numero_lote }}</td>
                            <td class="p-4">
                                @if($mov->animal)
                                    <span class="font-bold text-zinc-900 dark:text-white">{{ $mov->animal->arete }}</span> ({{ $mov->animal->nombre }})
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-mono font-bold {{ $mov->cantidad > 0 ? 'text-emerald-600' : 'text-zinc-900 dark:text-white' }}">
                                {{ $mov->cantidad > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $mov->cantidad, 3, '.', ''), '0'), '.') }} {{ $mov->unidad }}
                            </td>
                            <td class="p-4 text-right font-mono text-zinc-500">
                                {{ rtrim(rtrim(number_format((float) $mov->saldo_lote, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td class="p-4 text-zinc-500">{{ $mov->detalle ?: '—' }}</td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if($mov->animal)
                                        <x-table-action type="view" :href="route('animal.show', $mov->animal->id)" label="Ver animal" />
                                    @endif
                                    @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                        <x-table-action type="delete" wire:click="solicitarEliminacionMovimiento({{ $mov->id }})" label="Eliminar movimiento" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="p-8 text-center text-xs text-zinc-500">Sin movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista Móvil Movimientos --}}
        <div class="divide-y divide-zinc-200 dark:divide-zinc-800 md:hidden">
            @forelse($movimientos as $mov)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-mono text-xs text-zinc-500">{{ $mov->fecha_hora->format('d/m/Y H:i') }}</span>
                        <span class="font-mono text-xs font-bold {{ $mov->cantidad > 0 ? 'text-emerald-600' : 'text-zinc-900 dark:text-white' }}">
                            {{ $mov->cantidad > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $mov->cantidad, 3, '.', ''), '0'), '.') }} {{ $mov->unidad }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-100 pt-2 text-[10px] text-zinc-500 dark:border-zinc-800">
                        <span>{{ $mov->detalle ?: 'Movimiento' }}</span>
                        @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                            <button type="button" wire:click="solicitarEliminacionMovimiento({{ $mov->id }})" class="font-bold text-rose-500 hover:underline">
                                Eliminar
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-zinc-500">Sin movimientos registrados.</div>
            @endforelse
        </div>
    </section>

    <!-- Paginación Movimientos Insumo -->
    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <x-filter-select model="perPageMovimientos" :options="\App\Support\PaginationOptions::perPageCompactOptions()" tone="emerald" live compact />
        </div>
        <div class="min-w-0">
            {{ $movimientos->links('components.pagination') }}
        </div>
    </div>

    {{-- Modal Reabastecer --}}
    @if($showLoteModal)
        <x-insumo-lote-modal
            :nombre="$insumo->nombre"
            :unidad="$insumo->unidad_label"
            :codigo-anio="$lCodigoLoteAnio"
            :numero-lote="$lNumeroLote"
        />
    @endif
</div>
