<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Header --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ $isEdit ? route('medicamentos.show', $medicamentoId) : route('medicamentos.index') }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-amber-500/40 hover:bg-amber-50/50 hover:text-amber-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-amber-500/40 dark:hover:bg-amber-950/25 dark:hover:text-amber-300"
           aria-label="Volver">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 3h6v3l3 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V8l3-2V3Z"/><path d="M9 11h6M12 8v6"/>
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-600 dark:text-amber-400">Botiquín · Medicamentos</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-amber-500/30 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-amber-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Actualizar medicamento' : 'Registrar medicamento' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isEdit ? 'Producto, lote, precio y foto en una sola edición.' : 'Ficha, existencia y compra en un solo registro.' }}
            </p>
        </div>
    </header>

    <form wire:submit="save" autocomplete="off" x-data x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Columna Principal (7 columnas en desktop) --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Sección 1: Datos Principales --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-amber-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-amber-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path d="M9 3h6v3l3 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V8l3-2V3Z"/><path d="M9 11h6M12 8v6"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Datos principales</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Nombre comercial y tipo farmacológico del producto.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-12">
                        {{-- Nombre --}}
                        <div class="flex flex-col justify-start sm:col-span-12">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-nombre">
                                <span>Nombre del producto</span>
                                <span class="ml-1 font-bold text-amber-600 dark:text-amber-400">*</span>
                            </label>
                            <input id="med-nombre" type="text" wire:model="nombre" maxlength="255" autocomplete="off" placeholder="Ej. Ivermectina 1%"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600">
                            @error('nombre')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        {{-- Tipo --}}
                        <div class="flex flex-col justify-start sm:col-span-7">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Tipo de producto</span>
                                <span class="ml-1 font-bold text-amber-600 dark:text-amber-400">*</span>
                            </label>
                            <x-filter-select model="tipo" :options="$tipos" tone="amber" live searchable search-placeholder="Buscar tipo de medicamento..." />
                            @error('tipo')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        {{-- Unidad de stock --}}
                        <div class="flex flex-col justify-start sm:col-span-5">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Unidad de stock</span>
                                <span class="ml-1 font-bold text-amber-600 dark:text-amber-400">*</span>
                            </label>
                            <x-filter-select model="unidadStock" :options="$unidades" tone="amber" live />
                            @error('unidadStock')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                @if($agregarExistencia)
                {{-- Sección 2: Compra y lote --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">{{ $isEdit ? 'Compra y lote' : 'Existencia inicial' }}</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">{{ $isEdit ? 'Estos datos también actualizan el egreso vinculado en Finanzas.' : 'Primer lote del producto con su vencimiento.' }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        @if($isEdit)
                            <div class="flex flex-col justify-start sm:col-span-2">
                                <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    <span>Lote que deseas editar</span>
                                </label>
                                <x-filter-select model="loteId" :options="$lotesEditables" tone="amber" live searchable search-placeholder="Buscar lote..." />
                                @error('loteId')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                            </div>
                        @endif

                        {{-- Motivo de Entrada --}}
                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Motivo de entrada</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            @if($isEdit)
                                <span class="inline-flex h-11 items-center rounded-xl border border-emerald-500/25 bg-emerald-50/80 px-4 text-xs font-bold text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950/40 dark:text-emerald-300 shadow-sm">
                                    {{ $tipoIngreso === 'compra' ? '🛒 Compra vinculada a Finanzas' : '📦 Entrada sin egreso financiero' }}
                                </span>
                            @else
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach(['compra' => ['🛒', 'Compra'], 'donacion' => ['🎁', 'Donación'], 'saldo_inicial' => ['📦', 'Saldo Inicial']] as $val => [$ico, $lab])
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="tipoIngreso" value="{{ $val }}" class="peer sr-only">
                                        <span class="flex h-11 items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-600 shadow-sm transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-900 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-950/30 dark:peer-checked:text-emerald-200">
                                            <span>{{ $ico }}</span> {{ $lab }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('tipoIngreso')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        {{-- Código de Lote --}}
                        <div class="flex flex-col justify-start">
                            <x-medicamento-lot-code-input
                                model="numeroLote"
                                error-field="numeroLote"
                                :year="$codigoLoteAnio"
                                :number="$numeroLote"
                                tone="amber"
                                id="med-lote-codigo"
                            />
                        </div>

                        {{-- Fecha de Ingreso --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Fecha de ingreso</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-date-picker model="fechaIngreso" placeholder="dd/mm/aaaa" />
                            @error('fechaIngreso')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        {{-- Contenido neto --}}
                        <div class="flex flex-col justify-start" x-data="{ units: {{ Js::from($unidades) }}, unitKey: @entangle('unidadStock').live }">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-cantidad">
                                <span>Contenido neto</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <div class="relative">
                                <input id="med-cantidad" type="number" wire:model="cantidadInicial" step="0.001" min="0.001" inputmode="decimal"
                                       :placeholder="unitKey === 'ml' ? 'Ej. 500' : (unitKey === 'tableta' ? 'Ej. 12' : (unitKey === 'dosis' ? 'Ej. 50' : (unitKey === 'kg' || unitKey === 'g' ? 'Ej. 1000' : 'Ej. 10')))"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-3.5 pr-20 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs font-bold text-zinc-500 dark:text-zinc-400"
                                      x-text="units[unitKey] || unitKey || '{{ $unidades[$unidadStock] ?? $unidadStock }}'">
                                    {{ $unidades[$unidadStock] ?? $unidadStock }}
                                </span>
                            </div>
                            <span class="mt-1 block text-[11px] text-zinc-500 dark:text-zinc-400">
                                Cantidad total de <span class="font-semibold text-zinc-700 dark:text-zinc-300" x-text="units[unitKey] || unitKey || '{{ $unidades[$unidadStock] ?? $unidadStock }}'">{{ $unidades[$unidadStock] ?? $unidadStock }}</span> en este lote.
                            </span>
                            @error('cantidadInicial')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        {{-- Fecha de Vencimiento --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Fecha de vencimiento</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-date-picker model="fechaVencimiento" placeholder="dd/mm/aaaa" />
                            @error('fechaVencimiento')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        @if($tipoIngreso === 'compra')
                            <div class="flex flex-col justify-start">
                                <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-costo">
                                    <span>Costo total</span>
                                    <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-xs font-bold text-emerald-700 dark:text-emerald-400">S/</span>
                                    <input id="med-costo" type="number" wire:model="costoTotal" step="0.01" min="0" inputmode="decimal" placeholder="0.00"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white py-2 pl-10 pr-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                </div>
                                <span class="mt-1 block text-[11px] text-zinc-500">Genera egreso financiero automático.</span>
                                @error('costoTotal')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-col justify-start">
                                <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-proveedor">
                                    <span>Proveedor</span>
                                </label>
                                <input id="med-proveedor" type="text" wire:model="proveedor" maxlength="255" autocomplete="off" placeholder="Ej. Veterinaria El Sol"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                @error('proveedor')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                            </div>
                        @endif

                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-comprobante">
                                <span>Comprobante / ref.</span>
                            </label>
                            <input id="med-comprobante" type="text" wire:model="comprobante" maxlength="100" placeholder="Ej. F001-123"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            @error('comprobante')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-ubicacion">
                                <span>Ubicación en botiquín</span>
                            </label>
                            <input id="med-ubicacion" type="text" wire:model="ubicacion" maxlength="255" placeholder="Ej. Estante A - Fila 2"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            @error('ubicacion')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
                @endif

                {{-- Sección 3: Más detalles --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-sky-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-sky-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-400/15 dark:text-sky-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="M12 8v5m0 3h.01"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Detalles de uso y conservación</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Condiciones de guardado, vía habitual y alerta de reposición.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-3">
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Conservación</span>
                            </label>
                            <x-filter-select model="condicionAlmacenamiento" :options="$condiciones" tone="amber" />
                            @error('condicionAlmacenamiento')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Vía habitual</span>
                            </label>
                            <x-filter-select model="viaPredeterminada" :options="['' => 'Se elige al aplicar'] + $vias" tone="amber" />
                            @error('viaPredeterminada')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col justify-start" x-data="{ units: {{ Js::from($unidades) }}, unitKey: @entangle('unidadStock').live }">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-minimo">
                                <span>Alerta stock bajo</span>
                            </label>
                            <div class="grid grid-cols-[minmax(0,1fr)_5.5rem] gap-2">
                                <input id="med-minimo" type="number" wire:model="stockMinimo" step="0.001" min="0" inputmode="decimal" placeholder="0"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                <span class="flex h-11 items-center justify-center truncate rounded-xl bg-zinc-100 px-2 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
                                      x-text="units[unitKey] || unitKey || '{{ $unidades[$unidadStock] ?? $unidadStock }}'">
                                    {{ $unidades[$unidadStock] ?? $unidadStock }}
                                </span>
                            </div>
                            @error('stockMinimo')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="med-observaciones">
                            <span>Observaciones</span>
                        </label>
                        <textarea id="med-observaciones" wire:model="observaciones" rows="3" maxlength="2000" placeholder="Indicaciones terapéuticas o notas especiales..."
                                  class="w-full resize-y rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"></textarea>
                        @error('observaciones')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </div>

                    @if($isEdit)
                        <label class="mt-4 flex w-fit cursor-pointer items-center gap-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            <input type="checkbox" wire:model="activo" class="agro-checkbox h-4 w-4 rounded border-zinc-300 text-amber-600 focus:ring-amber-500/20">
                            <span>Producto activo en el botiquín</span>
                        </label>
                    @endif
                </section>
            </div>

            {{-- Columna Lateral: Foto del Envase (5 columnas en desktop) --}}
            @php
                $photoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
            @endphp
            <aside x-data="optimizedImageUpload" class="space-y-6 lg:col-span-5">
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-cyan-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-cyan-500 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 dark:bg-cyan-400/15 dark:text-cyan-400 shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 5.4 12.6a1.5 1.5 0 0 1 2.12 0l3.46 3.46m.75-1.49 1.11-1.11a1.5 1.5 0 0 1 2.12 0l4.05 4.05m-15 2.24h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Foto del envase</h2>
                                <p class="mt-0.5 text-xs text-zinc-500">JPG o PNG optimizada automáticamente.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-cyan-500/25 bg-cyan-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/15 dark:text-cyan-300 shadow-sm">
                            {{ $fotoActual && !$eliminarFoto && !$foto ? 'Guardada' : 'Opcional' }}
                        </span>
                    </div>

                    {{-- Contenedor de Imagen Proporcionado --}}
                    <label for="foto" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950/60">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa del envase" class="absolute inset-0 h-full w-full object-contain">
                        </template>

                        <span x-show="!previewUrl" class="absolute inset-0">
                            @if ($foto)
                                <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa del envase" class="h-full w-full object-cover" style="object-position: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%; transform: scale({{ $photoFrame['zoom'] }}); transform-origin: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                                    Cambiar imagen
                                </span>
                            @elseif ($fotoActual)
                                <img src="{{ asset('storage/' . $fotoActual) }}" alt="Foto actual del envase" class="h-full w-full object-cover" style="object-position: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%; transform: scale({{ $photoFrame['zoom'] }}); transform-origin: {{ $photoFrame['x'] }}% {{ $photoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                                    Cambiar imagen
                                </span>
                            @else
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-zinc-200 text-cyan-600 shadow-sm transition group-hover:border-cyan-500 group-hover:bg-cyan-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-cyan-400 dark:group-hover:bg-cyan-950/30">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                    </span>
                                    <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Seleccionar imagen del envase</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Opcional · Se optimiza automáticamente</span>
                                </span>
                            @endif
                        </span>

                        @if($foto && !$errors->has('foto'))
                            <x-image-frame-editor id="medicine-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @elseif($fotoActual)
                            <x-image-frame-editor id="medicine-photo-frame" :src="asset('storage/'.$fotoActual)" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @endif

                        <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-cyan-400" role="status">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                        </span>
                    </label>

                    <x-image-source-actions input-id="foto" class="mt-3" />

                    <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs font-semibold text-rose-500" role="alert"></span>
                    @error('foto') <span class="mt-1.5 block text-xs font-semibold text-rose-500">{{ $message }}</span> @enderror

                    @if($foto)
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400">Nueva foto lista</span>
                            <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="text-xs font-bold text-zinc-500 hover:text-rose-600 dark:hover:text-rose-400">Descartar</button>
                        </div>
                    @elseif($fotoActual && !$foto)
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-medium text-zinc-500">Foto guardada</span>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:underline dark:text-rose-400 cursor-pointer">
                                <input type="checkbox" wire:model="eliminarFoto" class="rounded border-zinc-300 text-rose-600 focus:ring-rose-500/20"> Eliminar foto
                            </label>
                        </div>
                    @endif
                </section>
            </aside>
        </div>

        {{-- Barra Inferior de Acciones --}}
        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-center text-xs text-zinc-500 sm:text-left">
                Campos marcados con <span class="font-bold text-amber-600 dark:text-amber-400">*</span> son obligatorios.
            </p>
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center">
                <a href="{{ $isEdit ? route('medicamentos.show', $medicamentoId) : route('medicamentos.index') }}" wire:navigate
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
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar medicamento' : 'Guardar medicamento' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
