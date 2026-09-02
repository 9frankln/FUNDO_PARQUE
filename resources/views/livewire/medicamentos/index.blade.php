<div class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <header class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[.2em] text-amber-600 dark:text-amber-400">Operaciones · Botiquín</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">Botiquín y Fármacos</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Control unificado de medicamentos, insumos de curación y aplicaciones sanitarias.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->tienePermiso('medicamentos', 'crear') && $tab === 'inventario')
                <button type="button" wire:click="openImportModal" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    <span>Importar</span>
                </button>
            @endif

            <button type="button" wire:click="openMedicamentosPdfModal" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Reporte PDF</span>
            </button>

            @if(auth()->user()->tienePermiso('medicamentos', 'crear'))
                @if($tab === 'inventario')
                    <a wire:navigate href="{{ route('medicamentos.create') }}" class="inline-flex h-11 w-full shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-amber-500 px-4 text-sm font-black text-zinc-950 shadow-sm transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40 sm:w-auto">
                        <svg class="h-5 w-5 shrink-0 text-zinc-950" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 3h6v3l3 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V8l3-2V3Z"/><path d="M9 11h6M12 8v6"/></svg>
                        <span>Nuevo Medicamento</span>
                    </a>
                @elseif($tab === 'insumos')
                    <a wire:navigate href="{{ route('insumos.create') }}" class="inline-flex h-11 w-full shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-emerald-600 px-4 text-sm font-black text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 sm:w-auto">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>Nuevo Insumo</span>
                    </a>
                @endif
            @endif
        </div>
    </header>

    {{-- Tabs de Navegación --}}
    <div class="mb-6 flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-3 dark:border-zinc-800">
        <button type="button" wire:click="setTab('inventario')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition {{ $tab === 'inventario' ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-zinc-700' }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            <span>Medicamentos</span>
            <span class="rounded-full bg-zinc-200/80 px-2 py-0.5 text-[10px] font-black text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">
                {{ $stats['productos'] }}
            </span>
        </button>

        <button type="button" wire:click="setTab('insumos')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition {{ $tab === 'insumos' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-zinc-700' }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span>Insumos y Materiales</span>
            <span class="rounded-full bg-zinc-200/80 px-2 py-0.5 text-[10px] font-black text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">
                {{ $statsInsumos['productos'] }}
            </span>
        </button>

        <button type="button" wire:click="setTab('aplicaciones')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition {{ $tab === 'aplicaciones' ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-zinc-700' }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span>Historial de Aplicaciones</span>
            <span class="rounded-full bg-zinc-200/80 px-2 py-0.5 text-[10px] font-black text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">
                {{ $aplicaciones->total() }}
            </span>
        </button>
    </div>

    @if($tab === 'inventario')
        {{-- Pestaña 1: Medicamentos --}}
        <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Resumen de medicamentos">
            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Fármacos</span>
                    <p class="text-2xl font-black tabular-nums text-zinc-900 dark:text-white">{{ $stats['productos'] }}</p>
                    <p class="text-[11px] text-zinc-600">Catálogo clínico</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 3h6v3l3 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V8l3-2V3Z"/><path d="M9 11h6M12 8v6"/></svg>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Con Stock Vigente</span>
                    <p class="text-2xl font-black tabular-nums text-emerald-600 dark:text-emerald-400">{{ $stats['con_stock'] }}</p>
                    <p class="text-[11px] text-zinc-600">Disponibles para uso</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vencen Pronto</span>
                    <p class="text-2xl font-black tabular-nums text-amber-600 dark:text-amber-400">{{ $stats['por_vencer'] }}</p>
                    <p class="text-[11px] text-zinc-600">En los próximos 30 días</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vencidos</span>
                    <p class="text-2xl font-black tabular-nums text-rose-600 dark:text-rose-400">{{ $stats['vencidos'] }}</p>
                    <p class="text-[11px] text-zinc-600">Bloqueados para descarte</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
        </section>

        <x-collapsible-filters :active="$search !== '' || $tipo !== '' || $estado !== 'todos' || $vencimientoDesde !== '' || $vencimientoHasta !== '' || $orden !== 'nombre_asc'"
                               title="Filtros de medicamentos"
                               description="Busca por nombre, principio activo, tipo, estado de stock, vencimiento u orden."
                               id="medicamentos-filter-content"
                               reset="resetFilters"
                               loading-target="search,tipo,estado,vencimientoDesde,vencimientoHasta,orden"
                               class="mb-4">
            <div class="space-y-3 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                        <div class="relative w-full">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-xs">&#x1F50D;</span>
                            <input type="search" wire:model.live.debounce.350ms="search" placeholder="Nombre, principio activo, laboratorio..." class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo de producto</label>
                        <x-filter-select model="tipo" :options="['' => 'Todos los tipos'] + $tipos" tone="amber" live compact />
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Ordenar por</label>
                        <x-filter-select model="orden" :options="[
                            'reciente' => 'Más recientes (ÃÅ¡ltimo registrado)',
                            'nombre_asc' => 'Nombre (A - Z)',
                            'nombre_desc' => 'Nombre (Z - A)',
                            'stock_desc' => 'Mayor Stock',
                            'stock_asc' => 'Menor Stock',
                            'vencimiento_asc' => 'Próximo a Vencer',
                        ]" tone="amber" live compact />
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
                                        class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $estado === $key ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Vencimiento específico</label>
                            <div class="flex items-center gap-1.5 text-[11px]">
                                <button type="button" wire:click="setPresetVencimiento('30d')" class="font-bold text-amber-600 hover:underline dark:text-amber-400">30d</button>
                                <span class="text-zinc-400">·</span>
                                <button type="button" wire:click="setPresetVencimiento('60d')" class="font-bold text-amber-600 hover:underline dark:text-amber-400">60d</button>
                                <span class="text-zinc-400">·</span>
                                <button type="button" wire:click="setPresetVencimiento('este_anio')" class="font-bold text-amber-600 hover:underline dark:text-amber-400">Este año</button>
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

        {{-- Tabla Medicamentos Desktop --}}
        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[960px] border-collapse text-left">
                    <caption class="sr-only">Listado de medicamentos en inventario</caption>
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950 text-xs font-bold uppercase tracking-wider text-zinc-400">
                            <th class="p-4">
                                <button type="button" wire:click="sortByField('nombre')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-amber-400 focus:outline-none transition">
                                    <span>Medicamento / Fármaco</span>
                                    @if($orden === 'nombre_asc')
                                        <span class="text-amber-400">&uarr; (A-Z)</span>
                                    @elseif($orden === 'nombre_desc')
                                        <span class="text-amber-400">&darr; (Z-A)</span>
                                    @elseif($orden === 'reciente')
                                        <span class="text-xs text-amber-400/80 font-normal">[Recientes]</span>
                                    @else
                                        <span class="text-zinc-600">ℹ️</span>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4 text-right">
                                <button type="button" wire:click="sortByField('stock')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-amber-400 focus:outline-none ml-auto transition">
                                    <span>Stock Disponible</span>
                                    @if($orden === 'stock_desc')
                                        <span class="text-amber-400">&darr; Mayor</span>
                                    @elseif($orden === 'stock_asc')
                                        <span class="text-amber-400">&uarr; Menor</span>
                                    @else
                                        <span class="text-zinc-600">ℹ️</span>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4">
                                <button type="button" wire:click="sortByField('vencimiento')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-amber-400 focus:outline-none transition">
                                    <span>Próximo Vencimiento</span>
                                    @if($orden === 'vencimiento_asc')
                                        <span class="text-amber-400">&uarr; Próximos</span>
                                    @else
                                        <span class="text-zinc-600">ℹ️</span>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                        @forelse($medicamentos as $med)
                            @php
                                $stock = (float) $med->stock_total;
                                $stockMin = (float) $med->stock_minimo;
                                $isLow = $stock <= $stockMin;
                            @endphp
                            <tr wire:key="med-row-{{ $med->id }}" class="transition duration-500 hover:bg-zinc-800/20">
                                <td class="p-4">
                                    <a wire:navigate href="{{ route('medicamentos.show', $med->id) }}" class="group block font-bold text-zinc-100 hover:text-amber-400">
                                        <span class="text-sm">{{ $med->nombre }}</span>
                                        @if($med->principio_activo || $med->concentracion)
                                            <span class="block text-xs font-normal text-zinc-400">{{ $med->principio_activo }} {{ $med->concentracion ? '· '.$med->concentracion : '' }}</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="p-4">
                                    <span class="rounded-lg bg-zinc-800 px-2.5 py-1 text-xs font-bold text-zinc-300">
                                        {{ $med->tipo_label }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="font-mono text-sm font-black {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-100' }}">
                                        {{ rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') }}
                                    </span>
                                    <span class="ml-1 text-xs text-zinc-400">{{ $med->unidad_stock }}</span>
                                    @if($isLow && $stock > 0)
                                        <span class="block text-[10px] font-semibold text-rose-600 dark:text-rose-400">Mín: {{ rtrim(rtrim(number_format($stockMin, 3, '.', ''), '0'), '.') }}</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($med->proximo_vencimiento)
                                        @php
                                            $expDate = \Carbon\Carbon::parse($med->proximo_vencimiento)->startOfDay();
                                            $dias = (int) round(now()->startOfDay()->diffInDays($expDate, false));
                                        @endphp
                                        @if($dias < 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800/50">Vencido</span>
                                        @elseif($dias === 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800/50">Vence hoy</span>
                                        @elseif($dias === 1)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800/50">Vence mañana</span>
                                        @elseif($dias <= 30)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800/50">Vence en {{ $dias }} d</span>
                                        @else
                                            <span class="font-mono text-xs text-zinc-400">{{ $expDate->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-zinc-500 text-xs">Sin stock</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" wire:click="openLoteModal({{ $med->id }})" class="inline-flex h-8 items-center gap-1 rounded-lg border border-amber-500/40 bg-amber-500/10 px-2.5 text-xs font-bold text-amber-600 transition hover:bg-amber-500/20 dark:text-amber-300">
                                            <span>+ Lote</span>
                                        </button>
                                        <x-table-action type="view" :href="route('medicamentos.show', $med->id)" label="Ver ficha" />
                                        @if(auth()->user()->tienePermiso('medicamentos', 'actualizar'))
                                            <x-table-action type="edit" :href="route('medicamentos.edit', $med->id)" />
                                        @endif
                                        @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                            <x-table-action type="delete" wire:click="solicitarEliminacion({{ $med->id }})" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <x-empty-state icon="syringe" title="Sin medicamentos registrados" description="Aún no se han registrado medicamentos en el botiquín." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Vista Móvil Medicamentos --}}
            <div class="divide-y divide-zinc-800 md:hidden">
                @forelse($medicamentos as $med)
                    @php
                        $stock = (float) $med->stock_total;
                        $stockMin = (float) $med->stock_minimo;
                        $isLow = $stock <= $stockMin;
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <a wire:navigate href="{{ route('medicamentos.show', $med->id) }}" class="font-bold text-zinc-100">
                                {{ $med->nombre }}
                            </a>
                            <span class="rounded bg-zinc-800 px-2 py-0.5 text-[10px] font-bold text-zinc-300">
                                {{ $med->tipo_label }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-400">Stock: <strong class="font-mono text-zinc-100">{{ rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') }} {{ $med->unidad_stock }}</strong></span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="openLoteModal({{ $med->id }})" class="rounded-lg bg-amber-500 px-2.5 py-1 text-xs font-bold text-zinc-950">
                                    + Lote
                                </button>
                                <a wire:navigate href="{{ route('medicamentos.show', $med->id) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </a>
                                @if(auth()->user()->tienePermiso('medicamentos', 'actualizar'))
                                    <a wire:navigate href="{{ route('medicamentos.edit', $med->id) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    </a>
                                @endif
                                @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                    <button type="button" wire:click="solicitarEliminacion({{ $med->id }})" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-rose-200 dark:border-rose-800/60 bg-white dark:bg-zinc-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-0">
                        <x-empty-state icon="syringe" title="Sin medicamentos registrados" description="Aún no se han registrado medicamentos en el botiquín." />
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Pagination Medicamentos -->
        <div class="agro-table-footer">
            <div class="agro-table-size">
                <span>Mostrar</span>
                <x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageOptions()" tone="amber" live compact />
            </div>
            <div class="min-w-0">
                {{ $medicamentos->links('components.pagination') }}
            </div>
        </div>

    @elseif($tab === 'insumos')
        {{-- Pestaña 2: Insumos y Materiales de Botiquín --}}
        <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Resumen de insumos">
            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Insumos</span>
                    <p class="text-2xl font-black tabular-nums text-zinc-900 dark:text-white">{{ $statsInsumos['productos'] }}</p>
                    <p class="text-[11px] text-zinc-600">Materiales y descartables</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Con Stock Vigente</span>
                    <p class="text-2xl font-black tabular-nums text-emerald-600 dark:text-emerald-400">{{ $statsInsumos['con_stock'] }}</p>
                    <p class="text-[11px] text-zinc-600">Disponibles para uso</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Por Vencer</span>
                    <p class="text-2xl font-black tabular-nums text-amber-600 dark:text-amber-400">{{ $statsInsumos['por_vencer'] }}</p>
                    <p class="text-[11px] text-zinc-600">Insumos con fecha límite</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vencidos / Descarte</span>
                    <p class="text-2xl font-black tabular-nums text-rose-600 dark:text-rose-400">{{ $statsInsumos['vencidos'] }}</p>
                    <p class="text-[11px] text-zinc-600">Material caducado</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
        </section>

        {{-- Filtros Insumos --}}
        <x-collapsible-filters :active="$searchInsumo !== '' || $tipoInsumo !== '' || $estadoInsumo !== 'todos' || $vencimientoDesdeInsumo !== '' || $vencimientoHastaInsumo !== '' || $ordenInsumo !== 'nombre_asc'"
                               title="Filtros de insumos y materiales"
                               description="Busca por nombre, marca, presentación, categoría, estado de stock, vencimiento u orden."
                               id="insumos-filter-content"
                               reset="resetInsumoFilters"
                               loading-target="searchInsumo,tipoInsumo,estadoInsumo,vencimientoDesdeInsumo,vencimientoHastaInsumo,ordenInsumo"
                               class="mb-4">
            <div class="space-y-3 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar insumo</label>
                        <div class="relative w-full">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-xs">&#x1F50D;</span>
                            <input type="search" wire:model.live.debounce.350ms="searchInsumo" placeholder="Buscar insumo, marca, presentación..." class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Categoría de insumo</label>
                        <x-filter-select model="tipoInsumo" :options="['' => 'Todos los tipos'] + $tiposInsumos" tone="emerald" live compact />
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Ordenar por</label>
                        <x-filter-select model="ordenInsumo" :options="[
                            'reciente' => 'Más recientes (ÃÅ¡ltimo registrado)',
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
                                <button type="button" wire:click="$set('estadoInsumo', '{{ $key }}')"
                                        class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $estadoInsumo === $key ? 'bg-emerald-500 text-zinc-950 shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Vencimiento (perecibles)</label>
                            <div class="flex items-center gap-1.5 text-[11px]">
                                <button type="button" wire:click="setPresetVencimientoInsumo('30d')" class="font-bold text-emerald-600 hover:underline dark:text-emerald-400">30d</button>
                                <span class="text-zinc-400">·</span>
                                <button type="button" wire:click="setPresetVencimientoInsumo('60d')" class="font-bold text-emerald-600 hover:underline dark:text-emerald-400">60d</button>
                                <span class="text-zinc-400">·</span>
                                <button type="button" wire:click="setPresetVencimientoInsumo('este_anio')" class="font-bold text-emerald-600 hover:underline dark:text-emerald-400">Este año</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <x-date-picker model="vencimientoDesdeInsumo" placeholder="Desde" compact />
                            <x-date-picker model="vencimientoHastaInsumo" placeholder="Hasta" compact />
                        </div>
                    </div>
                </div>
            </div>
        </x-collapsible-filters>

        {{-- Tabla Insumos Desktop --}}
        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[960px] border-collapse text-left">
                    <caption class="sr-only">Listado de insumos y materiales en inventario</caption>
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950 text-xs font-bold uppercase tracking-wider text-zinc-400">
                            <th class="p-4">
                                <button type="button" wire:click="sortByField('nombre')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-emerald-400 focus:outline-none transition">
                                    <span>Insumo / Material</span>
                                    @if($ordenInsumo === 'nombre_asc')
                                        <span class="text-emerald-400">&uarr; (A-Z)</span>
                                    @elseif($ordenInsumo === 'nombre_desc')
                                        <span class="text-emerald-400">&darr; (Z-A)</span>
                                    @elseif($ordenInsumo === 'reciente')
                                        <span class="text-xs text-emerald-400/80 font-normal">[Recientes]</span>
                                    @else
                                        <span class="text-zinc-600">ℹ️</span>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4">Categoría</th>
                            <th class="p-4 text-right">
                                <button type="button" wire:click="sortByField('stock')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-emerald-400 focus:outline-none ml-auto transition">
                                    <span>Stock Disponible</span>
                                    @if($ordenInsumo === 'stock_desc')
                                        <span class="text-emerald-400">&darr; Mayor</span>
                                    @elseif($ordenInsumo === 'stock_asc')
                                        <span class="text-emerald-400">&uarr; Menor</span>
                                    @else
                                        <span class="text-zinc-600">ℹ️</span>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4">
                                <button type="button" wire:click="sortByField('vencimiento')" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider hover:text-emerald-400 focus:outline-none transition">
                                    <span>Vencimiento</span>
                                    @if($ordenInsumo === 'vencimiento_asc')
                                        <span class="text-emerald-400">&uarr; Próximos</span>
                                    @else
                                        <span class="text-zinc-600">ℹ️</span>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                        @forelse($insumos as $ins)
                            @php
                                $stockIns = (float) $ins->stock_total;
                                $stockMinIns = (float) $ins->stock_minimo;
                                $isLowIns = $stockIns <= $stockMinIns;
                            @endphp
                            <tr wire:key="ins-row-{{ $ins->id }}" class="transition duration-500 hover:bg-zinc-800/20">
                                <td class="p-4">
                                    <a wire:navigate href="{{ route('insumos.show', $ins->id) }}" class="group block font-bold text-zinc-100 hover:text-emerald-400">
                                        <span class="text-sm">{{ $ins->nombre }}</span>
                                        @if($ins->marca_laboratorio || $ins->presentacion)
                                            <span class="block text-xs font-normal text-zinc-400">{{ $ins->marca_laboratorio }} {{ $ins->presentacion ? '· '.$ins->presentacion : '' }}</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="p-4">
                                    <span class="rounded-lg bg-zinc-800 px-2.5 py-1 text-xs font-bold text-zinc-300">
                                        {{ $ins->tipo_label }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="font-mono text-sm font-black {{ $isLowIns ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-100' }}">
                                        {{ rtrim(rtrim(number_format($stockIns, 3, '.', ''), '0'), '.') }}
                                    </span>
                                    <span class="ml-1 text-xs text-zinc-400">{{ $ins->unidad_label }}</span>
                                    @if($isLowIns && $stockIns > 0)
                                        <span class="block text-[10px] font-semibold text-rose-600 dark:text-rose-400">Mín: {{ rtrim(rtrim(number_format($stockMinIns, 3, '.', ''), '0'), '.') }}</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($ins->proximo_vencimiento)
                                        @php
                                            $expDateIns = \Carbon\Carbon::parse($ins->proximo_vencimiento)->startOfDay();
                                            $diasIns = (int) round(now()->startOfDay()->diffInDays($expDateIns, false));
                                        @endphp
                                        @if($diasIns < 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800/50">Vencido</span>
                                        @elseif($diasIns === 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700 border border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800/50">Vence hoy</span>
                                        @elseif($diasIns === 1)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800/50">Vence mañana</span>
                                        @elseif($diasIns <= 30)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800/50">Vence en {{ $diasIns }} d</span>
                                        @else
                                            <span class="font-mono text-xs text-zinc-400">{{ $expDateIns->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-zinc-400 text-xs font-medium">No perecible</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" wire:click="openInsumoLoteModal({{ $ins->id }})" class="inline-flex h-8 items-center gap-1 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-2.5 text-xs font-bold text-emerald-600 transition hover:bg-emerald-500/20 dark:text-emerald-300">
                                            <span>+ Lote</span>
                                        </button>
                                        <x-table-action type="view" :href="route('insumos.show', $ins->id)" label="Ver ficha" />
                                        @if(auth()->user()->tienePermiso('medicamentos', 'actualizar'))
                                            <x-table-action type="edit" :href="route('insumos.edit', $ins->id)" />
                                        @endif
                                        @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                            <x-table-action type="delete" wire:click="solicitarEliminacionInsumo({{ $ins->id }})" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-sm text-zinc-500">
                                    No se encontraron insumos o materiales de botiquín.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Vista Móvil Insumos --}}
            <div class="divide-y divide-zinc-800 md:hidden">
                @forelse($insumos as $ins)
                    @php
                        $stockIns = (float) $ins->stock_total;
                        $stockMinIns = (float) $ins->stock_minimo;
                        $isLowIns = $stockIns <= $stockMinIns;
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <a wire:navigate href="{{ route('insumos.show', $ins->id) }}" class="font-bold text-zinc-100">
                                {{ $ins->nombre }}
                            </a>
                            <span class="rounded bg-zinc-800 px-2 py-0.5 text-[10px] font-bold text-zinc-300">
                                {{ $ins->tipo_label }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-400">Stock: <strong class="font-mono text-zinc-100">{{ rtrim(rtrim(number_format($stockIns, 3, '.', ''), '0'), '.') }} {{ $ins->unidad_label }}</strong></span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="openInsumoLoteModal({{ $ins->id }})" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white">
                                    + Lote
                                </button>
                                <a wire:navigate href="{{ route('insumos.show', $ins->id) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-zinc-700 text-zinc-300">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </a>
                                @if(auth()->user()->tienePermiso('medicamentos', 'actualizar'))
                                    <a wire:navigate href="{{ route('insumos.edit', $ins->id) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-zinc-700 text-zinc-300">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    </a>
                                @endif
                                @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                    <button type="button" wire:click="solicitarEliminacionInsumo({{ $ins->id }})" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-rose-800/60 text-rose-600 dark:text-rose-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-zinc-500">No hay insumos registrados.</div>
                @endforelse
            </div>
        </section>

        <!-- Pagination Insumos -->
        <div class="agro-table-footer">
            <div class="agro-table-size">
                <span>Mostrar</span>
                <x-filter-select model="perPageInsumos" :options="\App\Support\PaginationOptions::perPageOptions()" tone="emerald" live compact />
            </div>
            <div class="min-w-0">
                {{ $insumos->links('components.pagination') }}
            </div>
        </div>

    @else
        {{-- Pestaña 3: Historial de Aplicaciones a Animales --}}
        <x-collapsible-filters :active="$searchAplicacion !== '' || $medicamentoAplicacionId !== '' || $fechaDesdeAplicacion !== '' || $fechaHastaAplicacion !== '' || $periodoAplicacion !== 'todos'"
                               title="Filtros de aplicaciones"
                               description="Filtra aplicaciones por arete de animal, producto, lote, motivo o período de fecha."
                               id="aplicaciones-filter-content"
                               reset="resetAplicacionFilters"
                               loading-target="searchAplicacion,medicamentoAplicacionId,fechaDesdeAplicacion,fechaHastaAplicacion,periodoAplicacion"
                               class="mb-4">
            <div class="space-y-3 border-t border-zinc-200 pt-3 dark:border-zinc-800">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar aplicación</label>
                        <div class="relative w-full">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-xs">&#x1F50D;</span>
                            <input type="search" wire:model.live.debounce.350ms="searchAplicacion" placeholder="Buscar arete, animal, lote, diagnóstico..." class="h-8 w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 pl-8 pr-3 text-xs text-zinc-700 dark:text-zinc-100 shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Producto / Fármaco</label>
                        <x-filter-select model="medicamentoAplicacionId" :options="['' => 'Todos los productos'] + $medicamentosLista->pluck('nombre', 'id')->all()" tone="amber" live compact />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 lg:items-end">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Período de aplicación</label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach([
                                'todos' => 'Histórico',
                                'hoy' => 'Hoy',
                                'semana' => 'Esta semana',
                                'mes' => 'Este mes',
                                'anio' => 'Este año',
                            ] as $key => $label)
                                <button type="button" wire:click="setPeriodoAplicacion('{{ $key }}')"
                                        class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $periodoAplicacion === $key ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Rango personalizado</label>
                        <div class="grid grid-cols-2 gap-2">
                            <x-date-picker model="fechaDesdeAplicacion" placeholder="Desde" compact />
                            <x-date-picker model="fechaHastaAplicacion" placeholder="Hasta" compact />
                        </div>
                    </div>
                </div>
            </div>
        </x-collapsible-filters>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
            {{-- Tabla escritorio aplicaciones --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[1020px] border-collapse text-left">
                    <caption class="sr-only">Historial de aplicaciones de medicamentos a animales</caption>
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950 text-xs font-bold uppercase tracking-wider text-zinc-400">
                            <th class="p-4">Fecha / Hora</th>
                            <th class="p-4">Animal Tratado</th>
                            <th class="p-4">Medicamento / Insumo</th>
                            <th class="p-4">Lote Aplicado</th>
                            <th class="p-4">Dosis / Cantidad</th>
                            <th class="p-4">Motivo / Diagnóstico</th>
                            <th class="p-4">Responsable</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                        @forelse($aplicaciones as $app)
                            @php
                                $dosisVal = abs((float) $app->cantidad);
                                $dosisText = rtrim(rtrim(number_format($dosisVal, 3, '.', ''), '0'), '.') ?: '0';
                                $animal = $app->animal;
                                $med = $app->medicamento;
                                $lote = $app->lote;
                                $evento = $app->dosis?->eventoSalud;
                                $responsable = $app->dosis?->responsable ?: ($app->usuario?->name ?: 'Personal del fundo');
                            @endphp
                            <tr wire:key="app-row-{{ $app->id }}" class="transition duration-500 hover:bg-zinc-800/20">
                                <td class="p-4">
                                    <span class="block font-mono text-xs font-bold text-zinc-100">{{ $app->fecha_hora->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-zinc-500">{{ $app->fecha_hora->format('H:i') }}</span>
                                </td>
                                <td class="p-4">
                                    @if($animal)
                                        <div class="flex items-center gap-2">
                                            <div>
                                                <span class="font-mono text-xs font-black text-amber-600 dark:text-amber-400">{{ $animal->arete }}</span>
                                                <span class="text-xs font-bold text-zinc-100">({{ $animal->nombre }})</span>
                                                <span class="block text-[10px] text-zinc-500">{{ $animal->especie?->nombre }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-500">—</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="block font-bold text-zinc-100">{{ $med?->nombre ?: 'Medicamento no disponible' }}</span>
                                    @if($med?->tipo_label)
                                        <span class="text-[10px] text-zinc-400">{{ $med->tipo_label }}</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="font-mono text-xs font-bold text-amber-600 dark:text-amber-400">
                                        {{ $lote?->numero_lote ?: 'Sin lote' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono text-xs font-black text-zinc-100">{{ $dosisText }}</span>
                                    <span class="text-xs text-zinc-400">{{ $app->unidad }}</span>
                                </td>
                                <td class="p-4">
                                    @if($evento)
                                        <span class="block text-xs font-bold text-zinc-200">{{ $evento->sintomas_diagnostico ?: $evento->tipo_evento }}</span>
                                        <span class="text-[10px] text-zinc-500">Caso #{{ $evento->id }}</span>
                                    @else
                                        <span class="text-xs text-zinc-400">{{ $app->detalle ?: 'Aplicación sanitaria' }}</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-zinc-400">
                                    {{ $responsable }}
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($animal)
                                            <x-table-action type="view" :href="route('animal.show', $animal->id)" label="Ver animal" />
                                        @endif
                                        @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                            <x-table-action type="delete" wire:click="solicitarEliminacionAplicacion({{ $app->id }})" label="Eliminar registro" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-sm text-zinc-500">
                                    No se encontraron aplicaciones a animales en este período.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tarjetas Móvil Aplicaciones --}}
            <div class="divide-y divide-zinc-800 md:hidden">
                @forelse($aplicaciones as $app)
                    @php
                        $dosisVal = abs((float) $app->cantidad);
                        $dosisText = rtrim(rtrim(number_format($dosisVal, 3, '.', ''), '0'), '.') ?: '0';
                        $animal = $app->animal;
                        $med = $app->medicamento;
                        $lote = $app->lote;
                        $evento = $app->dosis?->eventoSalud;
                        $responsable = $app->dosis?->responsable ?: ($app->usuario?->name ?: 'Personal del fundo');
                    @endphp
                    <div wire:key="app-card-{{ $app->id }}" class="p-4 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-xs font-black text-amber-600 dark:text-amber-400">{{ $animal?->arete ?: 'S/A' }}</span>
                                <span class="text-xs font-bold text-zinc-200">{{ $animal?->nombre }}</span>
                            </div>
                            <span class="font-mono text-[10px] text-zinc-400">{{ $app->fecha_hora->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="font-bold text-zinc-100">{{ $med?->nombre ?: 'Medicamento' }}</span>
                            <span class="font-mono font-black text-zinc-100">{{ $dosisText }} {{ $app->unidad }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-t border-zinc-800/60 pt-1.5 text-[10px] text-zinc-500">
                            <span>{{ $evento?->sintomas_diagnostico ?: ($app->detalle ?: 'Tratamiento') }}</span>
                            <div class="flex items-center gap-2">
                                <span>{{ $responsable }}</span>
                                @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                    <button type="button" wire:click="solicitarEliminacionAplicacion({{ $app->id }})" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                        Eliminar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-zinc-500">No hay aplicaciones registradas.</div>
                @endforelse
            </div>
        </section>

        <!-- Pagination Aplicaciones -->
        <div class="agro-table-footer">
            <div class="agro-table-size">
                <span>Mostrar</span>
                <x-filter-select model="perPageAplicaciones" :options="\App\Support\PaginationOptions::perPageOptions()" tone="amber" live compact />
            </div>
            <div class="min-w-0">
                {{ $aplicaciones->links('components.pagination') }}
            </div>
        </div>
    @endif

    {{-- Modal Lote Medicamento --}}
    @if($showLoteModal)
        <x-medicamento-lote-modal
            :nombre="$loteMedicamentoNombre"
            :unidad="$loteMedicamentoUnidad"
            :codigo-anio="$lCodigoLoteAnio"
            :numero-lote="$lNumeroLote"
        />
    @endif

    {{-- Modal Lote Insumo --}}
    @if($showInsumoLoteModal)
        <x-insumo-lote-modal
            :nombre="$insumoLoteNombre"
            :unidad="$insumoLoteUnidad"
            prefix="ins"
            wire-close="closeInsumoLoteModal"
            wire-submit="saveInsumoLote"
            :codigo-anio="$insCodigoLoteAnio"
            :numero-lote="$insNumeroLote"
        />
    @endif

    {{-- MODAL: Exportación de Reporte PDF --}}
    @if($showMedicamentosPdfModal)
        @php
            $pdfSections = \App\Livewire\Medicamentos\Index::pdfSectionOptions();
            $pdfLabels = \App\Livewire\Medicamentos\Index::pdfColumnLabels();
            $pdfThemes = [
                'medicamentos' => [
                    'selected' => 'border-amber-300 bg-amber-50 text-amber-950 ring-1 ring-amber-200 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-50',
                    'panel' => 'border-amber-200 bg-amber-50/55 dark:border-amber-500/30 dark:bg-amber-500/[.07]',
                    'title' => 'text-amber-900 dark:text-amber-100',
                    'action' => 'text-amber-700 hover:text-amber-600 dark:text-amber-300 dark:hover:text-amber-200',
                    'field' => 'border-amber-300 bg-white/90 text-amber-950 dark:border-amber-500/55 dark:bg-amber-500/10 dark:text-amber-50',
                    'check' => 'border-amber-500 bg-amber-500 dark:border-amber-400 dark:bg-amber-400',
                    'icon' => 'text-amber-950',
                ],
                'insumos' => [
                    'selected' => 'border-emerald-300 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-200 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'panel' => 'border-emerald-200 bg-emerald-50/55 dark:border-emerald-500/30 dark:bg-emerald-500/[.07]',
                    'title' => 'text-emerald-900 dark:text-emerald-100',
                    'action' => 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-300 dark:hover:text-emerald-200',
                    'field' => 'border-emerald-300 bg-white/90 text-emerald-950 dark:border-emerald-500/55 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'check' => 'border-emerald-600 bg-emerald-600 dark:border-emerald-400 dark:bg-emerald-400',
                    'icon' => 'text-white dark:text-emerald-950',
                ],
                'aplicaciones' => [
                    'selected' => 'border-sky-300 bg-sky-50 text-sky-950 ring-1 ring-sky-200 dark:border-sky-500/60 dark:bg-sky-500/10 dark:text-sky-50',
                    'panel' => 'border-sky-200 bg-sky-50/55 dark:border-sky-500/30 dark:bg-sky-500/[.07]',
                    'title' => 'text-sky-900 dark:text-sky-100',
                    'action' => 'text-sky-700 hover:text-sky-600 dark:text-sky-300 dark:hover:text-sky-200',
                    'field' => 'border-sky-300 bg-white/90 text-sky-950 dark:border-sky-500/55 dark:bg-sky-500/10 dark:text-sky-50',
                    'check' => 'border-sky-600 bg-sky-600 dark:border-sky-400 dark:bg-sky-400',
                    'icon' => 'text-white dark:text-sky-950',
                ],
            ];
        @endphp
        <div x-data="{
                sections: $wire.entangle('medicamentosPdfSections').live,
                columns: $wire.entangle('medicamentosPdfColumns').live,
                init() { document.body.classList.add('overflow-hidden') },
                destroy() { document.body.classList.remove('overflow-hidden') },
            }"
             x-on:keydown.escape.window="$wire.set('showMedicamentosPdfModal', false)"
             x-on:click.self="$wire.set('showMedicamentosPdfModal', false)"
             class="agro-dialog-overlay agro-dialog-overlay--full">
            <div role="dialog" aria-modal="true" aria-labelledby="medicamentos-report-title"
                 class="agro-dialog agro-dialog--full h-[calc(100dvh-0.5rem)] sm:h-[calc(100dvh-1.5rem)] sm:w-[calc(100vw-1.5rem)]">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-700">
                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-amber-600 dark:text-amber-400">Exportación de Botiquín</span>
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <span x-text="sections.length"></span> de {{ count($pdfSections) }} secciones
                            </span>
                        </div>
                        <h3 id="medicamentos-report-title" class="text-lg font-bold text-zinc-900 dark:text-white sm:text-xl">Generar Reporte PDF de Botiquín</h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Selecciona secciones y campos en un formato compacto A4 horizontal.</p>
                    </div>
                    <button type="button" wire:click="$set('showMedicamentosPdfModal', false)"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
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
                                    class="text-xs font-bold text-amber-600 transition hover:text-amber-500 focus:outline-none focus:underline dark:text-amber-400 dark:hover:text-amber-300">
                                Seleccionar todas
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-1">
                        @foreach($pdfSections as $key => $option)
                            @php $theme = $pdfThemes[$key]; @endphp
                            <label :class="sections.includes('{{ $key }}')
                                    ? @js($theme['selected'])
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-300 dark:hover:border-zinc-600'"
                                   class="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 transition focus-within:ring-2 focus-within:ring-amber-500/50">
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
                        @error('medicamentosPdfSections') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        @error('medicamentosPdfSections.*') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
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
                                    @error("medicamentosPdfColumns.{$sectionKey}") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                    @error("medicamentosPdfColumns.{$sectionKey}.*") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="hidden text-xs text-zinc-500 sm:block dark:text-zinc-400">Reporte A4 horizontal · Filtros activos · Formato compacto</p>
                    <div class="flex gap-2 sm:justify-end">
                        <button type="button" wire:click="$set('showMedicamentosPdfModal', false)"
                                class="h-11 flex-1 rounded-xl border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 sm:flex-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            Cancelar
                        </button>
                        <button type="button" wire:click="downloadMedicamentosReport" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70" wire:target="downloadMedicamentosReport"
                                class="h-11 flex-1 rounded-xl bg-emerald-600 px-6 text-sm font-bold text-white shadow-md shadow-emerald-600/15 transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait sm:flex-none inline-flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="downloadMedicamentosReport" class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ver Vista Previa PDF</span>
                            </span>
                            <span wire:loading wire:target="downloadMedicamentosReport">Generando vista previa...</span>
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
        :back-action="'$set(\'showMedicamentosPdfModal\', true); $set(\'showExportModal\', false)'"
    >
        {{-- No options slot needed for medicamentos (handled by showMedicamentosPdfModal) --}}
    </x-pdf-preview-modal>

    {{-- Modal Importación Masiva Medicamentos --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-950/60">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-zinc-900 dark:text-white">Importación Masiva de Medicamentos</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Carga medicamentos y lotes de inventario desde Excel (.xlsx) o CSV.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeImportModal" class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Paso 1: Descargar Plantilla --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">Paso 1: Descarga la plantilla oficial</p>
                            <p class="text-xs text-amber-900/80 dark:text-amber-200/70">Incluye pestaña de registro limpia y pestaña de guía con categorías y unidades.</p>
                        </div>
                        <button type="button" wire:click="downloadImportTemplate" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-amber-500 px-3.5 py-2 text-xs font-black text-zinc-950 shadow-sm transition hover:bg-amber-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            <span>Descargar Plantilla (.xlsx)</span>
                        </button>
                    </div>

                    {{-- Paso 2: Subir Archivo --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Paso 2: Sube el archivo completado</label>
                        <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 p-6 transition hover:border-amber-500 dark:border-zinc-700 dark:hover:border-amber-400">
                            <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                            <div class="text-center">
                                <svg class="mx-auto h-8 w-8 text-zinc-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                <p class="mt-2 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                    @if($importFile)
                                        <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $importFile->getClientOriginalName() }}</span>
                                    @else
                                        Arrastra tu archivo aquí o <span class="text-amber-600 underline dark:text-amber-400">haz clic para examinar</span>
                                    @endif
                                </p>
                                <p class="mt-1 text-[11px] text-zinc-400">Formatos permitidos: .xlsx, .xls, .csv (Máx. 10MB)</p>
                            </div>
                        </div>
                        @error('importFile') <p class="mt-1.5 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Resumen / Errores de Validación --}}
                    @if(!empty($importSummary))
                        <div class="rounded-xl border p-4 {{ $importSummary['invalid'] > 0 ? 'border-amber-300 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/20' : 'border-emerald-300 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/20' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black {{ $importSummary['invalid'] > 0 ? 'text-amber-900 dark:text-amber-200' : 'text-emerald-900 dark:text-emerald-200' }}">
                                    Resultado: {{ $importSummary['valid'] }} válidos de {{ $importSummary['total'] }} filas procesadas.
                                </span>
                                @if($importSummary['imported'] > 0)
                                    <span class="rounded-full bg-emerald-600 px-2.5 py-0.5 text-[10px] font-black text-white">
                                        {{ $importSummary['imported'] }} guardados
                                    </span>
                                @endif
                            </div>

                            @if(!empty($importSummary['errors']))
                                <div class="mt-3 max-h-40 overflow-y-auto space-y-1.5 border-t border-amber-200 pt-2 dark:border-amber-900/40">
                                    @foreach($importSummary['errors'] as $err)
                                        <p class="text-[11px] text-rose-700 dark:text-rose-400">
                                            <strong>Fila {{ $err['row'] }} ({{ $err['producto'] }}):</strong> {{ implode(', ', $err['messages']) }}
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-6 py-4 rounded-b-2xl dark:border-zinc-800 dark:bg-zinc-900/50">
                    <button type="button" wire:click="closeImportModal" class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-xs font-bold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        {{ $importSuccess ? 'Cerrar' : 'Cancelar' }}
                    </button>
                    @if(!$importSuccess)
                        <button type="button" wire:click="processImport" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2 text-xs font-black text-zinc-950 shadow-sm transition hover:bg-amber-400 disabled:opacity-50">
                            <span wire:loading.remove wire:target="processImport">Procesar e Importar</span>
                            <span wire:loading wire:target="processImport">Procesando...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

