<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex items-start gap-4">
        <a href="{{ route('engorde.index') }}"
           class="shrink-0 rounded-xl border border-emerald-950/10 bg-white p-2.5 text-zinc-500 shadow-sm transition hover:border-emerald-500/30 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:text-zinc-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-400 sm:text-3xl">
                {{ $isEdit ? 'Editar Lote' : 'Crear Lote de Engorde' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Configura datos principales y agrega foto opcional del lote.</p>
        </div>
    </div>

    <form wire:submit="save" x-data="optimizedImageUpload" x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        <section class="order-2 space-y-5 rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6 lg:order-1 lg:col-span-2">
            <div class="border-b border-emerald-950/10 pb-3 dark:border-zinc-800">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Información del lote</h2>
                <p class="mt-1 text-xs text-zinc-500">Identificación, periodo y estado operativo.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <span class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">Código automático</span>
                    <div class="flex min-h-[42px] items-center justify-between gap-3 rounded-xl border border-emerald-500/25 bg-emerald-50 px-4 py-2.5 dark:border-emerald-500/30 dark:bg-emerald-950/30">
                        <span class="font-mono text-base font-black tracking-wider text-emerald-800 dark:text-emerald-300">{{ $codigo ?: 'LOT--' }}</span>
                        <span class="rounded-lg bg-emerald-600/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Generado</span>
                    </div>
                    <span class="mt-1 block text-[11px] text-zinc-500">Prefijo, año y correlativo se asignan automáticamente.</span>
                    @error('codigo') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="nombre" class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">Nombre del lote <span class="font-normal text-zinc-500">(opcional)</span></label>
                    <input id="nombre" type="text" wire:model="nombre" maxlength="255"
                           class="w-full rounded-xl border border-emerald-950/15 bg-emerald-50/50 px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                           placeholder="Ej: Vacas de descarte">
                    @error('nombre') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="fechaInicio" class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">Fecha de inicio <span class="text-red-500">*</span></label>
                    <input id="fechaInicio" type="date" wire:model="fechaInicio"
                           class="w-full rounded-xl border border-emerald-950/15 bg-emerald-50/50 px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100">
                    @error('fechaInicio') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="fechaFin" class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">Fecha de cierre <span class="font-normal text-zinc-500">(opcional)</span></label>
                    <input id="fechaFin" type="date" wire:model="fechaFin" min="{{ $fechaInicio }}"
                           class="w-full rounded-xl border border-emerald-950/15 bg-emerald-50/50 px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100">
                    @error('fechaFin') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">Estado <span class="text-red-500">*</span></label>
                    <x-filter-select model="estado" :options="['activo' => 'Activo (en curso)', 'cerrado' => 'Cerrado (finalizado)']" tone="amber" />
                    @error('estado') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="observaciones" class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-400">Observaciones</label>
                    <textarea id="observaciones" wire:model="observaciones" rows="5" maxlength="5000"
                              class="w-full resize-y rounded-xl border border-emerald-950/15 bg-emerald-50/50 px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                              placeholder="Propósito del lote, alimentación especial y otros detalles..."></textarea>
                    @error('observaciones') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-emerald-950/10 pt-5 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <a href="{{ route('engorde.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-emerald-950/10 bg-emerald-50 px-5 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-emerald-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    Cancelar
                </a>
                <button type="submit" x-bind:disabled="busy || $store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,foto"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-3 text-sm font-bold text-zinc-950 shadow-lg shadow-emerald-500/10 transition hover:-translate-y-0.5 hover:from-emerald-400 hover:to-teal-400 disabled:cursor-wait disabled:opacity-60">
                    <svg wire:loading.remove wire:target="save" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4Z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v5h8V3M7 21v-8h10v8"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Guardar cambios' : 'Crear lote' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-950 border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </section>

        @php
            $lotePhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
        @endphp
        <aside class="order-1 space-y-4 rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm dark:border-zinc-800/80 dark:bg-zinc-900 sm:p-6 lg:order-2">
            <div class="flex items-start justify-between gap-3 border-b border-emerald-950/10 pb-3 dark:border-zinc-800">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Foto del lote</h2>
                    <p class="mt-1 text-xs text-zinc-500">Completa, proporcional y optimizada.</p>
                </div>
                <span class="shrink-0 rounded-full border border-emerald-950/10 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
                    {{ $existingFoto && !$removeFoto && !$foto ? 'Existente' : 'Opcional' }}
                </span>
            </div>

            <label for="lote-photo-input" class="group relative flex aspect-[4/3] w-full cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50 transition hover:border-emerald-500 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:hover:border-emerald-400 lg:aspect-square">
                <template x-if="previewUrl">
                    <img :src="previewUrl" alt="Vista previa de foto nueva" class="absolute inset-0 h-full w-full bg-zinc-100 object-contain dark:bg-zinc-950">
                </template>

                <span x-show="!previewUrl" class="absolute inset-0">
                    @if($foto && !$errors->has('foto'))
                        <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa de foto nueva" class="h-full w-full bg-zinc-100 object-cover dark:bg-zinc-950" style="object-position: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%; transform: scale({{ $lotePhotoFrame['zoom'] }}); transform-origin: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%;">
                        <span class="absolute left-3 top-3 rounded-lg bg-zinc-950/80 px-3 py-1.5 text-xs font-semibold text-white">Nueva imagen</span>
                    @elseif($existingFoto && !$removeFoto)
                        <img src="{{ '/storage/'.ltrim($existingFoto, '/') }}" alt="Foto actual del lote" class="h-full w-full bg-zinc-100 object-cover dark:bg-zinc-950" style="object-position: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%; transform: scale({{ $lotePhotoFrame['zoom'] }}); transform-origin: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%;">
                        <span class="absolute left-3 top-3 rounded-lg bg-zinc-950/80 px-3 py-1.5 text-xs font-semibold text-white">Foto actual</span>
                    @elseif($removeFoto)
                        <span class="flex h-full flex-col items-center justify-center px-5 text-center">
                            <svg class="h-10 w-10 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                            </svg>
                            <span class="mt-3 text-sm font-semibold text-rose-700 dark:text-rose-300">Eliminación preparada</span>
                            <span class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">Clic para elegir reemplazo.</span>
                        </span>
                    @else
                        <span class="flex h-full flex-col items-center justify-center px-5 text-center">
                            <svg class="h-10 w-10 text-emerald-700 transition group-hover:scale-105 dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.379a2.25 2.25 0 0 0 1.59-.659l.622-.622A2.25 2.25 0 0 1 10.432 4h3.136a2.25 2.25 0 0 1 1.591.659l.622.622A2.25 2.25 0 0 0 17.371 6h1.379A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.375a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
                            </svg>
                            <span class="mt-3 text-sm font-semibold text-emerald-950 dark:text-emerald-50">Cargar foto</span>
                            <span class="mt-1 text-xs text-emerald-900/60 dark:text-emerald-200/70">JPG, PNG o WebP</span>
                        </span>
                    @endif
                </span>

                @if($foto && !$errors->has('foto'))
                    <x-image-frame-editor id="lote-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                @elseif($existingFoto && !$removeFoto)
                    <x-image-frame-editor id="lote-photo-frame" :src="'/storage/'.ltrim($existingFoto, '/')" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                @endif

                <span x-cloak x-show="busy" class="absolute inset-x-3 bottom-3 z-30 rounded-xl bg-zinc-950/90 p-3 shadow-lg" role="status" aria-live="polite">
                    <span class="flex items-center justify-between gap-3 text-[11px] font-semibold text-emerald-300">
                        <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                        <span x-show="uploading" x-text="`${progress}%`"></span>
                    </span>
                    <span class="mt-2 block h-1 overflow-hidden rounded-full bg-zinc-700">
                        <span class="block h-full bg-emerald-400 transition-all duration-200" :style="`width: ${processing ? 18 : progress}%`"></span>
                    </span>
                </span>

            </label>

            <x-image-source-actions input-id="lote-photo-input" />

            <p class="text-center text-xs font-medium text-zinc-500">Se reduce a 1600 px y WebP. Ajusta foco y zoom sin modificar archivo original.</p>
            <span x-cloak x-show="clientError" x-text="clientError" class="block text-xs text-red-500" role="alert"></span>
            @error('foto') <span class="block text-xs text-red-500">{{ $message }}</span> @enderror

            <div class="grid gap-2">
                @if($foto)
                    <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()"
                            class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-800">
                        Descartar imagen nueva
                    </button>
                @elseif($existingFoto && !$removeFoto)
                    <button type="button" wire:click="requestPhotoRemoval"
                            class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-transparent dark:text-rose-400 dark:hover:bg-rose-500/10">
                        Eliminar imagen
                    </button>
                @elseif($removeFoto)
                    <button type="button" wire:click="cancelPhotoRemoval"
                            class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-800">
                        Deshacer eliminación
                    </button>
                @endif
            </div>

            <div class="rounded-xl border border-amber-500/25 bg-amber-50 p-3 text-xs leading-5 text-amber-800 dark:bg-amber-500/5 dark:text-amber-200/80">
                @if($existingFoto)
                    Foto actual permanece protegida hasta pulsar <strong>Guardar cambios</strong>.
                @else
                    Imagen solo se almacena al pulsar <strong>{{ $isEdit ? 'Guardar cambios' : 'Crear lote' }}</strong>.
                @endif
            </div>
        </aside>
    </form>
</div>
