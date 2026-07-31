<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['options' => [], 'max' => null, 'model' => 'animalIds']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['options' => [], 'max' => null, 'model' => 'animalIds']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    x-data="{
        open: false,
        search: '',
        selected: $wire.entangle('<?php echo e($model); ?>'),
        options: <?php echo \Illuminate\Support\Js::from($options)->toHtml() ?>,
        max: <?php echo \Illuminate\Support\Js::from($max)->toHtml() ?>,
        menuStyle: '',
        init() {
            this.selected = (this.selected || []).map(String);
        },
        normalize(value) {
            return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        },
        filteredOptions() {
            const query = this.normalize(this.search.trim());
            if (!query) return this.options;

            return this.options.filter((animal) => this.normalize([
                animal.code,
                animal.name,
                animal.type,
                animal.species,
                animal.breed,
                animal.sex,
            ].join(' ')).includes(query));
        },
        groupedOptions() {
            const groups = this.filteredOptions().reduce((result, animal) => {
                const group = animal.type || 'Otros';
                (result[group] ||= []).push(animal);
                return result;
            }, {});

            return Object.entries(groups).map(([type, animals]) => ({ type, animals }));
        },
        selectedOptions() {
            return this.options.filter((animal) => this.selected.includes(String(animal.id)));
        },
        toggle(id) {
            const value = String(id);
            if (this.selected.includes(value)) {
                this.selected = this.selected.filter((selectedId) => selectedId !== value);
                return;
            }
            if (this.max === 1) {
                this.selected = [value];
                this.open = false;
                return;
            }
            this.selected = [...this.selected, value];
        },
        remove(id) {
            const value = String(id);
            this.selected = this.selected.filter((selectedId) => selectedId !== value);
        },
        allFilteredSelected() {
            const filtered = this.filteredOptions();
            return filtered.length > 0 && filtered.every((animal) => this.selected.includes(String(animal.id)));
        },
        toggleFiltered() {
            const ids = this.filteredOptions().map((animal) => String(animal.id));
            this.selected = this.allFilteredSelected()
                ? this.selected.filter((id) => !ids.includes(id))
                : [...new Set([...this.selected, ...ids])];
        },
        positionMenu() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const width = Math.min(Math.max(rect.width, 520), 760, window.innerWidth - 16);
            const left = Math.min(Math.max(8, rect.left), window.innerWidth - width - 8);
            const height = Math.min(620, window.innerHeight - 24);
            const spaceBelow = window.innerHeight - rect.bottom;
            const openAbove = spaceBelow < Math.min(height, 420) && rect.top > spaceBelow;
            const top = openAbove
                ? Math.max(8, rect.top - height - 6)
                : Math.min(rect.bottom + 6, window.innerHeight - height - 8);

            this.menuStyle = `left: ${left}px; top: ${top}px; width: ${width}px; max-height: ${height}px;`;
        },
        openMenu() {
            this.open = true;
            this.$nextTick(() => {
                this.positionMenu();
                this.$refs.search?.focus();
            });
        },
    }"
    @click.window="if (open && !$refs.trigger.contains($event.target) && !$refs.menu.contains($event.target)) open = false"
    @keydown.escape.stop="open = false; $refs.trigger.focus()"
    @resize.window="open && positionMenu()"
    @scroll.window="open && positionMenu()"
    class="relative w-full"
