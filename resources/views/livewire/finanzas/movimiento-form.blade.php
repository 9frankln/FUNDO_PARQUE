<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ $animalVentaId && !$isEdit ? route('animal.index') : route('finanzas.index', ['tab' => 'movimientos']) }}"
           aria-label="{{ $animalVentaId && !$isEdit ? 'Volver al inventario animal' : 'Volver a movimientos' }}"
           class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 text-zinc-400 transition hover:border-zinc-700 hover:text-zinc-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-500">Libro de caja</p>
            <h1 class="agro-title mt-0.5 text-2xl font-extrabold tracking-tight sm:text-3xl">
                {{ $isEdit ? 'Editar movimiento' : 'Registrar movimiento' }}
            </h1>
            <p class="mt-0.5 text-xs text-zinc-400">Datos esenciales, categoría correcta y comprobante optimizado.</p>
        </div>
    </div>

    @if($animalVentaId && !$isEdit)
        <div class="rounded-2xl border border-emerald-300/50 bg-emerald-50 p-3.5 dark:border-emerald-500/25 dark:bg-emerald-500/10">
            <p class="text-xs font-black text-emerald-950 dark:text-emerald-100">Venta iniciada desde Inventario Animal</p>
            <p class="mt-0.5 text-xs leading-5 text-emerald-700 dark:text-emerald-300">Ingreso y categoría preparados. El animal seguirá activo hasta que registres correctamente este movimiento.</p>
        </div>
    @endif

    <form wire:submit="save" x-data x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-xl shadow-zinc-200/50 dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-2xl dark:shadow-zinc-950/20">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <section class="space-y-5 p-5 sm:p-6 lg:p-7">
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Datos del movimiento</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Registra qué ocurrió, cuánto y cuándo.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Tipo de movimiento <span class="text-rose-500">*</span></label>
                        <x-filter-select
                            model="tipo"
                            :options="['egreso' => 'Egreso · gasto o salida', 'ingreso' => 'Ingreso · entrada o venta']"
                            tone="emerald"
                            live
                        />
                        @error('tipo') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div wire:key="category-field-{{ $tipo }}">
                        <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Categoría <span class="text-rose-500">*</span></label>
                        <x-filter-select
                            model="categoriaId"
                            :options="['' => 'Selecciona una categoría'] + collect($categorias)->pluck('nombre', 'id')->all()"
                            tone="emerald"
                            live
                        />
                        <p class="mt-1 text-[11px] text-zinc-600">Opciones ajustadas al tipo seleccionado.</p>
                        @error('categoriaId') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    @if($tipo === 'ingreso' && $selectedCategoriaNombre)
                        @if(str_contains($selectedCategoriaNombre, 'préstamo') || str_contains($selectedCategoriaNombre, 'subsidio'))
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                                    {{ str_contains($selectedCategoriaNombre, 'préstamo') ? '¿Quién nos prestó?' : '¿De dónde proviene el subsidio?' }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" wire:model="dineroProviene"
                                       class="w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                                       placeholder="Ej: Banco Agrario, Ministerio, etc.">
                                @error('dineroProviene') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @elseif(stripos($selectedCategoriaNombre, 'venta de animal') !== false)
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">Animales <span class="text-rose-500">*</span></label>
                                <x-animal-multi-select
                                    model="animalesIds"
                                    :options="$animalesDisponibles"
                                />
                                <p class="mt-1 text-[11px] text-zinc-500">Al guardar: baja por venta, salida del stock y enlace permanente con este ingreso.</p>
                                @error('animalesIds') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">A quién se vendió</label>
                                <input type="text" wire:model="comprador"
                                       class="w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                                       placeholder="Ej: Juan Pérez (Opcional)">
                                @error('comprador') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @elseif(str_contains($selectedCategoriaNombre, 'venta de leche'))
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Cantidad (Litros) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.1" min="0.1" wire:model="cantidadLitros"
                                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-2.5 pl-3.5 pr-11 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                                           placeholder="Ej: 50.5">
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-xs font-bold text-zinc-500">Ltrs</span>
                                </div>
                                @error('cantidadLitros') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">A quién se vendió</label>
                                <input type="text" wire:model="comprador"
                                       class="w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                                       placeholder="Opcional">
                                @error('comprador') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @elseif(str_contains($selectedCategoriaNombre, 'venta de queso'))
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Cantidad de Quesos <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="1" min="1" wire:model="cantidadQuesos"
                                           class="w-full rounded-xl border border-zinc-200 bg-white py-2.5 pl-3.5 pr-11 text-sm text-zinc-900 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                                           placeholder="Ej: 15">
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-xs font-bold text-zinc-500">Unds</span>
                                </div>
                                @error('cantidadQuesos') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">A quién se vendió</label>
                                <input type="text" wire:model="comprador"
                                       class="w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                                       placeholder="Opcional">
                                @error('comprador') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @endif

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Monto <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-bold text-zinc-500">S/.</span>
                            <input type="number" min="0.01" step="0.01" inputmode="decimal" wire:model="monto"
                                   class="w-full rounded-xl border border-zinc-200 bg-white py-2.5 pl-11 pr-3.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100"
                                   placeholder="150.00">
                        </div>
                        @error('monto') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Fecha <span class="text-rose-500">*</span></label>
                        <input type="date" wire:model="fecha"
                               class="w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100">
                        @error('fecha') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Descripción breve</label>
                    <textarea wire:model="descripcion" rows="2.5"
                              class="w-full resize-none rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-600"
                              placeholder="Ej: Compra de alimento para el lote de engorde A"></textarea>
                    <div class="mt-1 flex justify-between gap-3 text-[11px] text-zinc-600">
                        <span>Opcional. Usa una frase fácil de encontrar.</span>
                        <span>Máx. 255</span>
                    </div>
                    @error('descripcion') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </section>

            @php
                $receiptFrame = \App\Support\ImageFrame::normalize($comprobanteEncuadre);
                $storedReceiptIsImage = $comprobanteRuta && preg_match('/\.(jpe?g|png|webp)$/i', $comprobanteRuta) === 1;
                $storedReceiptUrl = $storedReceiptIsImage && $movId
                    ? route('movimiento.comprobante', $movId).'?v='.sha1($comprobanteRuta)
                    : null;
            @endphp
            <aside x-data="optimizedAttachmentUpload('comprobante')" class="flex flex-col justify-between border-t border-zinc-100 bg-zinc-50/50 p-5 sm:p-6 lg:border-l lg:border-t-0 lg:p-7 dark:border-zinc-800 dark:bg-zinc-950/45">
                <div class="space-y-3.5">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Comprobante y Foto</h2>
                        <p class="mt-0.5 text-xs leading-5 text-zinc-500">JPG, PNG, WebP o PDF. Imágenes con encuadre editable.</p>
                    </div>

                    <div class="relative flex w-full min-h-[250px] sm:min-h-[280px] lg:min-h-[300px] aspect-[4/3] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-950 shadow-inner" x-bind:aria-busy="busy">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa inmediata del comprobante" class="absolute inset-0 h-full w-full object-contain" decoding="async">
                        </template>

                        <div x-show="!previewUrl" class="w-full text-center">
                            @if($comprobante && str_starts_with((string) $comprobante->getMimeType(), 'image/'))
                                <img src="{{ $comprobante->temporaryUrl() }}" alt="Vista previa del comprobante" class="absolute inset-0 h-full w-full object-cover" decoding="async" style="object-position: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%; transform: scale({{ $receiptFrame['zoom'] }}); transform-origin: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%;">
                            @elseif($comprobante)
                                <div class="space-y-2">
                                    <svg class="mx-auto h-10 w-10 text-rose-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5m-5.25-5.25L19.5 7.5m-5.25-5.25V7.5h5.25" /></svg>
                                    <p class="break-all text-xs font-semibold text-zinc-300">{{ $comprobante->getClientOriginalName() }}</p>
                                </div>
                            @elseif($storedReceiptIsImage && $movId)
                                <img src="{{ $storedReceiptUrl }}" alt="Comprobante guardado" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" style="object-position: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%; transform: scale({{ $receiptFrame['zoom'] }}); transform-origin: {{ $receiptFrame['x'] }}% {{ $receiptFrame['y'] }}%;">
                            @elseif($comprobanteRuta)
                                <div class="space-y-2 text-zinc-400">
                                    <svg class="mx-auto h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V7.5m-5.25-5.25L19.5 7.5m-5.25-5.25V7.5h5.25" /></svg>
                                    <p class="text-xs font-semibold">Comprobante guardado</p>
                                </div>
                            @else
                                <div class="space-y-2 text-zinc-600">
                                    <svg class="mx-auto h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m3 15 5-5c.7-.7 1.8-.7 2.5 0l4 4 1-1c.7-.7 1.8-.7 2.5 0l3 3" /></svg>
                                    <p class="text-xs font-medium">Sin comprobante seleccionado</p>
                                </div>
                            @endif
                        </div>

                        @if($comprobante && str_starts_with((string) $comprobante->getMimeType(), 'image/'))
                            <x-image-frame-editor id="receipt-photo-frame" :src="$comprobante->temporaryUrl()" x-model="comprobanteEncuadre.x" y-model="comprobanteEncuadre.y" zoom-model="comprobanteEncuadre.zoom" />
                        @elseif($storedReceiptIsImage && $movId)
                            <x-image-frame-editor id="receipt-photo-frame" :src="$storedReceiptUrl" x-model="comprobanteEncuadre.x" y-model="comprobanteEncuadre.y" zoom-model="comprobanteEncuadre.zoom" />
                        @endif

                        <div x-cloak x-show="busy" class="absolute inset-x-3 bottom-3 z-30 rounded-xl bg-zinc-950/90 p-3 shadow-lg" role="status" aria-live="polite">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-emerald-300">
                                <span x-text="processing ? 'Preparando archivo...' : 'Subiendo archivo...'"></span>
                                <span x-show="uploading" x-text="`${progress}%`"></span>
                            </div>
                            <progress max="100" x-bind:value="processing ? null : progress" class="mt-2 block h-1 w-full overflow-hidden rounded-full"></progress>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-image-source-actions input-id="movement-attachment-input" handler="selectAttachment" accept="image/jpeg,image/png,image/webp,application/pdf" gallery-label="Galería o PDF" />
                        @if($comprobante)
                            <button type="button" wire:click="cancelAttachmentChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-white dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 disabled:opacity-50">Descartar archivo nuevo</button>
                        @endif
                        <p class="text-center text-[11px] text-zinc-500">Formatos soportados: JPG, PNG, WebP o PDF hasta 25 MB.</p>
                        <span x-cloak x-show="clientError" x-text="clientError" class="block rounded-lg bg-rose-500/10 px-3 py-2 text-xs text-rose-400" role="alert"></span>
                        @error('comprobante') <p class="rounded-lg bg-rose-500/10 px-3 py-2 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </aside>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-zinc-800 bg-zinc-950/30 px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-8">
            <a href="{{ $animalVentaId && !$isEdit ? route('animal.index') : route('finanzas.index', ['tab' => 'movimientos']) }}"
               class="rounded-xl bg-zinc-950 px-5 py-3 text-center text-sm font-semibold text-zinc-400 transition hover:text-zinc-100">
                Cancelar
            </a>
            <button type="submit" x-bind:disabled="$store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,comprobante"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-7 py-3 text-sm font-bold text-zinc-950 shadow-lg shadow-emerald-500/10 transition hover:from-emerald-400 hover:to-teal-400 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Guardar cambios' : 'Registrar movimiento' }}</span>
                <span wire:loading wire:target="save" role="status">
                    {{ $isEdit ? 'Actualizando movimiento...' : 'Registrando movimiento...' }}
                </span>
            </button>
        </div>
    </form>
</div>
