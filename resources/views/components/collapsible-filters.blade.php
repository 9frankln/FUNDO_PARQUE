@props([
    'active' => false,
    'title' => 'Filtros',
    'description' => null,
    'id' => 'filter-content',
    'reset' => null,
    'loadingTarget' => null,
])

<div x-data="{ filtersOpen: @js((bool) $active) }"
     {{ $attributes->class(['space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-colors sm:p-6 dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h2>
            @if($description)
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>

        <div class="flex w-full flex-wrap items-center gap-2 md:w-auto md:justify-end">
            @if($loadingTarget)
                <span wire:loading.class.remove="hidden" wire:target="{{ $loadingTarget }}"
                      class="hidden items-center gap-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 inline-flex">
                    <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></span>
                    Actualizando
                </span>
            @endif

            @if($active)
                <span class="rounded-full border border-emerald-500/25 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Activos
                </span>
            @endif

            @if($reset)
                <button type="button" wire:click="{{ $reset }}" @disabled(! $active)
                        class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white">
                    Limpiar filtros
                </button>
            @endif

            <button type="button" x-on:click="filtersOpen = ! filtersOpen"
                    x-bind:aria-expanded="filtersOpen"
                    aria-controls="{{ $id }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-emerald-600 bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700 sm:flex-none dark:border-emerald-500 dark:bg-emerald-500 dark:text-white dark:hover:bg-emerald-400 dark:hover:text-emerald-950">
                <span x-text="filtersOpen ? 'Ocultar filtros' : 'Mostrar filtros'">{{ $active ? 'Ocultar filtros' : 'Mostrar filtros' }}</span>
                <svg class="h-4 w-4 transition-transform duration-200" x-bind:class="filtersOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
            </button>
        </div>
    </div>

    <div id="{{ $id }}"
         x-bind:style="filtersOpen ? 'display: block;' : 'display: none;'"
         style="{{ $active ? 'display: block;' : 'display: none;' }}"
         class="space-y-4">
        {{ $slot }}
    </div>
</div>