>
    <button
        x-ref="trigger"
        type="button"
        role="combobox"
        aria-haspopup="listbox"
        :aria-expanded="open"
        @click="open ? open = false : openMenu()"
        class="flex min-h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-left shadow-sm transition hover:border-sky-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-sky-400"
    >
        <span x-show="selected.length === 0" class="text-sm font-medium text-slate-500 dark:text-slate-400">Busca y selecciona animales...</span>
        <span x-show="selected.length > 0" class="flex min-w-0 flex-1 flex-wrap gap-1.5">
            <template x-for="animal in selectedOptions().slice(0, 6)" :key="animal.id">
                <span class="inline-flex max-w-48 items-center gap-1.5 rounded-lg bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-900 dark:bg-sky-400/10 dark:text-sky-100">
                    <span class="truncate" x-text="`${animal.code} · ${animal.name}`"></span>
                    <span @click.stop="remove(animal.id)" class="text-sky-500 hover:text-rose-600" aria-label="Quitar animal">&times;</span>
                </span>
            </template>
            <span x-show="selected.length > 6" class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300" x-text="`+${selected.length - 6}`"></span>
        </span>
        <span class="flex shrink-0 items-center gap-2">
            <span x-show="selected.length > 0" class="rounded-full bg-sky-600 px-2 py-0.5 text-[10px] font-bold text-white" x-text="selected.length"></span>
            <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" /></svg>
        </span>
    </button>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            x-ref="menu"
            :style="menuStyle"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-[.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-[.98]"
            class="fixed z-[100] flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="border-b border-slate-200 p-2.5 dark:border-slate-700">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.1-5.4a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                    <input x-ref="search" x-model.debounce.100ms="search" type="search" placeholder="Buscar por código, nombre, tipo, especie o raza..."
                           class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-800 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                </div>
                <button x-show="max !== 1" type="button" @click="toggleFiltered()" class="mt-2 flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-sky-700 dark:text-slate-300 dark:hover:text-sky-300">
                    <span class="flex h-4 w-4 items-center justify-center rounded border" :class="allFilteredSelected() ? 'border-sky-600 bg-sky-600 text-white' : 'border-slate-300 dark:border-slate-600'">
                        <svg x-show="allFilteredSelected()" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                    </span>
                    <span x-text="search ? 'Seleccionar resultados' : 'Seleccionar todos'"></span>
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-2" role="listbox" aria-multiselectable="true">
                <template x-for="group in groupedOptions()" :key="group.type">
                    <section class="mb-2 last:mb-0">
                        <div class="sticky top-0 z-10 flex items-center justify-between bg-slate-100/95 px-2.5 py-1.5 backdrop-blur dark:bg-slate-800/95">
                            <strong class="text-[10px] uppercase tracking-wider text-slate-600 dark:text-slate-300" x-text="group.type"></strong>
                            <span class="text-[10px] font-semibold text-slate-400" x-text="group.animals.length"></span>
                        </div>
                        <div class="mt-1 space-y-1">
                            <template x-for="animal in group.animals" :key="animal.id">
                                <button type="button" @click="toggle(animal.id)" role="option" :aria-selected="selected.includes(String(animal.id))"
                                        :class="selected.includes(String(animal.id)) ? 'border-sky-300 bg-sky-50 dark:border-sky-500/40 dark:bg-sky-400/10' : 'border-transparent hover:border-slate-200 hover:bg-slate-50 dark:hover:border-slate-700 dark:hover:bg-slate-800/70'"
                                        class="flex w-full items-center gap-2 rounded-lg border px-2 py-1 text-left transition">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-100 text-[10px] font-black text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        <template x-if="animal.photo"><img :src="animal.photo" :alt="animal.code" loading="lazy" class="h-full w-full object-cover" :style="{ objectPosition: `${Number.isFinite(Number(animal.frame?.x)) ? Number(animal.frame.x) : 50}% ${Number.isFinite(Number(animal.frame?.y)) ? Number(animal.frame.y) : 50}%`, transform: `scale(${Number.isFinite(Number(animal.frame?.zoom)) ? Number(animal.frame.zoom) : 1})`, transformOrigin: `${Number.isFinite(Number(animal.frame?.x)) ? Number(animal.frame.x) : 50}% ${Number.isFinite(Number(animal.frame?.y)) ? Number(animal.frame.y) : 50}%` }"></template>
                                        <span x-show="!animal.photo" x-text="animal.code.slice(0, 2)"></span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-baseline gap-x-2">
                                            <strong class="text-xs text-slate-900 dark:text-slate-100" x-text="animal.code"></strong>
                                            <span class="truncate text-xs font-semibold text-slate-600 dark:text-slate-300" x-text="animal.name"></span>
                                        </span>
                                        <span class="mt-0.5 block truncate text-[10px] text-slate-500 dark:text-slate-400" x-text="`${animal.species} · ${animal.breed} · ${animal.sex}`"></span>
                                    </span>
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2" :class="selected.includes(String(animal.id)) ? 'border-sky-600 bg-sky-600 text-white' : 'border-slate-300 dark:border-slate-600'">
                                        <svg x-show="selected.includes(String(animal.id))" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </section>
                </template>
                <div x-show="filteredOptions().length === 0" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Sin animales para esta búsqueda.</div>
            </div>

            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-950/60">
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400" x-text="`${selected.length} ${selected.length === 1 ? 'animal seleccionado' : 'animales seleccionados'}`"></span>
                <button type="button" @click="open = false" class="rounded-lg bg-sky-700 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-sky-600 dark:bg-sky-500 dark:text-sky-950">Listo</button>
            </div>
        </div>
    </template>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/animal-multi-select.blade.php ENDPATH**/ ?>