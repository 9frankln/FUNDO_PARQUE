<div class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <header class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex min-w-0 items-start gap-3">
            <a wire:navigate href="{{ route('medicamentos.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-white dark:border-zinc-700 dark:hover:bg-zinc-800" aria-label="Volver a medicamentos"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
            <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-white text-amber-700 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-amber-300">@if($medicine->foto_ruta)<img src="{{ asset('storage/'.$medicine->foto_ruta) }}" alt="" class="h-full w-full object-cover">@else<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 3h6v3l3 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V8l3-2V3Z"/><path d="M9 11h6M12 8v6"/></svg>@endif</span>
            <div class="min-w-0"><p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-600 dark:text-amber-400">Ficha del botiquín</p><h1 class="truncate text-2xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-3xl">{{ $medicine->nombre }}</h1><p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $medicine->tipo_label }} · {{ $medicine->presentacion ?: 'Sin detalle de presentación' }}</p></div>
        </div>

        <div class="flex flex-wrap gap-2 lg:justify-end">
            @if(auth()->user()->tienePermiso('medicamentos', 'crear'))<button type="button" wire:click="openLoteModal" class="inline-flex h-10 items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 text-xs font-black text-white transition hover:bg-emerald-500"><span class="text-base leading-none">+</span> Reabastecer stock</button>@endif
            @if(auth()->user()->tienePermiso('monitoreo', 'crear') && $stock > 0)<a wire:navigate href="{{ route('monitoreo.sanidad.create', ['medicamento' => $medicine->id]) }}" class="inline-flex h-10 items-center rounded-xl bg-amber-500 px-3.5 text-xs font-black text-zinc-950 transition hover:bg-amber-400">Aplicar a animal</a>@endif
            @if(auth()->user()->tienePermiso('medicamentos', 'actualizar') && (int)$medicine->fundo_id === (int)session('fundo_id'))<a wire:navigate href="{{ route('medicamentos.edit', $medicine->id) }}" class="inline-flex h-10 items-center rounded-xl border border-zinc-200 bg-zinc-100 px-3.5 text-xs font-bold text-zinc-700 transition hover:bg-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Editar ficha</a>@endif
            @if(auth()->user()->tienePermiso('medicamentos', 'eliminar') && (int)$medicine->fundo_id === (int)session('fundo_id'))<button type="button" wire:click="solicitarEliminacionMedicamento" class="inline-flex h-10 items-center rounded-xl border border-rose-200 bg-rose-100 px-3.5 text-xs font-bold text-rose-700 transition hover:bg-rose-200 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/60">Eliminar</button>@endif
        </div>
    </header>

    <section class="mb-5 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 text-zinc-800 shadow-sm dark:border-zinc-800 dark:bg-zinc-800 dark:text-zinc-200" aria-label="Estado del inventario">
        <div class="grid sm:grid-cols-[1.35fr_1fr_1fr_1fr]">
            <div class="flex items-center justify-between gap-4 border-b border-zinc-800 px-5 py-4 sm:border-b-0 sm:border-r">
                <div><span class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Stock vigente</span><div class="mt-1"><strong class="text-3xl font-black tabular-nums">{{ rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') ?: '0' }}</strong> <span class="text-sm text-zinc-400">{{ $medicine->unidad_label }}</span></div></div>
                <span class="h-3 w-3 rounded-full {{ $stock <= 0 ? 'bg-rose-500' : ($stock <= (float)$medicine->stock_minimo ? 'bg-amber-400' : 'bg-emerald-400') }} shadow-[0_0_0_6px_rgba(255,255,255,.04)]"></span>
            </div>
            <div class="border-b border-zinc-800 px-5 py-4 sm:border-b-0 sm:border-r"><span class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Usar antes de</span><strong class="mt-1 block text-sm text-zinc-100">{{ $nextExpiry ? \Carbon\Carbon::parse($nextExpiry)->format('d/m/Y') : 'Sin vencimiento próximo' }}</strong><span class="mt-1 block text-[10px] text-zinc-500">FEFO automático</span></div>
            <div class="border-b border-zinc-800 px-5 py-4 sm:border-b-0 sm:border-r"><span class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Saldo vencido</span><strong class="mt-1 block text-sm {{ $expiredStock > 0 ? 'text-rose-300' : 'text-zinc-100' }}">{{ rtrim(rtrim(number_format($expiredStock, 3, '.', ''), '0'), '.') ?: '0' }} {{ $medicine->unidad_label }}</strong><span class="mt-1 block text-[10px] text-zinc-500">No se aplica</span></div>
            <div class="px-5 py-4"><span class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Conservación</span><strong class="mt-1 block text-sm text-zinc-100">{{ \App\Models\Medicamento::STORAGE_CONDITIONS[$medicine->condicion_almacenamiento] ?? 'No indicada' }}</strong><span class="mt-1 block text-[10px] text-zinc-500">Mínimo {{ rtrim(rtrim(number_format((float)$medicine->stock_minimo, 3, '.', ''), '0'), '.') }} {{ $medicine->unidad_stock }}</span></div>
        </div>
    </section>

    {{-- Tabla 1: Historial de Entradas y Existencias (Lotes) --}}
    <section class="mb-5 overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
        <div class="flex items-center justify-between border-b border-zinc-800 bg-zinc-900/50 px-5 py-4">
            <div>
                <h2 class="text-sm font-black text-zinc-800 dark:text-zinc-200">Historial de entradas y existencias</h2>
                <p class="mt-0.5 text-[10px] text-zinc-400">Saldo, vencimiento y control individual de cada lote ingresado.</p>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->tienePermiso('medicamentos', 'crear'))
                    <button type="button" wire:click="openLoteModal" class="inline-flex h-8 items-center gap-1 rounded-lg bg-emerald-600 px-2.5 text-xs font-bold text-white transition hover:bg-emerald-500">
                        <span>+ Nuevo Lote</span>
                    </button>
                @endif
                <span class="rounded-full border border-zinc-700 bg-zinc-800 px-2.5 py-1 text-[10px] font-black text-zinc-400">{{ $lots->total() }}</span>
            </div>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-[920px] border-collapse text-left">
                <caption class="sr-only">Historial de existencias</caption>
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-100 text-xs font-bold uppercase tracking-wider text-zinc-800 dark:border-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                        <th class="p-4">Estado</th>
                        <th class="p-4">Código Lote / Fecha</th>
                        <th class="p-4">Vencimiento</th>
                        <th class="p-4">Saldo / Cantidad</th>
                        <th class="p-4">Origen / Costo</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                    @forelse($lots as $lot)
                        @php
                            [$rail,$badge]=match($lot->estado){
                                'vencido'=>['bg-rose-500','bg-rose-500/10 text-rose-400 border border-rose-500/20'],
                                'por_vencer'=>['bg-amber-500','bg-amber-500/10 text-amber-400 border border-amber-500/20'],
                                'agotado'=>['bg-zinc-500','bg-zinc-500/10 text-zinc-400 border border-zinc-500/20'],
                                default=>['bg-emerald-500','bg-emerald-500/10 text-emerald-400 border border-emerald-500/20']
                            };
                        @endphp
                        <tr wire:key="lot-row-{{ $lot->id }}" class="transition duration-500 hover:bg-zinc-800/20">
                            <td class="relative p-4">
                                <span class="absolute inset-y-3 left-0 w-1 rounded-r {{ $rail }}"></span>
                                <span class="inline-flex items-center rounded-lg px-2 py-1 text-[10px] font-bold uppercase {{ $badge }}">{{ str_replace('_', ' ', $lot->estado) }}</span>
                            </td>
                            <td class="p-4">
                                <strong class="block truncate font-mono text-xs font-bold text-amber-400">{{ $lot->numero_lote }}</strong>
                                <span class="mt-0.5 block truncate text-[10px] text-zinc-500">Ingresó {{ $lot->fecha_ingreso->format('d/m/Y') }}{{ $lot->ubicacion ? ' · '.$lot->ubicacion : '' }}</span>
                            </td>
                            <td class="p-4">
                                <strong class="text-xs {{ $lot->estado === 'vencido' ? 'text-rose-400' : ($lot->estado === 'por_vencer' ? 'text-amber-400' : 'text-zinc-200') }}">{{ $lot->fecha_vencimiento->format('d/m/Y') }}</strong>
                                <span class="mt-0.5 block text-[10px] text-zinc-500">{{ $lot->fecha_vencimiento->diffForHumans() }}</span>
                            </td>
                            <td class="p-4">
                                <strong class="text-sm font-black tabular-nums text-zinc-100">{{ rtrim(rtrim(number_format((float)$lot->cantidad_disponible, 3, '.', ''), '0'), '.') }}</strong> 
                                <span class="text-[10px] font-semibold text-zinc-500">/ {{ rtrim(rtrim(number_format((float)$lot->cantidad_inicial, 3, '.', ''), '0'), '.') }} {{ $medicine->unidad_stock }}</span>
                            </td>
                            <td class="p-4">
                                <span class="block truncate text-xs font-semibold text-zinc-300">{{ $lot->proveedor ?: 'Sin proveedor' }}</span>
                                <span class="mt-0.5 block truncate text-[10px] text-zinc-500">{{ $lot->costo_total !== null ? 'S/. '.number_format((float)$lot->costo_total, 2) : 'Sin costo' }}{{ $lot->comprobante ? ' · '.$lot->comprobante : '' }}</span>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if($lot->movimiento_id)
                                        <a wire:navigate href="{{ route('finanzas.index') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-700 text-zinc-300 transition hover:bg-zinc-800" title="Ver egreso en Finanzas">
                                            <svg class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </a>
                                    @endif
                                    @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                        <x-table-action type="delete" wire:click="solicitarEliminacionLote({{ $lot->id }})" label="Eliminar lote" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-12 text-center"><strong class="block text-sm font-bold text-zinc-400">Aún no hay existencias</strong><span class="mt-1 block text-xs text-zinc-500">Ingresa el primer lote para comenzar el control.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista Móvil Lotes --}}
        <div class="divide-y divide-zinc-800/60 md:hidden">
            @forelse($lots as $lot)
                @php $rail=match($lot->estado){'vencido'=>'bg-rose-500','por_vencer'=>'bg-amber-500','agotado'=>'bg-zinc-500',default=>'bg-emerald-500'}; @endphp
                <div class="relative p-4 pl-5 transition hover:bg-zinc-800/20">
                    <span class="absolute inset-y-3 left-0 w-1 rounded-r {{ $rail }}"></span>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <strong class="font-mono text-xs text-amber-400">{{ $lot->numero_lote }}</strong>
                            <span class="mt-1 block text-[10px] text-zinc-500">Vence {{ $lot->fecha_vencimiento->format('d/m/Y') }}</span>
                        </div>
                        <div class="text-right">
                            <strong class="block text-sm font-black text-zinc-100">{{ rtrim(rtrim(number_format((float)$lot->cantidad_disponible, 3, '.', ''), '0'), '.') }} {{ $medicine->unidad_stock }}</strong>
                            <span class="text-[10px] text-zinc-500">de {{ rtrim(rtrim(number_format((float)$lot->cantidad_inicial, 3, '.', ''), '0'), '.') }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between border-t border-zinc-800/80 pt-2 text-[10px] text-zinc-500">
                        <span>{{ $lot->proveedor ?: 'Sin proveedor' }}{{ $lot->ubicacion ? ' · '.$lot->ubicacion : '' }}</span>
                        @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                            <button type="button" wire:click="solicitarEliminacionLote({{ $lot->id }})" class="font-bold text-rose-400 hover:underline">
                                Eliminar
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-zinc-500">Aún no hay lotes.</div>
            @endforelse
        </div>
    </section>

    <!-- Paginación Lotes -->
    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <x-filter-select model="perPage" :options="\App\Support\PaginationOptions::perPageCompactOptions()" tone="amber" live compact />
        </div>
        <div class="min-w-0">
            {{ $lots->links('components.pagination') }}
        </div>
    </div>

    {{-- Tabla 2: Aplicaciones Sanitarias a Animales --}}
    <section class="mt-8 overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
        <div class="flex items-center justify-between border-b border-zinc-800 bg-zinc-900/50 px-5 py-4">
            <div>
                <h2 class="text-sm font-black text-zinc-800 dark:text-zinc-200">Historial de aplicaciones en animales</h2>
                <p class="mt-0.5 text-[10px] text-zinc-400">Dosis y tratamientos administrados con este producto.</p>
            </div>
            <span class="rounded-full border border-zinc-700 bg-zinc-800 px-2.5 py-1 text-[10px] font-black text-amber-400">{{ $aplicaciones->total() }}</span>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-[920px] border-collapse text-left">
                <caption class="sr-only">Historial de aplicaciones a animales</caption>
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-100 text-xs font-bold uppercase tracking-wider text-zinc-800 dark:border-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                        <th class="p-4">Fecha / Hora</th>
                        <th class="p-4">Animal Tratado</th>
                        <th class="p-4">Lote Usado</th>
                        <th class="p-4">Dosis Aplicada</th>
                        <th class="p-4">Motivo / Diagnóstico</th>
                        <th class="p-4">Responsable</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                    @forelse($aplicaciones as $app)
                        @php
                            $dosisVal = abs((float) $app->cantidad);
                            $dosisText = rtrim(rtrim(number_format($dosisVal, 3, '.', ''), '0'), '.') ?: '0';
                            $animal = $app->animal;
                            $lote = $app->lote;
                            $evento = $app->dosis?->eventoSalud;
                            $responsable = $app->dosis?->responsable ?: ($app->usuario?->name ?: 'Personal del fundo');
                        @endphp
                        <tr wire:key="show-app-{{ $app->id }}" class="transition duration-500 hover:bg-zinc-800/20">
                            <td class="p-4">
                                <span class="block font-mono text-xs font-bold text-zinc-100">{{ $app->fecha_hora->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-zinc-500">{{ $app->fecha_hora->format('H:i') }}</span>
                            </td>
                            <td class="p-4">
                                @if($animal)
                                    <div>
                                        <span class="font-mono text-xs font-black text-amber-400">{{ $animal->arete }}</span>
                                        @if($animal->nombre)
                                            <span class="block text-xs font-bold text-zinc-100">{{ $animal->nombre }}</span>
                                        @endif
                                        <span class="text-[10px] text-zinc-500">{{ $animal->especie?->nombre ?? 'Animal' }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-500">Animal no asignado</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($lote)
                                    <span class="inline-flex items-center rounded-lg border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 font-mono text-xs font-black text-amber-400">
                                        {{ $lote->numero_lote }}
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-500">—</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <strong class="text-sm font-black tabular-nums text-zinc-100">{{ $dosisText }}</strong>
                                <span class="text-xs font-semibold text-zinc-400">{{ $app->unidad }}</span>
                            </td>
                            <td class="p-4">
                                @if($evento)
                                    <span class="block text-xs font-bold text-zinc-200">{{ $evento->sintomas_diagnostico ?: $evento->tipo_evento }}</span>
                                    <span class="text-[10px] text-zinc-500">Caso #{{ $evento->id }}</span>
                                @else
                                    <span class="text-xs text-zinc-400">{{ $app->detalle ?: 'Aplicación de tratamiento' }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="text-xs font-semibold text-zinc-300">{{ $responsable }}</span>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if($animal)
                                        <x-table-action type="view" :href="route('animal.show', $animal->id)" label="Ver animal" />
                                    @endif
                                    @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                        <x-table-action type="delete" wire:click="solicitarEliminacionAplicacion({{ $app->id }})" label="Eliminar registro" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-sm text-zinc-500">
                                <div class="text-2xl">💊</div>
                                <div class="mt-1 text-xs font-bold text-zinc-400">Sin aplicaciones registradas</div>
                                <div class="text-[10px] text-zinc-500">Aún no se han administrado dosis de este producto.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista Móvil Aplicaciones --}}
        <div class="divide-y divide-zinc-800/60 md:hidden">
            @forelse($aplicaciones as $app)
                @php
                    $dosisVal = abs((float) $app->cantidad);
                    $dosisText = rtrim(rtrim(number_format($dosisVal, 3, '.', ''), '0'), '.') ?: '0';
                    $animal = $app->animal;
                    $lote = $app->lote;
                    $evento = $app->dosis?->eventoSalud;
                    $responsable = $app->dosis?->responsable ?: ($app->usuario?->name ?: 'Personal del fundo');
                @endphp
                <div wire:key="show-app-card-{{ $app->id }}" class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono text-xs font-black text-amber-400">{{ $animal?->arete ?? 'Sin arete' }}</span>
                            @if($animal?->nombre)
                                <span class="text-xs font-bold text-zinc-100"> · {{ $animal->nombre }}</span>
                            @endif
                        </div>
                        <span class="font-mono text-xs text-zinc-400">{{ $app->fecha_hora->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-zinc-800/60 pt-1.5">
                        <span class="text-xs text-zinc-300">{{ $evento?->sintomas_diagnostico ?: ($app->detalle ?: 'Tratamiento') }}</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-black text-zinc-100">{{ $dosisText }} {{ $app->unidad }}</span>
                            @if(auth()->user()->tienePermiso('medicamentos', 'eliminar'))
                                <button type="button" wire:click="solicitarEliminacionAplicacion({{ $app->id }})" class="font-bold text-rose-400 hover:underline text-xs">
                                    Eliminar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-zinc-500">Sin aplicaciones registradas.</div>
            @endforelse
        </div>
    </section>

    <!-- Paginación Aplicaciones -->
    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <x-filter-select model="perPageAplicaciones" :options="\App\Support\PaginationOptions::perPageCompactOptions()" tone="amber" live compact />
        </div>
        <div class="min-w-0">
            {{ $aplicaciones->links('components.pagination') }}
        </div>
    </div>

{{-- ââ€¢ââ€¢ââ€¢ MODAL: Reabastecer stock (componente compartido) ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ââ€¢ --}}
@if($showLoteModal)
    <x-medicamento-lote-modal :nombre="$medicine->nombre" :unidad="$medicine->unidad_label" :codigo-anio="$lCodigoLoteAnio" :numero-lote="$lNumeroLote" />
@endif
</div>

