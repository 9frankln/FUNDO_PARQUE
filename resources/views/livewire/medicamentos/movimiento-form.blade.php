<div class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <header class="mb-6 flex items-start gap-4">
        <a wire:navigate href="{{ route('medicamentos.show', $medicamento->id) }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-sky-500/40 hover:bg-sky-50/50 hover:text-sky-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-sky-500/40 dark:hover:bg-sky-950/25 dark:hover:text-sky-300"
           aria-label="Volver">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
            </svg>
        </a>
        <div>
            <p class="text-[10px] font-black uppercase tracking-[.18em] text-sky-600 dark:text-sky-400">Corrección de inventario</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">Ajustar existencias</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $medicamento->nombre }} · no usar para aplicaciones a animales.</p>
        </div>
    </header>

    <form wire:submit="save" autocomplete="off">
        <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-sky-500 bg-white shadow-sm dark:border-zinc-800 dark:border-t-sky-500 dark:bg-zinc-900">
            <div class="p-5 sm:p-7">
                <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Movimiento</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['ajuste_salida' => 'Salida', 'descarte' => 'Descarte', 'ajuste_entrada' => 'Entrada'] as $key => $label)
                        <button type="button" wire:click="$set('tipo', '{{ $key }}')" class="h-11 rounded-xl border text-xs font-bold transition {{ $tipo === $key ? 'border-sky-500 bg-sky-50 text-sky-800 ring-2 ring-sky-500/15 dark:bg-sky-400/10 dark:text-sky-300' : 'border-zinc-200 bg-white text-zinc-500 hover:border-sky-300 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400' }}">{{ $label }}</button>
                    @endforeach
                </div>

                @php $lotOptions=['' => 'Selecciona un lote']; foreach($lotes as $lot){$lotOptions[(string)$lot->id]='Lote '.$lot->numero_lote.' · '.$lot->cantidad_disponible.' '.$medicamento->unidad_stock.' · vence '.$lot->fecha_vencimiento->format('d/m/Y');} @endphp
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Lote <span class="font-bold text-sky-600 dark:text-sky-400">*</span></label>
                        <x-filter-select model="loteId" :options="$lotOptions" tone="sky" />
                        @error('loteId')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ajuste-cantidad">Cantidad <span class="font-bold text-sky-600 dark:text-sky-400">*</span></label>
                        <div class="relative">
                            <input id="ajuste-cantidad" type="number" wire:model="cantidad" step="0.001" min="0.001" inputmode="decimal" placeholder="0" class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-3.5 pr-16 text-sm font-semibold text-zinc-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs font-bold text-zinc-400">{{ $medicamento->unidad_stock }}</span>
                        </div>
                        @error('cantidad')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="ajuste-motivo">Motivo <span class="font-bold text-sky-600 dark:text-sky-400">*</span></label>
                        <input id="ajuste-motivo" type="text" wire:model="detalle" maxlength="500" placeholder="Ej. Frasco roto" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        @error('detalle')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <details class="group mt-4 rounded-xl border border-zinc-200 bg-zinc-50/70 dark:border-zinc-800 dark:bg-zinc-950/40" @error('fecha') open @enderror>
                    <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-xs font-bold text-zinc-600 outline-none transition hover:text-sky-700 focus-visible:ring-2 focus-visible:ring-sky-500/40 dark:text-zinc-300 dark:hover:text-sky-300">
                        <span>Cambiar fecha <small class="ml-1 font-normal text-zinc-400">(hoy por defecto)</small></span>
                        <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                        <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Fecha</label>
                        <x-date-picker model="fecha" placeholder="Fecha" />
                        @error('fecha')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </div>
                </details>
            </div>
            <footer class="flex flex-col-reverse gap-3 border-t border-zinc-200/90 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:justify-end sm:px-7">
                <a wire:navigate href="{{ route('medicamentos.show', $medicamento->id) }}" class="agro-button-secondary">Cancelar</a>
                <button type="submit" wire:loading.attr="disabled" class="agro-button">
                    <span wire:loading.remove wire:target="save">Guardar ajuste</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </footer>
        </section>
    </form>
</div>
