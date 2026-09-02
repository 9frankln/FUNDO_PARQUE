<div class="mx-auto max-w-7xl space-y-6">
    <header class="flex items-start gap-4">
        <a href="{{ route('monitoreo.index') }}" wire:navigate
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-teal-500/40 hover:bg-teal-50/50 hover:text-teal-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-teal-500/40 dark:hover:bg-teal-950/25 dark:hover:text-teal-300"
           aria-label="Volver a monitoreo">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" /></svg>
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600 dark:bg-teal-400/15 dark:text-teal-400 shadow-sm">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M12 3c-1.55 1.2-3.54 1.5-5.5 1.5v6.75c0 4.72 2.76 7.86 5.5 9.75 2.74-1.89 5.5-5.03 5.5-9.75V4.5C15.54 4.5 13.55 4.2 12 3Z" />
                    </svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal-600 dark:text-teal-400">Monitoreo · Sanidad</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEdit ? 'border-sky-500/30 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300' : 'border-teal-500/30 bg-teal-50 text-teal-700 dark:border-teal-500/30 dark:bg-teal-500/15 dark:text-teal-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdit ? 'bg-sky-500' : 'bg-teal-500' }}"></span>
                    {{ $isEdit ? 'Modo edición' : 'Nueva atención' }}
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">{{ $isEdit ? 'Editar atención de salud' : 'Nueva atención de salud' }}</h1>
            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">Elige el motivo y la ficha mostrará únicamente los datos que corresponden.</p>
        </div>
    </header>

    <form wire:submit="save"
          autocomplete="off"
          x-data="optimizedMultiImageUpload('fotos', 3, {{ count($existingPhotos) + count($fotos) }})"
          x-on:submit="if ($store?.imageUploads?.busy) $event.preventDefault()">
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_19rem]">
            <main class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-teal-500 bg-white shadow-sm dark:border-zinc-800 dark:border-t-teal-500 dark:bg-zinc-900">
                <section class="space-y-5 p-5 sm:p-6">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:bg-teal-400/15 dark:text-teal-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M12 3c-1.55 1.2-3.54 1.5-5.5 1.5v6.75c0 4.72 2.76 7.86 5.5 9.75 2.74-1.89 5.5-5.03 5.5-9.75V4.5C15.54 4.5 13.55 4.2 12 3Z" /></svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">Animal y motivo</h2>
                            <p class="mt-0.5 text-xs text-zinc-500">Los únicos datos obligatorios para iniciar.</p>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex h-5 items-center justify-between gap-3">
                            <label class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Animal{{ $isEdit ? '' : '(es)' }} <span class="font-bold text-teal-600 dark:text-teal-400">*</span></label>
                            <span class="text-[10px] text-zinc-500">Busca por código, nombre o tipo</span>
                        </div>
                        <x-animal-multi-select :options="$animales" :max="$isEdit ? 1 : null" />
                        @error('animalIds') <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        @error('animalIds.*') <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="w-full sm:w-48">
                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Fecha <span class="font-bold text-teal-600 dark:text-teal-400">*</span></label>
                        <x-date-picker model="fechaEvento" max="{{ now()->toDateString() }}" placeholder="dd/mm/aaaa" />
                        @error('fechaEvento') <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    @php
                        $routeMeta = [
                            'problema' => [
                                'action' => 'Detecté',
                                'label' => 'un problema',
                                'question' => '¿Qué observaste?',
                                'help' => 'Síntomas, lesión o cambio de comportamiento',
                                'iconClass' => 'bg-rose-500/10 text-rose-600 dark:bg-rose-400/15 dark:text-rose-400',
                                'dotClass' => 'bg-rose-500',
                                'icon' => 'M3 12h4l2.5-6 5 12 2.5-6H21',
                            ],
                            'aplicacion' => [
                                'action' => 'Apliqué',
                                'label' => 'un producto',
                                'question' => '¿Qué aplicación realizaste?',
                                'help' => 'Vacuna, antiparasitario o suplemento',
                                'iconClass' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400',
                                'dotClass' => 'bg-amber-500',
                                'icon' => 'm4 20 5.5-5.5m1.5-7 6 6m-8-8 9 9m-7 2 7-7 2 2-7 7H9v-2Z',
                            ],
                            'procedimiento' => [
                                'action' => 'Realicé',
                                'label' => 'un manejo',
                                'question' => '¿Qué procedimiento realizaste?',
                                'help' => 'Curación, pezuñas o intervención',
                                'iconClass' => 'bg-sky-500/10 text-sky-600 dark:bg-sky-400/15 dark:text-sky-400',
                                'dotClass' => 'bg-sky-500',
                                'icon' => 'm14.7 6.3 3-3 3 3-3 3m-3-3L4 17v3h3L17.7 9.3',
                            ],
                            'control' => [
                                'action' => 'Revisé',
                                'label' => 'al animal',
                                'question' => '¿Qué control realizaste?',
                                'help' => 'Seguimiento, cuarentena o revisión',
                                'iconClass' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400',
                                'dotClass' => 'bg-emerald-500',
                                'icon' => 'm5 12 4 4L19 6',
                            ],
                        ];
                        $selectedRoute = $routeMeta[$grupoActual] ?? null;
                    @endphp

                    <div x-data="{ group: @js($grupoActual) }" class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3.5 dark:border-zinc-800 dark:bg-zinc-950/50 sm:p-4">
                        @if(!$mostrarSelectorMotivo && $motivoActual && $selectedRoute)
                            <div class="flex items-center gap-3 rounded-xl border border-teal-500/40 bg-white p-3 shadow-sm dark:bg-zinc-900">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $selectedRoute['iconClass'] }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $selectedRoute['icon'] }}" /></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-[10px] font-black uppercase tracking-[.16em] text-teal-700 dark:text-teal-400">Motivo elegido</span>
                                    <strong class="mt-0.5 block text-sm leading-5 text-zinc-900 dark:text-white">{{ $motivoActual['label'] }}</strong>
                                </span>
                                <button type="button" wire:click="showMotivePicker"
                                        class="shrink-0 rounded-xl border border-zinc-300 bg-white px-3 py-1.5 text-xs font-bold text-zinc-700 transition hover:border-teal-500 hover:text-teal-700 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">
                                    Cambiar
                                </button>
                            </div>
                        @endif

                        @if($mostrarSelectorMotivo)
                        <div>
                            <div class="mb-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">¿Qué vas a registrar? <span class="font-bold text-teal-600 dark:text-teal-400">*</span></h3>
                                <p class="mt-0.5 text-xs text-zinc-500">Elige una acción. Verás solo las opciones necesarias.</p>
                            </div>

                            <div class="grid grid-cols-2 gap-2 lg:grid-cols-4" role="tablist" aria-label="Tipo de atención">
                                @foreach($routeMeta as $routeKey => $route)
                                    <button type="button" role="tab" :aria-selected="group === @js($routeKey)"
                                            @click="group = @js($routeKey)"
                                            :class="group === @js($routeKey) ? 'border-teal-500 bg-white ring-2 ring-teal-500/15 dark:bg-zinc-900' : 'border-zinc-200 bg-white/70 hover:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-900/50'"
                                            class="flex min-h-20 items-center gap-2.5 rounded-xl border p-3 text-left transition focus:outline-none focus:ring-2 focus:ring-teal-500/30">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $route['iconClass'] }}">
                                            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $route['icon'] }}" /></svg>
                                        </span>
                                        <span class="min-w-0">
                                            <strong class="block text-xs font-bold text-zinc-900 dark:text-white">{{ $route['action'] }}</strong>
                                            <span class="block text-[10px] leading-4 text-zinc-500">{{ $route['label'] }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            <div x-show="!group" class="mt-3 rounded-xl border border-dashed border-zinc-300 px-4 py-3 text-center text-xs font-semibold text-zinc-500 dark:border-zinc-700">
                                Toca una de las cuatro acciones para continuar.
                            </div>

                            @foreach($motivoGroups as $groupKey => $group)
                                @php($route = $routeMeta[$groupKey])
                                <div x-show="group === @js($groupKey)" class="mt-3 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                                    <div class="flex items-center gap-3 border-b border-zinc-100 px-3.5 py-3 dark:border-zinc-800">
                                        <span class="h-2.5 w-2.5 rounded-full {{ $route['dotClass'] }}"></span>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900 dark:text-white">{{ $route['question'] }}</h4>
                                            <p class="mt-0.5 text-[10px] text-zinc-500">{{ $route['help'] }}</p>
                                        </div>
                                    </div>
                                    <div class="grid gap-px bg-zinc-200 dark:bg-zinc-800 sm:grid-cols-2">
                                        @foreach($group['items'] as $value => $item)
                                            <button type="button" wire:click="selectMotive('{{ $value }}')"
                                                    class="group flex min-h-12 items-center gap-3 bg-white px-3.5 py-2.5 text-left transition hover:bg-teal-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-500 dark:bg-zinc-900 dark:hover:bg-teal-400/5 {{ $motivoAtencion === $value ? 'text-teal-700 dark:text-teal-300' : 'text-zinc-700 dark:text-zinc-200' }}">
                                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border {{ $motivoAtencion === $value ? 'border-teal-500 bg-teal-500 text-white' : 'border-zinc-300 text-transparent group-hover:border-teal-500 dark:border-zinc-700' }}">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                                </span>
                                                <span class="text-xs font-bold leading-5">{{ $item['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif

                        @error('motivoAtencion') <p class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </section>

                @if($motivoActual)
                    @if($grupoActual === 'problema')
                        <section class="border-t border-zinc-100 bg-rose-50/40 p-5 dark:border-zinc-800 dark:bg-rose-950/10 sm:p-6">
                            <div class="mb-4">
                                <h2 class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Triaje rápido</h2>
                                <p class="mt-0.5 text-xs text-zinc-500">Marca cómo está el animal ahora.</p>
                            </div>
                            <fieldset>
                                <legend class="sr-only">Nivel de atención</legend>
                                <div class="grid gap-2 sm:grid-cols-3">
                                    @foreach([
                                        'estable' => ['Estable', 'Come, camina y respira normal', 'emerald'],
                                        'vigilar' => ['Vigilar', 'Hay un cambio evidente', 'amber'],
                                        'urgente' => ['Urgente', 'Necesita atención inmediata', 'rose'],
                                    ] as $value => [$label, $help, $tone])
                                        <label class="cursor-pointer rounded-xl border px-3.5 py-3 transition {{ $nivelAtencion === $value ? ($tone === 'emerald' ? 'border-emerald-500 bg-emerald-100/70 ring-1 ring-emerald-500/30 dark:bg-emerald-400/10' : ($tone === 'amber' ? 'border-amber-500 bg-amber-100/70 ring-1 ring-amber-500/30 dark:bg-amber-400/10' : 'border-rose-500 bg-rose-100/70 ring-1 ring-rose-500/30 dark:bg-rose-400/10')) : 'border-zinc-200 bg-white hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900' }}">
                                            <input type="radio" class="sr-only" wire:model.live="nivelAtencion" value="{{ $value }}">
                                            <span class="flex items-center gap-2 text-xs font-black text-zinc-900 dark:text-white">
                                                <span class="h-2 w-2 rounded-full {{ $tone === 'emerald' ? 'bg-emerald-500' : ($tone === 'amber' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                                {{ $label }}
                                            </span>
                                            <span class="mt-1 block pl-4 text-[10px] leading-4 text-zinc-500">{{ $help }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div class="mt-4 grid gap-3 {{ ($motivoActual['location'] ?? false) ? 'sm:grid-cols-2' : '' }}">
                                @if($motivoActual['location'] ?? false)
                                    <div>
                                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                            <span>Zona afectada</span>
                                            <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                        </label>
                                        <input type="text" wire:model="ubicacionCorporal" maxlength="150" placeholder="Ej. pata posterior derecha"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                @endif
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 bg-white px-3.5 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                                    <input type="checkbox" wire:model.live="requiereAislamiento" class="h-4 w-4 rounded border-zinc-300 text-rose-600 focus:ring-rose-500">
                                    <span>
                                        <strong class="block text-xs text-zinc-800 dark:text-zinc-100">Aislar al animal</strong>
                                        <span class="block text-[10px] text-zinc-500">Genera seguimiento de cuarentena.</span>
                                    </span>
                                </label>
                            </div>
                        </section>
                    @elseif($motivoActual['location'] ?? false)
                        <section class="border-t border-zinc-100 p-5 dark:border-zinc-800 sm:p-6">
                            <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span>Zona atendida</span>
                                <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                            </label>
                            <input type="text" wire:model="ubicacionCorporal" maxlength="150" placeholder="Ej. pezuña anterior izquierda"
                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </section>
                    @endif

                    @if(in_array($grupoActual, ['problema', 'procedimiento'], true) && !($motivoActual['plan'] ?? false))
                        <section class="border-t border-zinc-100 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <label class="flex cursor-pointer items-center justify-between gap-4">
                                <span>
                                    <strong class="block text-sm text-zinc-900 dark:text-white">¿Se administró un producto?</strong>
                                    <span class="mt-0.5 block text-xs text-zinc-500">Activa dosis, vía, calendario y tiempo de retiro.</span>
                                </span>
                                <span class="relative inline-flex shrink-0">
                                    <input type="checkbox" wire:model.live="administraProducto" class="peer sr-only">
                                    <span class="h-6 w-11 rounded-full bg-zinc-300 transition peer-checked:bg-teal-600 peer-focus:ring-2 peer-focus:ring-teal-500/30 dark:bg-zinc-700"></span>
                                    <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                        </section>
                    @endif

                    @if($administraProducto)
                        <section class="border-t border-amber-500/30 bg-amber-50/40 p-5 dark:border-amber-500/20 dark:bg-amber-950/10 sm:p-6">
                            <div class="mb-5 flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.7 6.3 3-3 3 3-3 3m-3-3L4 17v3h3L17.7 9.3M9 15l-2-2" /></svg>
                                </span>
                                <div>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Producto y aplicación</h2>
                                    <p class="mt-0.5 text-xs text-zinc-500">Un solo producto por registro; las siguientes dosis quedan programadas aquí.</p>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Producto <span class="font-bold text-amber-600 dark:text-amber-400">*</span></label>
                                    <x-filter-select model="productoOpcion" :options="$productoOptions" tone="amber" live />
                                </div>
                                @if(isset($productoTipo) && $productoTipo === 'nuevo')
                                    <div class="sm:col-span-2">
                                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Nombre del producto nuevo <span class="font-bold text-amber-600 dark:text-amber-400">*</span></label>
                                        <input type="text" wire:model="nombreProductoNuevo" maxlength="255" placeholder="Ej. Oxitetraciclina 200"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                        @error('nombreProductoNuevo') <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div>
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Dosis por animal <span class="font-bold text-amber-600 dark:text-amber-400">*</span></label>
                                    <input type="text" wire:model="dosisCantidad" maxlength="100" placeholder="Ej. 5 ml"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    @error('dosisCantidad') <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Vía <span class="font-bold text-amber-600 dark:text-amber-400">*</span></label>
                                    <x-filter-select model="viaAdministracion" :options="['' => 'Selecciona la vía'] + $viaOptions" tone="amber" />
                                    @error('viaAdministracion') <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Aplicaciones <span class="font-bold text-amber-600 dark:text-amber-400">*</span></label>
                                    <x-filter-select
                                        model="numeroAplicaciones"
                                        :options="[
                                            '1' => '1 aplicación',
                                            '2' => '2 aplicaciones',
                                            '3' => '3 aplicaciones',
                                            '4' => '4 aplicaciones',
                                            '5' => '5 aplicaciones',
                                            '6' => '6 aplicaciones',
                                        ]"
                                        tone="amber"
                                        live
                                        compact
                                    />
                                    @error('numeroAplicaciones') <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>
                                @if((int) $numeroAplicaciones > 1)
                                    <div>
                                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">Cada cuántos días <span class="font-bold text-amber-600 dark:text-amber-400">*</span></label>
                                        <input type="number" wire:model.live.debounce.400ms="intervaloDias" min="0" max="365" inputmode="numeric"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5 overflow-hidden rounded-xl border border-amber-500/30 bg-white dark:border-amber-500/20 dark:bg-zinc-900">
                                <div class="flex items-center justify-between border-b border-amber-500/20 bg-amber-50/50 px-3.5 py-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
                                    <strong class="text-xs font-bold text-zinc-800 dark:text-zinc-100">Calendario</strong>
                                    <span class="text-[10px] font-semibold text-zinc-500">Marca solo lo ya aplicado</span>
                                </div>
                                <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    @foreach($dosisPlan as $index => $dosis)
                                        <div wire:key="dose-date-{{ $index }}" class="grid items-center gap-3 px-3.5 py-3 sm:grid-cols-[3.5rem_11rem_minmax(0,1fr)]">
                                            <span class="text-xs font-black text-amber-800 dark:text-amber-300">D{{ $index + 1 }}</span>
                                            <x-date-picker model="dosisPlan.{{ $index }}.fecha_programada" placeholder="Fecha" />
                                            <div class="flex flex-wrap items-center gap-3">
                                                <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-bold text-zinc-700 dark:text-zinc-200">
                                                    <input type="checkbox" wire:model.live="dosisPlan.{{ $index }}.aplicada" class="rounded border-zinc-300 text-teal-600 focus:ring-teal-500">
                                                    Aplicada
                                                </label>
                                                @if($dosis['aplicada'] ?? false)
                                                    <div class="w-36"><x-date-picker model="dosisPlan.{{ $index }}.fecha_aplicada" placeholder="Fecha aplicada" /></div>
                                                @else
                                                    <span class="text-[10px] font-semibold text-amber-700 dark:text-amber-300">Se creará recordatorio</span>
                                                @endif
                                            </div>
                                            @error("dosisPlan.$index.fecha_programada") <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 sm:col-span-2 sm:col-start-2">{{ $message }}</p> @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-950/60">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <div>
                                        <strong class="block text-xs font-bold text-zinc-800 dark:text-zinc-100">Tiempo de retiro</strong>
                                        <span class="text-[10px] text-zinc-500">Copia la etiqueta o indicación veterinaria; usa 0 si no aplica.</span>
                                    </div>
                                    <svg class="h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z" /></svg>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Carne <span class="font-normal text-zinc-500">(días)</span>
                                        <input type="number" wire:model="retiroCarneDias" min="0" max="3650" inputmode="numeric"
                                               class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-sm font-semibold text-zinc-900 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                    </label>
                                    <label class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Leche <span class="font-normal text-zinc-500">(horas)</span>
                                        <input type="number" wire:model="retiroLecheHoras" min="0" max="8760" inputmode="numeric"
                                               class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-sm font-semibold text-zinc-900 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                    </label>
                                </div>
                            </div>
                        </section>
                    @endif

                    <section class="border-t border-zinc-100 p-5 dark:border-zinc-800 sm:p-6">
                        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            <span>Observaciones</span>
                            <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                        </label>
                        <textarea wire:model="sintomasDiagnostico" rows="3" maxlength="3000" placeholder="Escribe solo si necesitas agregar un detalle que no aparece arriba."
                                  class="w-full resize-y rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-600"></textarea>
                        @error('sintomasDiagnostico') <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror

                        <details class="group mt-4 rounded-xl border border-zinc-200 bg-zinc-50/70 dark:border-zinc-800 dark:bg-zinc-950/50">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-xs font-bold text-zinc-700 dark:text-zinc-200">
                                Responsable y evidencia
                                <svg class="h-4 w-4 text-zinc-400 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" /></svg>
                            </summary>
                            <div class="space-y-4 border-t border-zinc-200 p-4 dark:border-zinc-800">
                                <div>
                                    <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                        <span>Responsable</span>
                                        <span class="ml-1 text-[11px] font-normal text-zinc-400">(opcional)</span>
                                    </label>
                                    <input type="text" wire:model="responsable" maxlength="255" placeholder="Veterinario u operario"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                </div>
                                <x-record-photo-upload title="Evidencia del evento" :existing-photos="$existingPhotos" :new-photos="$fotos" :new-frames="$fotoEncuadres" />
                            </div>
                        </details>
                    </section>
                @endif

                <footer class="flex flex-col-reverse gap-3 border-t border-zinc-100 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-950/60 sm:flex-row sm:items-center sm:justify-end sm:px-6">
                    <a href="{{ route('monitoreo.index') }}" wire:navigate class="agro-button-secondary">Cancelar</a>
                    <button type="submit" x-bind:disabled="busy || $store?.imageUploads?.busy" wire:loading.attr="disabled" wire:target="save,fotos"
                            class="agro-button">
                        <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Actualizar Atención' : 'Registrar Atención' }}</span>
                        <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span wire:loading wire:target="save">{{ $isEdit ? 'Actualizando...' : 'Guardando...' }}</span>
                    </button>
                </footer>
            </main>

            <aside class="xl:sticky xl:top-5">
                <div class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white shadow-sm dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 text-zinc-800 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-200">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Resumen automático</p>
                        <h2 class="mt-0.5 text-sm font-bold">Ficha clínica</h2>
                    </div>
                    <dl class="divide-y divide-zinc-100 px-4 dark:divide-zinc-800">
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="text-xs text-zinc-500">Animales</dt>
                            <dd class="text-xs font-black text-zinc-900 dark:text-white">{{ count($animalIds) ?: 'Pendiente' }}</dd>
                        </div>
                        <div class="py-3">
                            <dt class="text-xs text-zinc-500">Motivo</dt>
                            <dd class="mt-1 text-xs font-bold leading-5 text-zinc-900 dark:text-white">{{ $motivoActual['label'] ?? 'Pendiente' }}</dd>
                        </div>
                        @if($grupoActual === 'problema')
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-xs text-zinc-500">Prioridad</dt>
                                <dd class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase {{ $nivelAtencion === 'urgente' ? 'bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300' : ($nivelAtencion === 'vigilar' ? 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300') }}">{{ $nivelAtencion }}</dd>
                            </div>
                        @endif
                        @if($administraProducto)
                            <div class="py-3">
                                <dt class="text-xs text-zinc-500">Aplicación</dt>
                                <dd class="mt-1 text-xs font-bold leading-5 text-zinc-900 dark:text-white">
                                    {{ $productoLabel ?: 'Producto pendiente' }}
                                    <span class="block font-normal text-zinc-500">{{ (int) $numeroAplicaciones }} {{ (int) $numeroAplicaciones === 1 ? 'dosis' : 'dosis programadas' }}</span>
                                </dd>
                            </div>
                        @endif
                    </dl>
                    <div class="m-4 rounded-xl bg-teal-50/70 p-3 dark:bg-teal-400/5">
                        <p class="flex gap-2 text-[10px] leading-4 text-teal-900 dark:text-teal-200">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                            La clasificación, el estado y los recordatorios se generan sin pedir más campos.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>
