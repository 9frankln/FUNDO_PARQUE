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
                @if($movimiento->comprobante_ruta && $movimiento->comprobanteEsImagen())
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
            </dl>

            <div class="mt-5 rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Descripción</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-300">{{ $movimiento->descripcion ?: 'Sin descripción adicional.' }}</p>
            </div>
        </section>
    </div>

</div>
