<div class="mx-auto w-full max-w-6xl space-y-6">
    {{-- Header --}}
    <header class="flex items-start gap-4">
        <a wire:navigate href="{{ route('animal.index') }}"
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Inventario · Animales</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-emerald-500/30 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-emerald-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nuevo registro' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                {{ $isEdit ? 'Editar Animal' : 'Registrar Nuevo Animal' }}
            </h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isEdit ? 'Actualiza la ficha técnica, genealogía y datos sanitarios del animal.' : 'Completa la ficha técnica para ingresarlo al inventario y padrón.' }}
            </p>
        </div>
    </header>

    <!-- Form Layout -->
    <form wire:submit="save" autocomplete="off" x-data x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()" class="space-y-6">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            {{-- Columna Principal (7 columnas en desktop) --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Sección 1: Identificación --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Identificación y raza</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Selecciona tipo y raza. El código se estructura automáticamente.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        {{-- Tipo de Animal --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Tipo de animal</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select model="especieId" :options="['' => 'Selecciona tipo'] + collect($especies)->pluck('nombre', 'id')->all()" tone="emerald" live />
                            @error('especieId') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Raza --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Raza</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select wire:key="raza-select-{{ $especieId ?: 'empty' }}" model="razaId" :options="['' => 'Selecciona raza'] + collect($razas)->pluck('nombre', 'id')->all()" tone="emerald" :disabled="empty($especieId)" />
                            @error('razaId') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Código --}}
                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Código del animal (Arete)</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <div class="grid grid-cols-[minmax(0,1fr)_6rem] overflow-hidden rounded-xl border border-zinc-300 bg-white transition focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950">
                                <div class="flex h-11 items-center border-r border-zinc-200 bg-zinc-50/80 px-4 text-sm font-black tracking-wider text-emerald-800 dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-emerald-300">
                                    {{ $codigoPrefijo && $codigoAnio ? strtoupper($codigoPrefijo).str_pad((string) ((int) $codigoAnio % 100), 2, '0', STR_PAD_LEFT).'-' : 'TIPO-AÑO-' }}
                                </div>
                                <input type="text" inputmode="numeric" maxlength="3" wire:model.blur="codigoNumero"
                                       class="h-11 border-0 bg-transparent px-3 text-center font-mono text-sm font-black tracking-widest text-zinc-900 outline-none focus:ring-0 dark:text-zinc-100"
                                       placeholder="001" @disabled(!$codigoPrefijo)>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                                <span class="text-zinc-500">Prefijo y año fijados. Editable: últimos 3 dígitos correlativos.</span>
                                <span class="rounded-md bg-emerald-500/10 px-2 py-0.5 font-bold text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300">{{ $this->codigoPreview }}</span>
                            </div>
                            @error('codigoNumero') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nombre --}}
                        <div class="flex flex-col justify-start sm:col-span-2">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Nombre o alias</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input type="text" wire:model="nombre" maxlength="100"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                   placeholder="Ej: Estrella">
                            @error('nombre') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                {{-- Sección 2: Clasificación y Edad --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-sky-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-sky-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-400/15 dark:text-sky-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Clasificación, edad y peso</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Sexo, ingreso y cronología del animal.</p>
                        </div>
                    </div>

                    {{-- Sexo --}}
                    <div class="mt-5">
                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            <span>Sexo biológico</span>
                            <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border font-bold text-xs transition {{ $genero === 'hembra' ? 'border-pink-500/40 bg-pink-50 text-pink-700 dark:border-pink-500/30 dark:bg-pink-500/15 dark:text-pink-300 shadow-sm' : 'border-zinc-300 bg-white text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400' }}">
                                <input type="radio" wire:model.live="genero" value="hembra" class="sr-only">
                                <span class="text-base">&#x2640;</span> Hembra
                            </label>
                            <label class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border font-bold text-xs transition {{ $genero === 'macho' ? 'border-sky-500/40 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300 shadow-sm' : 'border-zinc-300 bg-white text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400' }}">
                                <input type="radio" wire:model.live="genero" value="macho" class="sr-only">
                                <span class="text-base">&#x2642;</span> Macho
                            </label>
                        </div>
                        @error('genero') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-4 grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        {{-- Procedencia --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Procedencia</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            <x-filter-select model="tipoAlta" :options="['compra' => 'Compra', 'parto' => 'Nacimiento / parto', 'donacion' => 'Donación', 'traslado' => 'Traslado', 'prestamo' => 'Préstamo']" tone="sky" live />
                            @error('tipoAlta') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fecha de Alta/Nacimiento --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>{{ $this->admissionDateLabel }}</span>
                                <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                            </label>
                            @if($tipoAlta === 'parto')
                                <x-date-picker model="fechaNacimiento" :max="now()->toDateString()" placeholder="dd/mm/aaaa" />
                                @error('fechaNacimiento') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            @else
                                <x-date-picker model="fechaAlta" :max="now()->toDateString()" placeholder="dd/mm/aaaa" />
                                @error('fechaAlta') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        {{-- Precio de Compra (si aplica) --}}
                        @if($tipoAlta === 'compra')
                            <div class="flex flex-col justify-start sm:col-span-2">
                                <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    <span>Precio de compra</span>
                                    <span class="ml-1 font-bold text-emerald-600 dark:text-emerald-400">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-xs font-bold text-emerald-700 dark:text-emerald-400">S/</span>
                                    <input type="number" min="0.01" max="9999999999.99" step="0.01" wire:model="precioCompra"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white py-2 pl-10 pr-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                           placeholder="2850.00">
                                </div>
                                <p class="mt-1 text-[11px] text-zinc-500">Monto pagado en soles registrado como egreso en finanzas.</p>
                                @error('precioCompra') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Edad Estimada (si no es parto) --}}
                        @if($tipoAlta !== 'parto')
                            <div class="sm:col-span-2 -mb-2 mt-1">
                                <p class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Edad estimada al ingreso</p>
                                <p class="text-[11px] text-zinc-500">Completa años y/o meses iniciales para calcular la fecha aproximada de nacimiento.</p>
                            </div>
                            <div class="flex flex-col justify-start">
                                <label class="mb-2 flex h-5 items-center text-xs font-semibold text-zinc-600 dark:text-zinc-400">Años</label>
                                <input type="number" min="0" max="100" wire:model.live.debounce.250ms="edadEstimadaAnios"
                                       x-on:focus="if ($el.value === '0') { $el.value = ''; $el.dispatchEvent(new Event('input', { bubbles: true })) }"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                       placeholder="0">
                                @error('edadEstimadaAnios') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex flex-col justify-start">
                                <label class="mb-2 flex h-5 items-center text-xs font-semibold text-zinc-600 dark:text-zinc-400">Meses adicionales</label>
                                <input type="number" min="0" max="11" wire:model.live.debounce.250ms="edadEstimadaMeses"
                                       x-on:focus="if ($el.value === '0') { $el.value = ''; $el.dispatchEvent(new Event('input', { bubbles: true })) }"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                       placeholder="0">
                                @error('edadEstimadaMeses') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Peso --}}
                        <div class="flex flex-col justify-start">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>{{ $this->weightLabel }}</span>
                            </label>
                            <div class="relative">
                                <input type="number" min="0" step="0.01" wire:model="peso"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 pr-12 text-sm font-semibold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                                       placeholder="0.00">
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">kg</span>
                                </span>
                            </div>
                            @error('peso') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Estado Reproductivo (si es hembra apta) --}}
                        @if($genero === 'hembra' && (!$this->showMilkingOption || $this->canEnableMilking))
                            <div class="flex flex-col justify-start">
                                <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    <span>Estado reproductivo</span>
                                </label>
                                <x-filter-select model="estadoReproductivo" :options="['' => 'Sin registrar'] + \App\Models\Animal::REPRODUCTIVE_STATES" tone="amber" />
                                @error('estadoReproductivo') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    {{-- Tarjeta de Preview de Clasificación & Edad --}}
                    <div class="mt-5 grid grid-cols-1 items-center gap-4 rounded-xl border border-emerald-500/20 bg-emerald-50/60 p-4 dark:border-emerald-500/20 dark:bg-emerald-950/25 sm:grid-cols-[auto_1fr]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 text-2xl font-black text-white shadow-sm dark:bg-emerald-500 dark:text-zinc-950">
                            {{ $genero === 'hembra' ? '♀' : '♂' }}
                        </div>
                        <div>
                            <div class="text-sm font-extrabold text-emerald-950 dark:text-emerald-100">{{ $this->clasificacionPreview }}</div>
                            <div class="mt-0.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">{{ $this->edadPreview }}</div>
                            @if($this->denticionPreview)
                                <div class="mt-1 text-[11px] font-medium text-amber-800 dark:text-amber-300">🦷 Dentición estimada: {{ $this->denticionPreview }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Checkboxes de Estado --}}
                    <div class="mt-5 flex flex-col gap-4 border-t border-zinc-100 pt-4 dark:border-zinc-800 sm:flex-row">
                        @if($this->showMilkingOption)
                            <label class="flex items-center gap-3 {{ $this->canEnableMilking ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}">
                                <input type="checkbox" wire:model="aptaOrdeno" class="agro-checkbox h-5 w-5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500/20 dark:border-zinc-700" @disabled(!$this->canEnableMilking)>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Apta para ordeño</div>
                                    <div class="text-[10px] text-zinc-500">{{ $this->milkingEligibilityMessage }}</div>
                                </div>
                            </label>
                        @endif
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="activo" class="agro-checkbox h-5 w-5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500/20 dark:border-zinc-700">
                            <div class="text-left">
                                <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Animal activo</div>
                                <div class="text-[10px] text-zinc-500">Disponible para reproducción, ordeño o engorde</div>
                            </div>
                        </label>
                    </div>
                </section>

                {{-- Sección 3: Observaciones --}}
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-amber-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-amber-500 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400 shadow-sm">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Observaciones y notas</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Anotaciones clínicas, señas particulares o procedencia.</p>
                        </div>
                    </div>
                    <textarea wire:model="observaciones" rows="3" maxlength="5000"
                              class="mt-4 w-full resize-y rounded-xl border border-zinc-300 bg-white p-3 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"
                              placeholder="Escribe aquí cualquier observación relevante sobre el animal..."></textarea>
                    @error('observaciones') <span class="mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                </section>
            </div>

            {{-- Columna Lateral: Foto del Animal (5 columnas en desktop) --}}
            @php
                $animalPhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
            @endphp
            <div x-data="optimizedImageUpload" class="space-y-6 lg:col-span-5">
                <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-teal-500 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:border-t-teal-500 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:bg-teal-400/15 dark:text-teal-400 shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 5.4 12.6a1.5 1.5 0 0 1 2.12 0l3.46 3.46m.75-1.49 1.11-1.11a1.5 1.5 0 0 1 2.12 0l4.05 4.05m-15 2.24h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Foto del animal</h2>
                                <p class="mt-0.5 text-xs text-zinc-500">Ficha fotográfica optimizada.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-teal-500/25 bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:border-teal-500/20 dark:bg-teal-500/15 dark:text-teal-300 shadow-sm">
                            {{ $existingFoto && !$removeFoto && !$foto ? 'Guardada' : 'Opcional' }}
                        </span>
                    </div>

                    {{-- Contenedor de Imagen Proporcionado --}}
                    <label for="animal-photo-input" class="group relative mt-4 flex aspect-[16/9] w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 transition hover:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950/60">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Vista previa de foto nueva" class="absolute inset-0 h-full w-full object-contain">
                        </template>

                        <span x-show="!previewUrl" class="absolute inset-0">
                            @if($foto && !$errors->has('foto'))
                                <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa de foto nueva" class="h-full w-full object-cover" style="object-position: {{ $animalPhotoFrame['x'] }}% {{ $animalPhotoFrame['y'] }}%; transform: scale({{ $animalPhotoFrame['zoom'] }}); transform-origin: {{ $animalPhotoFrame['x'] }}% {{ $animalPhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar imagen</span>
                            @elseif($existingFoto && !$removeFoto)
                                <img src="{{ '/storage/'.ltrim($existingFoto, '/') }}" alt="Foto actual del animal" class="h-full w-full object-cover" style="object-position: {{ $animalPhotoFrame['x'] }}% {{ $animalPhotoFrame['y'] }}%; transform: scale({{ $animalPhotoFrame['zoom'] }}); transform-origin: {{ $animalPhotoFrame['x'] }}% {{ $animalPhotoFrame['y'] }}%;">
                                <span class="absolute inset-x-0 bottom-0 bg-zinc-950/80 px-3 py-2 text-center text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">Cambiar imagen</span>
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
                                    <span class="mt-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">Cargar foto del animal</span>
                                    <span class="mt-0.5 text-[10px] text-zinc-400">Opcional · Se optimiza automáticamente</span>
                                </span>
                            @endif
                        </span>

                        @if($foto && !$errors->has('foto'))
                            <x-image-frame-editor id="animal-photo-frame" :src="$foto->temporaryUrl()" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @elseif($existingFoto && !$removeFoto)
                            <x-image-frame-editor id="animal-photo-frame" :src="'/storage/'.ltrim($existingFoto, '/')" x-model="fotoEncuadre.x" y-model="fotoEncuadre.y" zoom-model="fotoEncuadre.zoom" />
                        @endif

                        <span x-cloak x-show="busy" class="absolute inset-x-0 bottom-0 z-30 bg-zinc-950/90 p-2.5 text-center text-xs font-bold text-teal-400" role="status">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                        </span>
                    </label>

                    <x-image-source-actions input-id="animal-photo-input" class="mt-3" />

                    <span x-cloak x-show="clientError" x-text="clientError" class="mt-1.5 block text-xs font-semibold text-rose-500" role="alert"></span>
                    @error('foto') <span class="mt-1.5 block text-xs font-semibold text-rose-500">{{ $message }}</span> @enderror

                    @if($foto)
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-teal-600 dark:text-teal-400">Nueva foto lista</span>
                            <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()" class="text-xs font-bold text-zinc-500 hover:text-rose-600 dark:hover:text-rose-400">Descartar</button>
                        </div>
                    @elseif($existingFoto && !$removeFoto)
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-medium text-zinc-500">Foto guardada</span>
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
        </div>

        {{-- Barra Inferior de Acciones --}}
        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-zinc-200/90 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-center text-xs text-zinc-500 sm:text-left">
                Campos marcados con <span class="font-bold text-emerald-600 dark:text-emerald-400">*</span> son obligatorios.
            </p>
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center">
                <a wire:navigate href="{{ route('animal.index') }}"
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
                    <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar animal' : 'Guardar animal' }}</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </form>
</div>
