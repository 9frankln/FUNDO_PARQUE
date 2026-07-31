<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-start gap-4">
        <a href="{{ route('queso.index') }}"
           class="shrink-0 p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-100 transition duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="agro-title text-2xl font-extrabold tracking-tight sm:text-3xl">
                {{ $isEdit ? 'Editar Registro' : 'Registrar Elaboración de Queso' }}
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Completa los datos del lote y agrega una foto si deseas.</p>
        </div>
    </div>

    <form wire:submit="save" x-data x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Production data -->
        <div class="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-5">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Datos de Producción</h2>
                <p class="text-xs text-zinc-500 mt-1">Información correspondiente al lote elaborado.</p>
            </div>

            <div class="border-t border-zinc-800/80"></div>

            <div>
                <label for="fecha" class="block text-xs font-semibold text-zinc-400 mb-1.5">
                    Fecha de Producción <span class="text-red-500">*</span>
                </label>
                <input id="fecha" type="date" wire:model="fecha"
                       class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 focus:border-emerald-500/50 focus:ring-emerald-500/20 text-zinc-100 text-sm transition outline-none">
                @error('fecha') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <section class="pt-1 space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-200">Presentaciones elaboradas</h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Selecciona peso y cantidad para cada tipo de queso.</p>
                    </div>
                    @if(count($presentaciones) < count($pesoOptions))
                        <button type="button" wire:click="addPresentacion"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-3.5 py-2 text-xs font-bold text-emerald-400 transition hover:border-emerald-500/50 hover:bg-emerald-500/15">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" d="M12 5v14M5 12h14"></path>
                            </svg>
                            Agregar presentación
                        </button>
                    @endif
                </div>

                @if($legacyWithoutPresentations && $presentaciones === [])
                    <div class="rounded-xl border border-amber-500/25 bg-amber-500/10 p-4">
                        <p class="text-sm font-bold text-amber-400">Registro anterior sin desglose</p>
                        <p class="text-xs text-zinc-500 mt-1">Conserva {{ $legacyUnidades }} unidades y {{ $legacyPesoTotalKg }} kg. Agrega presentaciones para actualizar su distribución.</p>
                    </div>
                @endif

                <div class="space-y-3">
                    @foreach($presentaciones as $index => $presentacion)
                        @php
                            $subtotalKg = ((int) ($presentacion['peso_gramos'] ?? 0) * (int) ($presentacion['cantidad'] ?? 0)) / 1000;
                        @endphp
                        <div wire:key="queso-presentacion-{{ $index }}" class="rounded-xl border border-zinc-800 bg-zinc-950/60 p-3">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_8rem_7rem_2.5rem] sm:items-end">
                                <div>
                                    <label for="peso-{{ $index }}" class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1.5">Peso unitario</label>
                                    <select id="peso-{{ $index }}" wire:model.live="presentaciones.{{ $index }}.peso_gramos"
                                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3.5 py-2.5 text-sm font-semibold text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-emerald-500/20">
                                        @foreach($pesoOptions as $peso => $label)
                                            <option value="{{ $peso }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error("presentaciones.$index.peso_gramos") <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="cantidad-{{ $index }}" class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1.5">Cantidad</label>
                                    <input id="cantidad-{{ $index }}" type="number" min="1" max="100000" step="1"
                                           wire:model.live.debounce.250ms="presentaciones.{{ $index }}.cantidad"
                                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3.5 py-2.5 text-sm font-semibold text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-emerald-500/20">
                                    @error("presentaciones.$index.cantidad") <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1.5">Subtotal</span>
                                    <div class="flex h-[42px] items-center rounded-xl border border-zinc-800/70 bg-zinc-900 px-3 text-sm font-bold text-teal-400">
                                        {{ number_format($subtotalKg, 2) }} kg
                                    </div>
                                </div>

                                <button type="button" wire:click="removePresentacion({{ $index }})"
                                        class="flex h-[42px] w-full items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-400 transition hover:border-rose-500/40 hover:bg-rose-500/15 sm:w-10"
                                        title="Quitar presentación" aria-label="Quitar presentación">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0 .7 12h6.6L16 7m-6-3h4l1 3H9l1-3Z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('presentaciones') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Total quesos</span>
                        <span class="block mt-1 text-xl font-black text-zinc-100">{{ $this->totalUnidades }}</span>
                        <span class="text-xs text-zinc-500">unidades</span>
                    </div>
                    <div class="rounded-xl border border-teal-500/20 bg-teal-500/10 p-4">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Peso calculado</span>
                        <span class="block mt-1 text-xl font-black text-teal-400">{{ $this->pesoTotalKg }}</span>
                        <span class="text-xs text-zinc-500">kilogramos</span>
                    </div>
                </div>
            </section>

            <div>
                <label for="observaciones" class="block text-xs font-semibold text-zinc-400 mb-1.5">Observaciones</label>
                <textarea id="observaciones" wire:model="observaciones" rows="5"
                          class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 focus:border-emerald-500/50 focus:ring-emerald-500/20 text-zinc-100 placeholder-zinc-600 text-sm transition outline-none resize-y"
                          placeholder="Tipo de queso, detalles del lote u otras anotaciones..."></textarea>
                @error('observaciones') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Optional photo and actions -->
        @php
            $cheesePhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
        @endphp
        <aside x-data="optimizedImageUpload" class="space-y-4">
            <div class="p-5 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-4">
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Foto del Lote</h2>
                        <span class="px-2 py-1 rounded-md bg-zinc-800 text-[10px] font-semibold uppercase tracking-wide text-zinc-400">Opcional</span>
                    </div>
                    <p class="text-xs text-zinc-500 mt-1">JPG o PNG, máximo 2 MB.</p>
                </div>

                <label for="foto" class="relative block w-full aspect-square rounded-2xl border-2 border-dashed border-zinc-700 hover:border-emerald-500/60 bg-zinc-950 overflow-hidden cursor-pointer group transition-colors">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Vista previa del lote" class="absolute inset-0 h-full w-full object-contain">
                    </template>

                    <span x-show="!previewUrl" class="absolute inset-0">
                        @if ($foto)
                            <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa del lote" class="h-full w-full object-cover" style="object-position: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%; transform: scale({{ $cheesePhotoFrame['zoom'] }}); transform-origin: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%;">
                            <span class="absolute inset-x-0 bottom-0 px-3 py-2.5 bg-zinc-950/85 text-center text-xs font-semibold text-zinc-200 opacity-0 group-hover:opacity-100 transition-opacity">
                                Cambiar imagen
                            </span>
                        @elseif ($fotoRuta)
                            <img src="{{ asset('storage/' . $fotoRuta) }}" alt="Foto actual del lote" class="h-full w-full object-cover" style="object-position: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%; transform: scale({{ $cheesePhotoFrame['zoom'] }}); transform-origin: {{ $cheesePhotoFrame['x'] }}% {{ $cheesePhotoFrame['y'] }}%;">
                            <span class="absolute inset-x-0 bottom-0 px-3 py-2.5 bg-zinc-950/85 text-center text-xs font-semibold text-zinc-200 opacity-0 group-hover:opacity-100 transition-opacity">
                                Cambiar imagen
                            </span>
                        @else
                            <span class="absolute inset-0 flex flex-col items-center justify-center p-5 text-center">
                                <span class="w-12 h-12 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-500 group-hover:text-emerald-400 group-hover:border-emerald-500/30 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.086a2.25 2.25 0 0 0 1.591-.659l.82-.82A2.25 2.25 0 0 1 10.34 3.86h3.318a2.25 2.25 0 0 1 1.591.659l.82.82A2.25 2.25 0 0 0 17.664 6h1.086A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
                                    </svg>
                                </span>
                                <span class="text-sm font-semibold text-zinc-300 mt-3">Seleccionar imagen</span>
                                <span class="text-xs text-zinc-600 mt-1">Se optimiza antes de subir</span>
                            </span>
                        @endif
                    </span>

                    @if($foto && !$errors->has('foto'))
                        <x-image-frame-editor id="cheese-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                    @elseif($fotoRuta)
                        <x-image-frame-editor id="cheese-photo-frame" :src="asset('storage/'.$fotoRuta)" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                    @endif

                    <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 p-3 bg-zinc-950/90" role="status" aria-live="polite">
                        <span class="flex items-center justify-between gap-3 text-[11px] font-semibold text-emerald-400">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                            <span x-show="uploading" x-text="`${progress}%`"></span>
                        </span>
                        <span class="block h-1 mt-2 rounded-full bg-zinc-800 overflow-hidden">
                            <span class="block h-full bg-emerald-400 transition-all duration-200" :style="`width: ${processing ? 18 : progress}%`"></span>
                        </span>
                    </span>

                </label>

                <x-image-source-actions input-id="foto" />
                @if($foto)
                    <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="w-full rounded-xl border border-zinc-700 px-3 py-2 text-xs font-bold text-zinc-300 transition hover:bg-zinc-800 disabled:opacity-50">Descartar imagen nueva</button>
                @endif

                <p class="text-[11px] leading-relaxed text-zinc-500">Redimensión a 1600 px y WebP. Foco y zoom se guardan sin alterar original.</p>
                <span x-cloak x-show="clientError" x-text="clientError" class="text-xs text-red-500 block" role="alert"></span>
                @error('foto') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-1 gap-3">
                <button type="submit"
                        class="order-1 lg:order-none w-full px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 disabled:opacity-60 disabled:cursor-wait text-zinc-950 font-bold text-sm transition shadow-lg shadow-emerald-500/10 flex items-center justify-center gap-2"
                        :disabled="busy || $store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,foto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4Z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v5h8V3M7 21v-8h10v8"></path>
                    </svg>
                    {{ $isEdit ? 'Actualizar Registro' : 'Guardar Registro' }}
                </button>
                <a href="{{ route('queso.index') }}"
                   class="w-full px-4 py-3 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-200 text-sm font-semibold text-center transition">
                    Cancelar
                </a>
            </div>
        </aside>
    </form>
</div>
