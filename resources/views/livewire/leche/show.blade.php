<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('leche.index') }}" 
               class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-100 transition duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-emerald-800 dark:text-emerald-300">
                    Ordeño del {{ $ordeno->fecha->format('d/m/Y') }}
                </h1>
                <p class="text-zinc-400 text-sm mt-1">Turno: {{ \App\Models\Ordeno::turnoLabel($ordeno->turno) }} | Tipo: {{ \App\Models\Ordeno::tipoLabel($ordeno->tipo_registro) }}</p>
            </div>
        </div>
        @if(auth()->user()->tienePermiso('leche', 'actualizar'))
            <a href="{{ route('leche.edit', $ordeno->id) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 dark:bg-emerald-500 dark:text-emerald-950 dark:hover:bg-emerald-400">
                Editar ordeño
            </a>
        @endif
    </div>

    @if($fotoRuta)
        @php
            $milkingDetailFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
        @endphp
        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
            <div class="border-b border-zinc-800 bg-zinc-950 px-5 py-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Foto diaria de la producción</h3>
            </div>
            <a href="{{ asset('storage/'.$fotoRuta) }}" target="_blank" rel="noopener" class="block aspect-[16/10] overflow-hidden">
                <img src="{{ asset('storage/'.$fotoRuta) }}" alt="Producción de leche del {{ $ordeno->fecha->format('d/m/Y') }}" class="h-full w-full bg-zinc-950/5 object-cover transition duration-300" style="object-position: {{ $milkingDetailFrame['x'] }}% {{ $milkingDetailFrame['y'] }}%; transform: scale({{ $milkingDetailFrame['zoom'] }}); transform-origin: {{ $milkingDetailFrame['x'] }}% {{ $milkingDetailFrame['y'] }}%;">
            </a>
        </div>
    @endif

    <!-- Overview Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80">
            <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Total de Leche</span>
            <span class="text-2xl font-black text-zinc-100 mt-1 block">{{ $ordeno->litros_total }} Litros</span>
        </div>
        <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80">
            <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Vacas Ordeñadas</span>
            <span class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1 block">{{ $ordeno->cantidad_vacas }} cabezas</span>
        </div>
        <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80">
            <span class="block text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Promedio por Vaca</span>
            <span class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1 block">
                {{ $ordeno->cantidad_vacas > 0 ? round($ordeno->litros_total / $ordeno->cantidad_vacas, 2) . ' Lts' : '0 Lts' }}
            </span>
        </div>
    </div>

    <!-- Observations -->
    @if($ordeno->observaciones)
        <div class="p-6 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 border-b border-zinc-850 pb-2">Observaciones</h3>
            <p class="text-sm text-zinc-350 leading-relaxed italic">"{{ $ordeno->observaciones }}"</p>
        </div>
    @endif

    <!-- Individual Details list -->
    @if($ordeno->tipo_registro === 'individual')
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-zinc-200">Producción Individual Detallada</h3>

            <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                            <th class="p-4">Vaca</th>
                            <th class="p-4">Raza</th>
                            <th class="p-4">Producción</th>
                            <th class="p-4">Incidencia / Causa Excepción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                        @foreach($ordeno->detalles as $det)
                            @php
                                $animal = $det->animal;
                            @endphp
                            <tr class="hover:bg-zinc-850/20 transition duration-200">
                                <td class="p-4">
                                    @if($animal && auth()->user()->tienePermiso('animal', 'leer'))
                                        <a href="{{ route('animal.show', $animal->id) }}" class="font-bold text-zinc-100 transition hover:text-emerald-700 dark:hover:text-emerald-400">
                                            {{ $animal->arete }}
                                        </a>
                                    @else
                                        <span class="font-bold text-zinc-100">{{ $animal?->arete ?? 'Animal no disponible' }}</span>
                                    @endif
                                    <span class="text-zinc-500 text-xs block mt-0.5">{{ $animal?->nombre ?? 'Sin nombre' }}</span>
                                </td>
                                <td class="p-4 text-zinc-400">{{ $animal?->raza?->nombre ?? '-' }}</td>
                                <td class="p-4">
                                    @if($det->litros > 0)
                                        <span class="font-bold text-zinc-100">{{ $det->litros }} Litros</span>
                                    @else
                                        <span class="text-zinc-550 font-bold">-</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($det->causa_excepcion)
                                        <x-status-badge :value="$det->causa_excepcion" :label="'Excepción: '.ucfirst($det->causa_excepcion).($det->causa_excepcion === 'otros' && $det->justificacion_otros ? ' ('.$det->justificacion_otros.')' : '')" tone="rose" />
                                    @else
                                        <x-status-badge value="activo" label="Sin incidencias" tone="emerald" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
