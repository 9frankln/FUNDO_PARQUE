<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex items-start gap-4">
        <a href="{{ route('leche.index') }}"
           class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-400 transition duration-200 hover:border-zinc-700 hover:bg-zinc-800 hover:text-zinc-100">
            <span class="sr-only">Volver</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="agro-title text-2xl font-extrabold tracking-tight sm:text-3xl">
                {{ $isEdit ? 'Editar ordeño diario' : 'Registrar ordeño diario' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-400">
                {{ $isEdit ? 'Actualiza la producción, las incidencias y la evidencia del día.' : 'Registra la producción del turno y las incidencias de cada vaca.' }}
            </p>
        </div>
    </div>

    <form wire:submit="save" x-data x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6 lg:col-span-2">
                <div class="border-b border-zinc-800 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Datos del turno</h2>
                    <p class="mt-1 text-xs text-zinc-500">Fecha, jornada y modalidad del registro.</p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Fecha del ordeño</label>
                        <input type="date" wire:model.live="fecha" max="{{ now()->toDateString() }}"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/10">
                        @error('fecha') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Turno</label>
                        <x-filter-select model="turno" :options="['manana' => 'Mañana', 'tarde' => 'Tarde', 'noche' => 'Noche']" tone="emerald" />
                        @error('turno') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2 md:col-span-1">
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Modalidad</label>
                        <x-filter-select model="tipoRegistro" :options="['individual' => 'Individual, vaca por vaca', 'lote' => 'Lote, resumen grupal']" tone="emerald" live />
                        @error('tipoRegistro') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            @php
                $dailyPhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
            @endphp
            <section x-data="optimizedImageUpload('foto')" class="rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3 border-b border-zinc-800 pb-3">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Foto diaria</h2>
                        <p class="mt-1 text-xs text-zinc-500">Evidencia compartida por fecha.</p>
                    </div>
                    @if($existingFoto && !$removeFoto && !$foto)
                        <span class="shrink-0 rounded-full border border-zinc-800 bg-zinc-950 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Existente</span>
                    @endif
                </div>

                <label for="leche-photo-input" class="group relative mt-5 flex aspect-[4/3] w-full cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50 transition hover:border-emerald-500 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:hover:border-emerald-400 lg:aspect-square">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Vista previa de la nueva foto" class="absolute inset-0 h-full w-full bg-zinc-100 object-contain dark:bg-zinc-950">
                    </template>
                    <div x-show="!previewUrl" class="absolute inset-0">
                    @if($foto && !$errors->has('foto'))
                        <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa de la nueva foto" class="absolute inset-0 h-full w-full bg-zinc-100 object-cover dark:bg-zinc-950" style="object-position: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%; transform: scale({{ $dailyPhotoFrame['zoom'] }}); transform-origin: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%;">
                        <span class="absolute left-3 top-3 z-10 rounded-lg bg-zinc-950/80 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">
                            Nueva imagen
                        </span>
                    @elseif($existingFoto && !$removeFoto)
                        <img src="{{ '/storage/'.ltrim($existingFoto, '/') }}" alt="Foto diaria actual" class="absolute inset-0 h-full w-full bg-zinc-100 object-cover dark:bg-zinc-950" style="object-position: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%; transform: scale({{ $dailyPhotoFrame['zoom'] }}); transform-origin: {{ $dailyPhotoFrame['x'] }}% {{ $dailyPhotoFrame['y'] }}%;">
                        <span class="absolute left-3 top-3 z-10 rounded-lg bg-zinc-950/80 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">
                            Foto actual
                        </span>
                    @elseif($removeFoto)
                        <div class="px-5 text-center">
                            <svg class="mx-auto h-10 w-10 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <p class="mt-3 text-sm font-semibold text-[#9f1239] dark:text-[#fda4af]">Eliminación preparada</p>
                            <p class="mt-1 text-xs text-[#5f6f66] dark:text-[#b7cfc0]">Clic para elegir una imagen nueva.</p>
                        </div>
                    @else
                        <div class="px-5 text-center">
                            <svg class="mx-auto h-10 w-10 text-emerald-700 transition group-hover:scale-105 dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.379a2.25 2.25 0 0 0 1.59-.659l.622-.622A2.25 2.25 0 0 1 10.432 4h3.136a2.25 2.25 0 0 1 1.591.659l.622.622A2.25 2.25 0 0 0 17.371 6h1.379A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.375a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
                            </svg>
                            <p class="mt-3 text-sm font-semibold text-[#183c2c] dark:text-[#ecfdf5]">Cargar foto diaria</p>
                            <p class="mt-1 text-xs text-[#567365] dark:text-[#a7c9b5]">JPG, PNG o WebP · compresión automática</p>
                        </div>
                    @endif
                    </div>
                    @if($foto && !$errors->has('foto'))
                        <x-image-frame-editor id="daily-milking-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                    @elseif($existingFoto && !$removeFoto)
                        <x-image-frame-editor id="daily-milking-photo-frame" :src="'/storage/'.ltrim($existingFoto, '/')" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                    @endif
                    <div x-cloak x-show="busy" class="absolute inset-x-3 bottom-3 z-30 rounded-lg bg-zinc-950/90 px-3 py-2 text-xs font-semibold text-white shadow-lg" role="status" aria-live="polite">
                        <span x-text="processing ? 'Comprimiendo imagen...' : `Subiendo ${progress}%`"></span>
                    </div>
                </label>

                <x-image-source-actions input-id="leche-photo-input" class="mt-3" />

                <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs text-red-500" role="alert"></span>
                @error('foto') <span class="mt-1.5 block text-xs text-red-500">{{ $message }}</span> @enderror

                @if($foto && $photoConfirmed)
                    <p class="mt-3 text-center text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                        Imagen nueva preparada. Se aplicará al actualizar el ordeño.
                    </p>
                @elseif($existingFoto && !$removeFoto)
                    <p class="mt-3 text-center text-xs font-medium text-[#496357] dark:text-[#a8c2b2]">
                        Imagen actual sin filtros. Clic sobre ella para reemplazarla.
                    </p>
                @endif

                <div class="mt-4 grid gap-2">
                    @if($foto)
                        <button type="button" wire:click="cancelPhotoChange"
                                class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-300">
                            Descartar imagen nueva
                        </button>
                    @elseif($existingFoto && !$removeFoto)
                        <button type="button" wire:click="requestPhotoRemoval"
                                class="inline-flex items-center justify-center rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:border-rose-400 hover:bg-rose-100 dark:border-rose-500/30 dark:bg-transparent dark:text-rose-400 dark:hover:bg-rose-500/10">
                            Eliminar imagen
                        </button>
                    @elseif($removeFoto)
                        <button type="button" wire:click="cancelPhotoRemoval"
                                class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-300">
                            Deshacer eliminación
                        </button>
                    @endif
                </div>

                <div class="mt-4 rounded-xl border border-amber-500/25 bg-amber-50 p-3 text-xs leading-5 text-amber-800 dark:bg-amber-500/5 dark:text-amber-200/80">
                    @if($existingFoto)
                        La foto actual se conserva hasta pulsar <strong>{{ $isEdit ? 'Actualizar ordeño' : 'Guardar ordeño' }}</strong>. Antes puedes reemplazarla, eliminarla o deshacer la acción.
                    @else
                        La imagen es opcional y solo se almacenará al pulsar <strong>{{ $isEdit ? 'Actualizar ordeño' : 'Guardar ordeño' }}</strong>.
                    @endif
                </div>
            </section>
        </div>

        @if($tipoRegistro === 'lote')
            <section class="rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
                <div class="border-b border-zinc-800 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Resumen de producción en lote</h2>
                    <p class="mt-1 text-xs text-zinc-500">Consolidado total del turno.</p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Litros producidos <span class="text-emerald-700 dark:text-emerald-400">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" wire:model="litrosTotal"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 pr-12 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/10"
                                   placeholder="245.50">
                            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-xs font-semibold text-emerald-700 dark:text-emerald-400">L</span>
                        </div>
                        @error('litrosTotal') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Vacas ordeñadas <span class="text-emerald-700 dark:text-emerald-400">*</span></label>
                        <input type="number" min="1" wire:model="cantidadVacas"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/10"
                               placeholder="18">
                        @error('cantidadVacas') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-zinc-800/80 bg-zinc-900">
                <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Producción individual</h2>
                        <p class="mt-1 text-xs text-zinc-500">Registra litros o una incidencia para cada vaca.</p>
                    </div>
                    <span class="w-fit rounded-full border border-zinc-800 bg-zinc-950 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                        {{ count($vacas) }} {{ count($vacas) === 1 ? 'vaca' : 'vacas' }}
                    </span>
                </div>

                @error('detalles')
                    <div class="mx-5 mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-400 sm:mx-6 sm:mb-6">{{ $message }}</div>
                @enderror

                <div class="overflow-x-auto border-t border-zinc-800">
                    <table class="w-full min-w-[940px] border-collapse text-left">
                        <thead>
                            <tr class="border-b border-zinc-800 bg-zinc-950 text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-500">
                                <th class="px-5 py-4 sm:px-6">Identificación</th>
                                <th class="px-4 py-4">Raza</th>
                                <th class="w-48 px-4 py-4">Producción</th>
                                <th class="w-64 px-4 py-4">Excepción</th>
                                <th class="w-64 px-5 py-4 sm:px-6">Justificación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-850/60">
                            @forelse($vacas as $vaca)
                                @php
                                    $vacaId = data_get($vaca, 'id');
                                @endphp
                                <tr wire:key="vaca-{{ $vacaId }}" class="transition duration-200 hover:bg-zinc-850/20">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-bold text-zinc-100">{{ data_get($vaca, 'arete') }}</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">{{ data_get($vaca, 'nombre') ?: 'Sin nombre' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium text-zinc-400">{{ data_get($vaca, 'raza.nombre', '-') }}</td>
                                    <td class="px-4 py-4 align-top">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                   wire:model="detalles.{{ $vacaId }}.litros"
                                                   @disabled(filled(data_get($detalles, $vacaId.'.causa_excepcion')))
                                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 pr-9 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/10 disabled:cursor-not-allowed disabled:opacity-35"
                                                   placeholder="0.00">
                                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">L</span>
                                        </div>
                                        @error("detalles.$vacaId.litros") <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <x-filter-select model="detalles.{{ $vacaId }}.causa_excepcion"
                                            :options="['' => 'Sin incidencia', 'secado' => 'Secado', 'mastitis' => 'Mastitis', 'enfermedad' => 'Enfermedad', 'dosificacion' => 'Dosificación', 'cria_reciente' => 'Cría reciente', 'baja_produccion' => 'Baja producción', 'otros' => 'Otros']"
                                            tone="amber" live compact />
                                        @error("detalles.$vacaId.causa_excepcion") <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-5 py-4 align-top sm:px-6">
                                        @if(data_get($detalles, $vacaId.'.causa_excepcion') === 'otros')
                                            <input type="text" wire:model="detalles.{{ $vacaId }}.justificacion_otros" maxlength="1000"
                                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/10"
                                                   placeholder="Especifique la causa">
                                            @error("detalles.$vacaId.justificacion_otros") <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                        @else
                                            <span class="inline-flex rounded-lg bg-zinc-950/60 px-3 py-2 text-xs text-zinc-500">No aplica</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-14 text-center">
                                        <p class="font-semibold text-zinc-300">No hay vacas disponibles para ordeño.</p>
                                        <p class="mt-1 text-sm text-zinc-500">Activa una hembra bovina apta para ordeño en el inventario.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
            <div class="border-b border-zinc-800 pb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Observaciones</h2>
            </div>
            <textarea wire:model="observaciones" rows="4" maxlength="5000"
                      class="mt-4 w-full resize-y rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm leading-6 text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/10"
                      placeholder="Anomalías, condiciones del ordeño o notas del turno..."></textarea>
            @error('observaciones') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
        </section>

        <div class="flex flex-col-reverse gap-4 border-t border-zinc-800 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-center text-xs text-zinc-500 sm:text-left">Los campos marcados con * son obligatorios.</p>
            <div class="flex flex-col-reverse gap-3 sm:flex-row">
                <a href="{{ route('leche.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-red-500/30 bg-red-50 px-5 py-3 text-sm font-semibold text-red-700 transition hover:border-red-500/50 hover:bg-red-100 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-500/10">
                    Cancelar
                </a>
                <button type="submit" x-bind:disabled="$store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,foto"
                        class="inline-flex min-w-48 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-3 text-sm font-bold text-zinc-950 shadow-lg shadow-emerald-500/10 transition hover:from-emerald-400 hover:to-teal-400 disabled:cursor-wait disabled:opacity-60">
                    <svg wire:loading.remove wire:target="save" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar ordeño' : 'Guardar ordeño' }}</span>
                    <span wire:loading wire:target="save" class="h-5 w-5 animate-spin rounded-full border-2 border-zinc-950 border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
