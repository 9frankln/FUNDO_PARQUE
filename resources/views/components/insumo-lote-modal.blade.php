@props([
    'nombre' => '',
    'unidad' => '',
    'wireSubmit' => 'saveLote',
    'wireClose' => 'closeLoteModal',
    'prefix' => 'l',
    'codigoAnio',
    'numeroLote' => '',
])

@php
    $mTipoIngreso = $prefix === 'l' ? 'lTipoIngreso' : $prefix.'TipoIngreso';
    $mNumeroLote = $prefix === 'l' ? 'lNumeroLote' : $prefix.'NumeroLote';
    $mFechaVencimiento = $prefix === 'l' ? 'lFechaVencimiento' : $prefix.'FechaVencimiento';
    $mCantidad = $prefix === 'l' ? 'lCantidad' : $prefix.'Cantidad';
    $mFechaIngreso = $prefix === 'l' ? 'lFechaIngreso' : $prefix.'FechaIngreso';
    $mProveedor = $prefix === 'l' ? 'lProveedor' : $prefix.'Proveedor';
    $mCostoTotal = $prefix === 'l' ? 'lCostoTotal' : $prefix.'CostoTotal';
    $mComprobante = $prefix === 'l' ? 'lComprobante' : $prefix.'Comprobante';
    $mUbicacion = $prefix === 'l' ? 'lUbicacion' : $prefix.'Ubicacion';
    $mObservaciones = $prefix === 'l' ? 'lObservaciones' : $prefix.'Observaciones';
@endphp

<div
    x-data="{ tipoIngreso: @entangle($mTipoIngreso) }"
    x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
    class="agro-dialog-overlay"
    x-on:keydown.escape.window="$wire.{{ $wireClose }}()">
    <section role="dialog" aria-modal="true" aria-label="Reabastecer insumo" class="agro-dialog agro-dialog--md agro-dialog--scroll">
        <header class="flex shrink-0 items-start justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Entrada de inventario</p>
                <h2 class="mt-0.5 text-lg font-black text-zinc-900 dark:text-white">Reabastecer insumo</h2>
                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $nombre }} · {{ $unidad }}</p>
            </div>
            <button type="button" wire:click="{{ $wireClose }}" class="agro-icon-button !h-9 !w-9 shrink-0" aria-label="Cerrar">&times;</button>
        </header>

        <form wire:submit="{{ $wireSubmit }}" class="agro-dialog__scroll">
            <div class="space-y-4 p-5">
                {{-- Motivo de entrada --}}
                <div>
                    <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Motivo de entrada <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['compra' => ['🛒', 'Compra'], 'donacion' => ['🎁', 'Donación'], 'saldo_inicial' => ['📦', 'Saldo Inicial']] as $val => [$ico, $lab])
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="{{ $mTipoIngreso }}" value="{{ $val }}" class="peer sr-only">
                            <span class="flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-xl border border-zinc-200 px-2 py-2 text-center text-xs font-bold transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-900 hover:border-zinc-300 dark:border-zinc-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/10 dark:peer-checked:text-emerald-200">
                                <span class="text-base">{{ $ico }}</span>{{ $lab }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error($mTipoIngreso)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <x-insumo-lot-code-input
                    :model="$mNumeroLote"
                    :error-field="$mNumeroLote"
                    :year="$codigoAnio"
                    :number="$numeroLote"
                    tone="emerald"
                    id="modal-insumo-lot-code"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Vencimiento (opcional para insumos como instrumental o gasas sin vencimiento estricto) --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Vencimiento <span class="text-zinc-400 font-normal">(opcional)</span></label>
                        <x-date-picker :model="$mFechaVencimiento" placeholder="Sin vencimiento" />
                        @error($mFechaVencimiento)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    {{-- Cantidad --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="ins-lote-cantidad">Cantidad recibida <span class="text-rose-500">*</span></label>
                        <div class="relative"><input id="ins-lote-cantidad" type="number" wire:model="{{ $mCantidad }}" step="0.001" min="0.001" inputmode="decimal" placeholder="0" class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-3.5 pr-16 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950"><span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs font-bold text-zinc-400">{{ $unidad }}</span></div>
                        @error($mCantidad)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 dark:border-zinc-800 dark:bg-zinc-950/40">
                    <div class="px-4 py-3 text-xs font-bold text-zinc-600 dark:text-zinc-300">
                        Datos opcionales (fecha, costo, ubicación)
                    </div>
                    <div class="grid gap-4 border-t border-zinc-200 px-4 py-4 dark:border-zinc-800 sm:grid-cols-2">
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200">Fecha de ingreso</label><x-date-picker :model="$mFechaIngreso" placeholder="Fecha" />@error($mFechaIngreso)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="ins-lote-proveedor"><span x-text="tipoIngreso === 'donacion' ? 'Donante' : (tipoIngreso === 'saldo_inicial' ? 'Entregado por' : 'Proveedor')"></span></label><input id="ins-lote-proveedor" type="text" wire:model="{{ $mProveedor }}" maxlength="255" placeholder="Opcional" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error($mProveedor)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>

                        <div class="contents" x-show="tipoIngreso === 'compra'" x-cloak>
                            <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="ins-lote-costo">Costo total (S/.)</label><input id="ins-lote-costo" type="number" wire:model="{{ $mCostoTotal }}" step="0.01" min="0" inputmode="decimal" placeholder="0.00" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error($mCostoTotal)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                            <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="ins-lote-comprobante">Comprobante</label><input id="ins-lote-comprobante" type="text" wire:model="{{ $mComprobante }}" maxlength="100" placeholder="Boleta o factura" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error($mComprobante)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                        </div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="ins-lote-ubicacion">Ubicación</label><input id="ins-lote-ubicacion" type="text" wire:model="{{ $mUbicacion }}" maxlength="255" placeholder="Estante o almacén" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error($mUbicacion)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-200" for="ins-lote-observaciones">Observación</label><input id="ins-lote-observaciones" type="text" wire:model="{{ $mObservaciones }}" maxlength="2000" placeholder="Solo si es necesaria" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">@error($mObservaciones)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
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
