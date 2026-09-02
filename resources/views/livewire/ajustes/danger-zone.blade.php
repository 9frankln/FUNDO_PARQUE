@php $dangerCounts = $this->dangerZoneCounts(); @endphp
<section class="agro-card space-y-4 border-rose-200/70 p-4 sm:p-6 dark:border-rose-400/20">
    <div class="flex items-start gap-4">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-700 dark:bg-rose-400/15 dark:text-rose-300">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </span>
        <div class="min-w-0">
            <p class="agro-kicker">Zona de peligro</p>
            <h2 class="text-xl font-extrabold text-zinc-900 dark:text-zinc-100">Borrado total de datos</h2>
            <p class="mt-1 text-xs leading-5 text-zinc-500">Elimina de forma permanente todos los datos operativos del fundo. Solo administradores, con contraseña de autorización.</p>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3.5 dark:border-rose-400/20 dark:bg-rose-400/10">
            <div class="flex items-center justify-between gap-2">
                <h3 class="text-[11px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-300">Se eliminará</h3>
                <span class="shrink-0 rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-black tabular-nums text-white" title="{{ number_format($dangerCounts['total']) }} elementos en total">{{ number_format($dangerCounts['records']) }} registros</span>
            </div>
            <ul class="mt-2 space-y-2 text-xs leading-relaxed text-zinc-700 dark:text-zinc-300">
                @foreach($dangerCounts['groups'] as $group)
                    <li class="border-b border-rose-200/60 pb-1.5 last:border-0 dark:border-rose-400/15">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-zinc-800 dark:text-zinc-200">• {{ $group['label'] }}</span>
                            <span class="shrink-0 rounded-md bg-white/80 px-1.5 py-0.5 font-black tabular-nums text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">{{ number_format($group['total']) }}</span>
                        </div>
                        @if(! empty($group['items']))
                            <ul class="mt-1 space-y-0.5 pl-4 text-[11px] text-zinc-500 dark:text-zinc-400">
                                @foreach($group['items'] as $item => $itemCount)
                                    <li class="flex items-center justify-between gap-2">
                                        <span>· {{ $item }}</span>
                                        <span class="shrink-0 tabular-nums font-semibold text-zinc-600 dark:text-zinc-300">{{ number_format($itemCount) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
                <li class="flex items-center justify-between gap-2 pt-0.5">
                    <span class="font-bold text-zinc-800 dark:text-zinc-200">• Fotos, evidencias y archivos adjuntos</span>
                    <span class="shrink-0 rounded-md bg-white/80 px-1.5 py-0.5 font-black tabular-nums text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">{{ number_format($dangerCounts['files']) }}</span>
                </li>
            </ul>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3.5 dark:border-emerald-400/20 dark:bg-emerald-400/10">
            @php $preservedCounts = $this->dangerPreservedCounts(); @endphp
            <h3 class="text-[11px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Se conserva</h3>
            <ul class="mt-2 space-y-1.5 text-xs leading-relaxed text-zinc-700 dark:text-zinc-300">
                @foreach($preservedCounts as $label => $count)
                    <li class="flex items-center justify-between gap-2">
                        <span>• {{ $label }}</span>
                        <span class="shrink-0 rounded-md bg-white/80 px-1.5 py-0.5 font-black tabular-nums text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">{{ number_format($count) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="flex flex-col gap-3 rounded-xl border border-rose-300/60 bg-rose-50/40 p-3.5 sm:flex-row sm:items-center sm:justify-between dark:border-rose-400/30 dark:bg-rose-400/5">
        <p class="text-xs leading-5 text-zinc-600 dark:text-zinc-400">
            <strong class="text-rose-700 dark:text-rose-300">Acción irreversible.</strong>
            Una vez confirmado no se puede deshacer. Puedes crear una copia de seguridad dentro del modal antes de ejecutar.
        </p>
        <button type="button" wire:click="openDangerDeleteModal" class="shrink-0 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/40">
            Borrar todos los datos
        </button>
    </div>
</section>

@if($showDangerDeleteModal)
    <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
        <section role="dialog" aria-modal="true" aria-label="Confirmar borrado total de datos" class="agro-dialog agro-dialog--compact agro-dialog--scroll p-4 sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="agro-kicker">Zona de peligro</p>
                    <h3 class="mt-1 text-xl font-extrabold text-zinc-900 dark:text-zinc-100">¿Borrar todos los datos?</h3>
                </div>
                <button wire:click="closeDangerDeleteModal" class="agro-icon-button" aria-label="Cerrar">&times;</button>
            </div>

            <div class="mt-4 space-y-4">
                <div class="flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50/70 p-3 text-[11px] leading-relaxed text-rose-900 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p>Esta acción <strong>eliminará permanentemente</strong> todos los datos operativos del fundo (animales, leche, queso, finanzas, monitoreo, botiquín, gestión web, fotos, alertas y auditoría). No se puede deshacer.</p>
                </div>

                <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-zinc-200 p-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/60">
                    <input type="checkbox" wire:model="dangerCreateBackup" class="agro-checkbox mt-0.5 h-4 w-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <span>
                        <strong class="block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">Crear copia de seguridad antes de borrar</strong>
                        <span class="mt-0.5 block text-[11px] leading-relaxed text-zinc-500 dark:text-zinc-400">Genera un backup completo cifrado para poder restaurar todo si es necesario.</span>
                    </span>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold text-zinc-800 dark:text-zinc-200">Contraseña para autorizar</span>
                    <input type="password" wire:model="dangerPassword" placeholder="Tu contraseña actual" autocomplete="current-password" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs text-zinc-900 placeholder-zinc-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                    @error('dangerPassword')<span class="mt-1 block text-xs font-medium text-rose-500">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button wire:click="closeDangerDeleteModal" class="agro-button-secondary !min-h-9 !px-4 !py-2 text-xs">Cancelar</button>
                <button wire:click="confirmDangerDelete" wire:loading.attr="disabled" wire:target="confirmDangerDelete" class="!min-h-9 rounded-xl bg-rose-600 px-4 py-2 text-xs font-extrabold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/40">
                    <span wire:loading.remove wire:target="confirmDangerDelete">Borrar todos los datos</span>
                    <span wire:loading wire:target="confirmDangerDelete">Borrando...</span>
                </button>
            </div>
        </section>
    </div>
@endif
