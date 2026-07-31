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
                {{ $isEdit ? 'Editar Profilaxis' : 'Registrar Profilaxis / Vacuna' }}
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Completa los campos para asentar la intervención veterinaria preventiva.</p>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit="save" x-data="optimizedMultiImageUpload('fotos', 3, {{ count($existingPhotos) + count($fotos) }})" x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="space-y-6">
        <!-- Datos de la intervención -->
        <section class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 border-b border-zinc-850 pb-2">Datos de la Intervención</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Animales tratados <span class="text-red-500">*</span></label>
                    <x-animal-multi-select :options="$animales" model="selectedAnimals" />
                    <p class="mt-1 text-[10px] text-zinc-500">Busca por código, nombre, clasificación, especie o raza.</p>
                    @error('selectedAnimals') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    @error('selectedAnimals.*') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo Intervencion -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Tipo de Intervención <span class="text-red-500">*</span></label>
                    <x-filter-select model="tipoIntervencion" :options="['vacuna' => '💉 Vacuna (Inmunización)', 'desparasitante_interno' => '💊 Desparasitante Interno', 'desparasitante_externo' => '🧼 Desparasitante Externo', 'vitamina' => '🍊 Vitamina / Reconstituyente']" tone="amber" />
                    @error('tipoIntervencion') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Fecha Aplicacion -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Fecha de Aplicación <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fechaAplicacion"
                           class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 focus:border-emerald-500/50 focus:ring-emerald-500/20 text-zinc-100 text-sm transition outline-none">
                    @error('fechaAplicacion') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Producto Marca -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Producto / Marca Comercial <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="productoMarca"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: Ivermectina 1%">
                    @error('productoMarca') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Propósito -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Propósito / Destino</label>
                    <input type="text" wire:model="proposito"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: Control anual de garrapatas">
                    @error('proposito') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Dosis -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Dosis Aplicada</label>
                    <input type="text" wire:model="dosis"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: 2 ml por cabeza">
                    @error('dosis') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Responsable -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Veterinario / Responsable</label>
                    <input type="text" wire:model="responsable"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: Dr. Manuel Torres">
                    @error('responsable') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            <!-- Observaciones -->
            <div>
                <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Observaciones</label>
                <textarea wire:model="observaciones" rows="3"
                          class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                          placeholder="Reacciones observadas, detalles del lote del producto, etc..."></textarea>
                @error('observaciones') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </section>

        <!-- Dose schedule -->
        <section class="space-y-4 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
            <div class="flex flex-col gap-3 border-b border-zinc-850 pb-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Calendario de dosis</h3>
                    <p class="mt-1 text-xs text-zinc-500">La aplicación registrada corresponde a Dosis 1. Agrega solo fechas futuras necesarias.</p>
                </div>
                <button type="button" wire:click="addDoseDate"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 text-xs font-bold text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/15">
                    <span class="text-lg leading-none">+</span> Agregar otra dosis
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-500/25 dark:bg-emerald-500/[.07]">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Dosis 1 · Aplicada</span>
                    <span class="mt-1 block text-sm font-bold text-emerald-950 dark:text-emerald-100">{{ $fechaAplicacion ? \Carbon\Carbon::parse($fechaAplicacion)->format('d/m/Y') : 'Define fecha de aplicación' }}</span>
                </div>

                @foreach($dosisProgramadas as $index => $dose)
                    <div wire:key="scheduled-dose-{{ $index }}" class="rounded-xl border border-amber-200 bg-amber-50/65 p-3 dark:border-amber-500/25 dark:bg-amber-500/[.06]">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Dosis {{ $index + 2 }}</label>
                            <button type="button" wire:click="removeDoseDate({{ $index }})" class="flex h-7 w-7 items-center justify-center rounded-lg text-rose-600 transition hover:bg-rose-100 dark:text-rose-400 dark:hover:bg-rose-500/10" aria-label="Quitar dosis {{ $index + 2 }}">&times;</button>
                        </div>
                        <input type="date" wire:model="dosisProgramadas.{{ $index }}.fecha"
                               class="w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-amber-500/30 dark:bg-slate-950 dark:text-slate-100">
                        @error('dosisProgramadas.'.$index.'.fecha') <span class="mt-1 block text-[10px] text-red-500">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>

            @if($dosisProgramadas === [])
                <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 py-5 text-center text-xs text-zinc-500 dark:border-zinc-700 dark:bg-zinc-950/40">
                    Dosis única. Usa “Agregar otra dosis” cuando exista una siguiente aplicación.
                </div>
            @endif
            @error('dosisProgramadas') <span class="block text-xs text-red-500">{{ $message }}</span> @enderror
        </section>

        <x-record-photo-upload title="Fotos de la intervención" :existing-photos="$existingPhotos" :new-photos="$fotos" :new-frames="$fotoEncuadres" />

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('monitoreo.index') }}"
               class="px-5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-200 text-sm font-semibold transition">
                Cancelar
            </a>
            <button type="submit" x-bind:disabled="busy || $store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,fotos"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-zinc-950 font-bold text-sm transition shadow-lg shadow-emerald-500/10 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="save">&#x1F4BE; {{ $isEdit ? 'Actualizar' : 'Registrar' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
