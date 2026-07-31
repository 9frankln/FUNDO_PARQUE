<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('finanzas.index', ['tab' => 'asignaciones']) }}"
           aria-label="Volver a asignaciones familiares"
           class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 text-zinc-400 transition hover:border-zinc-700 hover:text-zinc-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </a>
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-violet-400">Asignación familiar</p>
            <h1 class="mt-1 bg-gradient-to-r from-violet-400 to-indigo-300 bg-clip-text text-3xl font-extrabold tracking-tight text-transparent">
                {{ $isEdit ? 'Editar asignación' : 'Registrar asignación' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-400">Entrega, beneficiario y evidencia visual en una sola ficha.</p>
        </div>
    </div>

    <form wire:submit="save" x-data x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-2xl shadow-zinc-950/20">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="space-y-6 p-5 sm:p-7 lg:p-8">
                <div>
                    <h2 class="text-base font-bold text-zinc-100">Información de entrega</h2>
                    <p class="mt-1 text-xs text-zinc-500">Completa solo los datos necesarios para identificarla.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Beneficiario <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="beneficiario"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20"
                               placeholder="Ej: María Delgado (hija)">
                        @error('beneficiario') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Propósito <span class="text-rose-500">*</span></label>
                        <x-filter-select
                            model="proposito"
                            :options="['estudio' => 'Estudios', 'salud' => 'Salud', 'alimentacion' => 'Alimentación', 'vivienda' => 'Vivienda', 'transporte' => 'Transporte', 'ropa' => 'Ropa', 'gastos_personales' => 'Gastos personales', 'emergencia' => 'Emergencia', 'otros' => 'Otros']"
                            tone="violet"
                            live
                        />
                        @error('proposito') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Monto <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-zinc-500">S/.</span>
                            <input type="number" min="0.01" step="0.01" inputmode="decimal" wire:model="monto"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-3 pl-12 pr-4 text-sm text-zinc-100 outline-none transition focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20"
                                   placeholder="300.00">
                        </div>
                        @error('monto') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Fecha de entrega <span class="text-rose-500">*</span></label>
                        <input type="date" wire:model="fecha"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-100 outline-none transition focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                        @error('fecha') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Detalle</label>
                    <textarea wire:model="descripcion" rows="3"
                              class="w-full resize-none rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20"
                              placeholder="Ej: Mensualidad universitaria de julio"></textarea>
                    <p class="mt-1.5 text-[11px] text-zinc-600">Opcional. Visible dentro de la ficha, no recarga la tabla.</p>
                    @error('descripcion') <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </section>

            @php
                $assignmentPhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
            @endphp
            <aside x-data="optimizedImageUpload('foto', 1080, 614400)" class="border-t border-zinc-800 bg-zinc-950/45 p-5 sm:p-7 lg:border-l lg:border-t-0">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-100">Foto de respaldo</h2>
                        <p class="mt-1 text-xs leading-5 text-zinc-500">Opcional. Se convierte a WebP y se reduce para cargar rápido.</p>
                    </div>

                    <div class="relative aspect-[4/3] overflow-hidden rounded-2xl border border-dashed border-zinc-700 bg-zinc-950" x-bind:aria-busy="busy">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa inmediata de la foto" class="absolute inset-0 h-full w-full object-contain" decoding="async">
                        </template>

                        <div x-show="!previewUrl" class="h-full">
                            @if($foto)
                                <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa de la foto" class="h-full w-full object-cover" decoding="async" style="object-position: {{ $assignmentPhotoFrame['x'] }}% {{ $assignmentPhotoFrame['y'] }}%; transform: scale({{ $assignmentPhotoFrame['zoom'] }}); transform-origin: {{ $assignmentPhotoFrame['x'] }}% {{ $assignmentPhotoFrame['y'] }}%;">
                            @elseif($fotoRuta && $asigId)
                                <img src="{{ route('asignacion.foto', $asigId) }}" alt="Foto guardada de la asignación" class="h-full w-full object-cover" loading="lazy" decoding="async" style="object-position: {{ $assignmentPhotoFrame['x'] }}% {{ $assignmentPhotoFrame['y'] }}%; transform: scale({{ $assignmentPhotoFrame['zoom'] }}); transform-origin: {{ $assignmentPhotoFrame['x'] }}% {{ $assignmentPhotoFrame['y'] }}%;">
                            @else
                                <div class="flex h-full flex-col items-center justify-center gap-2 text-zinc-600">
                                    <svg class="h-11 w-11" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.38a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                                    <span class="text-xs">Sin foto</span>
                                </div>
                            @endif
                        </div>

                        @if($foto)
                            <x-image-frame-editor id="assignment-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @elseif($fotoRuta && $asigId)
                            <x-image-frame-editor id="assignment-photo-frame" :src="route('asignacion.foto', $asigId)" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @endif

                        <div x-cloak x-show="busy" class="absolute inset-x-3 bottom-3 z-30 rounded-xl bg-zinc-950/90 p-3 shadow-lg" role="status" aria-live="polite">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-violet-300">
                                <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                                <span x-show="uploading" x-text="`${progress}%`"></span>
                            </div>
                            <progress max="100" x-bind:value="processing ? null : progress" class="mt-2 block h-1 w-full overflow-hidden rounded-full"></progress>
                        </div>
                    </div>

                    <x-image-source-actions input-id="asignacion-photo-input" />
                    @if($foto)
                        <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()" x-bind:disabled="busy" class="w-full rounded-xl border border-zinc-700 px-3 py-2 text-xs font-bold text-zinc-300 transition hover:bg-zinc-800 disabled:opacity-50">Descartar imagen nueva</button>
                    @endif
                    <p class="text-center text-[11px] text-zinc-600">JPG, PNG o WebP. Ajusta foco y zoom antes de guardar.</p>
                    <span x-cloak x-show="clientError" x-text="clientError" class="block rounded-lg bg-rose-500/10 px-3 py-2 text-xs text-rose-400" role="alert"></span>
                    @error('foto') <p class="rounded-lg bg-rose-500/10 px-3 py-2 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
            </aside>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-zinc-800 bg-zinc-950/30 px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-8">
            <a href="{{ route('finanzas.index', ['tab' => 'asignaciones']) }}"
               class="rounded-xl bg-zinc-950 px-5 py-3 text-center text-sm font-semibold text-zinc-400 transition hover:text-zinc-100">
                Cancelar
            </a>
            <button type="submit" x-bind:disabled="$store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,foto"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-500 to-indigo-500 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-violet-500/10 transition hover:from-violet-400 hover:to-indigo-400 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Guardar cambios' : 'Registrar asignación' }}</span>
                <span wire:loading.class.remove="hidden" wire:target="save" class="hidden">Guardando...</span>
            </button>
        </div>
    </form>
</div>
