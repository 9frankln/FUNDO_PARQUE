<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('monitoreo.index') }}"
           class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-100 transition duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                {{ $isEdit ? 'Editar Parto' : 'Registrar Parto' }}
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Registra los nacimientos y estado de salud de la madre y cría.</p>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit="save" x-data="optimizedMultiImageUpload('fotos', 3, {{ count($existingPhotos) + count($fotos) }})" x-on:profile-image-upload-state="profileImageBusy = $event.detail.busy" x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="space-y-6">
        <!-- Mother block -->
        <section class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 border-b border-zinc-850 pb-2">Información del Parto y la Madre</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
                <!-- Madre -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Madre (bovina de 24 meses o más) <span class="text-red-500">*</span></label>
                    <x-filter-select
                        model="animalMadreId"
                        :options="['' => 'Selecciona madre...'] + collect($madres)->mapWithKeys(fn ($md) => [data_get($md, 'id') => data_get($md, 'arete').' - '.(data_get($md, 'nombre') ?: 'Sin Nombre')])->all()"
                        tone="sky"
                        live
                    />
                    @error('animalMadreId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Fecha Parto -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Fecha del Parto <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fechaParto" max="{{ now()->toDateString() }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 focus:border-emerald-500/50 focus:ring-emerald-500/20 text-zinc-100 text-sm transition outline-none">
                    @error('fechaParto') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo Parto -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Tipo de Parto <span class="text-red-500">*</span></label>
                    <x-filter-select model="tipoParto" :options="['normal' => 'Normal (Sin ayuda)', 'asistido' => 'Asistido (Por personal)', 'cesarea' => 'Cesárea', 'aborto_prematuro' => 'Aborto / Prematuro']" tone="amber" />
                    @error('tipoParto') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Condicion Madre -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Condición Post-parto de la Madre <span class="text-red-500">*</span></label>
                    <x-filter-select model="condicionMadre" :options="['optima' => 'Óptima (Saludable)', 'retencion_placenta' => 'Retención de Placenta', 'fiebre_leche' => 'Fiebre de Leche (Hipocalcemia)', 'desgarro' => 'Desgarros vaginales/uterinos']" tone="amber" />
                    @error('condicionMadre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <!-- Calf block -->
        <section class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-teal-400 border-b border-zinc-850 pb-2">Información de la Cría Nacida</h3>
            <p class="text-xs text-zinc-500">Toda cría viva recibe código automático y se agrega al inventario.</p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Cria Sexo -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Sexo de la Cría <span class="text-red-500">*</span></label>
                    <x-filter-select model="criaSexo" :options="['macho' => 'Macho', 'hembra' => 'Hembra']" tone="sky" />
                    @error('criaSexo') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Nombre Cria -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Nombre de la Cría</label>
                    <input type="text" wire:model="criaNombre" maxlength="100" autocomplete="off"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: Lucero">
                    <p class="mt-1 text-[10px] text-zinc-500">Opcional. Si queda vacío, se genera desde el nombre de la madre.</p>
                    @error('criaNombre') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Peso Cria -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Peso al Nacer (Kg)</label>
                    <input type="number" step="0.01" wire:model="criaPesoNacer"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: 32.50">
                    @error('criaPesoNacer') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Raza Cria -->
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Raza de la Cría <span class="text-red-500">*</span></label>
                    <x-filter-select
                        model="criaRazaId"
                        :options="['' => 'Selecciona raza...'] + collect($razasCria)->mapWithKeys(fn ($raza) => [data_get($raza, 'id') => data_get($raza, 'nombre')])->all()"
                        tone="emerald"
                        :disabled="empty($animalMadreId)"
                    />
                    <p class="mt-1 text-[10px] text-zinc-500">Inicia con la raza de la madre. Puedes elegir otra raza bovina.</p>
                    @error('criaRazaId') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Estado cria -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Estado de Vitalidad de la Cría <span class="text-red-500">*</span></label>
                    <x-filter-select model="criaEstado" :options="['vivo_vigoroso' => 'Vivo y Vigoroso (Saludable)', 'debil' => 'Débil (Requiere cuidados especiales)', 'muerto_al_nacer' => 'Muerto al nacer / Nacido Muerto']" tone="rose" />
                    @error('criaEstado') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <!-- Calf profile photo -->
        @php
            $calfPhotoFrame = \App\Support\ImageFrame::normalize($criaFotoEncuadre);
        @endphp
        <section x-data="optimizedImageUpload('criaFoto')" class="space-y-4 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-4 sm:p-5">
            <div class="border-b border-zinc-850 pb-2.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Foto de perfil de la cría</h3>
                <p class="mt-0.5 text-[10px] text-zinc-500">Imagen principal del nuevo animal. No cuenta dentro de las fotos del evento.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-[14rem_minmax(0,1fr)] sm:items-start">
                <label for="calf-profile-photo" class="group relative flex aspect-[4/3] cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50 transition hover:border-emerald-500 dark:border-emerald-500/40 dark:bg-emerald-950/35 dark:hover:border-emerald-400">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Vista previa de foto de la cría" class="absolute inset-0 h-full w-full object-contain">
                    </template>
                    <span x-show="!previewUrl" class="absolute inset-0">
                        @if($criaFoto && !$errors->has('criaFoto'))
                            <img src="{{ $criaFoto->temporaryUrl() }}" alt="Foto nueva de la cría" class="h-full w-full object-cover" style="object-position: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%; transform: scale({{ $calfPhotoFrame['zoom'] }}); transform-origin: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%;">
                        @elseif($existingCriaFoto && !$removeCriaFoto)
                            <img src="{{ '/storage/'.ltrim($existingCriaFoto, '/') }}" alt="Foto actual de la cría" class="h-full w-full object-cover" style="object-position: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%; transform: scale({{ $calfPhotoFrame['zoom'] }}); transform-origin: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%;">
                        @elseif($removeCriaFoto)
                            <span class="flex h-full flex-col items-center justify-center px-4 text-center text-rose-700 dark:text-rose-300">
                                <span class="text-2xl">&times;</span>
                                <span class="mt-1 text-xs font-bold">Eliminación preparada</span>
                            </span>
                        @else
                            <span class="flex h-full flex-col items-center justify-center px-4 text-center">
                                <svg class="h-8 w-8 text-emerald-700 dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.379a2.25 2.25 0 0 0 1.59-.659l.622-.622A2.25 2.25 0 0 1 10.432 4h3.136a2.25 2.25 0 0 1 1.591.659l.622.622A2.25 2.25 0 0 0 17.371 6h1.379A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.375a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                                <span class="mt-2 text-xs font-bold text-emerald-900 dark:text-emerald-100">Elegir foto</span>
                            </span>
                        @endif
                    </span>
                    @if($criaFoto && !$errors->has('criaFoto'))
                        <x-image-frame-editor id="calf-photo-frame" :src="$criaFoto->temporaryUrl()" x-model="criaFotoEncuadre.x" y-model="criaFotoEncuadre.y" zoom-model="criaFotoEncuadre.zoom" />
                    @elseif($existingCriaFoto && !$removeCriaFoto)
                        <x-image-frame-editor id="calf-photo-frame" :src="'/storage/'.ltrim($existingCriaFoto, '/')" x-model="criaFotoEncuadre.x" y-model="criaFotoEncuadre.y" zoom-model="criaFotoEncuadre.zoom" />
                    @endif
                    <span x-cloak x-show="busy" class="absolute inset-x-2 bottom-2 rounded-lg bg-zinc-950/90 p-2 text-[10px] font-semibold text-emerald-300" role="status" aria-live="polite">
                        <span x-text="processing ? 'Optimizando...' : `Subiendo ${progress}%`"></span>
                    </span>
                </label>

                <div class="space-y-3">
                    <x-image-source-actions input-id="calf-profile-photo" />
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs leading-5 text-emerald-900 dark:border-emerald-500/25 dark:bg-emerald-500/[.07] dark:text-emerald-100">
                        Carga una foto clara de la cría. Se optimiza a WebP y aparece como imagen principal en inventario, partos y ficha animal.
                    </div>
                    <span x-cloak x-show="clientError" x-text="clientError" class="block text-xs text-red-500" role="alert"></span>
                    @error('criaFoto') <span class="block text-xs text-red-500">{{ $message }}</span> @enderror

                    @if($criaFoto)
                        <button type="button" wire:click="cancelCriaPhotoChange" x-on:click="releasePreview()" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800">Descartar foto nueva</button>
                    @elseif($existingCriaFoto && !$removeCriaFoto)
                        <button type="button" wire:click="requestCriaPhotoRemoval" class="w-full rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">Eliminar foto actual</button>
                    @elseif($removeCriaFoto)
                        <button type="button" wire:click="cancelCriaPhotoRemoval" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">Deshacer eliminación</button>
                    @endif
                </div>
            </div>
        </section>

        <!-- Observaciones -->
        <section class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80">
            <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Observaciones / Detalles Especiales</label>
            <textarea wire:model="observaciones" rows="3"
                      class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                      placeholder="Ej: Se inyectó calcio a la madre preventivamente..."></textarea>
            @error('observaciones') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </section>

        <x-record-photo-upload title="Fotos del evento de parto" :existing-photos="$existingPhotos" :new-photos="$fotos" :new-frames="$fotoEncuadres" />

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('monitoreo.index') }}"
               class="px-5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-200 text-sm font-semibold transition">
                Cancelar
            </a>
            <button type="submit" x-bind:disabled="busy || profileImageBusy || $store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,fotos,criaFoto"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-zinc-950 font-bold text-sm transition shadow-lg shadow-emerald-500/10 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="save">&#x1F4BE; {{ $isEdit ? 'Actualizar' : 'Registrar' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
