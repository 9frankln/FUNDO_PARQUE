@props([
    'model',
    'options' => [],
    'tone' => 'indigo',
    'live' => false,
    'disabled' => false,
    'compact' => false,
    'searchable' => false,
    'searchPlaceholder' => 'Buscar...',
    // Mapeo de valores especiales con su tono e icono: [ 'valor' => ['tone' => 'violet', 'icon' => 'user'] ] o [ 'valor' => 'violet' ]
    'specialItems' => [],
    'specialValues' => [],
    'specialTone' => 'violet',
])

@php
    $palettes = [
        'emerald' => [
            'trigger' => 'hover:border-emerald-400 focus:border-emerald-500 focus:ring-emerald-500/20 dark:hover:border-emerald-400',
            'selected' => 'bg-emerald-600 text-white font-bold dark:bg-emerald-500 dark:text-zinc-950',
            'active' => 'bg-emerald-100 text-emerald-950 font-semibold dark:bg-emerald-900/60 dark:text-emerald-100',
            'search' => 'focus:border-emerald-500 focus:ring-emerald-500/20 dark:focus:border-emerald-400',
            'text' => 'text-emerald-700 dark:text-emerald-300',
            'icon' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'sky' => [
            'trigger' => 'hover:border-sky-400 focus:border-sky-500 focus:ring-sky-500/20 dark:hover:border-sky-400',
            'selected' => 'bg-sky-600 text-white font-bold dark:bg-sky-500 dark:text-zinc-950',
            'active' => 'bg-sky-100 text-sky-950 font-semibold dark:bg-sky-900/60 dark:text-sky-100',
            'search' => 'focus:border-sky-500 focus:ring-sky-500/20 dark:focus:border-sky-400',
            'text' => 'text-sky-700 dark:text-sky-300',
            'icon' => 'text-sky-600 dark:text-sky-400',
        ],
        'cyan' => [
            'trigger' => 'hover:border-cyan-400 focus:border-cyan-500 focus:ring-cyan-500/20 dark:hover:border-cyan-400',
            'selected' => 'bg-cyan-600 text-white font-bold dark:bg-cyan-500 dark:text-zinc-950',
            'active' => 'bg-cyan-100 text-cyan-950 font-semibold dark:bg-cyan-900/60 dark:text-cyan-100',
            'search' => 'focus:border-cyan-500 focus:ring-cyan-500/20 dark:focus:border-cyan-400',
            'text' => 'text-cyan-700 dark:text-cyan-300',
            'icon' => 'text-cyan-600 dark:text-cyan-400',
        ],
        'violet' => [
            'trigger' => 'hover:border-violet-400 focus:border-violet-500 focus:ring-violet-500/20 dark:hover:border-violet-400',
            'selected' => 'bg-violet-600 text-white font-bold dark:bg-violet-500 dark:text-zinc-950',
            'active' => 'bg-violet-100 text-violet-950 font-semibold dark:bg-violet-900/60 dark:text-violet-100',
            'search' => 'focus:border-violet-500 focus:ring-violet-500/20 dark:focus:border-violet-400',
            'text' => 'text-violet-700 dark:text-violet-300',
            'icon' => 'text-violet-600 dark:text-violet-400',
        ],
        'amber' => [
            'trigger' => 'hover:border-amber-400 focus:border-amber-500 focus:ring-amber-500/20 dark:hover:border-amber-400',
            'selected' => 'bg-amber-600 text-white font-bold dark:bg-amber-500 dark:text-zinc-950',
            'active' => 'bg-amber-100 text-amber-950 font-semibold dark:bg-amber-900/60 dark:text-amber-100',
            'search' => 'focus:border-amber-500 focus:ring-amber-500/20 dark:focus:border-amber-400',
            'text' => 'text-amber-700 dark:text-amber-300',
            'icon' => 'text-amber-600 dark:text-amber-400',
        ],
        'rose' => [
            'trigger' => 'hover:border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 dark:hover:border-rose-400',
            'selected' => 'bg-rose-600 text-white font-bold dark:bg-rose-500 dark:text-zinc-950',
            'active' => 'bg-rose-100 text-rose-950 font-semibold dark:bg-rose-900/60 dark:text-rose-100',
            'search' => 'focus:border-rose-500 focus:ring-rose-500/20 dark:focus:border-rose-400',
            'text' => 'text-rose-700 dark:text-rose-300',
            'icon' => 'text-rose-600 dark:text-rose-400',
        ],
        'indigo' => [
            'trigger' => 'hover:border-indigo-400 focus:border-indigo-500 focus:ring-indigo-500/20 dark:hover:border-indigo-400',
            'selected' => 'bg-indigo-600 text-white font-bold dark:bg-indigo-500 dark:text-zinc-950',
            'active' => 'bg-indigo-100 text-indigo-950 font-semibold dark:bg-indigo-900/60 dark:text-indigo-100',
            'search' => 'focus:border-indigo-500 focus:ring-indigo-500/20 dark:focus:border-indigo-400',
            'text' => 'text-indigo-700 dark:text-indigo-300',
            'icon' => 'text-indigo-600 dark:text-indigo-400',
        ],
        'teal' => [
            'trigger' => 'hover:border-teal-400 focus:border-teal-500 focus:ring-teal-500/20 dark:hover:border-teal-400',
            'selected' => 'bg-teal-600 text-white font-bold dark:bg-teal-500 dark:text-zinc-950',
            'active' => 'bg-teal-100 text-teal-950 font-semibold dark:bg-teal-900/60 dark:text-teal-100',
            'search' => 'focus:border-teal-500 focus:ring-teal-500/20 dark:focus:border-teal-400',
            'text' => 'text-teal-700 dark:text-teal-300',
            'icon' => 'text-teal-600 dark:text-teal-400',
        ],
    ];
    $palette = $palettes[$tone] ?? $palettes['indigo'];

    // Normalización de items especiales con color e icono propio
    $normalizedSpecial = [];
    foreach ((array) $specialItems as $val => $cfg) {
        $valKey = (string) $val;
        if (is_array($cfg)) {
            $normalizedSpecial[$valKey] = [
                'tone' => $cfg['tone'] ?? 'violet',
                'icon' => $cfg['icon'] ?? 'user',
            ];
        } else {
            $toneName = (string) $cfg ?: 'violet';
            $normalizedSpecial[$valKey] = [
                'tone' => $toneName,
                'icon' => match ($toneName) {
                    'emerald' => 'box',
                    'cyan', 'sky' => 'medicine',
                    default => 'user',
                },
            ];
        }
    }
    foreach ((array) $specialValues as $v) {
        $vKey = (string) $v;
        if (!isset($normalizedSpecial[$vKey])) {
            $normalizedSpecial[$vKey] = [
                'tone' => $specialTone,
                'icon' => match ($specialTone) {
                    'emerald' => 'box',
                    'cyan', 'sky' => 'medicine',
                    default => 'user',
                },
            ];
        }
    }

    // Detección automática por texto para categorías estándar reconocidas
    foreach ($options as $val => $lbl) {
        $vKey = (string) $val;
        if ($vKey === '') continue;
        if (!isset($normalizedSpecial[$vKey])) {
            $lblLower = mb_strtolower((string) $lbl);
            if (str_contains($lblLower, 'asignaci') || str_contains($lblLower, 'familiar')) {
                $normalizedSpecial[$vKey] = ['tone' => 'violet', 'icon' => 'user'];
            } elseif (str_contains($lblLower, 'insumo') || str_contains($lblLower, 'material')) {
                $normalizedSpecial[$vKey] = ['tone' => 'emerald', 'icon' => 'box'];
            } elseif (str_contains($lblLower, 'medicamento') || str_contains($lblLower, 'fármaco') || str_contains($lblLower, 'farmaco')) {
                $normalizedSpecial[$vKey] = ['tone' => 'cyan', 'icon' => 'medicine'];
            }
        }
    }

    $items = collect($options)
        ->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])
        ->values()
        ->all();
    $autoKey = 'filter-select-'.\Illuminate\Support\Str::slug($model).'-'.md5(serialize($items));
    $selectId = 'filter-select-'.\Illuminate\Support\Str::slug($model);
