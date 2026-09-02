@props([
    'nombre' => '',
    'unidad' => '',
    'wireSubmit' => 'saveLote',
    'codigoAnio',
    'numeroLote' => '',
])

<div
    x-data="{ tipoIngreso: @entangle('lTipoIngreso') }"
    x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
    class="agro-dialog-overlay"
    x-on:keydown.escape.window="$wire.closeLoteModal()">
    <section role="dialog" aria-modal="true" aria-label="Reabastecer stock" class="agro-dialog agro-dialog--md agro-dialog--scroll">
        <header class="flex shrink-0 items-start justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Entrada de inventario</p>
                <h2 class="mt-0.5 text-lg font-black text-zinc-900 dark:text-white">Reabastecer stock</h2>
                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $nombre }} · {{ $unidad }}</p>
            </div>
            <button type="button" wire:click="closeLoteModal" class="agro-icon-button !h-9 !w-9 shrink-0" aria-label="Cerrar">&times;</button>
        </header>

        <form wire:submit="{{ $wireSubmit }}" class="agro-dialog__scroll">
            <div class="space-y-4 p-5">
                {{-- Motivo de entrada --}}
                <div>
                    <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Motivo de entrada <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['compra' => ['🛒', 'Compra'], 'donacion' => ['🎁', 'Donación'], 'saldo_inicial' => ['📦', 'Saldo Inicial']] as $val => [$ico, $lab])
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="lTipoIngreso" value="{{ $val }}" class="peer sr-only">
                            <span class="flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-xl border border-zinc-200 px-2 py-2 text-center text-xs font-bold transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-900 hover:border-zinc-300 dark:border-zinc-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/10 dark:peer-checked:text-emerald-200">
                                <span class="text-base">{{ $ico }}</span>{{ $lab }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('lTipoIngreso')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <x-medicamento-lot-code-input
                    model="lNumeroLote"
                    error-field="lNumeroLote"
                    :year="$codigoAnio"
                    :number="$numeroLote"
                    tone="emerald"
                    id="modal-medication-lot-code"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Vencimiento --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Vencimiento <span class="text-rose-500">*</span></label>
                        <x-date-picker model="lFechaVencimiento" placeholder="Fecha de vencimiento" />
                        @error('lFechaVencimiento')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    {{-- Cantidad --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="med-lote-cantidad">Contenido neto <span class="text-rose-500">*</span></label>
                        <div class="relative"><input id="med-lote-cantidad" type="number" wire:model="lCantidad" step="0.001" min="0.001" inputmode="decimal" placeholder="0" class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-3.5 pr-14 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950"><span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs font-bold text-zinc-400">{{ $unidad }}</span></div>
                        @error('lCantidad')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 dark:border-zinc-800 dark:bg-zinc-950/40">
                    <div class="px-4 py-3 text-xs font-bold text-zinc-600 dark:text-zinc-300">
                        Datos opcionales (fecha, costo, ubicación)
                    </div>
                    <div class="grid gap-4 border-t border-zinc-200 px-4 py-4 dark:border-zinc-800 sm:grid-cols-2">
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Fecha de ingreso</label><x-date-picker model="lFechaIngreso" placeholder="Fecha" />@error('lFechaIngreso')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="med-lote-proveedor"><span x-text="tipoIngreso === 'donacion' ? 'Donante' : (tipoIngreso === 'saldo_inicial' ? 'Entregado por' : 'Proveedor')"></span></label><input id="med-lote-proveedor" type="text" wire:model="lProveedor" maxlength="255" placeholder="Opcional" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error('lProveedor')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>

                        <div class="contents" x-show="tipoIngreso === 'compra'" x-cloak>
                            <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="med-lote-costo">Costo total (S/.)</label><input id="med-lote-costo" type="number" wire:model="lCostoTotal" step="0.01" min="0" inputmode="decimal" placeholder="0.00" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error('lCostoTotal')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                            <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="med-lote-comprobante">Comprobante</label><input id="med-lote-comprobante" type="text" wire:model="lComprobante" maxlength="100" placeholder="Boleta o factura" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error('lComprobante')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                        </div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="med-lote-ubicacion">Ubicación</label><input id="med-lote-ubicacion" type="text" wire:model="lUbicacion" maxlength="255" placeholder="Estante o refrigeradora" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error('lUbicacion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="med-lote-observaciones">Observación</label><input id="med-lote-observaciones" type="text" wire:model="lObservaciones" maxlength="2000" placeholder="Solo si es necesaria" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error('lObservaciones')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                    </div>
                </div>
            </div>

            <footer class="flex shrink-0 flex-col-reverse gap-3 border-t border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:justify-end">
                <button type="button" wire:click="closeLoteModal" class="inline-flex h-11 items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 text-sm font-bold text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="{{ $wireSubmit }}" class="h-11 rounded-xl bg-emerald-600 px-6 text-sm font-black text-white transition hover:bg-emerald-500 disabled:opacity-60">
                    <span wire:loading.remove wire:target="{{ $wireSubmit }}">Guardar entrada</span>
                    <span wire:loading wire:target="{{ $wireSubmit }}">Guardando...</span>
                </button>
            </footer>
        </form>
    </section>
</div>
