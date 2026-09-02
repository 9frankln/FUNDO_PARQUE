<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <a wire:navigate href="{{ route('queso.index') }}"
               class="shrink-0 p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Registro de producción</p>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-100 mt-1">
                    Elaboración del {{ $produccion->fecha->format('d/m/Y') }}
                </h1>
                <p class="text-sm text-zinc-500 mt-1">Detalle completo del lote de queso elaborado.</p>
            </div>
        </div>

        @if(auth()->user()->tienePermiso('queso', 'actualizar'))
            <a wire:navigate href="{{ route('queso.edit', $produccion->id) }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-zinc-950 shadow-lg shadow-emerald-500/15 transition hover:bg-emerald-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 15.07a4.5 4.5 0 0 1-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125V18A2.625 2.625 0 0 1 16.875 20.625H5.625A2.625 2.625 0 0 1 3 18V6.75a2.625 2.625 0 0 1 2.625-2.625H15"></path>
                </svg>
                Editar registro
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:items-start">
        <div class="space-y-6 lg:col-span-2">
            <div class="grid grid-cols-2 {{ $produccion->litros_leche_usados ? 'sm:grid-cols-3' : '' }} gap-4">
                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Quesos elaborados</span>
                    <span class="block mt-1 text-3xl font-black text-zinc-100">{{ $produccion->unidades }}</span>
                    <span class="text-xs text-zinc-500">unidades</span>
                </div>
                <div class="rounded-2xl border border-teal-500/20 bg-teal-500/10 p-5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Peso total</span>
                    <span class="block mt-1 text-3xl font-black text-teal-600 dark:text-teal-400">{{ number_format((float) $produccion->peso_total_kg, 2) }}</span>
                    <span class="text-xs text-zinc-500">kilogramos</span>
                </div>
                @if($produccion->litros_leche_usados)
                    <div class="col-span-2 sm:col-span-1 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Leche usada / Rendimiento</span>
                        <span class="block mt-1 text-2xl font-black text-amber-500">{{ number_format((float) $produccion->litros_leche_usados, 1) }} L</span>
                        <span class="text-xs font-semibold text-zinc-400">{{ $produccion->litros_por_kg }} L/kg ({{ $produccion->rendimiento_porcentaje }}%)</span>
                    </div>
                @endif
            </div>

            <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900">
                <div class="border-b border-zinc-800 px-5 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Distribución por presentación</h2>
                </div>

                @if($produccion->presentaciones->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-zinc-950 text-[10px] font-bold uppercase tracking-wider text-zinc-500">
                                <tr>
                                    <th class="px-5 py-3">Peso unitario</th>
                                    <th class="px-5 py-3 text-center">Cantidad</th>
                                    <th class="px-5 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/70 text-sm">
                                @foreach($produccion->presentaciones as $presentacion)
                                    <tr>
                                        <td class="px-5 py-4 font-bold text-zinc-200">{{ \App\Models\ProduccionQuesoPresentacion::pesoLabel($presentacion->peso_gramos) }}</td>
                                        <td class="px-5 py-4 text-center text-zinc-400">{{ $presentacion->cantidad }} unidades</td>
                                        <td class="px-5 py-4 text-right font-bold text-teal-600 dark:text-teal-400">{{ number_format($presentacion->subtotal_kg, 2) }} kg</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-zinc-700 bg-zinc-950/60 text-sm font-bold">
                                <tr>
                                    <td class="px-5 py-4 text-zinc-400">Total calculado</td>
                                    <td class="px-5 py-4 text-center text-zinc-200">{{ $produccion->unidades }} unidades</td>
                                    <td class="px-5 py-4 text-right text-emerald-600 dark:text-emerald-400">{{ number_format((float) $produccion->peso_total_kg, 2) }} kg</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="p-5">
                        <div class="rounded-xl border border-amber-500/25 bg-amber-500/10 p-4">
                            <p class="text-sm font-bold text-amber-600 dark:text-amber-400">Registro anterior sin desglose</p>
                            <p class="text-xs text-zinc-500 mt-1">Totales históricos conservados. Edita registro para agregar presentaciones.</p>
                        </div>
                    </div>
                @endif
            </section>

            @if($produccion->observaciones)
                <section class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Observaciones</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-zinc-300">{{ $produccion->observaciones }}</p>
                </section>
            @endif
        </div>

        <aside class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Foto del lote</h2>
            @if($produccion->foto_ruta)
                @php
                    $cheeseDetailFrame = \App\Support\ImageFrame::normalize($produccion->foto_encuadre);
                @endphp
                <a href="{{ asset('storage/'.$produccion->foto_ruta) }}" target="_blank" rel="noopener" class="group mt-4 block overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950">
                    <img src="{{ asset('storage/'.$produccion->foto_ruta) }}"
                         alt="Producción de queso del {{ $produccion->fecha->format('d/m/Y') }}"
                         class="aspect-square w-full object-cover transition duration-300"
                         style="object-position: {{ $cheeseDetailFrame['x'] }}% {{ $cheeseDetailFrame['y'] }}%; transform: scale({{ $cheeseDetailFrame['zoom'] }}); transform-origin: {{ $cheeseDetailFrame['x'] }}% {{ $cheeseDetailFrame['y'] }}%;"
                         decoding="async">
                </a>
                <p class="mt-3 text-center text-[11px] text-zinc-500">Haz clic para ampliar imagen</p>
            @else
                <div class="mt-4 flex aspect-square flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-800 bg-zinc-950 text-center">
                    <svg class="w-10 h-10 text-zinc-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"></path>
                    </svg>
                    <span class="mt-2 text-xs font-semibold text-zinc-600">Sin fotografía</span>
                </div>
            @endif
        </aside>
    </div>
</div>
