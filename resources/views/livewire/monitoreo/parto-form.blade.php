<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Header --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ route('monitoreo.index', ['tab' => 'partos']) }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-rose-500/40 hover:bg-rose-50/50 hover:text-rose-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-rose-500/40 dark:hover:bg-rose-950/25 dark:hover:text-rose-300"
           aria-label="Volver">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 dark:bg-rose-400/15 dark:text-rose-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-600 dark:text-rose-400">Monitoreo · Reproducción</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-rose-500/30 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-rose-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Editar Parto' : 'Registrar Parto' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">Registra los nacimientos y estado de salud de la madre y cría.</p>
        </div>
    </header>

    {{-- Form --}}
    <form wire:submit="save" autocomplete="off" x-data="optimizedMultiImageUpload('fotos', 3, {{ count($existingPhotos) + count($fotos) }})" x-on:profile-image-upload-state="profileImageBusy = $event.detail.busy" x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Columna Principal: Datos del parto, madre y cría (7 columnas en desktop) --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Bloque de la Madre --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-rose-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-rose-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-400/15 dark:text-rose-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Información del parto y la madre</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Datos de la madre y el evento de nacimiento.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        {{-- Madre --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Madre (vaca &ge; 24 m)</span>
                                <span class="ml-1 font-bold text-rose-600 dark:text-rose-400">*</span>
                            </label>
                            <x-filter-select
                                model="animalMadreId"
                                :options="['' => 'Selecciona madre...'] + collect($madres)->mapWithKeys(fn ($md) => [data_get($md, 'id') => data_get($md, 'arete').' - '.(data_get($md, 'nombre') ?: 'Sin Nombre')])->all()"
                                tone="rose"
                                searchable
                                search-placeholder="Buscar arete o nombre..."
                                live
                            />
                            @error('animalMadreId') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fecha Parto --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Fecha del parto</span>
                                <span class="ml-1 font-bold text-rose-600 dark:text-rose-400">*</span>
                            </label>
                            <x-date-picker model="fechaParto" :max="now()->toDateString()" placeholder="dd/mm/aaaa" />
                            @error('fechaParto') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tipo Parto --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Tipo de parto</span>
                                <span class="ml-1 font-bold text-rose-600 dark:text-rose-400">*</span>
                            </label>
                            <x-filter-select model="tipoParto" :options="['normal' => 'Normal (Sin ayuda)', 'asistido' => 'Asistido (Por personal)', 'cesarea' => 'Cesárea', 'aborto_prematuro' => 'Aborto / Prematuro']" tone="rose" compact />
                            @error('tipoParto') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Condicion Madre --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Condición post-parto</span>
                                <span class="ml-1 font-bold text-rose-600 dark:text-rose-400">*</span>
                            </label>
                            <x-filter-select model="condicionMadre" :options="['optima' => 'Óptima (Saludable)', 'retencion_placenta' => 'Retención de Placenta', 'fiebre_leche' => 'Fiebre de Leche (Hipocalcemia)', 'desgarro' => 'Desgarros vaginales/uterinos']" tone="amber" compact />
                            @error('condicionMadre') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                {{-- Bloque de la Cría --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Información de la cría nacida</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Toda cría viva recibe código automático y se registra en inventario.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        {{-- Cria Sexo --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Sexo de la cría</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select model="criaSexo" :options="['macho' => 'Macho', 'hembra' => 'Hembra']" tone="emerald" />
                            @error('criaSexo') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nombre Cria --}}
                        <div class="flex flex-col justify-start">
                            <label for="criaNombre" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Nombre de la cría</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input id="criaNombre" type="text" wire:model="criaNombre" maxlength="100" autocomplete="off"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                   placeholder="Ej: Lucero">
                            <p class="mt-1 text-[10px] text-zinc-500">Si queda vacío, se genera desde el nombre de la madre.</p>
                            @error('criaNombre') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Peso Cria --}}
                        <div class="flex flex-col justify-start">
                            <label for="criaPesoNacer" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Peso al nacer (kg)</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input id="criaPesoNacer" type="number" step="0.01" wire:model="criaPesoNacer"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                   placeholder="Ej: 32.50">
                            @error('criaPesoNacer') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Raza Cria --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Raza de la cría</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select
                                model="criaRazaId"
                                :options="['' => 'Selecciona raza...'] + collect($razasCria)->mapWithKeys(fn ($raza) => [data_get($raza, 'id') => data_get($raza, 'nombre')])->all()"
                                tone="emerald"
                                searchable
                                search-placeholder="Buscar raza..."
                                :disabled="empty($animalMadreId)"
                            />
                            <p class="mt-1 text-[10px] text-zinc-500">Inicia con la raza de la madre. Puedes elegir otra raza bovina.</p>
                            @error('criaRazaId') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Estado cria --}}
                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Estado de vitalidad</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select model="criaEstado" :options="['vivo_vigoroso' => 'Vivo y Vigoroso (Saludable)', 'debil' => 'Débil (Requiere cuidados especiales)', 'muerto_al_nacer' => 'Muerto al nacer / Nacido Muerto']" tone="emerald" />
                            @error('criaEstado') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                {{-- Observaciones --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-amber-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-amber-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Observaciones y detalles especiales</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Tratamientos a la madre, medicamentos o anotaciones del parto.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <textarea wire:model="observaciones" rows="3"
                                  class="w-full resize-y rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                  placeholder="Ej: Se inyectó calcio a la madre preventivamente..."></textarea>
                        @error('observaciones') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>
                </section>
            </div>

            {{-- Columna Lateral: Foto de la Cría + Fotos del Evento (5 columnas en desktop) --}}
            @php
                $calfPhotoFrame = \App\Support\ImageFrame::normalize($criaFotoEncuadre);
            @endphp
            <aside class="space-y-6 lg:col-span-5">
                {{-- Foto principal de la cría --}}
                <section x-data="optimizedImageUpload('criaFoto')" class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-teal-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-teal-500 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:bg-teal-400/15 dark:text-teal-400 shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 5.4 12.6a1.5 1.5 0 0 1 2.12 0l3.46 3.46m.75-1.49 1.11-1.11a1.5 1.5 0 0 1 2.12 0l4.05 4.05m-15 2.24h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Foto de la cría</h2>
                                <p class="mt-0.5 text-xs text-zinc-500">Foto de perfil para el nuevo animal.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-teal-500/25 bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:border-teal-500/20 dark:bg-teal-500/15 dark:text-teal-300 shadow-sm">
                            {{ $existingCriaFoto && !$removeCriaFoto && !$criaFoto ? 'Existente' : 'Opcional' }}
                        </span>
                    </div>

                    {{-- Contenedor de Imagen Proporcionado --}}
                    <label for="calf-profile-photo" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950/60">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa de foto de la cría" class="absolute inset-0 h-full w-full object-contain">
                        </template>
                        <span x-show="!previewUrl" class="absolute inset-0">
                            @if($criaFoto && !$errors->has('criaFoto'))
                                <img src="{{ $criaFoto->temporaryUrl() }}" alt="Foto nueva de la cría" class="h-full w-full object-cover" style="object-position: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%; transform: scale({{ $calfPhotoFrame['zoom'] }}); transform-origin: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar imagen</span>
                            @elseif($existingCriaFoto && !$removeCriaFoto)
                                <img src="{{ '/storage/'.ltrim($existingCriaFoto, '/') }}" alt="Foto actual de la cría" class="h-full w-full object-cover" style="object-position: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%; transform: scale({{ $calfPhotoFrame['zoom'] }}); transform-origin: {{ $calfPhotoFrame['x'] }}% {{ $calfPhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar imagen</span>
                            @elseif($removeCriaFoto)
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <svg class="h-8 w-8 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                    <span class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">Eliminación preparada</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Clic para elegir reemplazo.</span>
                                </span>
                            @else
                                <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-zinc-200 text-teal-600 shadow-sm transition group-hover:border-teal-500 group-hover:bg-teal-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-teal-400 dark:group-hover:bg-teal-950/30">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.086a2.25 2.25 0 0 0 1.591-.659l.82-.82A2.25 2.25 0 0 1 10.34 3.86h3.318a2.25 2.25 0 0 1 1.591.659l.82.82A2.25 2.25 0 0 0 17.664 6h1.086A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                        </svg>
                                    </span>
                                    <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Seleccionar foto de cría</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Opcional · Se optimiza automáticamente</span>
                                </span>
                            @endif
                        </span>
                        @if($criaFoto && !$errors->has('criaFoto'))
                            <x-image-frame-editor id="calf-photo-frame" :src="$criaFoto->temporaryUrl()" x-model="criaFotoEncuadre.x" y-model="criaFotoEncuadre.y" zoom-model="criaFotoEncuadre.zoom" />
                        @elseif($existingCriaFoto && !$removeCriaFoto)
                            <x-image-frame-editor id="calf-photo-frame" :src="'/storage/'.ltrim($existingCriaFoto, '/')" x-model="criaFotoEncuadre.x" y-model="criaFotoEncuadre.y" zoom-model="criaFotoEncuadre.zoom" />
                        @endif
                        <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-teal-400" role="status">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                        </span>
                    </label>

                    <x-image-source-actions input-id="calf-profile-photo" class="mt-3" />

                    <div class="mt-3 grid gap-2">
                        @if($criaFoto)
                            <button type="button" wire:click="cancelCriaPhotoChange" x-on:click="releasePreview()" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-zinc-100 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Descartar foto nueva</button>
                        @elseif($existingCriaFoto && !$removeCriaFoto)
                            <button type="button" wire:click="requestCriaPhotoRemoval" class="w-full rounded-xl border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-400">Eliminar foto actual</button>
                        @elseif($removeCriaFoto)
                            <button type="button" wire:click="cancelCriaPhotoRemoval" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Deshacer eliminación</button>
                        @endif
                    </div>
                    <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs font-semibold text-rose-500" role="alert"></span>
                    @error('criaFoto') <span class="mt-1.5 block text-xs font-semibold text-rose-500">{{ $message }}</span> @enderror
                </section>

                {{-- Fotos del evento de parto --}}
                <div class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-sky-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-sky-500 dark:bg-zinc-900">
                    <x-record-photo-upload title="Fotos del evento de parto" :existing-photos="$existingPhotos" :new-photos="$fotos" :new-frames="$fotoEncuadres" />
                </div>
            </aside>
        </div>

        {{-- Barra Inferior de Acciones --}}
        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-center text-xs text-zinc-500 sm:text-left">
                Campos marcados con <span class="font-bold text-rose-600 dark:text-rose-400">*</span> son obligatorios.
            </p>
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center">
                <a wire:navigate href="{{ route('monitoreo.index', ['tab' => 'partos']) }}"
                   class="agro-button-secondary">
                    Cancelar
                </a>
                <button type="submit"
                        x-bind:disabled="busy || profileImageBusy || $store?.imageUploads?.busy"
                        wire:loading.attr="disabled"
                        wire:target="save,fotos,criaFoto"
                        class="agro-button">
                    <svg wire:loading.remove wire:target="save" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar Parto' : 'Guardar Parto' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
