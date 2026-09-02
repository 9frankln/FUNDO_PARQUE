<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('finanzas.index', ['tab' => 'movimientos']) }}"
               aria-label="Volver a movimientos de caja"
               class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 text-zinc-400 transition hover:border-zinc-700 hover:text-zinc-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-400">Movimiento de caja</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-zinc-100">{{ $movimiento->categoria?->nombre ?? 'Sin categoría' }}</h1>
                <p class="mt-1 text-sm text-zinc-500">Referencia MOV-{{ str_pad((string) $movimiento->id, 6, '0', STR_PAD_LEFT) }} · {{ $movimiento->fecha->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->tienePermiso('finanzas', 'actualizar'))
                <a href="{{ route('finanzas.movimiento.edit', $movimiento->id) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/10 px-5 py-2.5 text-sm font-bold text-amber-300 transition hover:border-amber-400 hover:bg-amber-500/15">
                    Editar movimiento
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
        <section class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
            <div class="aspect-[4/3] bg-zinc-950">
                @if($movimiento->compraMedicamento?->medicamento?->foto_ruta)
                    @php
                        $sharedMedicine = $movimiento->compraMedicamento->medicamento;
                        $receiptDetailFrame = \App\Support\ImageFrame::normalize($sharedMedicine->foto_encuadre);
                        $receiptUrl = asset('storage/'.$sharedMedicine->foto_ruta).'?v='.sha1($sharedMedicine->foto_ruta);
                    @endphp
                    <a href="{{ route('medicamentos.show', $sharedMedicine->id) }}" wire:navigate title="Abrir medicamento">
                        <img src="{{ $receiptUrl }}" alt="Foto de {{ $sharedMedicine->nombre }}" class="h-full w-full object-cover" decoding="async" style="object-position: {{ $receiptDetailFrame['x'] }}% {{ $receiptDetailFrame['y'] }}%; transform: scale({{ $receiptDetailFrame['zoom'] }}); transform-origin: {{ $receiptDetailFrame['x'] }}% {{ $receiptDetailFrame['y'] }}%;">
                    </a>
                @elseif($movimiento->compraInsumo?->insumo?->foto_ruta)
                    @php
                        $sharedInsumo = $movimiento->compraInsumo->insumo;
                        $receiptDetailFrame = \App\Support\ImageFrame::normalize($sharedInsumo->foto_encuadre);
                        $receiptUrl = asset('storage/'.$sharedInsumo->foto_ruta).'?v='.sha1($sharedInsumo->foto_ruta);
                    @endphp
                    <a href="{{ route('insumos.show', $sharedInsumo->id) }}" wire:navigate title="Abrir insumo">
                        <img src="{{ $receiptUrl }}" alt="Foto de {{ $sharedInsumo->nombre }}" class="h-full w-full object-cover" decoding="async" style="object-position: {{ $receiptDetailFrame['x'] }}% {{ $receiptDetailFrame['y'] }}%; transform: scale({{ $receiptDetailFrame['zoom'] }}); transform-origin: {{ $receiptDetailFrame['x'] }}% {{ $receiptDetailFrame['y'] }}%;">
                    </a>
                @elseif($movimiento->comprobante_ruta && $movimiento->comprobanteEsImagen())
                    @php
                        $receiptDetailFrame = \App\Support\ImageFrame::normalize($movimiento->comprobante_encuadre);
                        $receiptUrl = route('movimiento.comprobante', $movimiento).'?v='.sha1($movimiento->comprobante_ruta);
                    @endphp
                    <a href="{{ $receiptUrl }}" target="_blank" rel="noopener" title="Abrir comprobante completo">
                        <img src="{{ $receiptUrl }}" alt="Comprobante del movimiento" class="h-full w-full object-cover" decoding="async" style="object-position: {{ $receiptDetailFrame['x'] }}% {{ $receiptDetailFrame['y'] }}%; transform: scale({{ $receiptDetailFrame['zoom'] }}); transform-origin: {{ $receiptDetailFrame['x'] }}% {{ $receiptDetailFrame['y'] }}%;">
                    </a>
                @elseif($movimiento->comprobante_ruta)
                    <div class="flex h-full flex-col items-center justify-center gap-4 text-zinc-500">
                        <svg class="h-16 w-16 text-rose-400" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M9 15h6M9 11h2" /></svg>
                        <a href="{{ route('movimiento.comprobante', $movimiento) }}" target="_blank" rel="noopener" class="rounded-xl border border-rose-500/25 bg-rose-500/10 px-4 py-2 text-sm font-bold text-rose-300">Abrir comprobante PDF</a>
                    </div>
                @else
                    <div class="flex h-full flex-col items-center justify-center gap-3 text-zinc-600">
                        <svg class="h-14 w-14" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" d="m5 19 14-14" /></svg>
                        <span class="text-sm">Sin comprobante adjunto</span>
                    </div>
                @endif
            </div>
            <div class="border-t border-zinc-800 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Monto del movimiento</p>
                <p class="mt-1 text-3xl font-extrabold {{ $movimiento->tipo === 'ingreso' ? 'text-emerald-300' : 'text-rose-300' }}">
                    {{ $movimiento->tipo === 'ingreso' ? '+' : '-' }} S/. {{ number_format((float) $movimiento->monto, 2) }}
                </p>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6 sm:p-8">
            <h2 class="text-base font-bold text-zinc-100">Detalle del movimiento</h2>
            <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo</dt>
                    <dd class="mt-1.5 font-semibold {{ $movimiento->tipo === 'ingreso' ? 'text-emerald-300' : 'text-rose-300' }}">{{ ucfirst($movimiento->tipo) }}</dd>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Categoría</dt>
                    <dd class="mt-1.5 font-semibold text-zinc-100">{{ $movimiento->categoria?->nombre ?? 'Sin categoría' }}</dd>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Fecha</dt>
                    <dd class="mt-1.5 font-semibold text-zinc-100">{{ $movimiento->fecha->format('d/m/Y') }}</dd>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Moneda</dt>
                    <dd class="mt-1.5 font-semibold text-zinc-100">{{ $movimiento->moneda }}</dd>
                </div>
                @if($movimiento->beneficiario && ! $movimiento->compraMedicamento && ! $movimiento->compraInsumo)
                    <div class="rounded-2xl border border-violet-500/25 bg-violet-500/10 p-4">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-violet-400">Beneficiario</dt>
                        <dd class="mt-1.5 font-bold text-violet-100">{{ $movimiento->beneficiario }}</dd>
                    </div>
                @endif
                @if($movimiento->proposito && ! $movimiento->compraMedicamento && ! $movimiento->compraInsumo)
                    <div class="rounded-2xl border border-violet-500/25 bg-violet-500/10 p-4">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-violet-400">Propósito</dt>
                        <dd class="mt-1.5 font-bold text-violet-100">{{ ucfirst(str_replace('_', ' ', $movimiento->proposito)) }}</dd>
                    </div>
                @endif
                @if($medicineLot = $movimiento->compraMedicamento)
                    <div class="rounded-2xl border border-cyan-500/25 bg-cyan-500/10 p-4 sm:col-span-2">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-cyan-400">Compra de medicamento</dt>
                        <dd class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-cyan-100">
                            <a href="{{ route('medicamentos.show', $medicineLot->medicamento_id) }}" wire:navigate class="font-black hover:text-cyan-300">{{ $medicineLot->medicamento->nombre }}</a>
                            <span>Lote {{ $medicineLot->numero_lote }}</span>
                            <span>{{ (float) $medicineLot->cantidad_inicial + 0 }} {{ $medicineLot->medicamento->unidad_stock }}</span>
                            <span>Vence {{ $medicineLot->fecha_vencimiento->format('d/m/Y') }}</span>
                        </dd>
                    </div>
                @elseif($insumoLot = $movimiento->compraInsumo)
                    <div class="rounded-2xl border border-emerald-500/25 bg-emerald-500/10 p-4 sm:col-span-2">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Compra de insumo / material</dt>
                        <dd class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-emerald-100">
                            <a href="{{ route('insumos.show', $insumoLot->insumo_id) }}" wire:navigate class="font-black hover:text-emerald-300">{{ $insumoLot->insumo->nombre }}</a>
                            <span>Lote {{ $insumoLot->numero_lote }}</span>
                            <span>{{ (float) $insumoLot->cantidad_inicial + 0 }} {{ $insumoLot->insumo->unidad_stock }}</span>
                            @if($insumoLot->fecha_vencimiento)
                                <span>Vence {{ $insumoLot->fecha_vencimiento->format('d/m/Y') }}</span>
                            @else
                                <span>No perecible</span>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>

            <div class="mt-5 rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Descripción</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-300">{{ $movimiento->descripcionLegible() ?: 'Sin descripción adicional.' }}</p>
            </div>

            @if($movimiento->animalesVendidos->isNotEmpty())
                <div class="mt-5">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Animales vendidos</h3>
                    <p class="mt-1 text-xs text-zinc-500">Toca el código para abrir la ficha del animal.</p>
                    @if($compradorVenta = $movimiento->compradorVentaAnimal())
                        <p class="mt-2 text-xs font-semibold text-zinc-400">Comprador: <span class="text-zinc-200">{{ $compradorVenta }}</span></p>
                    @endif
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($movimiento->animalesVendidos as $animalVendido)
                            <a href="{{ route('animal.show', $animalVendido->id) }}"
                               wire:navigate
                               aria-label="Ver ficha del animal {{ $animalVendido->arete }}"
                               class="group flex items-center gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-3 outline-none transition hover:border-emerald-400/50 hover:bg-emerald-500/10 focus-visible:ring-2 focus-visible:ring-emerald-400/60">
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-zinc-800">
                                    @if($animalVendido->foto_ruta)
                                        <img src="{{ asset('storage/' . $animalVendido->foto_ruta) }}" alt="{{ $animalVendido->nombre }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-lg" aria-hidden="true">🐄</div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-emerald-300 group-hover:text-emerald-200">{{ $animalVendido->arete }}</p>
                                    <p class="truncate text-xs text-zinc-400">{{ $animalVendido->nombre ?: 'Sin nombre' }}</p>
                                </div>
                                <svg class="ml-auto h-4 w-4 shrink-0 text-zinc-600 transition group-hover:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>

</div>
