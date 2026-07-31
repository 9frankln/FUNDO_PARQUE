@props([
    'model',
    'options' => [],
    'tone' => 'indigo',
    'live' => false,
    'disabled' => false,
    'compact' => false,
])

@php
    $palettes = [
        'emerald' => [
            'trigger' => 'hover:border-emerald-400 focus:border-emerald-500 focus:ring-emerald-500/20 dark:hover:border-emerald-400',
            'selected' => 'bg-emerald-600 text-white dark:bg-emerald-400/20 dark:text-emerald-50',
            'active' => 'bg-emerald-50 text-emerald-950 dark:bg-emerald-400/10 dark:text-emerald-100',
        ],
        'sky' => [
            'trigger' => 'hover:border-sky-400 focus:border-sky-500 focus:ring-sky-500/20 dark:hover:border-sky-400',
            'selected' => 'bg-sky-600 text-white dark:bg-sky-400/20 dark:text-sky-50',
            'active' => 'bg-sky-50 text-sky-950 dark:bg-sky-400/10 dark:text-sky-100',
        ],
        'cyan' => [
            'trigger' => 'hover:border-cyan-400 focus:border-cyan-500 focus:ring-cyan-500/20 dark:hover:border-cyan-400',
            'selected' => 'bg-cyan-600 text-white dark:bg-cyan-400/20 dark:text-cyan-50',
            'active' => 'bg-cyan-50 text-cyan-950 dark:bg-cyan-400/10 dark:text-cyan-100',
        ],
        'violet' => [
            'trigger' => 'hover:border-violet-400 focus:border-violet-500 focus:ring-violet-500/20 dark:hover:border-violet-400',
            'selected' => 'bg-violet-600 text-white dark:bg-violet-400/20 dark:text-violet-50',
            'active' => 'bg-violet-50 text-violet-950 dark:bg-violet-400/10 dark:text-violet-100',
        ],
        'amber' => [
            'trigger' => 'hover:border-amber-400 focus:border-amber-500 focus:ring-amber-500/20 dark:hover:border-amber-400',
            'selected' => 'bg-amber-600 text-white dark:bg-amber-400/20 dark:text-amber-50',
            'active' => 'bg-amber-50 text-amber-950 dark:bg-amber-400/10 dark:text-amber-100',
        ],
        'rose' => [
            'trigger' => 'hover:border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 dark:hover:border-rose-400',
            'selected' => 'bg-rose-600 text-white dark:bg-rose-400/20 dark:text-rose-50',
            'active' => 'bg-rose-50 text-rose-950 dark:bg-rose-400/10 dark:text-rose-100',
        ],
        'indigo' => [
            'trigger' => 'hover:border-indigo-400 focus:border-indigo-500 focus:ring-indigo-500/20 dark:hover:border-indigo-400',
            'selected' => 'bg-indigo-600 text-white dark:bg-indigo-400/20 dark:text-indigo-50',
            'active' => 'bg-indigo-50 text-indigo-950 dark:bg-indigo-400/10 dark:text-indigo-100',
        ],
    ];
    $palette = $palettes[$tone] ?? $palettes['indigo'];
    $items = collect($options)
        ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
        ->values()
        ->all();
    $selectId = 'filter-select-'.md5($model.json_encode($items).(int) $disabled.$tone.(int) $live.(int) $compact);
@endphp

