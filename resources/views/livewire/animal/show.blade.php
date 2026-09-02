<div class="mx-auto max-w-6xl space-y-6">
    @php
        $milkPreview = $animal->ordenoDetalles
            ->filter(fn ($detail) => $detail->ordeno !== null)
            ->sortByDesc(fn ($detail) => $detail->ordeno->fecha?->timestamp ?? 0)
            ->take(10);
        $showMilkHistory = $animal->apta_ordeno || $milkPreview->isNotEmpty();
    @endphp
    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-center gap-3 sm:gap-4">
            <a href="{{ route('animal.index') }}" 
               class="shrink-0 rounded-xl border border-emerald-950/10 bg-white p-2.5 text-emerald-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-emerald-200/15 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-400/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-2xl font-extrabold tracking-tight text-emerald-800 dark:text-emerald-300 sm:text-3xl">
                    Ficha de Animal
                </h1>
                <p class="mt-1 text-sm text-emerald-900/60 dark:text-emerald-100/60">Historial de salud, productivo y reproductivo del ejemplar.</p>
            </div>
        </div>

        <div class="flex w-full flex-col-reverse gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
            @if(auth()->user()->tienePermiso('animal', 'exportar'))
                <button type="button" wire:click="openAnimalReportModal"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-bold text-zinc-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 sm:w-auto dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-emerald-500/50 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-200">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Generar ficha PDF
                </button>
            @endif
            @if(auth()->user()->tienePermiso('animal', 'actualizar'))
                <a href="{{ route('animal.edit', $animal->id) }}"
                   class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white shadow-lg shadow-emerald-700/15 transition hover:bg-emerald-700 sm:w-auto dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 15.07a4.5 4.5 0 0 1-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 3.487Z" /></svg>
                    Editar perfil
                </a>
            @endif
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(22rem,26rem)_minmax(0,1fr)]">
        <!-- Left Panel: Profile summary -->
        <div class="space-y-6">
            <div class="agro-card space-y-5 p-4 text-center sm:p-6">
                <!-- Avatar / Photo -->
                <div class="relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-2xl border border-emerald-950/10 bg-emerald-50 shadow-inner dark:border-emerald-200/10 dark:bg-emerald-950/45">
                    @if($animal->foto_ruta)
                        @php
                            $animalProfileFrame = \App\Support\ImageFrame::normalize($animal->foto_encuadre);
                        @endphp
                        <a href="{{ '/storage/'.ltrim($animal->foto_ruta, '/') }}" target="_blank" rel="noopener noreferrer" class="h-full w-full">
                            <img src="{{ '/storage/'.ltrim($animal->foto_ruta, '/') }}" alt="Foto de {{ $animal->nombre ?: $animal->arete }}" class="h-full w-full object-cover transition duration-300" style="object-position: {{ $animalProfileFrame['x'] }}% {{ $animalProfileFrame['y'] }}%; transform: scale({{ $animalProfileFrame['zoom'] }}); transform-origin: {{ $animalProfileFrame['x'] }}% {{ $animalProfileFrame['y'] }}%;">
                        </a>
                    @else
                        <svg class="h-20 w-20 text-emerald-700/35 dark:text-emerald-300/30" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5A2.25 2.25 0 0 1 19.5 9.75v6A2.25 2.25 0 0 1 17.25 18H6.75a2.25 2.25 0 0 1-2.25-2.25v-6A2.25 2.25 0 0 1 6.75 7.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8 7.5 1.15-1.72A2.25 2.25 0 0 1 11.02 4.8h1.96a2.25 2.25 0 0 1 1.87.98L16 7.5M15.5 12.5h.01M8.5 12.5h.01M9 18v1.2m6-1.2v1.2" />
                        </svg>
                    @endif
                </div>

                <!-- Basic info -->
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-emerald-950 dark:text-emerald-50">{{ $animal->nombre ?? 'Sin Nombre' }}</h2>
                    <p class="mt-1 text-sm font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-300">Código: {{ $animal->arete }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 border-t border-emerald-950/10 pt-3 dark:border-emerald-200/10">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/80 p-3 text-center dark:border-emerald-300/10 dark:bg-emerald-400/5">
                        <span class="block text-[10px] font-bold uppercase text-emerald-900/55 dark:text-emerald-100/55">Género</span>
                        <span class="mt-0.5 inline-block text-sm font-semibold {{ $animal->genero === 'hembra' ? 'text-pink-600 dark:text-pink-300' : 'text-sky-700 dark:text-sky-300' }}">
                            {{ $animal->genero === 'hembra' ? '♀ Hembra' : '♂ Macho' }}
                        </span>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/80 p-3 text-center dark:border-emerald-300/10 dark:bg-emerald-400/5">
                        <span class="block text-[10px] font-bold uppercase text-emerald-900/55 dark:text-emerald-100/55">Especie</span>
                        <span class="mt-0.5 inline-block text-sm font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->especie->nombre ?? '-' }}</span>
                    </div>
                    <div class="col-span-2 rounded-xl border border-emerald-100 bg-emerald-50/80 p-3 text-center dark:border-emerald-300/10 dark:bg-emerald-400/5">
                        <span class="block text-[10px] font-bold uppercase text-emerald-900/55 dark:text-emerald-100/55">Raza</span>
                        <span class="mt-0.5 inline-block text-sm font-semibold text-emerald-800 dark:text-emerald-200">{{ $animal->raza->nombre ?? '-' }}</span>
                    </div>
                </div>

                <div class="pt-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge :value="$animal->activo ? 'activo' : 'inactivo'" :label="$animal->activo ? 'Estado: Activo' : 'Estado: Inactivo'" />
                        @if($animal->activo)
                            @php
                                $estadoBadge = [
                                    'sano' => ['value' => 'activo', 'label' => '🟢 Sano', 'class' => '!bg-emerald-500/10 !text-emerald-700 dark:!bg-emerald-400/10 dark:!text-emerald-300'],
                                    'tratamiento' => ['value' => 'en_tratamiento', 'label' => '🟠 En tratamiento', 'class' => '!bg-amber-500/10 !text-amber-700 dark:!bg-amber-400/10 dark:!text-amber-300'],
                                    'alerta' => ['value' => 'critico', 'label' => '🔴 Crítico / Cuarentena', 'class' => '!bg-rose-500/10 !text-rose-700 dark:!bg-rose-400/10 dark:!text-rose-300'],
                                ][$estadoClinicoActual] ?? ['value' => 'activo', 'label' => '🟢 Sano', 'class' => ''];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $estadoBadge['class'] }}">
                                {{ $estadoBadge['label'] }}
                            </span>
                        @endif
                    </div>
                    @unless($animal->activo)
                        <div class="mt-3 rounded-xl border border-amber-300/40 bg-amber-100/60 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                            <p class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300">Motivo de baja</p>
                            <p class="mt-1 text-sm font-bold text-amber-950 dark:text-amber-100">
                                {{ $animal->motivo_baja_label }}
                                @if($animal->fecha_baja) · {{ $animal->fecha_baja->format('d/m/Y') }} @endif
                            </p>
                            @if($animal->comprador_baja)
                                <p class="mt-1 text-xs text-amber-800 dark:text-amber-200">Comprador: {{ $animal->comprador_baja }}</p>
                            @endif
                            @if($animal->movimientoVenta)
                                <a href="{{ route('finanzas.movimiento.show', $animal->movimientoVenta) }}"
                                   class="mt-2 inline-flex text-xs font-black text-emerald-700 hover:underline dark:text-emerald-300">
                                    Ver movimiento de venta
                                </a>
                            @endif
                        </div>
                    @endunless
                </div>
            </div>

            <!-- Ficha Técnica -->
            <div class="agro-card space-y-3.5 p-5 sm:p-6">
                <h3 class="border-b border-emerald-950/10 pb-2 text-xs font-bold uppercase tracking-wider text-emerald-700 dark:border-emerald-200/10 dark:text-emerald-300">Especificaciones de alta</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-emerald-900/55 dark:text-emerald-100/55">Procedencia:</span>
                        <span class="font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->tipo_alta === 'parto' ? 'Nacimiento / parto' : ucfirst($animal->tipo_alta) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-900/55 dark:text-emerald-100/55">Fecha de registro:</span>
                        <span class="font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->fecha_alta->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-900/55 dark:text-emerald-100/55">Clasificación:</span>
                        <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{ $animal->clasificacion_edad }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-emerald-900/55 dark:text-emerald-100/55">Edad:</span>
                        <span class="text-right font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->edad_texto }}</span>
                    </div>
                    @if($animal->denticion_estimada)
                        <div class="rounded-xl border border-amber-500/20 bg-amber-50 p-3 dark:bg-amber-500/5">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Dentición estimada por edad</span>
                            <span class="mt-1 block text-xs text-amber-900/75 dark:text-amber-100/75">{{ $animal->denticion_estimada }}. No corresponde a observación clínica.</span>
                        </div>
                    @endif
                    @if($animal->fecha_nacimiento)
                        <div class="flex justify-between">
                            <span class="text-emerald-900/55 dark:text-emerald-100/55">Nacimiento:</span>
                            <span class="font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->fecha_nacimiento->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-emerald-900/55 dark:text-emerald-100/55">Peso registrado:</span>
                        <span class="font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->peso ? $animal->peso . ' Kg' : '-' }}</span>
                    </div>
                    @if($animal->tipo_alta === 'compra')
                        <div class="flex justify-between">
                            <span class="text-emerald-900/55 dark:text-emerald-100/55">Precio de compra:</span>
                            <span class="font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->precio_compra !== null ? 'S/ '.number_format((float) $animal->precio_compra, 2) : 'No registrado' }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-emerald-900/55 dark:text-emerald-100/55">Estado reproductivo:</span>
                        <span class="font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->estado_reproductivo_label }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-900/55 dark:text-emerald-100/55">Apta para ordeño:</span>
                        <span class="font-semibold text-emerald-950 dark:text-emerald-50">{{ $animal->apta_ordeno ? 'Sí' : 'No' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Medical & Production Timelines -->
        <div class="min-w-0 space-y-6">
            <!-- Observaciones -->
            @if($animal->observaciones)
                <div class="agro-card space-y-2 p-5 sm:p-6">
                    <h3 class="border-b border-emerald-950/10 pb-2 text-xs font-bold uppercase tracking-wider text-emerald-700 dark:border-emerald-200/10 dark:text-emerald-300">Observaciones</h3>
                    <p class="text-sm italic leading-relaxed text-emerald-900/75 dark:text-emerald-100/75">"{{ $animal->observaciones }}"</p>
                </div>
            @endif

            <!-- Tabs/Sections container -->
            <div x-data="{ tab: 'salud' }" class="space-y-4">
                <!-- Navigation -->
                <div class="no-scrollbar flex gap-2 overflow-x-auto border-b border-emerald-950/10 dark:border-emerald-200/10">
                    <button @click="tab = 'salud'"
                            :class="tab === 'salud' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-emerald-900/55 hover:text-emerald-700 dark:text-emerald-100/55 dark:hover:text-emerald-200'"
                            class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none">
                        🧬 Salud
                    </button>
                    <button x-show="false" @click="tab = 'clinico'"
                            :class="tab === 'clinico' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-emerald-900/55 hover:text-emerald-700 dark:text-emerald-100/55 dark:hover:text-emerald-200'"
                            class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none">
                        🩺 Historial Clínico
                    </button>
                    @if($animal->genero === 'hembra')
                        <button @click="tab = 'reproductivo'" 
                                :class="tab === 'reproductivo' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-emerald-900/55 hover:text-emerald-700 dark:text-emerald-100/55 dark:hover:text-emerald-200'"
                                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none">
                            🐄 Partos / Crías
                        </button>
                        @if($showMilkHistory)
                            <button @click="tab = 'lacteo'" 
                                    :class="tab === 'lacteo' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-emerald-900/55 hover:text-emerald-700 dark:text-emerald-100/55 dark:hover:text-emerald-200'"
                                    class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition outline-none">
                                🥛 Producción Láctea
                            </button>
                        @endif
                    @endif
                </div>

                <!-- Tab content 0: Salud (línea de tiempo unificada) -->
                <div x-show="tab === 'salud'" class="space-y-4">
                    <div class="agro-card space-y-4 p-5 sm:p-6">
                        <h3 class="text-sm font-bold text-emerald-950 dark:text-emerald-50">Línea de tiempo de salud</h3>
                        <p class="text-xs text-emerald-900/55 dark:text-emerald-100/55">Eventos, atenciones y dosis vinculados a este animal.</p>
                        <div class="relative space-y-3 before:absolute before:inset-y-1 before:left-[15px] before:w-px before:bg-emerald-950/10 dark:before:bg-emerald-200/10">
                            @forelse($timeline as $event)
                                @php
                                    $tone = match($event['categoria'] ?? 'otro') { 'lesion' => 'rose', 'enfermedad' => 'amber', 'parasitos' => 'violet', 'vacunacion' => 'sky', default => 'emerald' };
                                    $icon = match($event['categoria'] ?? 'otro') { 'lesion' => '!', 'enfermedad' => '+', 'parasitos' => 'P', 'vacunacion' => 'V', 'suplementacion' => 'S', 'procedimiento' => 'C', default => '·' };
                                @endphp
                                <article class="relative flex gap-3 pl-0">
                                    <span class="z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-950/10 bg-white text-sm dark:border-emerald-200/10 dark:bg-zinc-900">{{ $icon }}</span>
                                    <div class="min-w-0 flex-1 rounded-xl border border-emerald-950/10 bg-emerald-50/40 p-3 dark:border-emerald-200/10 dark:bg-emerald-950/20">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <a href="{{ $event['url'] }}" class="block truncate text-sm font-bold text-emerald-950 hover:underline dark:text-emerald-50">{{ $event['titulo'] }}</a>
                                                <span class="mt-0.5 block text-[11px] text-emerald-900/55 dark:text-emerald-100/55">{{ $event['subtitulo'] }} · {{ $event['fecha']->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-2">
                                                @if($event['aplicadas'] || $event['pendientes'])<span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300">{{ $event['aplicadas'] }} aplicadas · {{ $event['pendientes'] }} pendientes</span>@endif
                                                <x-status-badge :value="$event['estado']" :label="$event['estado_label']" :tone="$tone" class="text-[10px]" />
                                            </div>
                                        </div>
                                        <p class="mt-1.5 break-words text-xs leading-relaxed text-emerald-900/70 dark:text-emerald-100/70">{{ $event['detalle'] }}</p>
                                        @if($event['atencion'])<p class="mt-1 break-words text-[11px] text-emerald-900/60 dark:text-emerald-100/60"><strong>Atención:</strong> {{ $event['atencion'] }}</p>@endif
                                        @if($event['medicamento'])
                                            <p class="mt-1 break-words text-[11px] font-semibold text-emerald-900/60 dark:text-emerald-100/60">{{ $event['medicamento'] }}</p>
                                        @endif
                                        @if($event['cierre'])
                                            <p class="mt-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">Completado el {{ $event['cierre']->format('d/m/Y') }}</p>
                                        @endif
                                        @if($event['fotos']->isNotEmpty())
                                            <div class="mt-2">
                                                <x-record-photo-gallery :photos="$event['fotos']" />
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="py-6 text-center text-xs italic text-emerald-900/50 dark:text-emerald-100/50">
                                    Sin eventos de salud registrados.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Tab content 1: Clinico -->
                <div x-show="false" class="space-y-4">
                    <div class="agro-card space-y-4 p-5 sm:p-6">
                        <h3 class="text-sm font-bold text-emerald-950 dark:text-emerald-50">Enfermedades y lesiones registradas</h3>
                        <div class="divide-y divide-emerald-950/10 dark:divide-emerald-200/10">
                            @forelse($animal->sanidadRegistros as $sr)
                                <article x-data="{ expanded: false }" class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex flex-col justify-between gap-2 text-sm sm:flex-row sm:gap-4">
                                        <div class="min-w-0 break-words">
                                            <div class="font-bold text-emerald-950 dark:text-emerald-50">{{ $sr->sintomas_diagnostico }}</div>
                                            <div class="mt-0.5 text-xs text-emerald-900/55 dark:text-emerald-100/55">Clasificación: {{ ucfirst(str_replace('_', ' ', $sr->clasificacion)) }}</div>
                                            <div class="mt-1 text-xs text-emerald-900/70 dark:text-emerald-100/70">Tratamiento: {{ $sr->tratamiento ?? 'Ninguno' }} (Medicamento: {{ $sr->medicamento?->nombre ?? $sr->medicamento_nombre ?? '-' }})</div>
                                        </div>
                                        <div class="flex shrink-0 items-center justify-between gap-2 sm:flex-col sm:items-end">
                                            <span class="text-xs font-semibold text-emerald-900/55 dark:text-emerald-100/55">{{ $sr->fecha_evento->format('d/m/Y') }}</span>
                                            <x-status-badge :value="$sr->estado_clinico" :dot="false" class="text-[10px] uppercase" />
                                        </div>
                                    </div>
                                    <button type="button" @click="expanded = !expanded" class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-bold text-sky-700 hover:text-sky-600 dark:text-sky-300 dark:hover:text-sky-200">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        <span x-text="expanded ? 'Ocultar detalle' : 'Ver detalle y fotos'"></span>
                                        @if($sr->fotos->isNotEmpty())<span class="rounded-full bg-sky-100 px-1.5 py-0.5 text-[9px] text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">{{ $sr->fotos->count() }}</span>@endif
                                    </button>
                                    <div x-cloak x-show="expanded" style="display: none;" class="mt-3 grid gap-3 rounded-xl border border-emerald-950/10 bg-emerald-50/50 p-3 sm:grid-cols-[minmax(0,1fr)_15rem] dark:border-emerald-200/10 dark:bg-emerald-950/25">
                                        <dl class="grid content-start gap-2 text-[11px] sm:grid-cols-2">
                                            <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Diagnóstico</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ $sr->sintomas_diagnostico }}</dd></div>
                                            <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Estado clínico</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ ucfirst(str_replace('_', ' ', $sr->estado_clinico)) }}</dd></div>
                                            <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Tratamiento</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ $sr->tratamiento ?: 'Sin tratamiento' }}</dd></div>
                                            <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Medicamento y dosis</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ $sr->medicamento?->nombre ?? $sr->medicamento_nombre ?? '-' }} · {{ $sr->dosis_via ?: 'Sin dosis registrada' }}</dd></div>
                                        </dl>
                                        <div class="space-y-2">
                                            <x-record-photo-gallery :photos="$sr->fotos" />
                                            @if($sr->fotos->isEmpty() && $sr->evidencia_ruta)
                                                <a href="{{ '/storage/'.ltrim($sr->evidencia_ruta, '/') }}" target="_blank" rel="noopener noreferrer" class="block rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-center text-[10px] font-bold text-sky-700 hover:bg-sky-100 dark:border-sky-500/25 dark:bg-sky-500/10 dark:text-sky-300">Ver adjunto anterior</a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="py-4 text-center text-xs italic text-emerald-900/50 dark:text-emerald-100/50">
                                    No se registran eventos médicos.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Tab content 2: Reproductivo -->
                @if($animal->genero === 'hembra')
                    <div x-show="tab === 'reproductivo'" class="space-y-4" style="display: none;">
                        <div class="agro-card space-y-4 p-5 sm:p-6">
                            <h3 class="text-sm font-bold text-emerald-950 dark:text-emerald-50">Historial de partos</h3>
                            <div class="divide-y divide-emerald-950/10 dark:divide-emerald-200/10">
                                @forelse($animal->partosMadre as $pr)
                                    <article x-data="{ expanded: false }" class="py-3 first:pt-0 last:pb-0">
                                        <div class="flex flex-col gap-2 text-sm sm:flex-row sm:justify-between sm:gap-4">
                                            <div class="min-w-0 break-words">
                                                <div class="font-bold text-emerald-950 dark:text-emerald-50">Parto {{ ucfirst(str_replace('_', ' ', $pr->tipo_parto)) }}</div>
                                                <div class="mt-1 text-xs text-emerald-900/70 dark:text-emerald-100/70">Cría: {{ $pr->cria?->nombre ?: 'Sin nombre' }} · {{ $pr->cria?->arete ?? 'Sin código' }} ({{ ucfirst($pr->cria?->genero ?? $pr->cria_sexo ?? 'no registrado') }}) | Peso: {{ $pr->cria_peso_nacer ?? '-' }} Kg</div>
                                                <div class="mt-0.5 text-xs text-emerald-900/55 dark:text-emerald-100/55">Estado: {{ ucfirst(str_replace('_', ' ', $pr->cria_estado ?? '-')) }} | Condición madre: {{ ucfirst(str_replace('_', ' ', $pr->condicion_madre)) }}</div>
                                            </div>
                                            <div class="shrink-0 text-xs font-semibold text-emerald-900/55 dark:text-emerald-100/55">{{ $pr->fecha_parto->format('d/m/Y') }}</div>
                                        </div>
                                        <button type="button" @click="expanded = !expanded" class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-bold text-violet-700 hover:text-violet-600 dark:text-violet-300 dark:hover:text-violet-200">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                            <span x-text="expanded ? 'Ocultar detalle' : 'Ver detalle y fotos'"></span>
                                            @if($pr->fotos->isNotEmpty())<span class="rounded-full bg-violet-100 px-1.5 py-0.5 text-[9px] text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">{{ $pr->fotos->count() }}</span>@endif
                                        </button>
                                        <div x-cloak x-show="expanded" style="display: none;" class="mt-3 grid gap-3 rounded-xl border border-emerald-950/10 bg-emerald-50/50 p-3 sm:grid-cols-[minmax(0,1fr)_15rem] dark:border-emerald-200/10 dark:bg-emerald-950/25">
                                            <dl class="grid content-start gap-2 text-[11px] sm:grid-cols-2">
                                                <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Tipo y fecha</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ ucfirst(str_replace('_', ' ', $pr->tipo_parto)) }} · {{ $pr->fecha_parto->format('d/m/Y') }}</dd></div>
                                                <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Cría</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ $pr->cria?->nombre ?: 'Sin nombre' }} · {{ $pr->cria?->arete ?? 'Sin código' }} · {{ ucfirst($pr->cria?->genero ?? $pr->cria_sexo ?? '-') }} · {{ $pr->cria_peso_nacer ?? '-' }} kg</dd></div>
                                                <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Estado de cría</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ ucfirst(str_replace('_', ' ', $pr->cria_estado ?? '-')) }}</dd></div>
                                                <div><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Condición madre</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ ucfirst(str_replace('_', ' ', $pr->condicion_madre)) }}</dd></div>
                                                @if($pr->observaciones)<div class="sm:col-span-2"><dt class="font-bold text-emerald-900/55 dark:text-emerald-100/55">Observaciones</dt><dd class="mt-0.5 text-emerald-950 dark:text-emerald-50">{{ $pr->observaciones }}</dd></div>@endif
                                            </dl>
                                            <x-record-photo-gallery :photos="$pr->fotos" />
                                        </div>
                                    </article>
                                @empty
                                    <div class="py-4 text-center text-xs italic text-emerald-900/50 dark:text-emerald-100/50">
                                        No se registran partos para este ejemplar.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Tab content 3: Lacteo -->
                    @if($showMilkHistory)
                        <div x-show="tab === 'lacteo'" class="space-y-4" style="display: none;">
                            <div class="agro-card space-y-4 p-5 sm:p-6">
                                <h3 class="text-sm font-bold text-emerald-950 dark:text-emerald-50">ÃÅ¡ltimos registros de ordeño</h3>
                                <div class="divide-y divide-emerald-950/10 dark:divide-emerald-200/10">
                                    @forelse($milkPreview as $od)
                                        <div class="flex flex-col gap-2 py-3 text-sm first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <div class="font-bold text-emerald-950 dark:text-emerald-50">Turno: {{ ucfirst($od->ordeno->turno ?? '-') }}</div>
                                                <div class="mt-0.5 text-xs text-emerald-900/55 dark:text-emerald-100/55">Fecha: {{ $od->ordeno->fecha ? $od->ordeno->fecha->format('d/m/Y') : '-' }}</div>
                                            </div>
                                            <div class="sm:text-right">
                                                @if($od->litros > 0)
                                                    <span class="text-base font-bold text-emerald-700 dark:text-emerald-300">{{ $od->litros }} Litros</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-red-500/10 text-red-400 border border-red-500/20">
                                                        Excepción: {{ ucfirst($od->causa_excepcion) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-4 text-center text-xs italic text-emerald-900/50 dark:text-emerald-100/50">
                                            No se registran ordeños individuales para este ejemplar.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if($showReportModal)
        @php
            $reportOptions = \App\Livewire\Animal\Show::reportSectionOptions();
            $columnOptions = \App\Livewire\Animal\Show::reportColumnOptions();
            $sectionThemes = [
                'identity' => [
                    'selected' => 'border-emerald-300 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-200 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'panel' => 'border-emerald-200 bg-emerald-50/55 dark:border-emerald-500/30 dark:bg-emerald-500/[.07]',
                    'title' => 'text-emerald-900 dark:text-emerald-100',
                    'action' => 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-300 dark:hover:text-emerald-200',
                    'field' => 'border-emerald-300 bg-white/90 text-emerald-950 dark:border-emerald-500/55 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'check' => 'border-emerald-600 bg-emerald-600 dark:border-emerald-400 dark:bg-emerald-400',
                    'icon' => 'text-white dark:text-emerald-950',
                ],
                'productive' => [
                    'selected' => 'border-amber-300 bg-amber-50 text-amber-950 ring-1 ring-amber-200 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-50',
                    'panel' => 'border-amber-200 bg-amber-50/55 dark:border-amber-500/30 dark:bg-amber-500/[.07]',
                    'title' => 'text-amber-900 dark:text-amber-100',
                    'action' => 'text-amber-700 hover:text-amber-600 dark:text-amber-300 dark:hover:text-amber-200',
                    'field' => 'border-amber-300 bg-white/90 text-amber-950 dark:border-amber-500/55 dark:bg-amber-500/10 dark:text-amber-50',
                    'check' => 'border-amber-600 bg-amber-600 dark:border-amber-400 dark:bg-amber-400',
                    'icon' => 'text-white dark:text-amber-950',
                ],
                'clinical' => [
                    'selected' => 'border-rose-300 bg-rose-50 text-rose-950 ring-1 ring-rose-200 dark:border-rose-500/60 dark:bg-rose-500/10 dark:text-rose-50',
                    'panel' => 'border-rose-200 bg-rose-50/55 dark:border-rose-500/30 dark:bg-rose-500/[.07]',
                    'title' => 'text-rose-900 dark:text-rose-100',
                    'action' => 'text-rose-700 hover:text-rose-600 dark:text-rose-300 dark:hover:text-rose-200',
                    'field' => 'border-rose-300 bg-white/90 text-rose-950 dark:border-rose-500/55 dark:bg-rose-500/10 dark:text-rose-50',
                    'check' => 'border-rose-600 bg-rose-600 dark:border-rose-400 dark:bg-rose-400',
                    'icon' => 'text-white dark:text-rose-950',
                ],
                'preventive' => [
                    'selected' => 'border-sky-300 bg-sky-50 text-sky-950 ring-1 ring-sky-200 dark:border-sky-500/60 dark:bg-sky-500/10 dark:text-sky-50',
                    'panel' => 'border-sky-200 bg-sky-50/55 dark:border-sky-500/30 dark:bg-sky-500/[.07]',
                    'title' => 'text-sky-900 dark:text-sky-100',
                    'action' => 'text-sky-700 hover:text-sky-600 dark:text-sky-300 dark:hover:text-sky-200',
                    'field' => 'border-sky-300 bg-white/90 text-sky-950 dark:border-sky-500/55 dark:bg-sky-500/10 dark:text-sky-50',
                    'check' => 'border-sky-600 bg-sky-600 dark:border-sky-400 dark:bg-sky-400',
                    'icon' => 'text-white dark:text-sky-950',
                ],
                'reproductive' => [
                    'selected' => 'border-violet-300 bg-violet-50 text-violet-950 ring-1 ring-violet-200 dark:border-violet-500/60 dark:bg-violet-500/10 dark:text-violet-50',
                    'panel' => 'border-violet-200 bg-violet-50/55 dark:border-violet-500/30 dark:bg-violet-500/[.07]',
                    'title' => 'text-violet-900 dark:text-violet-100',
                    'action' => 'text-violet-700 hover:text-violet-600 dark:text-violet-300 dark:hover:text-violet-200',
                    'field' => 'border-violet-300 bg-white/90 text-violet-950 dark:border-violet-500/55 dark:bg-violet-500/10 dark:text-violet-50',
                    'check' => 'border-violet-600 bg-violet-600 dark:border-violet-400 dark:bg-violet-400',
                    'icon' => 'text-white dark:text-violet-950',
                ],
                'milk' => [
                    'selected' => 'border-teal-300 bg-teal-50 text-teal-950 ring-1 ring-teal-200 dark:border-teal-500/60 dark:bg-teal-500/10 dark:text-teal-50',
                    'panel' => 'border-teal-200 bg-teal-50/55 dark:border-teal-500/30 dark:bg-teal-500/[.07]',
                    'title' => 'text-teal-900 dark:text-teal-100',
                    'action' => 'text-teal-700 hover:text-teal-600 dark:text-teal-300 dark:hover:text-teal-200',
                    'field' => 'border-teal-300 bg-white/90 text-teal-950 dark:border-teal-500/55 dark:bg-teal-500/10 dark:text-teal-50',
                    'check' => 'border-teal-600 bg-teal-600 dark:border-teal-400 dark:bg-teal-400',
                    'icon' => 'text-white dark:text-teal-950',
                ],
            ];
            if ($animal->genero !== 'hembra') {
                unset($reportOptions['reproductive']);
            }
            if (! $animal->apta_ordeno && $animal->ordenoDetalles->isEmpty()) {
                unset($reportOptions['milk']);
            }
        @endphp
        <div x-data="{
                sections: $wire.entangle('selectedReportSections').live,
                columns: $wire.entangle('reportColumns').live,
                init() { document.body.classList.add('overflow-hidden') },
                destroy() { document.body.classList.remove('overflow-hidden') },
            }"
             x-on:keydown.escape.window="$wire.set('showReportModal', false)"
             x-on:click.self="$wire.set('showReportModal', false)"
             class="agro-dialog-overlay agro-dialog-overlay--full">
            <div role="dialog" aria-modal="true" aria-labelledby="animal-report-title"
                 class="agro-dialog agro-dialog--full h-[calc(100dvh-0.5rem)] sm:h-[calc(100dvh-1.5rem)] sm:w-[calc(100vw-1.5rem)]">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-700">
                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Exportación PDF</span>
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <span x-text="sections.length"></span> de {{ count($reportOptions) }} secciones
                            </span>
                        </div>
                        <h3 id="animal-report-title" class="text-lg font-bold text-zinc-900 dark:text-white sm:text-xl">Generar ficha PDF del animal</h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Selecciona contenido del expediente. Salida A4 horizontal optimizada.</p>
                    </div>
                    <button type="button" wire:click="$set('showReportModal', false)"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                            aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto xl:grid xl:grid-cols-[21rem_minmax(0,1fr)] xl:overflow-hidden">
                    <aside class="border-b border-zinc-200 bg-zinc-50/70 p-4 xl:overflow-y-auto xl:border-b-0 xl:border-r dark:border-zinc-700 dark:bg-zinc-950/25">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-300">Secciones del expediente</span>
                            <button type="button"
                                    x-on:click="sections = sections.length === {{ count($reportOptions) }} ? [] : @js(array_keys($reportOptions))"
                                    class="text-xs font-bold text-emerald-700 transition hover:text-emerald-600 focus:outline-none focus:underline dark:text-emerald-400 dark:hover:text-emerald-300">
                                Seleccionar todas
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-1">
                            @foreach($reportOptions as $key => $option)
                                @php
                                    $theme = $sectionThemes[$key];
                                @endphp
                                <label :class="sections.includes('{{ $key }}')
                                        ? @js($theme['selected'])
                                        : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-300 dark:hover:border-zinc-600'"
                                       class="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 transition focus-within:ring-2 focus-within:ring-emerald-500/50">
                                    <input type="checkbox" x-model="sections" value="{{ $key }}" class="sr-only">
                                    <span :class="sections.includes('{{ $key }}') ? @js($theme['check']) : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900'"
                                          class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 transition">
                                        <svg x-cloak x-show="sections.includes('{{ $key }}')" class="h-3 w-3 {{ $theme['icon'] }}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                    </span>
                                    <span class="min-w-0 leading-tight">
                                        <strong class="block text-sm">{{ $option['label'] }}</strong>
                                        <small class="mt-0.5 block text-[11px] font-normal opacity-65">{{ $option['description'] }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedReportSections') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        @error('selectedReportSections.*') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </aside>

                    <div class="p-4 sm:p-5 xl:overflow-y-auto 2xl:p-6">
                        <div x-cloak x-show="sections.length === 0" class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 text-center dark:border-zinc-700 dark:bg-zinc-950/30">
                            <div class="max-w-sm">
                                <strong class="block text-sm text-zinc-700 dark:text-zinc-200">Sin secciones seleccionadas</strong>
                                <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Activa una sección para elegir campos del reporte.</span>
                            </div>
                        </div>
                        <div class="grid content-start gap-4 xl:grid-cols-2 2xl:gap-5">
                            @foreach($reportOptions as $sectionKey => $sectionOption)
                                @php
                                    $fields = $columnOptions[$sectionKey] ?? [];
                                    $theme = $sectionThemes[$sectionKey];
                                @endphp
                                <section x-cloak x-show="sections.includes('{{ $sectionKey }}')"
                                         class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <strong class="block text-sm {{ $theme['title'] }}">Campos: {{ $sectionOption['label'] }}</strong>
                                            <small class="mt-0.5 block text-[11px] text-zinc-500 dark:text-zinc-400">Elige información incluida en esta sección.</small>
                                        </div>
                                        <button type="button"
                                                x-on:click="columns['{{ $sectionKey }}'] = columns['{{ $sectionKey }}'].length === {{ count($fields) }} ? [] : @js(array_keys($fields))"
                                                class="shrink-0 text-[11px] font-bold transition focus:outline-none focus:underline {{ $theme['action'] }}">
                                            Seleccionar todos
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-2 2xl:grid-cols-3">
                                        @foreach($fields as $fieldKey => $fieldLabel)
                                            <label :class="columns['{{ $sectionKey }}'].includes('{{ $fieldKey }}')
                                                    ? @js($theme['field'])
                                                    : 'border-zinc-200 bg-white/75 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                                   class="flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border px-2.5 py-2 text-xs font-semibold leading-tight transition focus-within:ring-2 focus-within:ring-current/30">
                                                <input type="checkbox" x-model="columns['{{ $sectionKey }}']" value="{{ $fieldKey }}" class="sr-only">
                                                <span :class="columns['{{ $sectionKey }}'].includes('{{ $fieldKey }}') ? @js($theme['check']) : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-950'"
                                                       class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition">
                                                    <svg x-cloak x-show="columns['{{ $sectionKey }}'].includes('{{ $fieldKey }}')" class="h-3 w-3 {{ $theme['icon'] }}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                                </span>
                                                <span>{{ $fieldLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error("reportColumns.{$sectionKey}") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                    @error("reportColumns.{$sectionKey}.*") <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="hidden text-xs text-zinc-500 sm:block dark:text-zinc-400">A4 horizontal · Secciones configurables · Fotografía e historiales</p>
                    <div class="flex gap-2 sm:justify-end">
                        <button type="button" wire:click="$set('showReportModal', false)"
                                class="h-11 flex-1 rounded-xl border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 sm:flex-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            Cancelar
                        </button>
                        <button type="button" wire:click="downloadAnimalReport" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70" wire:target="downloadAnimalReport"
                                class="h-11 flex-1 rounded-xl bg-emerald-700 px-6 text-sm font-bold text-white shadow-md shadow-emerald-700/15 transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait sm:flex-none dark:bg-emerald-500 dark:text-emerald-950 dark:hover:bg-emerald-400 dark:focus:ring-offset-zinc-900">
                            <span wire:loading.remove wire:target="downloadAnimalReport" class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ver Vista Previa PDF</span>
                            </span>
                            <span wire:loading wire:target="downloadAnimalReport">Generando vista previa...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL LIVE PDF PREVIEW --}}
    <x-pdf-preview-modal
        :show-export-modal="$showExportModal && $exportStep === 'preview'"
        export-step="preview"
        :pdf-preview-data="$pdfPreviewData"
        :pdf-preview-token="$pdfPreviewToken"
        :pdf-preview-filename="$pdfPreviewFilename"
        :pdf-preview-title="$pdfPreviewTitle"
        :pdf-preview-row-count="$pdfPreviewRowCount"
        :pdf-preview-page-count="$pdfPreviewPageCount"
        :pdf-include-signatures="$pdfIncludeSignatures"
        :pdf-scale="$pdfScale"
        :has-pdf-customization="true"
        :back-action="'$set(\'showReportModal\', true); $set(\'showExportModal\', false)'"
    >
    </x-pdf-preview-modal>
</div>

