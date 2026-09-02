<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Header --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ route('insumos.index') }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-emerald-500/40 hover:bg-emerald-50/50 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-950/25 dark:hover:text-emerald-300"
           aria-label="Volver">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Botiquín · Materiales e Insumos</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-emerald-500/30 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-emerald-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Editar Insumo' : 'Registrar Nuevo Insumo' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                Control simple de descartables, materiales de curación y accesorios del botiquín.
            </p>
        </div>
    </header>

    <form wire:submit="save" autocomplete="off" x-data x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Columna Principal (7 columnas en desktop) --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Sección 1: Datos Principales --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Datos principales</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Nombre, categoría y forma de conteo.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-12">
                        <div class="flex flex-col justify-start sm:col-span-12">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-nombre">
                                <span>Nombre del insumo / material</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <input id="ins-nombre" type="text" wire:model="nombre" placeholder="Ej: Guantes de látex M, Alcohol 70°, Gasas esterilizadas, Jeringas 10ml..."
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600">
                            @error('nombre')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-7">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Categoría / Tipo</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select model="tipo" :options="$tipos" tone="emerald" searchable search-placeholder="Buscar categoría..." />
                            @error('tipo')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-5">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Unidad de medida</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select model="unidadStock" :options="$unidades" tone="emerald" live />
                            @error('unidadStock')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-5">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-marca">
                                <span>Marca / Fabricante</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input id="ins-marca" type="text" wire:model="marcaLaboratorio" placeholder="Ej: 3M, Nipro, Cranberry..."
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600">
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-7">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-presentacion">
                                <span>Presentación comercial</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input id="ins-presentacion" type="text" wire:model="presentacion" placeholder="Ej: Caja x 100 unidades, Frasco 500ml, Paquete x 10..."
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600">
                        </div>
                    </div>
                </section>

                @if($agregarExistencia)
                {{-- Sección 2: Entrada y lote --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-sky-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-sky-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-400/15 dark:text-sky-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">{{ $isEdit ? 'Entrada y lote' : 'Existencia inicial' }}</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">{{ $isEdit ? 'Estos datos actualizan el registro y el egreso vinculado en Finanzas.' : 'Primer lote del insumo o material con su stock inicial.' }}</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        @if($isEdit)
                            <div class="flex flex-col justify-start">
                                <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    <span>Lote que deseas editar</span>
                                </label>
                                <x-filter-select model="loteId" :options="$lotesEditables" tone="sky" live searchable search-placeholder="Buscar lote..." />
                                @error('loteId')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                            </div>
                        @endif

                        {{-- Motivo de Entrada --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Motivo de entrada</span>
                                <span class="ml-1 font-bold text-sky-600 dark:text-sky-400">*</span>
                            </label>
                            @if($isEdit)
                                <span class="inline-flex h-11 items-center rounded-xl border border-sky-500/25 bg-sky-50/80 px-4 text-xs font-bold text-sky-800 dark:border-sky-500/30 dark:bg-sky-950/40 dark:text-sky-300 shadow-sm">
                                    {{ $tipoIngreso === 'compra' ? '🛒 Compra vinculada a Finanzas' : '📦 Entrada sin egreso financiero' }}
                                </span>
                            @else
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach(['compra' => ['🛒', 'Compra'], 'donacion' => ['🎁', 'Donación'], 'saldo_inicial' => ['📦', 'Saldo Inicial']] as $val => [$ico, $lab])
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="tipoIngreso" value="{{ $val }}" class="peer sr-only">
                                        <span class="flex h-11 items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-600 shadow-sm transition peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-900 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400 dark:peer-checked:border-sky-400 dark:peer-checked:bg-sky-950/30 dark:peer-checked:text-sky-200">
                                            <span>{{ $ico }}</span> {{ $lab }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('tipoIngreso')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                            <div class="flex flex-col justify-start">
                                <x-insumo-lot-code-input
                                    model="numeroLote"
                                    error-field="numeroLote"
                                    :year="$codigoLoteAnio"
                                    :number="$numeroLote"
                                    tone="emerald"
                                />
                            </div>

                            <div class="flex flex-col justify-start" x-data="{ units: {{ Js::from($unidades) }}, unitKey: @entangle('unidadStock').live }">
                                <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    <span>Cantidad recibida</span>
                                    <span class="ml-1 font-bold text-sky-600 dark:text-sky-400">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="cantidadInicial" step="0.001" min="0.001" placeholder="0"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-4 pr-20 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs font-bold text-zinc-500 dark:text-zinc-400"
                                          x-text="units[unitKey] || unitKey || '{{ $unidades[$unidadStock] ?? $unidadStock }}'">
                                        {{ $unidades[$unidadStock] ?? $unidadStock }}
                                    </span>
                                </div>
                                @error('cantidadInicial')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                            </div>

                            @if($tipoIngreso === 'compra')
                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>Costo total</span>
                                        <span class="ml-1 font-bold text-sky-600 dark:text-sky-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-xs font-bold text-sky-700 dark:text-sky-400">S/</span>
                                        <input type="number" wire:model="costoTotal" step="0.01" min="0" placeholder="0.00"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white py-2 pl-10 pr-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                    <span class="mt-1 block text-[11px] text-zinc-500">Genera egreso financiero automático.</span>
                                    @error('costoTotal')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                                </div>

                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-comprobante">
                                        <span>Comprobante / ref.</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <input id="ins-comprobante" type="text" wire:model="comprobante" placeholder="Ej: F001-00234 o B001-4567"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    @error('comprobante')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                                </div>
                            @endif

                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>{{ $tipoIngreso === 'donacion' ? 'Donante / Entidad' : 'Proveedor / Tienda' }}</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <input type="text" wire:model="proveedor" placeholder="Ej: Botica Veterinaria San Martín..."
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                </div>

                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-lote-ubicacion">
                                        <span>Ubicación en botiquín</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <input id="ins-lote-ubicacion" type="text" wire:model="ubicacion" placeholder="Ej: Gaveta 2, Estante B..."
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                </div>

                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>Fecha de ingreso</span>
                                    </label>
                                    <x-date-picker model="fechaIngreso" placeholder="Fecha de ingreso" />
                                    @error('fechaIngreso')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                                </div>

                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>Fecha de vencimiento</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <x-date-picker model="fechaVencimiento" placeholder="dd/mm/aaaa (opcional)" />
                                    @error('fechaVencimiento')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Sección 3: Más Detalles del Insumo --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-amber-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-amber-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Más detalles del insumo</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Conservación, alerta de stock y notas adicionales.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Conservación</span>
                            </label>
                            <x-filter-select model="condicionAlmacenamiento" :options="$condiciones" tone="emerald" />
                            @error('condicionAlmacenamiento')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-stock-min">
                                <span>Alerta stock bajo</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <div class="grid grid-cols-[minmax(0,1fr)_4.5rem] gap-2">
                                <input id="ins-stock-min" type="number" wire:model="stockMinimo" step="0.001" min="0" placeholder="Ej. 5"
                                       class="h-11 rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                <span class="flex h-11 items-center justify-center rounded-xl bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $unidades[$unidadStock] ?? $unidadStock }}</span>
                            </div>
                            @error('stockMinimo')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-ubicacion-pred">
                                <span>Ubicación predeterminada en botiquín</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input id="ins-ubicacion-pred" type="text" wire:model="ubicacionPredeterminada" placeholder="Ej: Botiquín principal, Gabinete A, Estante 2..."
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ins-obs">
                                <span>Observaciones del insumo</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <textarea id="ins-obs" wire:model="observaciones" rows="3" placeholder="Notas sobre uso o especificaciones..."
                                      class="w-full resize-y rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"></textarea>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Columna Lateral: Foto del Insumo (5 columnas en desktop) --}}
            @php
                $photoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
            @endphp
            <aside x-data="optimizedImageUpload" class="space-y-6 lg:col-span-5">
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-teal-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-teal-500 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:bg-teal-400/15 dark:text-teal-400 shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 5.4 12.6a1.5 1.5 0 0 1 2.12 0l3.46 3.46m.75-1.49 1.11-1.11a1.5 1.5 0 0 1 2.12 0l4.05 4.05m-15 2.24h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Foto del insumo</h2>
                                <p class="mt-0.5 text-xs text-zinc-500">JPG o PNG optimizada automáticamente.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-teal-500/25 bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:border-teal-500/20 dark:bg-teal-500/15 dark:text-teal-300 shadow-sm">
                            {{ $fotoActual && !$foto ? 'Guardada' : 'Opcional' }}
                        </span>
                    </div>

                    {{-- Contenedor de Imagen Proporcionado --}}
                    <label for="foto" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950/60">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa del insumo" class="absolute inset-0 h-full w-full object-contain">
                        </template>

                        <span x-show="!previewUrl" class="absolute inset-0">
                            @if ($foto)
                                <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa del insumo" class="h-full w-full object-cover" style="object-position: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%; transform: scale({{ $photoFrame['zoom'] }}); transform-origin: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                                    Cambiar imagen
                                </span>
                            @elseif ($fotoActual)
                                <img src="{{ asset('storage/' . $fotoActual) }}" alt="Foto actual del insumo" class="h-full w-full object-cover" style="object-position: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%; transform: scale({{ $photoFrame['zoom'] }}); transform-origin: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                                    Cambiar imagen
                                </span>
                            @else
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-zinc-200 text-teal-600 shadow-sm transition group-hover:border-teal-500 group-hover:bg-teal-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-teal-400 dark:group-hover:bg-teal-950/30">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                    </span>
                                    <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Seleccionar imagen del insumo</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Opcional · Se optimiza automáticamente</span>
                                </span>
                            @endif
                        </span>

                        @if($foto && !$errors->has('foto'))
                            <x-image-frame-editor id="insumo-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @elseif($fotoActual)
                            <x-image-frame-editor id="insumo-photo-frame" :src="asset('storage/'.$fotoActual)" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @endif

                        <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-teal-400" role="status">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                        </span>
                    </label>

                    <x-image-source-actions input-id="foto" class="mt-3" />

                    <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs font-semibold text-rose-500" role="alert"></span>
                    @error('foto') <span class="mt-1.5 block text-xs font-semibold text-rose-500">{{ $message }}</span> @enderror

                    @if($foto)
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-teal-600 dark:text-teal-400">Nueva foto lista</span>
                            <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="text-xs font-bold text-zinc-500 hover:text-rose-600 dark:hover:text-rose-400">Descartar</button>
                        </div>
                    @elseif($fotoActual)
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-medium text-zinc-500">Foto guardada</span>
                            <button type="button" wire:click="removePhoto" class="text-xs font-bold text-rose-600 hover:underline dark:text-rose-400">Eliminar foto</button>
                        </div>
                    @endif
                </section>
            </aside>
        </div>

        {{-- Barra Inferior de Acciones --}}
        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-center text-xs text-zinc-500 sm:text-left">
                Campos marcados con <span class="font-bold text-emerald-600 dark:text-emerald-400">*</span> son obligatorios.
            </p>
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center">
                <a wire:navigate href="{{ route('insumos.index') }}"
                   class="agro-button-secondary">
                    Cancelar
                </a>
                <button type="submit"
                        x-bind:disabled="$store?.imageUploads?.busy"
                        wire:loading.attr="disabled"
                        wire:target="save,foto"
                        class="agro-button">
                    <svg wire:loading.remove wire:target="save" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar Insumo' : 'Guardar Insumo' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>div>
        </aside>
    </form>
</div>
