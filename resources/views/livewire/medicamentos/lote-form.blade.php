<div class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <header class="mb-6 flex items-start gap-4">
        <a wire:navigate href="{{ route('medicamentos.show', $medicamento->id) }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200/90 bg-white text-zinc-600 shadow-sm transition hover:border-emerald-500/40 hover:bg-emerald-50/50 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-950/25 dark:hover:text-emerald-300"
           aria-label="Volver">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
            </svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Entrada de inventario</p>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300">
                    Nuevo lote
                </span>
            </div>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">Ingresar stock</h1>
            <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $medicamento->nombre }} · {{ $medicamento->presentacion }}</p>
        </div>
    </header>

    <form wire:submit="save" autocomplete="off">
        <section class="overflow-hidden rounded-2xl border border-zinc-200/90 border-t-4 border-t-emerald-500 bg-white shadow-sm dark:border-zinc-800 dark:border-t-emerald-500 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-emerald-50/45 px-5 py-3.5 dark:border-zinc-800 dark:bg-emerald-400/[.025] sm:px-7">
                <div class="flex items-center justify-between gap-4">
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Se contará en {{ $medicamento->unidad_label }}</span>
                    <span class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Ingreso: hoy</span>
                </div>
            </div>

            <div class="p-5 sm:p-7">
                {{-- Tipo de ingreso --}}
                <div class="mb-5">
                    <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Motivo de entrada <span class="font-bold text-emerald-600 dark:text-emerald-400">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['compra' => ['🛒', 'Compra'], 'donacion' => ['🎁', 'Donación'], 'saldo_inicial' => ['📦', 'Saldo Inicial']] as $val => [$ico, $lab])
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="tipoIngreso" value="{{ $val }}" class="peer sr-only">
                            <span class="flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-xl border border-zinc-200 bg-white px-2 py-2 text-center text-xs font-bold transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-900 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-950 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/10 dark:peer-checked:text-emerald-200">
                                <span class="text-base">{{ $ico }}</span>{{ $lab }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('tipoIngreso')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="mb-5">
                    <x-medicamento-lot-code-input
                        model="numeroLote"
                        error-field="numeroLote"
                        :year="$codigoLoteAnio"
                        :number="$numeroLote"
                        tone="emerald"
                        id="standalone-medication-lot-code"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Vencimiento <span class="font-bold text-emerald-600 dark:text-emerald-400">*</span></label>
                        <x-date-picker model="fechaVencimiento" placeholder="Fecha de vencimiento" />
                        @error('fechaVencimiento')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="lote-cantidad">Contenido neto <span class="font-bold text-emerald-600 dark:text-emerald-400">*</span></label>
                        <div class="relative">
                            <input id="lote-cantidad" type="number" wire:model="cantidad" step="0.001" min="0.001" inputmode="decimal" placeholder="0" class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-3.5 pr-16 text-sm font-semibold text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs font-bold text-zinc-400">{{ $medicamento->unidad_stock }}</span>
                        </div>
                        @error('cantidad')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <details class="group mt-5 rounded-xl border border-zinc-200 bg-zinc-50/70 dark:border-zinc-800 dark:bg-zinc-950/40" @if($errors->hasAny(['fechaIngreso', 'costoTotal', 'proveedor', 'comprobante', 'ubicacion', 'observaciones'])) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-xs font-bold text-zinc-600 outline-none transition hover:text-emerald-700 focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:text-zinc-300 dark:hover:text-emerald-300">
                        <span>Datos opcionales de compra</span>
                        <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <div class="grid gap-4 border-t border-zinc-200 px-4 py-4 dark:border-zinc-800 sm:grid-cols-2">
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Fecha de ingreso</label><x-date-picker model="fechaIngreso" placeholder="Fecha" />@error('fechaIngreso')<p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="lote-proveedor">Proveedor</label><input id="lote-proveedor" type="text" wire:model="proveedor" maxlength="255" placeholder="Opcional" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"></div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="lote-costo">Costo total (S/.)</label><input id="lote-costo" type="number" wire:model="costoTotal" step="0.01" min="0" inputmode="decimal" placeholder="0.00" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"></div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="lote-comprobante">Comprobante</label><input id="lote-comprobante" type="text" wire:model="comprobante" maxlength="100" placeholder="Boleta o factura" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"></div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="lote-ubicacion">Ubicación</label><input id="lote-ubicacion" type="text" wire:model="ubicacion" maxlength="255" placeholder="Estante o refrigeradora" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"></div>
                        <div><label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300" for="lote-observacion">Observación</label><input id="lote-observacion" type="text" wire:model="observaciones" maxlength="2000" placeholder="Solo si es necesaria" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-semibold text-zinc-900 outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"></div>
                    </div>
                </details>
            </div>

            <footer class="flex flex-col-reverse gap-3 border-t border-zinc-200/90 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-950/40 sm:flex-row sm:justify-end sm:px-7">
                <a wire:navigate href="{{ route('medicamentos.show', $medicamento->id) }}" class="agro-button-secondary">Cancelar</a>
                <button type="submit" wire:loading.attr="disabled" class="agro-button">
                    <span wire:loading.remove wire:target="save">Guardar entrada</span>
                    <span wire:loading wire:target="save" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </footer>
        </section>
    </form>
</div>
