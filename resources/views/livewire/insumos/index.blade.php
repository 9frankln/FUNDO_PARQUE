<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                </span>
                <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Insumos y Materiales</h1>
            </div>
            <p class="mt-1 text-xs text-zinc-500">Material de botiquín, descartables, antisépticos y accesorios.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="openInsumosPdfModal" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 text-xs font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Reporte PDF</span>
            </button>

            @if(auth()->user()->tienePermiso('medicamentos', 'crear'))
                <a wire:navigate href="{{ route('insumos.create') }}" class="inline-flex h-10 items-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-black text-white shadow-sm transition hover:bg-emerald-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Nuevo Insumo</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Tabs de navegación --}}
    <div class="flex border-b border-zinc-200 dark:border-zinc-800">
        <button
            type="button"
            wire:click="setTab('inventario')"
            class="group relative flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-bold transition {{ $tab === 'inventario' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
            <span>Inventario y Stock</span>
            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $stats['productos'] }}</span>
        </button>

        <button
            type="button"
            wire:click="setTab('consumos')"
            class="group relative flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-bold transition {{ $tab === 'consumos' ? 'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
            <span>Historial de Consumos y Usos</span>
        </button>
    </div>

    @if($tab === 'inventario')
        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-500">Catálogo de Insumos</p>
                <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">{{ $stats['productos'] }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Con Stock Disponible</p>
                <p class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['con_stock'] }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">Por Vencer (30 días)</p>
                <p class="mt-1 text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['por_vencer'] }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400">Vencidos / Críticos</p>
                <p class="mt-1 text-2xl font-black text-rose-600 dark:text-rose-400">{{ $stats['vencidos'] }}</p>
            </div>
        </div>

        {{-- Filtros --}}
        <x-collapsible-filters :active="$search !== '' || $tipo !== '' || $estado !== 'todos' || $vencimientoDesde !== '' || $vencimientoHasta !== '' || $orden !== 'nombre_asc'"
                               title="Filtros de insumos y materiales"
                               description="Busca por nombre, marca, presentación, categoría, estado de stock, vencimiento u orden."
                               id="insumos-standalone-filter-content"
                               reset="resetFilters"
                               loading-target="search,tipo,estado,vencimientoDesde,vencimientoHasta,orden"
                               class="mb-4">
            <div class="space-y-3 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar insumo</label>
                        <div class="relative w-full">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-xs">&#x1F50D;</span>
                            <input type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar insumo, marca, presentación..." class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Categoría de insumo</label>
                        <x-filter-select model="tipo" :options="['' => 'Todos los tipos'] + $tipos" tone="emerald" live compact />
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Ordenar por</label>
                        <x-filter-select model="orden" :options="[
                            'reciente' => 'Más recientes (Último registrado)',
                            'nombre_asc' => 'Nombre (A - Z)',
                            'nombre_desc' => 'Nombre (Z - A)',
                            'stock_desc' => 'Mayor Stock',
                            'stock_asc' => 'Menor Stock',
                            'vencimiento_asc' => 'Próximo a Vencer',
                        ]" tone="emerald" live compact />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 lg:items-end">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Estado de stock</label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach([
                                'todos' => 'Todos',
                                'disponible' => 'Con stock',
                                'stock_bajo' => 'Stock bajo',
                                'por_vencer' => 'Por vencer (30d)',
                                'vencido' => 'Vencidos',
                                'archivado' => 'Archivados',
                            ] as $key => $label)
                                <button type="button" wire:click="$set('estado', '{{ $key }}')"
                                        class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $estado === $key ? 'bg-emerald-500 text-zinc-950 shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Vencimiento (perecibles)</label>
                            <div class="flex items-center gap-1.5 text-[11px]">
                                <button type="button" wire:click="setPresetVencimiento('30d')" class="font-bold text-emerald-600 hover:underline dark:text-emerald-400">30d</button>
                                <span class="text-zinc-400">·</span>
                                <button type="button" wire:click="setPresetVencimiento('60d')" class="font-bold text-emerald-600 hover:underline dark:text-emerald-400">60d</button>
                                <span class="text-zinc-400">·</span>
                                <button type="button" wire:click="setPresetVencimiento('este_anio')" class="font-bold text-emerald-600 hover:underline dark:text-emerald-400">Este año</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <x-date-picker model="vencimientoDesde" placeholder="Desde" compact />
                            <x-date-picker model="vencimientoHasta" placeholder="Hasta" compact />
                        </div>
                    </div>
                </div>
            </div>
        </x-collapsible-filters>

        {{-- Tabla de Insumos (Desktop) --}}
        <div class="agro-record-surface hidden overflow-hidden rounded-2xl border md:block">
            <table class="w-full text-left text-xs">
                <thead class="agro-record-header border-b text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">
                            <button type="button" wire:click="sortByField('nombre')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-emerald-600 dark:hover:text-emerald-400 focus:outline-none transition">
                                <span>Insumo / Material</span>
                                @if($orden === 'nombre_asc')
                                    <span class="text-emerald-500">↑ (A-Z)</span>
                                @elseif($orden === 'nombre_desc')
                                    <span class="text-emerald-500">↓ (Z-A)</span>
                                @elseif($orden === 'reciente')
                                    <span class="text-xs text-emerald-500/80 font-normal">[Recientes]</span>
                                @else
                                    <span class="text-zinc-400">↕</span>
                                @endif
                            </button>
                        </th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4 text-right">
                            <button type="button" wire:click="sortByField('stock')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-emerald-600 dark:hover:text-emerald-400 focus:outline-none ml-auto transition">
                                <span>Stock Disponible</span>
                                @if($orden === 'stock_desc')
                                    <span class="text-emerald-500">↓ Mayor</span>
                                @elseif($orden === 'stock_asc')
                                    <span class="text-emerald-500">↑ Menor</span>
                                @else
                                    <span class="text-zinc-400">↕</span>
                                @endif
                            </button>
                        </th>
                        <th class="p-4">
                            <button type="button" wire:click="sortByField('vencimiento')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-emerald-600 dark:hover:text-emerald-400 focus:outline-none transition">
                                <span>Alerta Vencimiento</span>
                                @if($orden === 'vencimiento_asc')
                                    <span class="text-emerald-500">↑ Próximos</span>
                                @else
                                    <span class="text-zinc-400">↕</span>
                                @endif
                            </button>
                        </th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="agro-record-list text-sm">
                    @forelse($insumos as $insumo)
                        @php
                            $stock = (float) $insumo->stock_total;
                            $stockMin = (float) $insumo->stock_minimo;
                            $isLow = $stock <= $stockMin;
                        @endphp
                        <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <td class="p-4">
                                <a wire:navigate href="{{ route('insumos.show', $insumo->id) }}" class="group block font-bold text-zinc-900 hover:text-emerald-600 dark:text-white dark:hover:text-emerald-400">
                                    <span class="text-sm">{{ $insumo->nombre }}</span>
                                    @if($insumo->marca_laboratorio || $insumo->presentacion)
                                        <span class="block text-[11px] font-normal text-zinc-500">{{ $insumo->marca_laboratorio }} {{ $insumo->presentacion ? '· '.$insumo->presentacion : '' }}</span>
                                    @endif
                                </a>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex rounded-lg bg-zinc-100 px-2.5 py-1 text-[10px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $insumo->tipo_label }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <span class="font-mono text-sm font-black {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-900 dark:text-white' }}">
                                    {{ rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') }}
                                </span>
                                <span class="ml-1 text-[10px] text-zinc-500">{{ $insumo->unidad_label }}</span>
                                @if($isLow && $stock > 0)
                                    <span class="block text-[10px] font-semibold text-rose-500">Mín: {{ rtrim(rtrim(number_format($stockMin, 3, '.', ''), '0'), '.') }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($insumo->proximo_vencimiento)
                                    @php
                                        $expDateInsumo = \Carbon\Carbon::parse($insumo->proximo_vencimiento)->startOfDay();
                                        $dias = (int) round(now()->startOfDay()->diffInDays($expDateInsumo, false));
                                    @endphp
                                    @if($dias < 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">Vencido</span>
                                    @elseif($dias === 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">Vence hoy</span>
                                    @elseif($dias === 1)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">Vence mañana</span>
                                    @elseif($dias <= 30)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">Vence en {{ $dias }} d</span>
                                    @else
                                        <span class="font-mono text-xs text-zinc-500">{{ $expDateInsumo->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400 text-[11px]">No perecible</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="openLoteModal({{ $insumo->id }})" class="inline-flex h-8 items-center gap-1 rounded-lg border border-emerald-600/30 bg-emerald-50 px-2.5 text-[11px] font-bold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/40 dark:bg-emerald-950/50 dark:text-emerald-300">
                                        <span>+ Reabastecer</span>
                                    </button>
                                    <a wire:navigate href="{{ route('insumos.show', $insumo->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Ver ficha">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </a>
                                    <a wire:navigate href="{{ route('insumos.edit', $insumo->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Editar insumo">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    </a>
                                    <button type="button" wire:click="solicitarEliminacionInsumo({{ $insumo->id }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 text-rose-600 transition hover:bg-rose-50 dark:border-rose-900/50 dark:text-rose-400 dark:hover:bg-rose-950/40" title="Eliminar insumo">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-0">
                                <x-empty-state icon="beaker" title="Sin insumos registrados" description="Aún no se han registrado insumos ni materiales." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="agro-table-footer">
                {{ $insumos->links('components.pagination') }}
            </div>
        </div>

        {{-- Vista móvil (Tarjetas) --}}
        <div class="space-y-3 md:hidden">
            @forelse($insumos as $insumo)
                @php
                    $stock = (float) $insumo->stock_total;
                    $stockMin = (float) $insumo->stock_minimo;
                    $isLow = $stock <= $stockMin;
                @endphp
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-2">
                        <a wire:navigate href="{{ route('insumos.show', $insumo->id) }}" class="font-bold text-zinc-900 dark:text-white">
                            {{ $insumo->nombre }}
                        </a>
                        <span class="rounded-lg bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $insumo->tipo_label }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        <div>
                            <span class="block text-[10px] uppercase tracking-wider text-zinc-400">Stock</span>
                            <span class="font-mono text-sm font-black {{ $isLow ? 'text-rose-600' : 'text-zinc-900 dark:text-white' }}">
                                {{ rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') }} {{ $insumo->unidad_label }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="openLoteModal({{ $insumo->id }})" class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white">
                                + Entrada
                            </button>
                            <a wire:navigate href="{{ route('insumos.edit', $insumo->id) }}" class="rounded-xl border border-zinc-200 p-1.5 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300" title="Editar">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            </a>
                            <button type="button" wire:click="solicitarEliminacionInsumo({{ $insumo->id }})" class="rounded-xl border border-rose-200 p-1.5 text-rose-600 dark:border-rose-900/50 dark:text-rose-400" title="Eliminar">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-0">
                    <x-empty-state icon="beaker" title="Sin insumos registrados" description="Aún no se han registrado insumos ni materiales." />
                </div>
            @endforelse
        </div>

        <div class="agro-table-footer">
            <div class="agro-table-size">
                <span>Mostrar</span>
                <x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageOptions()" tone="emerald" live compact />
            </div>
            <div class="min-w-0">
                {{ $insumos->links('components.pagination') }}
            </div>
        </div>

    @else
        {{-- Pestaña Consumos e Historial --}}
        <x-collapsible-filters :active="$searchConsumo !== '' || $insumoConsumoId !== '' || $fechaDesdeConsumo !== '' || $fechaHastaConsumo !== '' || $periodoConsumo !== 'todos'"
                               title="Filtros de consumos"
                               description="Filtra consumos por insumo, lote, animal, motivo o rango de fechas."
                               id="consumos-filter-content"
                               reset="resetConsumoFilters"
                               loading-target="searchConsumo,insumoConsumoId,fechaDesdeConsumo,fechaHastaConsumo,periodoConsumo"
                               class="mb-4">
            <div class="space-y-3 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar consumo</label>
                        <div class="relative w-full">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-xs">&#x1F50D;</span>
                            <input type="search" wire:model.live.debounce.350ms="searchConsumo" placeholder="Buscar por insumo, lote, animal o detalle..." class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Insumo / Material</label>
                        <x-filter-select model="insumoConsumoId" :options="['' => 'Todos los insumos'] + $insumosLista->pluck('nombre', 'id')->all()" tone="emerald" live compact />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 lg:items-end">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Período de consumo</label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach([
                                'todos' => 'Histórico',
                                'hoy' => 'Hoy',
                                'semana' => 'Esta semana',
                                'mes' => 'Este mes',
                                'anio' => 'Este año',
                            ] as $key => $label)
                                <button type="button" wire:click="setPeriodoConsumo('{{ $key }}')"
                                        class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $periodoConsumo === $key ? 'bg-emerald-500 text-zinc-950 shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Rango personalizado</label>
                        <div class="grid grid-cols-2 gap-2">
                            <x-date-picker model="fechaDesdeConsumo" placeholder="Desde" compact />
                            <x-date-picker model="fechaHastaConsumo" placeholder="Hasta" compact />
                        </div>
                    </div>
                </div>
            </div>
        </x-collapsible-filters>

        <div class="agro-record-surface hidden overflow-hidden rounded-2xl border md:block">
            <table class="w-full text-left text-xs">
                <thead class="agro-record-header border-b text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Fecha / Hora</th>
                        <th class="p-4">Insumo</th>
                        <th class="p-4">Lote Usado</th>
                        <th class="p-4">Destino / Animal</th>
                        <th class="p-4 text-right">Cantidad</th>
                        <th class="p-4">Detalle</th>
                    </tr>
                </thead>
                <tbody class="agro-record-list text-sm">
                    @forelse($consumos as $con)
                        <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <td class="p-4 font-mono text-zinc-500">{{ $con->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td class="p-4 font-bold text-zinc-900 dark:text-white">{{ $con->insumo?->nombre }}</td>
                            <td class="p-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $con->lote?->numero_lote }}</td>
                            <td class="p-4">
                                @if($con->animal)
                                    <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $con->animal->arete }}</span>
                                    <span class="text-zinc-500 text-[11px]">({{ $con->animal->nombre }})</span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-mono font-black text-zinc-900 dark:text-white">
                                {{ rtrim(rtrim(number_format(abs((float) $con->cantidad), 3, '.', ''), '0'), '.') }} {{ $con->unidad }}
                            </td>
                            <td class="p-4 text-zinc-500">{{ $con->detalle ?: 'Consumo de botiquín' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-xs text-zinc-500">No hay consumos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="agro-table-footer">
            <div class="agro-table-size">
                <span>Mostrar</span>
                <x-filter-select model="perPageConsumos" :options="\App\Support\PaginationOptions::perPageOptions()" tone="emerald" live compact />
            </div>
            <div class="min-w-0">
                {{ $consumos->links('components.pagination') }}
            </div>
        </div>
    @endif

    {{-- Modal Reabastecer Lote Insumo --}}
    @if($showLoteModal)
        <x-insumo-lote-modal
            :nombre="$loteInsumoNombre"
            :unidad="$loteInsumoUnidad"
            :codigo-anio="$lCodigoLoteAnio"
            :numero-lote="$lNumeroLote"
        />
    @endif

    {{-- MODAL: Exportación de Reporte PDF --}}
    @if($showInsumosPdfModal)
        @php
            $pdfSections = \App\Livewire\Insumos\Index::pdfSectionOptions();
            $pdfLabels = \App\Livewire\Insumos\Index::pdfColumnLabels();
            $pdfThemes = [
                'insumos' => [
                    'selected' => 'border-emerald-300 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-200 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'panel' => 'border-emerald-200 bg-emerald-50/55 dark:border-emerald-500/30 dark:bg-emerald-500/[.07]',
                    'title' => 'text-emerald-900 dark:text-emerald-100',
                    'action' => 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-300 dark:hover:text-emerald-200',
                    'field' => 'border-emerald-300 bg-white/90 text-emerald-950 dark:border-emerald-500/55 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'check' => 'border-emerald-600 bg-emerald-600 dark:border-emerald-400 dark:bg-emerald-400',
                    'icon' => 'text-white dark:text-emerald-950',
                ],
                'consumos' => [
                    'selected' => 'border-teal-300 bg-teal-50 text-teal-950 ring-1 ring-teal-200 dark:border-teal-500/60 dark:bg-teal-500/10 dark:text-teal-50',
                    'panel' => 'border-teal-200 bg-teal-50/55 dark:border-teal-500/30 dark:bg-teal-500/[.07]',
                    'title' => 'text-teal-900 dark:text-teal-100',
                    'action' => 'text-teal-700 hover:text-teal-600 dark:text-teal-300 dark:hover:text-teal-200',
                    'field' => 'border-teal-300 bg-white/90 text-teal-950 dark:border-teal-500/55 dark:bg-teal-500/10 dark:text-teal-50',
                    'check' => 'border-teal-600 bg-teal-600 dark:border-teal-400 dark:bg-teal-400',
                    'icon' => 'text-white dark:text-teal-950',
                ],
            ];
        @endphp
        <div x-data="{
                sections: $wire.entangle('insumosPdfSections').live,
                columns: $wire.entangle('insumosPdfColumns').live,
                init() { document.body.classList.add('overflow-hidden') },
                destroy() { document.body.classList.remove('overflow-hidden') },
            }"
             x-on:keydown.escape.window="$wire.set('showInsumosPdfModal', false)"
             x-on:click.self="$wire.set('showInsumosPdfModal', false)"
             class="agro-dialog-overlay agro-dialog-overlay--full">
            <div role="dialog" aria-modal="true" aria-labelledby="insumos-report-title"
                 class="agro-dialog agro-dialog--full h-[calc(100dvh-0.5rem)] sm:h-[calc(100dvh-1.5rem)] sm:w-[calc(100vw-1.5rem)]">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-700">
                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Exportación de Insumos</span>
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <span x-text="sections.length"></span> de {{ count($pdfSections) }} secciones
                            </span>
                        </div>
                        <h3 id="insumos-report-title" class="text-lg font-bold text-zinc-900 dark:text-white sm:text-xl">Generar Reporte PDF de Insumos</h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Selecciona secciones y campos en un formato compacto A4 horizontal.</p>
                    </div>
                    <button type="button" wire:click="$set('showInsumosPdfModal', false)"
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
                        @error('insumosPdfSections') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        @error('insumosPdfSections.*') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </aside>

                    <div class="p-4 sm:p-5 xl:overflow-y-auto 2xl:p-6">
                        <div x-cloak x-show="sections.length === 0" class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 text-center dark:border-zinc-700 dark:bg-zinc-950/30">
                            <div class="max-w-sm">
                                <strong class="block text-sm text-zinc-700 dark:text-zinc-200">Sin secciones seleccionadas</strong>
                                <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Activa una sección para configurar los campos del reporte.</span>
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
                                    @error("insumosPdfColumns.{$sectionKey}") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                    @error("insumosPdfColumns.{$sectionKey}.*") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="hidden text-xs text-zinc-500 sm:block dark:text-zinc-400">Reporte A4 horizontal · Filtros activos · Formato compacto</p>
                    <div class="flex gap-2 sm:justify-end">
                        <button type="button" wire:click="$set('showInsumosPdfModal', false)"
                                class="h-11 flex-1 rounded-xl border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 sm:flex-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            Cancelar
                        </button>
                        <button type="button" wire:click="downloadInsumosReport" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70" wire:target="downloadInsumosReport"
                                class="h-11 flex-1 rounded-xl bg-emerald-600 px-6 text-sm font-bold text-white shadow-md shadow-emerald-600/15 transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait sm:flex-none">
                            <span wire:loading.remove wire:target="downloadInsumosReport" class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ver Vista Previa PDF</span>
                            </span>
                            <span wire:loading wire:target="downloadInsumosReport">Generando vista previa...</span>
                        </button>
                    </div>
                </div>
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
        :back-action="'$set(\'showInsumosPdfModal\', true); $set(\'showExportModal\', false)'"
    >
    </x-pdf-preview-modal>
</div>
