<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Header --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ $animalVentaId && !$isEdit ? route('animal.index') : route('finanzas.index', ['tab' => 'movimientos']) }}"
           aria-label="{{ $animalVentaId && !$isEdit ? 'Volver al inventario animal' : 'Volver a movimientos' }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-emerald-500/40 hover:bg-emerald-50/50 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-950/25 dark:hover:text-emerald-300">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Libro de caja</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $tipo === 'ingreso' ? 'border-emerald-500/30 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300' : 'border-rose-500/30 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $tipo === 'ingreso' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ $tipo === 'ingreso' ? 'Ingreso' : 'Egreso' }}
                </span>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-emerald-500/30 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-emerald-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Editar movimiento' : 'Registrar movimiento' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">Datos esenciales, categoría correcta y comprobante optimizado.</p>
        </div>
    </header>

    @if($animalVentaId && !$isEdit)
        <div class="rounded-2xl border border-emerald-300/50 bg-emerald-50 p-4 dark:border-emerald-500/25 dark:bg-emerald-500/10">
            <p class="text-xs font-bold text-emerald-950 dark:text-emerald-100">Venta iniciada desde Inventario Animal</p>
            <p class="mt-0.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-300">Ingreso y categoría preparados. El animal seguirá activo hasta que registres correctamente este movimiento.</p>
        </div>
    @endif

    <form wire:submit="save" autocomplete="off" x-data="{ catId: @entangle('categoriaId') }" x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Columna Principal: Datos del movimiento (7 columnas en desktop) --}}
            <div class="space-y-6 lg:col-span-7">
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Datos del movimiento</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Registra qué ocurrió, cuánto y cuándo.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Tipo de movimiento</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select
                                model="tipo"
                                :options="['egreso' => 'Egreso · gasto o salida', 'ingreso' => 'Ingreso · entrada o venta']"
                                tone="emerald"
                                live
                            />
                            @error('tipo') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>

                        <div wire:key="category-field-{{ $tipo }}" class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Categoría</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select
                                model="categoriaId"
                                :options="['' => 'Selecciona una categoría'] + collect($categorias)->pluck('nombre', 'id')->all()"
                                tone="emerald"
                                searchable
                                search-placeholder="Buscar categoría..."
                                live
                            />
                            @error('categoriaId') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>

                        @if($tipo === 'ingreso' && $selectedCategoriaNombre)
                            @if(str_contains($selectedCategoriaNombre, 'préstamo') || str_contains($selectedCategoriaNombre, 'prestamo') || str_contains($selectedCategoriaNombre, 'subsidio'))
                                <div class="flex flex-col justify-start sm:col-span-2">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>{{ (str_contains($selectedCategoriaNombre, 'préstamo') || str_contains($selectedCategoriaNombre, 'prestamo')) ? '¿Quién nos prestó?' : '¿De dónde proviene el subsidio?' }}</span>
                                        <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                                    </label>
                                    <input type="text" wire:model="dineroProviene"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                           placeholder="Ej: Banco Agrario, Ministerio, etc.">
                                    @error('dineroProviene') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                            @elseif(stripos($selectedCategoriaNombre, 'venta de animal') !== false)
                                <div class="flex flex-col justify-start sm:col-span-2">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>Animales</span>
                                        <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                                    </label>
                                    <x-animal-multi-select
                                        model="animalesIds"
                                        :options="$animalesDisponibles"
                                    />
                                    <p class="mt-1 text-[11px] text-zinc-500">Al guardar: baja por venta, salida del stock y enlace permanente con este ingreso.</p>
                                    @error('animalesIds') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex flex-col justify-start sm:col-span-2">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>A quién se vendió</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <input type="text" wire:model="comprador"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                           placeholder="Ej: Juan Pérez (Opcional)">
                                    @error('comprador') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                            @elseif(str_contains($selectedCategoriaNombre, 'venta de leche'))
                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>Cantidad (Litros)</span>
                                        <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" step="0.1" min="0.1" wire:model="cantidadLitros"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-4 pr-12 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                               placeholder="Ej: 50.5">
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-xs font-bold text-zinc-500">Ltrs</span>
                                    </div>
                                    @error('cantidadLitros') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>A quién se vendió</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <input type="text" wire:model="comprador"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                           placeholder="Opcional">
                                    @error('comprador') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                            @elseif(str_contains($selectedCategoriaNombre, 'venta de queso'))
                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>Cantidad de Quesos</span>
                                        <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" step="1" min="1" wire:model="cantidadQuesos"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-4 pr-12 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                               placeholder="Ej: 15">
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-xs font-bold text-zinc-500">Unds</span>
                                    </div>
                                    @error('cantidadQuesos') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex flex-col justify-start">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>A quién se vendió</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <input type="text" wire:model="comprador"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                           placeholder="Opcional">
                                    @error('comprador') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        @endif

                        @php
                            $medCatIds = array_values(array_map('strval', $this->medicamentoCategoriaIds));
                            $insCatIds = array_values(array_map('strval', $this->insumoCategoriaIds));
                        @endphp

                        {{-- Panel condicional: Destino del medicamento --}}
                        @if(! empty($medCatIds) || $medicamentoLoteId)
                            <div x-show="{{ json_encode($medCatIds) }}.includes(String(catId)) || {{ $medicamentoLoteId ? 'true' : 'false' }}" x-cloak class="sm:col-span-2 space-y-4 rounded-xl border border-cyan-500/30 bg-cyan-50/60 p-4.5 dark:border-cyan-500/30 dark:bg-cyan-950/20">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-600 text-white shadow-sm shadow-cyan-600/30 dark:bg-cyan-400 dark:text-zinc-950 font-black">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-900 dark:text-cyan-200">Destino del medicamento</span>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-cyan-500 hover:bg-cyan-50 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-cyan-500 dark:hover:bg-cyan-900/30">
                                        <input type="radio" wire:model.live="destinoMedicamento" value="personas" class="h-4 w-4 border-zinc-300 text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-200">👨‍⚕️ Para personas (Botiquín)</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-cyan-500 hover:bg-cyan-50 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-cyan-500 dark:hover:bg-cyan-900/30">
                                        <input type="radio" wire:model.live="destinoMedicamento" value="animales" class="h-4 w-4 border-zinc-300 text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-200">🐄 Para animales (Inventario)</span>
                                    </label>
                                </div>
                                @if($destinoMedicamento === 'animales')
                                    <div class="rounded-xl border border-cyan-500/20 bg-white p-4 shadow-sm dark:bg-zinc-950/70">
                                        <p class="text-xs font-bold uppercase tracking-wider text-cyan-800 dark:text-cyan-300">Inventario veterinario sincronizado</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">Un solo guardado actualiza compra, lote, stock, egreso y foto.</p>

                                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                            <div class="sm:col-span-2">
                                                @if($medicamentoLoteId)
                                                    <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Medicamento vinculado <span class="text-rose-500">*</span></label>
                                                    <div class="flex h-11 items-center rounded-xl border border-zinc-200 bg-zinc-100 px-3.5 text-sm font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                                                        {{ $medicamentosDisponibles[$medicamentoId] ?? 'Medicamento vinculado' }}
                                                    </div>
                                                @else
                                                    <div class="space-y-3">
                                                        <div>
                                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Nombre del medicamento <span class="text-rose-500">*</span></label>
                                                            <input type="text" wire:model="nombreMedicamento" placeholder="Ej. Oxitetraciclina 200, Ivermectina 1%, Complejo B..." class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold outline-none transition focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                            @error('nombreMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                                        </div>

                                                        <div class="grid gap-3 sm:grid-cols-2">
                                                            <div>
                                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Tipo / Clasificación <span class="text-rose-500">*</span></label>
                                                                <x-filter-select model="tipoMedicamento" :options="\App\Models\Medicamento::TYPES" tone="cyan" />
                                                                @error('tipoMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                                            </div>
                                                            <div>
                                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Unidad de stock <span class="text-rose-500">*</span></label>
                                                                <x-filter-select model="unidadMedicamento" :options="\App\Models\Medicamento::UNITS" tone="cyan" live />
                                                                @error('unidadMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                                            </div>
                                                        </div>

                                                        <div class="grid gap-3 sm:grid-cols-2">
                                                            <div>
                                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Principio activo <span class="text-zinc-400 font-normal">(opcional)</span></label>
                                                                <input type="text" wire:model="principioActivoMedicamento" maxlength="150" placeholder="Ej. Oxitetraciclina Clorhidrato" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none transition focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                            </div>
                                                            <div>
                                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Concentración <span class="text-zinc-400 font-normal">(opcional)</span></label>
                                                                <input type="text" wire:model="concentracionMedicamento" maxlength="100" placeholder="Ej. 200 mg/ml, 1%" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none transition focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Cantidad comprada <span class="text-rose-500">*</span></label>
                                                <div class="grid grid-cols-[minmax(0,1fr)_5.5rem] gap-2">
                                                    <input type="number" min="0.001" step="0.001" wire:model="cantidadMedicamento" placeholder="Ej. 10" class="h-11 rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold outline-none focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                    <span class="flex h-11 items-center justify-center rounded-xl bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $unidadMedicamento }}</span>
                                                </div>
                                                @error('cantidadMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                            </div>

                                            <x-medicamento-lot-code-input
                                                model="numeroLoteMedicamento"
                                                error-field="numeroLoteMedicamento"
                                                :year="$codigoLoteMedicamentoAnio"
                                                :number="$numeroLoteMedicamento"
                                                tone="cyan"
                                                id="finance-medication-lot-code"
                                            />

                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Vencimiento <span class="text-rose-500">*</span></label>
                                                <x-date-picker model="fechaVencimientoMedicamento" placeholder="dd/mm/aaaa" />
                                                @error('fechaVencimientoMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                            </div>

                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Proveedor</label>
                                                <input type="text" wire:model="proveedorMedicamento" maxlength="255" placeholder="Veterinaria o proveedor" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                @error('proveedorMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                            </div>

                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Comprobante / referencia</label>
                                                <input type="text" wire:model="comprobanteMedicamento" maxlength="100" placeholder="Ej. F001-123" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                @error('comprobanteMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                            </div>

                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Ubicación en botiquín</label>
                                                <input type="text" wire:model="ubicacionMedicamento" maxlength="255" placeholder="Ej. Estante A" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                @error('ubicacionMedicamento')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                            </div>

                                            {{-- Tarjeta MÁS DETALLES (Conservación, vía habitual y alerta de stock) --}}
                                            <div class="sm:col-span-2 rounded-xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-800 dark:bg-zinc-900/60 space-y-4">
                                                <div class="flex items-center gap-2.5 border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                    </span>
                                                    <div>
                                                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Más Detalles</p>
                                                        <p class="text-[11px] text-zinc-500">Conservación, vía habitual y alerta de stock.</p>
                                                    </div>
                                                </div>

                                                <div class="grid gap-4 sm:grid-cols-3">
                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Conservación</label>
                                                        <x-filter-select
                                                            model="condicionAlmacenamientoMedicamento"
                                                            :options="[
                                                                 'ambiente' => 'Ambiente seco',
                                                                 'refrigerado_2_8' => 'Refrigerado (2–8 °C)',
                                                                 'congelado' => 'Congelado',
                                                                 'protegido_luz' => 'Protegido de luz',
                                                                 'otro' => 'Otra condición',
                                                             ]"
                                                            tone="cyan"
                                                        />
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Vía habitual</label>
                                                        <x-filter-select
                                                            model="viaPredeterminadaMedicamento"
                                                            :options="[
                                                                 '' => 'Se elige al aplicar',
                                                                 'oral' => 'Oral',
                                                                 'subcutanea' => 'Subcutánea',
                                                                 'intramuscular' => 'Intramuscular',
                                                                 'intravenosa' => 'Intravenosa',
                                                                 'topica' => 'Tópica / baño',
                                                                 'intramamaria' => 'Intramamaria',
                                                                 'ocular' => 'Ocular',
                                                                 'otra' => 'Otra vía',
                                                             ]"
                                                            tone="cyan"
                                                        />
                                                    </div>

                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Alerta stock bajo</label>
                                                        <div class="grid grid-cols-[minmax(0,1fr)_4.5rem] gap-2">
                                                            <input type="number" min="0" step="0.001" wire:model="stockMinimoMedicamento" class="h-11 rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                            <span class="flex h-11 items-center justify-center rounded-xl bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $unidadMedicamento }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="sm:col-span-3">
                                                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Observaciones</label>
                                                        <textarea wire:model="observacionesMedicamento" rows="2" placeholder="Indicaciones o notas especiales..." class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-sm outline-none focus:border-cyan-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 resize-y"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Panel condicional: Insumos --}}
                        @if(! empty($insCatIds) || $insumoLoteId)
                            <div x-show="{{ json_encode($insCatIds) }}.includes(String(catId)) || {{ $insumoLoteId ? 'true' : 'false' }}" x-cloak class="sm:col-span-2 space-y-4 rounded-xl border border-emerald-500/30 bg-emerald-50/60 p-4.5 dark:border-emerald-500/30 dark:bg-emerald-950/20">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm shadow-emerald-600/30 dark:bg-emerald-400 dark:text-zinc-950 font-black">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 dark:text-emerald-200">Inventario de insumos sincronizado</span>
                                        <p class="text-[11px] text-zinc-500">Un solo guardado actualiza compra, lote INS, stock, egreso y foto.</p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-emerald-500/20 bg-white p-4 shadow-sm dark:bg-zinc-950/70">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            @if($insumoLoteId)
                                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Insumo vinculado <span class="text-rose-500">*</span></label>
                                                <div class="flex h-11 items-center rounded-xl border border-zinc-200 bg-zinc-100 px-3.5 text-sm font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                                                    {{ $insumosDisponibles[$insumoId] ?? 'Insumo vinculado' }}
                                                </div>
                                            @else
                                                <div class="space-y-3">
                                                    <div>
                                                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Nombre del insumo / material <span class="text-rose-500">*</span></label>
                                                        <input type="text" wire:model="nombreInsumo" placeholder="Ej: Guantes de látex M, Alcohol 70°, Gasas esterilizadas, Jeringas 10ml..." class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold outline-none transition focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                        @error('nombreInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                                    </div>

                                                    <div class="grid gap-3 sm:grid-cols-2">
                                                        <div>
                                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Categoría / Tipo <span class="text-rose-500">*</span></label>
                                                            <x-filter-select
                                                                model="tipoInsumo"
                                                                :options="\App\Models\Insumo::TYPES"
                                                                tone="emerald"
                                                                searchable
                                                                search-placeholder="Buscar categoría..."
                                                            />
                                                            @error('tipoInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                                        </div>
                                                        <div>
                                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Unidad de medida <span class="text-rose-500">*</span></label>
                                                            <x-filter-select model="unidadInsumo" :options="\App\Models\Insumo::UNITS" tone="emerald" live />
                                                            @error('unidadInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                                        </div>
                                                    </div>

                                                    <div class="grid gap-3 sm:grid-cols-2">
                                                        <div>
                                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Marca / Fabricante <span class="text-zinc-400 font-normal">(opcional)</span></label>
                                                            <input type="text" wire:model="marcaLaboratorioInsumo" maxlength="150" placeholder="Ej: 3M, Nipro, Cranberry..." class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none transition focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                        </div>
                                                        <div>
                                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Presentación / Empaque <span class="text-zinc-400 font-normal">(opcional)</span></label>
                                                            <input type="text" wire:model="presentacionInsumo" maxlength="150" placeholder="Ej: Caja x 100 unidades, Frasco 1 Litro..." class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none transition focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Cantidad comprada <span class="text-rose-500">*</span></label>
                                            <div class="grid grid-cols-[minmax(0,1fr)_5.5rem] gap-2">
                                                <input type="number" min="0.001" step="0.001" wire:model="cantidadInsumo" placeholder="Ej. 10" class="h-11 rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                <span class="flex h-11 items-center justify-center rounded-xl bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $unidadInsumo }}</span>
                                            </div>
                                            @error('cantidadInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                        </div>

                                        <x-insumo-lot-code-input
                                            model="numeroLoteInsumo"
                                            error-field="numeroLoteInsumo"
                                            :year="$codigoLoteInsumoAnio"
                                            :number="$numeroLoteInsumo"
                                            tone="emerald"
                                            id="finance-insumo-lot-code"
                                        />

                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">
                                                Vencimiento <span class="text-[10px] font-normal text-zinc-500">(Opcional)</span>
                                            </label>
                                            <x-date-picker model="fechaVencimientoInsumo" placeholder="dd/mm/aaaa" />
                                            @error('fechaVencimientoInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                        </div>

                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Proveedor</label>
                                            <input type="text" wire:model="proveedorInsumo" maxlength="255" placeholder="Ej. Farmavet, Distribuidora..." class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                            @error('proveedorInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                        </div>

                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Comprobante / referencia</label>
                                            <input type="text" wire:model="comprobanteInsumo" maxlength="100" placeholder="Ej. B001-456, F001-123" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                            @error('comprobanteInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                        </div>

                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Ubicación en botiquín</label>
                                            <input type="text" wire:model="ubicacionInsumo" maxlength="255" placeholder="Ej. Gaveta 2, Estante B" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                            @error('ubicacionInsumo')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                        </div>

                                        {{-- Tarjeta MÁS DETALLES (Conservación, Alerta de stock y Observaciones) --}}
                                        <div class="sm:col-span-2 rounded-xl border border-zinc-200 bg-zinc-50/80 p-4 dark:border-zinc-800 dark:bg-zinc-900/60 space-y-4">
                                            <div class="flex items-center gap-2.5 border-b border-zinc-200 pb-3 dark:border-zinc-800">
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                </span>
                                                <div>
                                                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Más Detalles del Insumo</p>
                                                    <p class="text-[11px] text-zinc-500">Conservación, alerta de stock y notas adicionales.</p>
                                                </div>
                                            </div>

                                            <div class="grid gap-4 sm:grid-cols-2">
                                                <div>
                                                    <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Conservación</label>
                                                    <x-filter-select
                                                        model="condicionAlmacenamientoInsumo"
                                                        :options="\App\Models\Insumo::STORAGE_CONDITIONS"
                                                        tone="emerald"
                                                    />
                                                </div>

                                                <div>
                                                    <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Alerta stock bajo</label>
                                                    <div class="grid grid-cols-[minmax(0,1fr)_5rem] gap-2">
                                                        <input type="number" min="0" step="1" wire:model="stockMinimoInsumo" placeholder="Ej. 5" class="h-11 rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                        <span class="flex h-11 items-center justify-center rounded-xl bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $unidadInsumo }}</span>
                                                    </div>
                                                </div>

                                                <div class="sm:col-span-2">
                                                    <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Observaciones del insumo</label>
                                                    <textarea wire:model="observacionesInsumo" rows="2" placeholder="Notas sobre uso o especificaciones..." class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 resize-y"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Panel condicional: Asignación Familiar --}}
                        @if($this->asignacionCategoriaId)
                            <div x-show="String(catId) === '{{ $this->asignacionCategoriaId }}'" x-cloak class="sm:col-span-2 space-y-4 rounded-xl border border-violet-500/30 bg-violet-50/60 p-4.5 dark:border-violet-500/30 dark:bg-violet-950/20">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-600 text-white shadow-sm shadow-violet-600/30 dark:bg-violet-400 dark:text-zinc-950 font-black">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-violet-900 dark:text-violet-200">Datos de asignación familiar</span>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="flex flex-col justify-start">
                                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                            <span>Beneficiario</span>
                                            <span class="ml-1 font-bold text-violet-600 dark:text-violet-400">*</span>
                                        </label>
                                        <input type="text" wire:model="beneficiario"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                               placeholder="Ej: María Delgado (hija)">
                                        @error('beneficiario') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="flex flex-col justify-start">
                                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                            <span>Propósito</span>
                                            <span class="ml-1 font-bold text-violet-600 dark:text-violet-400">*</span>
                                        </label>
                                        <x-filter-select
                                            model="proposito"
                                            :options="[
                                                'estudio' => 'Estudios',
                                                'salud' => 'Salud',
                                                'alimentacion' => 'Alimentación',
                                                'vivienda' => 'Vivienda',
                                                'transporte' => 'Transporte',
                                                'ropa' => 'Ropa',
                                                'gastos_personales' => 'Gastos personales',
                                                'emergencia' => 'Emergencia',
                                                'otros' => 'Otros'
                                            ]"
                                            tone="violet"
                                        />
                                        @error('proposito') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Monto</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-xs font-bold text-emerald-700 dark:text-emerald-400">S/</span>
                                <input type="number" min="0.01" step="0.01" inputmode="decimal" wire:model="monto"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-10 pr-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                       placeholder="150.00">
                            </div>
                            @error('monto') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Fecha</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-date-picker model="fecha" placeholder="dd/mm/aaaa" />
                            @error('fecha') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Descripción breve</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <textarea wire:model="descripcion" rows="2"
                                      class="w-full resize-y rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                      placeholder="Ej: Compra de alimento para el lote de engorde A"></textarea>
                            @error('descripcion') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- Columna Lateral: Foto / Comprobante (5 columnas en desktop) --}}
            @php
                $receiptFrame = \App\Support\ImageFrame::normalize($comprobanteEncuadre);
                $sharedItemPhoto = $this->isMedicationInventoryPurchase
                    ? $medicamentoFotoActual
                    : ($this->isInsumoInventoryPurchase ? $insumoFotoActual : null);
                $storedReceiptIsImage = $sharedItemPhoto
                    || ($comprobanteRuta && preg_match('/\.(jpe?g|png|webp)$/i', $comprobanteRuta) === 1);
                $storedReceiptUrl = $sharedItemPhoto
                    ? asset('storage/'.$sharedItemPhoto).'?v='.sha1($sharedItemPhoto)
                    : (($storedReceiptIsImage && $movId)
                        ? route('movimiento.comprobante', $movId).'?v='.sha1($comprobanteRuta)
                        : null);
                $storedReceiptAlt = $this->isMedicationInventoryPurchase
                    ? 'Foto compartida del medicamento'
                    : ($this->isInsumoInventoryPurchase ? 'Foto compartida del insumo' : 'Comprobante guardado');
            @endphp
            <aside x-data="optimizedAttachmentUpload('comprobante')" class="space-y-6 lg:col-span-5">
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-teal-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-teal-500 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:bg-teal-400/15 dark:text-teal-400 shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                                    {{ $this->isMedicationInventoryPurchase ? 'Foto del fármaco' : ($this->isInsumoInventoryPurchase ? 'Foto del insumo' : 'Comprobante / Foto') }}
                                </h2>
                                <p class="mt-0.5 text-xs text-zinc-500">
                                    {{ ($this->isMedicationInventoryPurchase || $this->isInsumoInventoryPurchase) ? 'Sincronizada con Botiquín.' : 'JPG, PNG, WebP o PDF.' }}
                                </p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-teal-500/25 bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:border-teal-500/20 dark:bg-teal-500/15 dark:text-teal-300 shadow-sm">
                            Opcional
                        </span>
                    </div>

                    {{-- Contenedor de Imagen Proporcionado --}}
                    <label for="movement-attachment-input" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950/60" x-bind:aria-busy="busy">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa inmediata del comprobante" class="absolute inset-0 h-full w-full object-contain" decoding="async">
                        </template>

                        <span x-show="!previewUrl" class="absolute inset-0">
                            @if($comprobante && str_starts_with((string) $comprobante->getMimeType(), 'image/'))
                                <img src="{{ $comprobante->temporaryUrl() }}" alt="Vista previa del comprobante" class="h-full w-full object-cover" decoding="async" style="object-position: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%; transform: scale({{ $receiptFrame['zoom'] }}); transform-origin: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar archivo</span>
                            @elseif($comprobante)
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center space-y-2">
                                    <svg class="mx-auto h-10 w-10 text-rose-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5m-5.25-5.25L19.5 7.5m-5.25-5.25V7.5h5.25" /></svg>
                                    <p class="break-all text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $comprobante->getClientOriginalName() }}</p>
                                </span>
                            @elseif($storedReceiptIsImage && $storedReceiptUrl)
                                <img src="{{ $storedReceiptUrl }}" alt="{{ $storedReceiptAlt }}" class="h-full w-full object-cover" loading="lazy" decoding="async" style="object-position: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%; transform: scale({{ $receiptFrame['zoom'] }}); transform-origin: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar imagen</span>
                            @elseif($comprobanteRuta)
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center space-y-2 text-zinc-400">
                                    <svg class="mx-auto h-10 w-10 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5m-5.25-5.25L19.5 7.5m-5.25-5.25V7.5h5.25" /></svg>
                                    <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Comprobante guardado</p>
                                </span>
                            @else
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-zinc-200 text-teal-600 shadow-sm transition group-hover:border-teal-500 group-hover:bg-teal-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-teal-400 dark:group-hover:bg-teal-950/30">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.086a2.25 2.25 0 0 0 1.591-.659l.82-.82A2.25 2.25 0 0 1 10.34 3.86h3.318a2.25 2.25 0 0 1 1.591.659l.82.82A2.25 2.25 0 0 0 17.664 6h1.086A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
                                        </svg>
                                    </span>
                                    <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Seleccionar imagen o PDF</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Se optimiza automáticamente</span>
                                </span>
                            @endif
                        </span>

                        @if($comprobante && str_starts_with((string) $comprobante->getMimeType(), 'image/'))
                            <x-image-frame-editor id="receipt-photo-frame" :src="$comprobante->temporaryUrl()" x-model="comprobanteEncuadre.x" y-model="comprobanteEncuadre.y" zoom-model="comprobanteEncuadre.zoom" />
                        @elseif($storedReceiptIsImage && $storedReceiptUrl)
                            <x-image-frame-editor id="receipt-photo-frame" :src="$storedReceiptUrl" x-model="comprobanteEncuadre.x" y-model="comprobanteEncuadre.y" zoom-model="comprobanteEncuadre.zoom" />
                        @endif

                        <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-teal-400" role="status">
                            <span x-text="processing ? 'Optimizando archivo...' : 'Subiendo archivo...'"></span>
                        </span>
                    </label>

                    <div class="mt-4 space-y-2">
                        <x-image-source-actions input-id="movement-attachment-input" handler="selectAttachment" :accept="$this->isMedicationInventoryPurchase ? 'image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp,application/pdf'" :gallery-label="$this->isMedicationInventoryPurchase ? 'Elegir imagen' : 'Galería o PDF'" />
                        @if($comprobante)
                            <button type="button" wire:click="cancelAttachmentChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-zinc-100 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Descartar archivo nuevo</button>
                        @endif
                        <p class="text-[11px] leading-relaxed text-zinc-500">{{ $this->isMedicationInventoryPurchase ? 'Redimensión a 1600 px y WebP. Máx. 2 MB.' : 'Imágenes WebP o PDF hasta 25 MB.' }}</p>
                        <span x-cloak x-show="clientError" x-text="clientError" class="block rounded-lg bg-rose-500/10 px-3 py-2 text-xs font-semibold text-rose-500" role="alert"></span>
                        @error('comprobante') <p class="rounded-lg bg-rose-500/10 px-3 py-2 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </section>
            </aside>
        </div>

        {{-- Barra Inferior de Acciones --}}
        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-center text-xs text-zinc-500 sm:text-left">
                Campos marcados con <span class="font-bold text-emerald-600 dark:text-emerald-400">*</span> son obligatorios.
            </p>
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center">
                <a wire:navigate href="{{ $animalVentaId && !$isEdit ? route('animal.index') : route('finanzas.index', ['tab' => 'movimientos']) }}"
                   class="agro-button-secondary">
                    Cancelar
                </a>
                <button type="submit"
                        x-bind:disabled="$store?.imageUploads?.busy"
                        wire:loading.attr="disabled"
                        wire:target="save,comprobante"
                        class="agro-button">
                    <svg wire:loading.remove wire:target="save" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar movimiento' : 'Guardar movimiento' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
