@props([
    'model',
    'placeholder' => 'Seleccionar fecha',
    'min' => '',
    'max' => '',
    'compact' => false,
])

@php
    $pickerId = 'date-picker-'.\Illuminate\Support\Str::slug($model);
@endphp

<div
    wire:key="{{ $pickerId }}"
    x-data="{
        open: false,
        draft: null,
        value: $wire.entangle(@js($model)).live,
        minDate: @js($min),
        maxDate: @js($max),
        viewYear: 0,
        viewMonth: 0,
        menuStyle: '',
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        init() {
            const now = this.value ? new Date(String(this.value) + 'T00:00:00') : new Date();
            this.viewYear = now.getFullYear();
            this.viewMonth = now.getMonth();
            this._onDocClick = (e) => {
                if (! this.open) return;
                if (this.$refs.menu && this.$refs.menu.contains(e.target)) return;
                if (this.$el.contains(e.target)) return;
                this.open = false;
            };
            document.addEventListener('click', this._onDocClick);
        },
        destroy() {
            document.removeEventListener('click', this._onDocClick);
        },
        get monthName() {
            return this.monthNames[this.viewMonth];
        },
        get grid() {
            const cells = [];
            const first = new Date(this.viewYear, this.viewMonth, 1);
            const start = new Date(first);
            start.setDate(first.getDate() - ((first.getDay() + 6) % 7));
            const now = new Date();
            const todayIso = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            for (let i = 0; i < 42; i++) {
                const date = new Date(start);
                date.setDate(start.getDate() + i);
                const iso = date.toISOString().slice(0, 10);
                cells.push({
                    key: iso,
                    day: date.getDate(),
                    date: iso,
                    inMonth: date.getMonth() === this.viewMonth,
                    isToday: iso === todayIso,
                    isSelected: String(this.value ?? '') === iso,
                    outOfRange: (this.minDate && iso < this.minDate) || (this.maxDate && iso > this.maxDate),
                });
            }
            return cells;
        },
        get display() {
            if (! this.value) return '';
            const d = new Date(String(this.value) + 'T00:00:00');
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            return `${dd}/${mm}/${d.getFullYear()}`;
        },
        syncViewToValue() {
            const now = this.value ? new Date(String(this.value) + 'T00:00:00') : new Date();
            this.viewYear = now.getFullYear();
            this.viewMonth = now.getMonth();
        },
        shiftMonth(step) {
            this.viewMonth += step;
            if (this.viewMonth < 0) { this.viewMonth = 11; this.viewYear--; }
            if (this.viewMonth > 11) { this.viewMonth = 0; this.viewYear++; }
        },
        shiftYear(step) {
            this.viewYear += step;
        },
        pick(iso) {
            const cell = this.grid.find(c => c.date === iso);
            if (! cell || ! cell.inMonth || cell.outOfRange) return;
            this.draft = null;
            this.value = iso;
            // NO cerramos aquí — el usuario confirma con el botón OK
        },
        toggle() {
            if (this.open) {
                this.open = false;
            } else {
                this.open = true;
                this.syncViewToValue();
                this.$nextTick(() => this.positionMenu());
            }
        },
        openInput() {
            if (this.open) { this.positionMenu(); return; }
            this.open = true;
            this.syncViewToValue();
            this.$nextTick(() => this.positionMenu());
        },
        parseDraft(text) {
            const t = String(text ?? '').trim();
            if (! t) return null;
            let m = t.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
            let d, mo, y;
            if (m) { y = +m[1]; mo = +m[2]; d = +m[3]; }
            else {
                m = t.match(/^(\d{1,2})[\/\-.]?(\d{1,2})[\/\-.]?(\d{2,4})$/);
                if (! m) return null;
                let a = +m[1], b = +m[2], c = +m[3];
                if (c < 100) c += c < 70 ? 2000 : 1900;
                if (a > 12) { d = a; mo = b; }
                else if (b > 12) { d = b; mo = a; }
                else { d = a; mo = b; }
                y = c;
            }
            if (mo < 1 || mo > 12 || d < 1 || d > 31) return null;
            const date = new Date(y, mo - 1, d);
            if (date.getFullYear() !== y || date.getMonth() !== mo - 1 || date.getDate() !== d) return null;
            return `${y}-${String(mo).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        },
        commitDraft(close) {
            if (this.draft === null) {
                if (close) { this.open = false; this.$refs.trigger.blur(); }
                return;
            }
            const iso = this.parseDraft(this.draft);
            this.draft = null;
            if (iso) {
                if ((this.minDate && iso < this.minDate) || (this.maxDate && iso > this.maxDate)) return;
                this.value = iso;
                this.syncViewToValue();
            }
            if (close) { this.open = false; this.$refs.trigger.blur(); }
        },
        closeMenu() {
            this.open = false;
            this.$nextTick(() => this.$refs.trigger.blur());
        },
        onInput(e) {
            this.draft = e.target.value;
        },
        positionMenu() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const menuHeight = this.$refs.menu?.offsetHeight || 320;
            const width = Math.min(216, window.innerWidth - 16);
            const left = Math.min(Math.max(8, rect.left), window.innerWidth - width - 8);
            const spaceBelow = window.innerHeight - rect.bottom;
            const openAbove = spaceBelow < menuHeight + 8 && rect.top > spaceBelow;
            const top = openAbove
                ? Math.max(8, rect.top - menuHeight - 6)
                : Math.min(rect.bottom + 6, window.innerHeight - menuHeight - 8);
            this.menuStyle = `left: ${left}px; top: ${top}px; width: ${width}px;`;
        },
    }"
    @keydown.escape.stop="open = false; $refs.trigger.blur()"
    @resize.window="open && positionMenu()"
    @scroll.window="open && positionMenu()"
    class="relative w-full"
>
    <div class="relative w-full">
        <input
            x-ref="trigger"
            type="text"
            inputmode="numeric"
            autocomplete="off"
            spellcheck="false"
            role="combobox"
            aria-haspopup="dialog"
            :aria-expanded="open"
            :value="draft ?? (display || '')"
            placeholder="{{ $placeholder }}"
            @input="onInput($event)"
            @keydown.enter.prevent="commitDraft(true)"
            @keydown.escape.stop="draft = null; open = false; $refs.trigger.blur()"
            @keydown.arrow-down.prevent="open || openInput()"
            @focus="open || openInput()"
            @blur="commitDraft(false)"
            class="w-full border border-zinc-300 bg-white font-medium text-zinc-700 shadow-sm transition placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500 dark:focus:border-emerald-400 dark:focus:ring-emerald-400/20 {{ $compact ? 'h-8 py-1 pl-3 pr-8 text-xs rounded-lg' : 'h-10 py-2 pl-3.5 pr-10 text-sm rounded-xl' }}"
        />
        <button
            type="button"
            aria-label="Abrir calendario"
            @click="toggle()"
            class="absolute {{ $compact ? 'right-1.5 h-6 w-6' : 'right-2 h-7 w-7' }} top-1/2 flex -translate-y-1/2 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-emerald-50 hover:text-emerald-600 dark:text-zinc-500 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
        >
            <svg class="{{ $compact ? 'h-3.5 w-3.5' : 'h-4 w-4' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
        </button>
    </div>

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
            role="dialog"
            aria-label="Selector de fecha"
            class="fixed z-[100000] origin-top overflow-hidden rounded-xl border border-zinc-200 bg-white p-1 shadow-xl shadow-zinc-950/15 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
        >
            <div class="mb-1 flex items-center justify-between gap-1">
                <div class="flex items-center gap-0.5">
                    <button type="button" @click="shiftYear(-1)" class="flex h-6 w-6 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-zinc-400 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300" aria-label="Año anterior">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" @click="shiftMonth(-1)" class="flex h-6 w-6 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-zinc-400 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300" aria-label="Mes anterior">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                </div>
                <div class="text-center leading-none">
                    <span class="block text-[11px] font-extrabold" x-text="monthName"></span>
                    <span class="block text-[9px] font-bold text-zinc-500 dark:text-zinc-400" x-text="viewYear"></span>
                </div>
                <div class="flex items-center gap-0.5">
                    <button type="button" @click="shiftMonth(1)" class="flex h-6 w-6 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-zinc-400 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300" aria-label="Mes siguiente">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button type="button" @click="shiftYear(1)" class="flex h-6 w-6 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-zinc-400 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300" aria-label="Año siguiente">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    {{-- Botón OK — cierra el picker --}}
                    <button
                        type="button"
                        @click="closeMenu()"
                        :disabled="!value"
                        :class="value
                            ? 'bg-emerald-600 text-white hover:bg-emerald-500 cursor-pointer shadow-sm'
                            : 'bg-zinc-200 text-zinc-400 cursor-not-allowed dark:bg-zinc-700 dark:text-zinc-500'"
                        class="ml-0.5 flex h-6 items-center rounded-md px-2 text-[10px] font-bold tracking-wide transition active:scale-95"
                        aria-label="Confirmar fecha"
                    >OK</button>
                </div>
            </div>

            {{-- Cabecera días semana --}}
            <div class="grid grid-cols-7 gap-0.5 text-center text-[8px] font-extrabold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                <template x-for="d in ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do']" :key="d">
                    <span class="py-0.5" x-text="d"></span>
                </template>
            </div>

            {{-- Grid de días --}}
            <div class="grid grid-cols-7 gap-0.5">
                <template x-for="cell in grid" :key="cell.key">
                    <button
                        type="button"
                        @click="pick(cell.date)"
                        :class="cell.isSelected
                            ? 'rounded-full bg-emerald-600 text-white shadow-sm font-bold'
                            : (cell.isToday && cell.inMonth && ! cell.outOfRange
                                ? 'rounded-full bg-rose-500 text-white shadow-sm font-extrabold dark:bg-rose-500'
                                : (cell.inMonth && ! cell.outOfRange
                                    ? 'rounded-lg text-zinc-800 hover:bg-emerald-50 hover:text-emerald-700 dark:text-zinc-100 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300'
                                    : 'cursor-default rounded-lg text-zinc-400 opacity-30 dark:text-zinc-500'))"
                        class="flex h-6 w-full items-center justify-center text-[10px] transition"
                        x-text="cell.day"
                    ></button>
                </template>
            </div>
        </div>
    </template>
</div>
