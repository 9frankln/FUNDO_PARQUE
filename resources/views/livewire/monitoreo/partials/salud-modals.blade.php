<div x-cloak x-show="$wire.showVerCasoModal" x-transition.opacity
     x-on:keydown.escape.window="$wire.closeVerCasoModal()" x-on:click.self="$wire.closeVerCasoModal()"
     class="fixed inset-0 z-[10000] flex items-center justify-center overflow-y-auto bg-zinc-950/70 p-4 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div class="w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white p-5 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
        <header class="flex items-start justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-800">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-600">Historial de salud</p>
                <h3 class="mt-1 text-xl font-black text-zinc-900 dark:text-zinc-100">{{ $verCasoData['categoria_label'] ?? 'Evento de salud' }}</h3>
                <p class="mt-1 text-xs text-zinc-500">
                    @if(!empty($verCasoData['animal_url']))<a href="{{ $verCasoData['animal_url'] }}" wire:navigate class="font-bold text-emerald-600 hover:underline">{{ $verCasoData['arete'] ?? '-' }} · {{ $verCasoData['nombre'] ?? '-' }}</a>@endif
                    · {{ $verCasoData['fecha_evento'] ?? '-' }}
                </p>
            </div>
            <button type="button" wire:click="closeVerCasoModal" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800" aria-label="Cerrar">×</button>
        </header>

        @if(!empty($verCasoData))
            <div class="mt-4 space-y-4">
                <dl class="grid grid-cols-2 gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/50 sm:grid-cols-4">
                    <div><dt class="text-[9px] font-black uppercase text-zinc-500">Tipo</dt><dd class="mt-1 text-sm font-bold">{{ ucfirst(str_replace('_', ' ', $verCasoData['subtipo'] ?? 'otro')) }}</dd></div>
                    <div><dt class="text-[9px] font-black uppercase text-zinc-500">Estado</dt><dd class="mt-1 text-sm font-bold">{{ $verCasoData['estado_label'] ?? '-' }}</dd></div>
                    <div><dt class="text-[9px] font-black uppercase text-zinc-500">Severidad</dt><dd class="mt-1 text-sm font-bold">{{ ucfirst($verCasoData['severidad'] ?? '') ?: '-' }}</dd></div>
                    <div><dt class="text-[9px] font-black uppercase text-zinc-500">Zona corporal</dt><dd class="mt-1 text-sm font-bold">{{ $verCasoData['ubicacion_corporal'] ?? '-' }}</dd></div>
                </dl>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <section class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800"><h4 class="text-[10px] font-black uppercase text-zinc-500">Hallazgo o motivo</h4><p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $verCasoData['sintomas'] ?? '-' }}</p></section>
                    <section class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800"><h4 class="text-[10px] font-black uppercase text-zinc-500">Atención e indicaciones</h4><p class="mt-2 text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $verCasoData['tratamiento'] ?? '-' }}</p>@if(($verCasoData['producto_marca'] ?? '-') !== '-')<p class="mt-2 text-xs font-bold text-emerald-600">{{ $verCasoData['producto_marca'] }}</p>@endif</section>
                </div>
                @if(!empty($verCasoData['dosisPlan']))
                    <section class="rounded-2xl border border-amber-500/20 bg-amber-50/50 p-4 dark:bg-amber-950/10">
                        <h4 class="text-[10px] font-black uppercase text-amber-700 dark:text-amber-300">Plan de dosis</h4>
                        <div class="mt-3 space-y-2">
                            @foreach($verCasoData['dosisPlan'] as $dosis)
                                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs dark:border-zinc-800 dark:bg-zinc-900">
                                    <span class="font-black">D{{ $dosis['numero'] }} · {{ $dosis['nombre'] }}</span>
                                    <span class="font-bold {{ $dosis['aplicada'] ? 'text-emerald-600' : 'text-amber-600' }}">{{ $dosis['aplicada'] ? 'Aplicada '.$dosis['fecha_aplicada'] : 'Programada '.$dosis['fecha_programada'] }}</span>
                                    @if($dosis['dosis'] || $dosis['via'])<span class="w-full text-zinc-500">{{ $dosis['dosis'] ?: 'Sin cantidad' }} · {{ $dosis['via'] ?: 'Sin vía' }}</span>@endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
                @if(!empty($verCasoData['fotos']))
                    <section><h4 class="text-[10px] font-black uppercase text-zinc-500">Evidencia</h4><div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">@foreach($verCasoData['fotos'] as $foto)<a href="{{ $foto['url'] }}" target="_blank" class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800"><img src="{{ $foto['url'] }}" alt="Evidencia" class="h-20 w-full object-cover"></a>@endforeach</div></section>
                @endif
            </div>
        @endif
        <footer class="mt-5 flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
            <button type="button" wire:click="closeVerCasoModal" class="rounded-xl border border-zinc-300 px-5 py-2.5 text-sm font-bold dark:border-zinc-700">Cerrar</button>
            @if(auth()->user()->tienePermiso('monitoreo', 'actualizar') && $verCasoId)<a href="{{ route('monitoreo.sanidad.edit', $verCasoId) }}" wire:navigate class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-black text-emerald-950">Editar evento</a>@endif
        </footer>
    </div>
</div>

<div x-cloak x-show="$wire.showRecuperarCasoModal" x-transition.opacity
     x-on:keydown.escape.window="$wire.closeRecuperarCasoModal()" x-on:click.self="$wire.closeRecuperarCasoModal()"
     class="fixed inset-0 z-[10001] flex items-center justify-center bg-zinc-950/70 p-4 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div class="w-full max-w-lg rounded-3xl border border-zinc-200 bg-white p-5 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
        <header class="flex items-start justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-800">
            <div><p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-600">Seguimiento</p><h3 class="mt-1 text-xl font-black">Finalizar evento</h3><p class="mt-1 text-xs text-zinc-500">Confirma el cierre; el historial permanece disponible.</p></div>
            <button type="button" wire:click="closeRecuperarCasoModal" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800">×</button>
        </header>
        @if(!empty($recuperarCasoData))
            <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/50">
                <p class="text-sm font-black">{{ $recuperarCasoData['arete'] }} · {{ $recuperarCasoData['categoria'] ?? 'Evento de salud' }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $recuperarCasoData['diagnostico'] }}</p>
                <p class="mt-2 text-[10px] font-bold text-zinc-500">{{ $recuperarCasoData['aplicadas'] }} dosis aplicadas · {{ $recuperarCasoData['pendientes'] }} pendientes</p>
            </div>
        @endif
        <div class="mt-4 space-y-3">
            <label class="block"><span class="mb-1.5 block text-xs font-bold">Fecha de cierre</span><x-date-picker model="recuperarCasoFecha" :max="now()->toDateString()" placeholder="dd/mm/aaaa" />@error('recuperarCasoFecha')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror</label>
            <label class="block"><span class="mb-1.5 block text-xs font-bold">Observación final <span class="font-normal text-zinc-500">(opcional)</span></span><textarea wire:model="recuperarCasoObservaciones" rows="3" maxlength="1000" class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950" placeholder="Evolución y condición final"></textarea></label>
        </div>
        <footer class="mt-5 flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
            <button type="button" wire:click="closeRecuperarCasoModal" class="rounded-xl border border-zinc-300 px-5 py-2.5 text-sm font-bold dark:border-zinc-700">Cancelar</button>
            <button type="button" wire:click="confirmarRecuperacion" wire:loading.attr="disabled" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-black text-emerald-950 disabled:opacity-60">Finalizar seguimiento</button>
        </footer>
    </div>
</div>
