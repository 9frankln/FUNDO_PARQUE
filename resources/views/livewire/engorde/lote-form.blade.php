<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Header --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ route('engorde.index') }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-emerald-500/40 hover:bg-emerald-50/50 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-950/25 dark:hover:text-emerald-300"
           aria-label="Volver">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Engorde · Lotes</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-emerald-500/30 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-emerald-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Editar Lote' : 'Crear Lote de Engorde' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">Configura datos principales y agrega foto opcional del lote.</p>
        </div>
    </header>

    <form wire:submit="save" autocomplete="off" x-data="optimizedImageUpload" x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Columna Principal: Datos del lote (7 columnas en desktop) --}}
            <div class="space-y-6 lg:col-span-7">
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Información del lote</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Identificación, periodo y estado operativo.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <div class="flex flex-col justify-start">
                            <span class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Código automático</span>
                            </span>
                            <div class="flex h-11 items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 dark:border-zinc-700 dark:bg-zinc-950">
                                <span class="font-mono text-sm font-black tracking-wider text-emerald-800 dark:text-emerald-400">{{ $codigo ?: 'LOT--' }}</span>
                                <span class="rounded-lg bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300">Generado</span>
                            </div>
                            <span class="mt-1 block text-[11px] text-zinc-500">Prefijo, año y correlativo automáticos.</span>
                            @error('codigo') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col justify-start">
                            <label for="nombre" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Nombre del lote</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input id="nombre" type="text" wire:model="nombre" maxlength="255" autocomplete="off"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                   placeholder="Ej: Vacas de descarte">
                            @error('nombre') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col justify-start">
                            <label for="fechaInicio" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Fecha de inicio</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-date-picker model="fechaInicio" placeholder="dd/mm/aaaa" />
                            @error('fechaInicio') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col justify-start">
                            <label for="fechaFin" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Fecha de cierre</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <x-date-picker model="fechaFin" :min="$fechaInicio" placeholder="dd/mm/aaaa" />
                            @error('fechaFin') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-2">
                            <span class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Estado operativo</span>
                            </span>
                            <div class="flex h-11 items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 dark:border-zinc-700 dark:bg-zinc-950">
                                <div class="flex items-center gap-2">
                                    <x-status-badge :value="$estado" />
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ $estado === 'activo' ? 'En proceso de engorde (Activo)' : 'Ciclo finalizado (Cerrado)' }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-zinc-400 dark:text-zinc-500">Gestión de venta/cierre directa en el lote</span>
                            </div>
                        </div>

                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label for="observaciones" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Observaciones</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <textarea id="observaciones" wire:model="observaciones" rows="4" maxlength="5000"
                                      class="w-full resize-y rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                      placeholder="Propósito del lote, alimentación especial y otros detalles..."></textarea>
                            @error('observaciones') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- Columna Lateral: Foto del Lote (5 columnas en desktop) --}}
            @php
                $lotePhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
            @endphp
            <aside class="space-y-6 lg:col-span-5">
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
                                <p class="mt-0.5 text-xs text-zinc-500">Completa, proporcional y optimizada.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-teal-500/25 bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:border-teal-500/20 dark:bg-teal-500/15 dark:text-teal-300 shadow-sm">
                            {{ $existingFoto && !$removeFoto && !$foto ? 'Existente' : 'Opcional' }}
                        </span>
                    </div>

                    {{-- Contenedor de Imagen Proporcionado --}}
                    <label for="lote-photo-input" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950/60">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa de foto nueva" class="absolute inset-0 h-full w-full object-contain">
                        </template>

                        <span x-show="!previewUrl" class="absolute inset-0">
                            @if($foto && !$errors->has('foto'))
                                <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa de foto nueva" class="h-full w-full object-cover" style="object-position: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%; transform: scale({{ $lotePhotoFrame['zoom'] }}); transform-origin: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar imagen</span>
                            @elseif($existingFoto && !$removeFoto)
                                <img src="{{ '/storage/'.ltrim($existingFoto, '/') }}" alt="Foto actual del lote" class="h-full w-full object-cover" style="object-position: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%; transform: scale({{ $lotePhotoFrame['zoom'] }}); transform-origin: {{ $lotePhotoFrame['x'] }}% {{ $lotePhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar imagen</span>
                            @elseif($removeFoto)
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <svg class="h-8 w-8 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">Eliminación preparada</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Clic para elegir reemplazo.</span>
                                </span>
                            @else
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-zinc-200 text-teal-600 shadow-sm transition group-hover:border-teal-500 group-hover:bg-teal-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-teal-400 dark:group-hover:bg-teal-950/30">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.086a2.25 2.25 0 0 0 1.591-.659l.82-.82A2.25 2.25 0 0 1 10.34 3.86h3.318a2.25 2.25 0 0 1 1.591.659l.82.82A2.25 2.25 0 0 0 17.664 6h1.086A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
                                        </svg>
                                    </span>
                                    <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Cargar foto del lote</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Opcional · Se optimiza automáticamente</span>
                                </span>
                            @endif
                        </span>

                        @if($foto && !$errors->has('foto'))
                            <x-image-frame-editor id="lote-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @elseif($existingFoto && !$removeFoto)
                            <x-image-frame-editor id="lote-photo-frame" :src="'/storage/'.ltrim($existingFoto, '/')" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @endif

                        <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-teal-400" role="status">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                        </span>
                    </label>

                    <x-image-source-actions input-id="lote-photo-input" class="mt-3" />

                    <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs font-semibold text-rose-500" role="alert"></span>
                    @error('foto') <span class="mt-1.5 block text-xs font-semibold text-rose-500">{{ $message }}</span> @enderror

                    <div class="mt-3 grid gap-2">
                        @if($foto)
                            <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-zinc-100 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                Descartar imagen nueva
                            </button>
                        @elseif($existingFoto && !$removeFoto)
                            <button type="button" wire:click="requestPhotoRemoval"
                                    class="w-full rounded-xl border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-400">
                                Eliminar foto
                            </button>
                        @elseif($removeFoto)
                            <button type="button" wire:click="cancelPhotoRemoval"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                Deshacer eliminación
                            </button>
                        @endif
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
                <a wire:navigate href="{{ route('engorde.index') }}"
                   class="agro-button-secondary">
                    Cancelar
                </a>
                <button type="submit"
                        x-bind:disabled="busy || $store?.imageUploads?.busy"
                        wire:loading.attr="disabled"
                        wire:target="save,foto"
                        class="agro-button">
                    <svg wire:loading.remove wire:target="save" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar Lote' : 'Crear Lote' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
