<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Header --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ route('queso.index') }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-amber-500/40 hover:bg-amber-50/50 hover:text-amber-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-amber-500/40 dark:hover:bg-amber-950/25 dark:hover:text-amber-300"
           aria-label="Volver">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-600 dark:text-amber-400">Quesería · Producción</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-amber-500/30 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-amber-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Editar Registro' : 'Registrar Elaboración de Queso' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">Completa los datos del lote y agrega una foto opcional.</p>
        </div>
    </header>

    <form wire:submit="save" autocomplete="off" x-data x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Columna Principal: Datos de producción (7 columnas en desktop) --}}
            <div class="space-y-6 lg:col-span-7">
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-amber-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-amber-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Datos de producción</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Información correspondiente al lote elaborado.</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-5">
                        <div class="flex flex-col justify-start">
                            <label for="fecha" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Fecha de producción</span>
                                <span class="ml-1 font-bold text-amber-600 dark:text-amber-400">*</span>
                            </label>
                            <x-date-picker model="fecha" placeholder="dd/mm/aaaa" />
                            @error('fecha') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Sección de presentaciones --}}
                        <div class="space-y-3 pt-1">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Presentaciones elaboradas</h3>
                                    <p class="mt-0.5 text-xs text-zinc-500">Selecciona peso y cantidad para cada tipo de queso.</p>
                                </div>
                                @if(count($presentaciones) < count($pesoOptions))
                                    <button type="button" wire:click="addPresentacion"
                                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-500/30 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-950/30 dark:text-amber-300 dark:hover:bg-amber-950/50">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                        </svg>
                                        <span>Agregar presentación</span>
                                    </button>
                                @endif
                            </div>

                            @if($legacyWithoutPresentations && $presentaciones === [])
                                <div class="rounded-xl border border-amber-500/30 bg-amber-50/70 p-4 dark:border-amber-500/30 dark:bg-amber-950/20">
                                    <p class="text-xs font-bold text-amber-800 dark:text-amber-300">Registro anterior sin desglose</p>
                                    <p class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">Conserva {{ $legacyUnidades }} unidades y {{ $legacyPesoTotalKg }} kg. Agrega presentaciones para actualizar su distribución.</p>
                                </div>
                            @endif

                            <div class="space-y-3">
                                @foreach($presentaciones as $index => $presentacion)
                                    @php
                                        $subtotalKg = ((int) ($presentacion['peso_gramos'] ?? 0) * (int) ($presentacion['cantidad'] ?? 0)) / 1000;
                                    @endphp
                                    <div wire:key="queso-presentacion-{{ $index }}" class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-3.5 dark:border-zinc-800 dark:bg-zinc-950/60">
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_7.5rem_6.5rem_2.75rem] sm:items-end">
                                            <div class="flex flex-col justify-start">
                                                <label for="peso-{{ $index }}" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Peso unitario</label>
                                                <x-filter-select model="presentaciones.{{ $index }}.peso_gramos"
                                                                 :options="$pesoOptions"
                                                                 tone="amber"
                                                                 live />
                                                @error("presentaciones.$index.peso_gramos") <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="flex flex-col justify-start">
                                                <label for="cantidad-{{ $index }}" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Cantidad</label>
                                                <input id="cantidad-{{ $index }}" type="number" min="1" max="100000" step="1"
                                                       wire:model.live.debounce.250ms="presentaciones.{{ $index }}.cantidad"
                                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                                @error("presentaciones.$index.cantidad") <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="flex flex-col justify-start">
                                                <span class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-500">Subtotal</span>
                                                <div class="flex h-11 items-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-bold text-amber-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-amber-400">
                                                    {{ number_format($subtotalKg, 2) }} kg
                                                </div>
                                            </div>

                                            <button type="button" wire:click="removePresentacion({{ $index }})"
                                                    class="flex h-11 w-full items-center justify-center rounded-xl border border-rose-300 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-950/30 dark:text-rose-400 sm:w-11"
                                                    title="Quitar presentación" aria-label="Quitar presentación">
                                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0 .7 12h6.6L16 7m-6-3h4l1 3H9l1-3Z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @error('presentaciones') <span class="block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror

                            {{-- Trazabilidad de Leche y Rendimiento --}}
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex-1">
                                        <label for="litrosLeche" class="mb-1 flex items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                            <span>Leche cruda utilizada</span>
                                            <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional para calcular rendimiento)</span>
                                        </label>
                                        <div class="relative">
                                            <input id="litrosLeche" type="number" step="0.1" min="0" max="99999"
                                                   wire:model.live.debounce.250ms="litrosLeche"
                                                   inputmode="decimal"
                                                   placeholder="Ej. 100"
                                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white pr-12 pl-3.5 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs font-bold text-zinc-400">
                                                Litros
                                            </span>
                                        </div>
                                        @error('litrosLeche') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                                    </div>
                                    @php
                                        $pesoNum = (float) $this->pesoTotalKg;
                                        $litrosNum = (float) $litrosLeche;
                                        $ratioLKg = ($pesoNum > 0 && $litrosNum > 0) ? round($litrosNum / $pesoNum, 2) : null;
                                        $yieldPct = ($litrosNum > 0 && $pesoNum > 0) ? round(($pesoNum / $litrosNum) * 100, 1) : null;
                                    @endphp
                                    @if($ratioLKg !== null)
                                        <div class="rounded-xl border border-amber-500/30 bg-white p-3 shadow-xs sm:w-56 dark:border-amber-500/30 dark:bg-zinc-900">
                                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400">Rendimiento Lácteo</p>
                                            <p class="mt-0.5 text-lg font-black text-zinc-900 dark:text-white">
                                                {{ $ratioLKg }} <small class="text-xs font-bold text-zinc-500">L / kg</small>
                                                <span class="ml-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">({{ $yieldPct }}%)</span>
                                            </p>
                                            <p class="mt-0.5 text-[10px] font-medium text-zinc-500">
                                                {{ $ratioLKg >= 8.5 && $ratioLKg <= 11.5 ? '✓ Rendimiento óptimo' : ($ratioLKg < 8.5 ? 'Alto rendimiento en queso' : 'Uso elevado de leche') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Totales calculados --}}
                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <div class="rounded-xl border border-amber-500/25 bg-amber-50/70 p-4 dark:border-amber-500/25 dark:bg-amber-950/25">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Total quesos</span>
                                    <span class="mt-1 block text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $this->totalUnidades }}</span>
                                    <span class="text-xs text-zinc-500">unidades</span>
                                </div>
                                <div class="rounded-xl border border-teal-500/25 bg-teal-50/70 p-4 dark:border-teal-500/25 dark:bg-teal-950/25">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-teal-800 dark:text-teal-300">Peso calculado</span>
                                    <span class="mt-1 block text-2xl font-extrabold text-teal-700 dark:text-teal-400">{{ $this->pesoTotalKg }}</span>
                                    <span class="text-xs text-zinc-500">kilogramos</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col justify-start">
                            <label for="observaciones" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Observaciones</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <textarea id="observaciones" wire:model="observaciones" rows="4"
                                      class="w-full resize-y rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                      placeholder="Tipo de queso, detalles del lote u otras anotaciones..."></textarea>
                            @error('observaciones') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- Columna Lateral: Foto del Lote (5 columnas en desktop) --}}
            @php
                $cheesePhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
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
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Foto del lote</h2>
                                <p class="mt-0.5 text-xs text-zinc-500">JPG o PNG optimizada automáticamente.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-teal-500/25 bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:border-teal-500/20 dark:bg-teal-500/15 dark:text-teal-300 shadow-sm">
                            Opcional
                        </span>
                    </div>

                    {{-- Contenedor de Imagen Proporcionado --}}
                    <label for="foto" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950/60">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa del lote" class="absolute inset-0 h-full w-full object-contain">
                        </template>

                        <span x-show="!previewUrl" class="absolute inset-0">
                            @if ($foto)
                                <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa del lote" class="h-full w-full object-cover" style="object-position: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%; transform: scale({{ $cheesePhotoFrame['zoom'] }}); transform-origin: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                                    Cambiar imagen
                                </span>
                            @elseif ($fotoRuta)
                                <img src="{{ asset('storage/' . $fotoRuta) }}" alt="Foto actual del lote" class="h-full w-full object-cover" style="object-position: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%; transform: scale({{ $cheesePhotoFrame['zoom'] }}); transform-origin: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                                    Cambiar imagen
                                </span>
                            @else
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-zinc-200 text-teal-600 shadow-sm transition group-hover:border-teal-500 group-hover:bg-teal-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-teal-400 dark:group-hover:bg-teal-950/30">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.086a2.25 2.25 0 0 0 1.591-.659l.82-.82A2.25 2.25 0 0 1 10.34 3.86h3.318a2.25 2.25 0 0 1 1.591.659l.82.82A2.25 2.25 0 0 0 17.664 6h1.086A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                        </svg>
                                    </span>
                                    <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Seleccionar imagen del lote</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Opcional · Se optimiza automáticamente</span>
                                </span>
                            @endif
                        </span>

                        @if($foto && !$errors->has('foto'))
                            <x-image-frame-editor id="cheese-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @elseif($fotoRuta)
                            <x-image-frame-editor id="cheese-photo-frame" :src="asset('storage/'.$fotoRuta)" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @endif

                        <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-teal-400" role="status">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                        </span>
                    </label>

                    <x-image-source-actions input-id="foto" class="mt-3" />

                    @if($foto)
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-teal-600 dark:text-teal-400">Nueva foto lista</span>
                            <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="text-xs font-bold text-zinc-500 hover:text-rose-600 dark:hover:text-rose-400">Descartar</button>
                        </div>
                    @endif

                    <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs font-semibold text-rose-500" role="alert"></span>
                    @error('foto') <span class="mt-1.5 block text-xs font-semibold text-rose-500">{{ $message }}</span> @enderror
                </section>
            </aside>
        </div>

        {{-- Barra Inferior de Acciones --}}
        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-center text-xs text-zinc-500 sm:text-left">
                Campos marcados con <span class="font-bold text-amber-600 dark:text-amber-400">*</span> son obligatorios.
            </p>
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center">
                <a wire:navigate href="{{ route('queso.index') }}"
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
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar Registro' : 'Guardar Registro' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
