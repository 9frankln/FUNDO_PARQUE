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
                {{ $isEdit ? 'Editar Evento Clínico' : 'Registrar Evento Clínico' }}
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Completa los datos de la evaluación sanitaria del animal.</p>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit="save" x-data="optimizedMultiImageUpload('fotos', 3, {{ count($existingPhotos) + count($fotos) }})" x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="space-y-6">
        <!-- Datos principales -->
        <section class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 border-b border-zinc-850 pb-2">Identificación y Diagnóstico</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Animal -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <label class="block text-xs font-semibold text-zinc-400">Animales <span class="text-red-500">*</span></label>
                        <span class="text-[10px] text-zinc-500">Busca por código, nombre, tipo, especie o raza</span>
                    </div>
                    <x-animal-multi-select :options="$animales" :max="$isEdit ? 1 : null" />
                    @error('animalIds') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    @error('animalIds.*') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Fecha Evento -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Fecha del Diagnóstico <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fechaEvento"
                           class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 focus:border-emerald-500/50 focus:ring-emerald-500/20 text-zinc-100 text-sm transition outline-none">
                    @error('fechaEvento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Clasificacion -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Clasificación <span class="text-red-500">*</span></label>
                    <x-filter-select model="clasificacion" :options="['enfermedad_infecciosa' => 'Infecciosa (Fiebre aftosa, etc.)', 'trastorno_metabolico' => 'Metabólica (Timpanismo, cetosis, etc.)', 'lesion_accidente' => 'Lesión / Accidente']" tone="rose" />
                    @error('clasificacion') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Estado Clinico -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Estado Clínico <span class="text-red-500">*</span></label>
                    <x-filter-select model="estadoClinico" :options="['en_tratamiento' => 'En tratamiento (Activo)', 'recuperada' => 'Recuperada (Alta)', 'cuarentena' => 'Cuarentena (Aislamiento - Auto Alerta en 7 días)', 'critico' => 'Crítico (Bajo observación estricta)', 'baja' => 'Baja (Muerte / Descarte sanitario)']" tone="rose" />
                    @error('estadoClinico') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Sintomas Diagnostico -->
            <div>
                <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Síntomas y Diagnóstico <span class="text-red-500">*</span></label>
                <textarea wire:model="sintomasDiagnostico" rows="3"
                          class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                          placeholder="Ej: Fiebre alta, decaimiento, lagrimeo continuo. Sospecha de parásitos."></textarea>
                @error('sintomasDiagnostico') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tratamiento -->
            <div>
                <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Tratamiento Planificado</label>
                <textarea wire:model="tratamiento" rows="3"
                          class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                          placeholder="Ej: Aislamiento temporal en galpón 2. Baño antiparasitario."></textarea>
                @error('tratamiento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </section>

        <!-- Medicación -->
        <section class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 border-b border-zinc-850 pb-2">Medicación Aplicada <span class="text-xs font-normal lowercase tracking-normal text-zinc-500">(opcional)</span></h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Medicamento Nombre -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Nombre del Medicamento</label>
                    <input type="text" wire:model="medicamentoNombre"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: Ivermectina 1%, Oxitetraciclina LA...">
                    @error('medicamentoNombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Dosis y Via -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5">Dosis y Vía de Administración</label>
                    <input type="text" wire:model="dosisVia"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Ej: 5ml Vía Subcutánea, 2 comprimidos Oral...">
                    @error('dosisVia') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <x-record-photo-upload title="Fotos clínicas" :existing-photos="$existingPhotos" :new-photos="$fotos" :new-frames="$fotoEncuadres" />

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
