<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('finanzas.index', ['tab' => 'asignaciones']) }}"
               aria-label="Volver a asignaciones familiares"
               class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 text-zinc-400 transition hover:border-zinc-700 hover:text-zinc-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-violet-400">Asignación familiar</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-zinc-100">{{ $asignacion->beneficiario }}</h1>
                <p class="mt-1 text-sm text-zinc-500">Entrega del {{ $asignacion->fecha->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->tienePermiso('finanzas', 'actualizar'))
                <a href="{{ route('finanzas.asignacion.edit', $asignacion->id) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/10 px-5 py-2.5 text-sm font-bold text-amber-300 transition hover:border-amber-400 hover:bg-amber-500/15">
                    Editar asignación
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
        <section class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900">
            <div class="aspect-[4/3] bg-zinc-950">
                @if($asignacion->foto_ruta)
                    @php
                        $assignmentDetailFrame = \App\Support\ImageFrame::normalize($asignacion->foto_encuadre);
                    @endphp
                    <a href="{{ route('asignacion.foto', $asignacion) }}" target="_blank" rel="noopener" title="Abrir foto completa">
                        <img src="{{ route('asignacion.foto', $asignacion) }}" alt="Foto de respaldo de {{ $asignacion->beneficiario }}" class="h-full w-full object-cover" decoding="async" style="object-position: {{ $assignmentDetailFrame['x'] }}% {{ $assignmentDetailFrame['y'] }}%; transform: scale({{ $assignmentDetailFrame['zoom'] }}); transform-origin: {{ $assignmentDetailFrame['x'] }}% {{ $assignmentDetailFrame['y'] }}%;">
                    </a>
                @else
                    <div class="flex h-full flex-col items-center justify-center gap-3 text-zinc-600">
                        <svg class="h-14 w-14" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" d="m5 19 14-14" /></svg>
                        <span class="text-sm">Sin foto de respaldo</span>
                    </div>
                @endif
            </div>
            <div class="border-t border-zinc-800 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Monto entregado</p>
                <p class="mt-1 text-3xl font-extrabold text-violet-300">S/. {{ number_format((float) $asignacion->monto, 2) }}</p>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-800 bg-zinc-900 p-6 sm:p-8">
            <h2 class="text-base font-bold text-zinc-100">Detalle de la asignación</h2>
            <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Beneficiario</dt>
                    <dd class="mt-1.5 font-semibold text-zinc-100">{{ $asignacion->beneficiario }}</dd>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Propósito</dt>
                    <dd class="mt-1.5 font-semibold text-zinc-100">{{ ucfirst(str_replace('_', ' ', $asignacion->proposito)) }}</dd>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Fecha</dt>
                    <dd class="mt-1.5 font-semibold text-zinc-100">{{ $asignacion->fecha->format('d/m/Y') }}</dd>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Moneda</dt>
                    <dd class="mt-1.5 font-semibold text-zinc-100">{{ $asignacion->moneda }}</dd>
                </div>
            </dl>

            <div class="mt-5 rounded-2xl border border-zinc-800 bg-zinc-950/60 p-4">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Descripción</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-300">{{ $asignacion->descripcion ?: 'Sin descripción adicional.' }}</p>
            </div>
        </section>
    </div>

</div>