<div
    wire:key="{{ $selectId }}"
    x-data="{
        open: false,
        value: $wire.entangle(@js($model)){{ $live ? '.live' : '' }},
        disabled: @js((bool) $disabled),
        options: @js($items),
        activeIndex: 0,
        menuStyle: '',
        selectedIndex() {
            return this.options.findIndex(option => String(option.value) === String(this.value ?? ''));
        },
        selectedLabel() {
            return this.options[this.selectedIndex()]?.label ?? 'Seleccionar';
        },
        positionMenu() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const menuHeight = this.$refs.menu?.offsetHeight || Math.min(this.options.length * 40 + 12, 256);
            const width = Math.min(rect.width, window.innerWidth - 16);
            const left = Math.min(Math.max(8, rect.left), window.innerWidth - width - 8);
            const spaceBelow = window.innerHeight - rect.bottom;
            const openAbove = spaceBelow < menuHeight + 8 && rect.top > spaceBelow;
            const top = openAbove
                ? Math.max(8, rect.top - menuHeight - 6)
                : Math.min(rect.bottom + 6, window.innerHeight - menuHeight - 8);

            this.menuStyle = `left: ${left}px; top: ${top}px; width: ${width}px;`;
        },
        openMenu() {
            if (this.disabled) return;
            this.open = true;
            const selected = this.selectedIndex();
            this.activeIndex = selected >= 0 ? selected : 0;
            this.$nextTick(() => {
                this.positionMenu();
                this.$refs.options?.children[this.activeIndex]?.scrollIntoView({ block: 'nearest' });
            });
        },
        move(step) {
            if (this.disabled) return;
            if (! this.open) {
                this.openMenu();
                return;
            }
            this.activeIndex = (this.activeIndex + step + this.options.length) % this.options.length;
            this.$nextTick(() => this.$refs.options?.children[this.activeIndex]?.scrollIntoView({ block: 'nearest' }));
        },
        choose(index) {
            const option = this.options[index];
            if (! option) return;
            this.value = option.value;
            this.open = false;
            this.$nextTick(() => this.$refs.trigger.focus());
        },
    }"
    @click.outside="open = false"
    @keydown.escape.stop="open = false; $refs.trigger.focus()"
    @resize.window="open && positionMenu()"
    @scroll.window="open && positionMenu()"
    {{ $attributes->class(['relative w-full', $compact ? 'min-w-[8.5rem]' : 'min-w-0']) }}
>
    <button
        x-ref="trigger"
        type="button"
        role="combobox"
        aria-haspopup="listbox"
        aria-controls="{{ $selectId }}-options"
        :aria-expanded="open"
        @click="open ? open = false : openMenu()"
        @keydown.arrow-down.prevent="move(1)"
        @keydown.arrow-up.prevent="move(-1)"
        @keydown.home.prevent="openMenu(); activeIndex = 0"
        @keydown.end.prevent="openMenu(); activeIndex = options.length - 1"
        @keydown.enter.prevent="open ? choose(activeIndex) : openMenu()"
        @keydown.space.prevent="open ? choose(activeIndex) : openMenu()"
        @disabled($disabled)
        class="flex w-full items-center justify-between gap-3 border border-slate-300 bg-white text-left font-medium text-slate-700 shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 disabled:opacity-70 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-800/60 {{ $palette['trigger'] }} {{ $compact ? 'px-3 py-1.5 text-xs rounded-lg' : 'px-3.5 py-2.5 text-sm rounded-xl' }}"
    >
        <span class="min-w-0 truncate" x-text="selectedLabel()"></span>
        <svg class="h-4 w-4 shrink-0 text-slate-400 dark:text-zinc-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
        </svg>
    </button>

    <template x-teleport="body">
        <div
            x-ref="menu"
            x-cloak
            x-show="open"
            :style="menuStyle"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
            class="fixed z-[100] origin-top overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-950/15 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
        >
            <div x-ref="options" id="{{ $selectId }}-options" role="listbox" class="max-h-64 overflow-y-auto overscroll-contain">
                <template x-for="(option, index) in options" :key="option.value">
                    <button
                        type="button"
                        role="option"
                        :aria-selected="String(option.value) === String(value ?? '')"
                        @mouseenter="activeIndex = index"
                        @click="choose(index)"
                        :class="String(option.value) === String(value ?? '')
                            ? @js($palette['selected'])
                            : (activeIndex === index ? @js($palette['active']) : 'text-slate-600 dark:text-slate-300')"
                        class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                    >
                        <span class="truncate" x-text="option.label"></span>
                        <svg x-show="String(option.value) === String(value ?? '')" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                    </button>
                </template>
            </div>
        </div>
    </template>
</div>