@endphp

<div
    wire:key="{{ $attributes->get('wire:key') ?? $autoKey }}"
    x-data="{
        open: false,
        value: $wire.entangle(@js($model)){{ $live ? '.live' : '' }},
        disabled: @js((bool) $disabled),
        options: @js($items),
        searchable: @js((bool) $searchable),
        searchQuery: '',
        specialConfig: @js($normalizedSpecial),
        palettes: @js($palettes),
        activeIndex: 0,
        menuStyle: '',
        getSpecial(val) {
            return this.specialConfig[String(val ?? '')] || null;
        },
        getOptionClass(option, index) {
            const isSelected = String(option.value) === String(this.value ?? '');
            const isActive = this.activeIndex === index;
            const special = this.getSpecial(option.value);
            const p = special ? (this.palettes[special.tone] || this.palettes['violet']) : (this.palettes[@js($tone)] || this.palettes['indigo']);

            if (isSelected) {
                return p.selected;
            }
            if (isActive) {
                return p.active;
            }
            if (special) {
                return p.text + ' font-bold';
            }
            return 'text-zinc-600 dark:text-zinc-300';
        },
        getTriggerTextClass() {
            const special = this.getSpecial(this.value);
            if (special) {
                const p = this.palettes[special.tone] || this.palettes['violet'];
                return p.text + ' font-bold';
            }
            return '';
        },
        filteredOptions() {
            if (!this.searchable || !this.searchQuery || !this.searchQuery.trim()) {
                return this.options;
            }
            const q = this.searchQuery.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
            return this.options.filter(opt => {
                const l = String(opt.label).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return l.includes(q);
            });
        },
        selectedIndex() {
            const list = this.filteredOptions();
            return list.findIndex(option => String(option.value) === String(this.value ?? ''));
        },
        selectedLabel() {
            const opt = this.options.find(option => String(option.value) === String(this.value ?? ''));
            return opt?.label ?? 'Seleccionar';
        },
        positionMenu() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const menuHeight = this.$refs.menu?.offsetHeight || Math.min(this.options.length * 40 + 50, 300);
            const targetWidth = this.searchable ? Math.max(rect.width, 300) : rect.width;
            const width = Math.min(targetWidth, window.innerWidth - 16);
            let left = rect.left;
            if (left + width > window.innerWidth - 8) {
                left = window.innerWidth - width - 8;
            }
            left = Math.max(8, left);
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
            this.searchQuery = '';
            const selected = this.selectedIndex();
            this.activeIndex = selected >= 0 ? selected : 0;
            this.$nextTick(() => {
                this.positionMenu();
                if (this.searchable && this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
                this.$refs.options?.children[this.activeIndex]?.scrollIntoView({ block: 'nearest' });
            });
        },
        move(step) {
            if (this.disabled) return;
            if (! this.open) {
                this.openMenu();
                return;
            }
            const list = this.filteredOptions();
            if (list.length === 0) return;
            this.activeIndex = (this.activeIndex + step + list.length) % list.length;
            this.$nextTick(() => this.$refs.options?.children[this.activeIndex]?.scrollIntoView({ block: 'nearest' }));
        },
        choose(index) {
            const list = this.filteredOptions();
            const option = list[index];
            if (! option) return;
            this.value = option.value;
            this.open = false;
            this.$nextTick(() => this.$refs.trigger.focus());
        },
        onSearchInput() {
            this.activeIndex = 0;
            this.$nextTick(() => this.positionMenu());
        }
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
        class="flex w-full items-center justify-between gap-3 border border-zinc-300 bg-white text-left font-medium text-zinc-700 shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-400 disabled:opacity-70 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-800/60 {{ $palette['trigger'] }} {{ $compact ? 'h-8 px-3 py-1 text-xs rounded-lg' : 'h-11 px-3.5 py-2 text-sm rounded-xl' }}"
    >
        <span class="flex min-w-0 items-center gap-2">
            <template x-if="getSpecial(value)?.icon === 'user'">
                <svg class="h-4 w-4 shrink-0 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </template>
            <template x-if="getSpecial(value)?.icon === 'box'">
                <svg class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6.75 3.75h3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
            </template>
            <template x-if="getSpecial(value)?.icon === 'medicine'">
                <svg class="h-4 w-4 shrink-0 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.75a2.25 2.25 0 0 0-2.25 2.25v12a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-12a2.25 2.25 0 0 0-2.25-2.25H15M9 3.75v1.5a.75.75 0 0 0 .75.75h4.5a.75.75 0 0 0 .75-.75v-1.5M9 3.75h6M12 10.5v6m-3-3h6" />
                </svg>
            </template>
            <span class="truncate" :class="getTriggerTextClass()" x-text="selectedLabel()"></span>
        </span>
        <svg class="h-4 w-4 shrink-0 text-zinc-400 dark:text-zinc-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
            class="fixed z-[100000] origin-top overflow-hidden rounded-xl border border-zinc-200 bg-white p-1.5 shadow-xl shadow-zinc-950/15 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
        >
            <template x-if="searchable">
                <div class="mb-1.5 border-b border-zinc-100 pb-1.5 dark:border-zinc-700/70">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-zinc-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.5-4.5m2-5.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" /></svg>
                        </span>
                        <input
                            x-ref="searchInput"
                            type="text"
                            x-model="searchQuery"
                            @input="onSearchInput()"
                            @keydown.arrow-down.prevent="move(1)"
                            @keydown.arrow-up.prevent="move(-1)"
                            @keydown.enter.prevent="choose(activeIndex)"
                            @keydown.escape.stop="open = false; $refs.trigger.focus()"
                            placeholder="{{ $searchPlaceholder }}"
                            autocomplete="off"
                            class="w-full rounded-lg border border-zinc-200 bg-zinc-50 py-1.5 pl-8 pr-2.5 text-xs text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:bg-white focus:ring-1 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-500 {{ $palette['search'] }}"
                        >
                    </div>
                </div>
            </template>

            <div x-ref="options" id="{{ $selectId }}-options" role="listbox" class="max-h-60 overflow-y-auto overscroll-contain">
                <template x-for="(option, index) in filteredOptions()" :key="option.value">
                    <button
                        type="button"
                        role="option"
                        :aria-selected="String(option.value) === String(value ?? '')"
                        @mouseenter="activeIndex = index"
                        @click="choose(index)"
                        :class="getOptionClass(option, index)"
                        class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                    >
                        <span class="flex min-w-0 items-center gap-2">
                            <template x-if="getSpecial(option.value)?.icon === 'user'">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </template>
                            <template x-if="getSpecial(option.value)?.icon === 'box'">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6.75 3.75h3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                            </template>
                            <template x-if="getSpecial(option.value)?.icon === 'medicine'">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.75a2.25 2.25 0 0 0-2.25 2.25v12a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-12a2.25 2.25 0 0 0-2.25-2.25H15M9 3.75v1.5a.75.75 0 0 0 .75.75h4.5a.75.75 0 0 0 .75-.75v-1.5M9 3.75h6M12 10.5v6m-3-3h6" />
                                </svg>
                            </template>
                            <span class="text-xs font-semibold leading-snug" x-text="option.label"></span>
                        </span>
                        <svg x-show="String(option.value) === String(value ?? '')" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                    </button>
                </template>
                <div x-show="filteredOptions().length === 0" class="px-3 py-4 text-center text-xs text-zinc-400">
                    No se encontraron opciones
                </div>
            </div>
        </div>
    </template>
</div>

