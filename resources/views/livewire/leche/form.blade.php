<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Encabezado del Formulario --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ route('leche.index') }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-emerald-500/40 hover:bg-emerald-50/50 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-950/25 dark:hover:text-emerald-300"
           title="Volver al listado">
            <span class="sr-only">Volver</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Producción · Control Lechero</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-emerald-500/30 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-emerald-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Editar ordeño diario' : 'Registrar ordeño diario' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isEdit ? 'Actualiza la producción, las incidencias sanitarias y la evidencia del día.' : 'Registra la producción del turno y las incidencias sanitarias de cada vaca.' }}
            </p>
        </div>
    </header>

    <form wire:submit="save" autocomplete="off" x-data x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        {{-- Bloque Superior: Datos del Turno + Evidencia Fotográfica --}}
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Tarjeta 1: Datos del Turno (7 columnas en desktop) --}}
            <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white p-5 shadow-sm sm:p-6 lg:col-span-7 dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900"
                     x-data="{ currentTurno: $wire.entangle('turno').live }">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Datos del turno</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Fecha, jornada y modalidad de ordeño.</p>
                        </div>
                    </div>

                    {{-- Badge dinámico reactivo en tiempo real (0ms) --}}
                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-wider transition-all duration-200 shadow-sm"
                          :class="{
                              'border-amber-500/30 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-300': currentTurno === 'manana',
                              'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300': currentTurno === 'tarde',
                              'border-indigo-500/30 bg-indigo-50 text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/15 dark:text-indigo-300': currentTurno === 'noche',
                              'border-emerald-500/30 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300': !['manana','tarde','noche'].includes(currentTurno)
                          }">
                        <span class="h-1.5 w-1.5 rounded-full transition-colors duration-200"
                              :class="{
                                  'bg-amber-500': currentTurno === 'manana',
                                  'bg-sky-500': currentTurno === 'tarde',
                                  'bg-indigo-500': currentTurno === 'noche',
                                  'bg-emerald-500': !['manana','tarde','noche'].includes(currentTurno)
                              }"></span>
                        <span x-text="currentTurno === 'manana' ? 'Turno Mañana' : (currentTurno === 'tarde' ? 'Turno Tarde' : (currentTurno === 'noche' ? 'Turno Noche' : 'Turno Mañana'))"></span>
                    </span>
                </div>

                {{-- Inputs con altura y baseline nivelados milimétricamente --}}
                <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-3">
                    {{-- Fecha --}}
                    <div class="flex flex-col justify-start">
                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            <span>Fecha del ordeño</span>
                            <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                        </label>
                        <x-date-picker model="fecha" :max="now()->toDateString()" placeholder="dd/mm/aaaa" />
                        @error('fecha') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Turno --}}
                    <div class="flex flex-col justify-start">
                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            <span>Turno</span>
                            <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                        </label>
                        <x-filter-select model="turno" :options="['manana' => 'Mañana', 'tarde' => 'Tarde', 'noche' => 'Noche']" tone="emerald" live />
                        @error('turno') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Modalidad --}}
                    <div class="flex flex-col justify-start">
                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            <span>Modalidad</span>
                            <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                        </label>
                        <x-filter-select model="tipoRegistro" :options="['individual' => 'Individual (vaca x vaca)', 'lote' => 'Lote (resumen total)']" tone="emerald" live />
                        @error('tipoRegistro') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Banner contextual con acento de color vivo y suave --}}
                <div class="mt-5 rounded-xl border border-emerald-500/20 bg-emerald-50/60 p-3.5 text-xs text-emerald-950 dark:border-emerald-500/20 dark:bg-emerald-950/25 dark:text-emerald-200 flex items-start gap-3 shadow-sm">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-700 dark:bg-emerald-400/20 dark:text-emerald-300 text-xs font-bold">💡</span>
                    <div class="leading-relaxed">
                        @if($tipoRegistro === 'individual')
                            <p class="font-medium"><strong class="font-bold text-emerald-800 dark:text-emerald-300">Modo Individual activo:</strong> Ingresa la producción de cada vaca en la tabla inferior y reporta excepciones (secado, mastitis, etc.).</p>
                        @else
                            <p class="font-medium"><strong class="font-bold text-emerald-800 dark:text-emerald-300">Modo Lote activo:</strong> Registra la producción consolidada del turno en un solo valor total.</p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Tarjeta 2: Evidencia Fotográfica (5 columnas en desktop) --}}
            @php
                $dailyPhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
            @endphp
            <section x-data="optimizedImageUpload('foto')" class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-teal-500 bg-white p-5 shadow-sm sm:p-6 lg:col-span-5 dark:border-zinc-800 dark:border-t-teal-500 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:bg-teal-400/15 dark:text-teal-400 shadow-sm">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 5.4 12.6a1.5 1.5 0 0 1 2.12 0l3.46 3.46m.75-1.49 1.11-1.11a1.5 1.5 0 0 1 2.12 0l4.05 4.05m-15 2.24h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Foto diaria</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Evidencia compartida del turno.</p>
                        </div>
                    </div>
                    @if($existingFoto && !$removeFoto && !$foto)
                        <span class="shrink-0 rounded-full border border-teal-500/25 bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:border-teal-500/20 dark:bg-teal-500/15 dark:text-teal-300 shadow-sm">Guardada</span>
                    @endif
                </div>

                {{-- Contenedor de Imagen Proporcionado --}}
                <label for="leche-photo-input" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950/60">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Vista previa" class="absolute inset-0 h-full w-full object-contain">
                    </template>
                    <span x-show="!previewUrl" class="absolute inset-0">
                    @if($foto && !$errors->has('foto'))
                        <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa de la foto" class="absolute inset-0 h-full w-full object-cover" style="object-position: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%; transform: scale({{ $dailyPhotoFrame['zoom'] }}); transform-origin: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%;">
                        <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                            Cambiar imagen
                        </span>
                    @elseif($existingFoto && !$removeFoto)
                        <img src="{{ '/storage/'.ltrim($existingFoto, '/') }}" alt="Foto diaria actual" class="absolute inset-0 h-full w-full object-cover" style="object-position: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%; transform: scale({{ $dailyPhotoFrame['zoom'] }}); transform-origin: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%;">
                        <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">
                            Cambiar imagen
                        </span>
                    @elseif($removeFoto)
                        <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                            <svg class="mx-auto h-8 w-8 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">Eliminación preparada</p>
                            <p class="mt-0.5 text-[11px] text-zinc-500">Haz clic para cargar una nueva.</p>
                        </span>
                    @else
                        <span class="flex h-full w-full flex-col items-center justify-center p-4 text-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-zinc-200 text-teal-600 shadow-sm transition group-hover:border-teal-500 group-hover:bg-teal-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-teal-400 dark:group-hover:bg-teal-950/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </span>
                            <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Cargar evidencia fotográfica</span>
                            <span class="mt-0.5 text-[10px] text-zinc-400">Opcional · Se optimiza automáticamente</span>
                        </span>
                    @endif
                    </span>
                    @if($foto && !$errors->has('foto'))
                        <x-image-frame-editor id="daily-milking-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                    @elseif($existingFoto && !$removeFoto)
                        <x-image-frame-editor id="daily-milking-photo-frame" :src="'/storage/'.ltrim($existingFoto, '/')" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                    @endif
                    <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-teal-400" role="status">
                        <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                    </span>
                </label>

                <x-image-source-actions input-id="leche-photo-input" class="mt-3" />

                <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs font-semibold text-rose-500" role="alert"></span>
                @error('foto') <span class="mt-1.5 block text-xs font-semibold text-rose-500">{{ $message }}</span> @enderror

                @if($foto)
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-teal-600 dark:text-teal-400">Nueva foto lista</span>
                        <button type="button" wire:click="cancelPhotoChange" class="text-xs font-bold text-zinc-500 hover:text-rose-600 dark:hover:text-rose-400">Descartar</button>
                    </div>
                @elseif($existingFoto && !$removeFoto)
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-zinc-500">Foto guardada en el turno</span>
                        <button type="button" wire:click="requestPhotoRemoval" class="text-xs font-bold text-rose-600 hover:underline dark:text-rose-400">Eliminar foto</button>
                    </div>
                @elseif($removeFoto)
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-rose-600">Marcada para eliminar</span>
                        <button type="button" wire:click="cancelPhotoRemoval" class="text-xs font-bold text-zinc-500 hover:underline dark:text-zinc-300">Deshacer</button>
                    </div>
                @endif
            </section>
        </div>

        {{-- Bloque Modalidad: Lote vs Individual --}}
        @if($tipoRegistro === 'lote')
            {{-- Modo Lote --}}
            <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-sky-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-sky-500 dark:bg-zinc-900">
                <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-400/15 dark:text-sky-400 shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Resumen de producción en lote</h2>
                        <p class="mt-0.5 text-xs text-zinc-500">Consolidado general del turno.</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            Litros producidos <span class="text-emerald-600 dark:text-emerald-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" wire:model="litrosTotal"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 pr-12 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                   placeholder="0.00">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <span class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300">L</span>
                            </span>
                        </div>
                        @error('litrosTotal') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            Vacas ordeñadas <span class="text-emerald-600 dark:text-emerald-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" min="1" wire:model="cantidadVacas"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 pr-16 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                   placeholder="0">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <span class="rounded-lg bg-sky-500/10 border border-sky-500/20 px-2 py-0.5 text-xs font-bold text-sky-700 dark:bg-sky-400/15 dark:text-sky-300">vacas</span>
                            </span>
                        </div>
                        @error('cantidadVacas') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        <p class="mt-1.5 text-[11px] text-zinc-500">Se pre-completa con el número de vacas aptas para la fecha seleccionada.</p>
                    </div>
                </div>
            </section>
        @else
            {{-- Modo Individual: Tabla de Vacas con Resumen en Vivo --}}
            <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-600 bg-white shadow-sm dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200/80 bg-zinc-50/75 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900/80">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 shadow-sm">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Producción individual por vaca</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Ingresa los litros o reporta una causa de excepción.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-emerald-500/25 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ count($vacas) }} {{ count($vacas) === 1 ? 'vaca en padrón' : 'vacas en padrón' }}
                        </span>
                    </div>
                </div>

                @error('detalles')
                    <div class="m-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-bold text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-400">
                        {{ $message }}
                    </div>
                @enderror

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] border-collapse text-left">
                        <thead class="agro-record-header text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Identificación</th>
                                <th class="px-4 py-3">Raza</th>
                                <th class="w-44 px-4 py-3">Producción (L)</th>
                                <th class="w-56 px-4 py-3">Excepción</th>
                                <th class="w-56 px-4 py-3">Justificación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 text-sm dark:divide-zinc-800/60">
                            @forelse($vacas as $vaca)
                                @php
                                    $vacaId = data_get($vaca, 'id');
                                    $hasException = filled(data_get($detalles, $vacaId.'.causa_excepcion'));
                                @endphp
                                <tr wire:key="vaca-{{ $vacaId }}" class="transition hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 {{ $hasException ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex rounded-lg border border-emerald-500/25 bg-emerald-50/80 px-2 py-0.5 text-xs font-extrabold text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950/50 dark:text-emerald-300 shadow-sm">
                                                {{ data_get($vaca, 'arete') }}
                                            </span>
                                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-200">{{ data_get($vaca, 'nombre') ?: 'Sin nombre' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ data_get($vaca, 'raza', '-') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                   wire:model="detalles.{{ $vacaId }}.litros"
                                                   @disabled($hasException)
                                                   class="h-9 w-full rounded-xl border border-zinc-300 bg-white px-3 pr-8 text-xs font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600 dark:disabled:bg-zinc-900 dark:disabled:text-zinc-600"
                                                   placeholder="0.00">
                                            <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-[10px] font-bold text-emerald-600 dark:text-emerald-400">L</span>
                                        </div>
                                        @error("detalles.$vacaId.litros") <span class="mt-1 block text-[10px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-filter-select model="detalles.{{ $vacaId }}.causa_excepcion"
                                            :options="['' => 'Sin incidencia', 'secado' => 'Secado', 'mastitis' => 'Mastitis', 'enfermedad' => 'Enfermedad', 'dosificacion' => 'Dosificación', 'cria_reciente' => 'Cría reciente', 'baja_produccion' => 'Baja producción', 'otros' => 'Otros']"
                                            tone="amber" live compact />
                                        @error("detalles.$vacaId.causa_excepcion") <span class="mt-1 block text-[10px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(data_get($detalles, $vacaId.'.causa_excepcion') === 'otros')
                                            <input type="text" wire:model="detalles.{{ $vacaId }}.justificacion_otros" maxlength="1000"
                                                   class="h-9 w-full rounded-xl border border-zinc-300 bg-white px-3 text-xs text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                                   placeholder="Especifique la causa...">
                                            @error("detalles.$vacaId.justificacion_otros") <span class="mt-1 block text-[10px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                                        @else
                                            <span class="text-xs text-zinc-400 dark:text-zinc-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8">
                                        <x-empty-state title="No hay vacas aptas para ordeño"
                                                       description="Para registrar un ordeño individual, debes tener hembras bovinas activas y aptas en el inventario." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- Bloque Observaciones --}}
        <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-amber-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-amber-500 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400 shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Observaciones del turno</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Notas adicionales, clima o incidencias operativas.</p>
                </div>
            </div>
            <textarea wire:model="observaciones" rows="3" maxlength="5000"
                      class="mt-4 w-full resize-y rounded-xl border border-zinc-300 bg-white p-3 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                      placeholder="Escribe aquí cualquier observación relevante sobre este ordeño..."></textarea>
            @error('observaciones') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
        </section>

        {{-- Barra Inferior de Acciones --}}
        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-center text-xs text-zinc-500 sm:text-left">
                Campos marcados con <span class="font-bold text-emerald-600 dark:text-emerald-400">*</span> son obligatorios.
            </p>
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center">
                <a wire:navigate href="{{ route('leche.index') }}"
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
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar ordeño' : 'Guardar ordeño' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
