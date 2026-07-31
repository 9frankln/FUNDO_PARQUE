<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['data']));

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

foreach (array_filter((['data']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section wire:ignore x-data="financeDashboard(<?php echo \Illuminate\Support\Js::from($data)->toHtml() ?>)" x-cloak
         class="overflow-hidden rounded-[1.75rem] border border-emerald-900/10 bg-white shadow-[0_24px_70px_-38px_rgba(6,78,59,0.35)] dark:border-zinc-800 dark:bg-zinc-900/75 dark:shadow-black/30">
    <div class="relative overflow-hidden border-b border-emerald-900/10 bg-gradient-to-br from-emerald-50 via-white to-sky-50 px-4 py-5 dark:border-zinc-800 dark:from-emerald-900/45 dark:via-zinc-900 dark:to-sky-900/35 sm:px-6">
        <div class="pointer-events-none absolute -right-16 -top-20 h-52 w-52 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-400/10"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-emerald-600/15 bg-emerald-600/10 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-emerald-800 dark:border-emerald-300/15 dark:bg-emerald-300/10 dark:text-emerald-300">Panel financiero</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Actualizado <?php echo e($data['generatedAt']); ?></span>
                </div>
                <h2 class="mt-2 text-xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">Pulso económico del fundo</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Ingresos, egresos, balance y destinos principales en una sola lectura.</p>
            </div>
            
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-end">
                <!-- Dropdown Selector -->
                <div class="block w-full sm:w-52 relative" 
                     x-data="{ 
                        dropdownOpen: false,
                        menuStyle: '',
                        positionMenu() {
                            const rect = this.$refs.trigger.getBoundingClientRect();
                            const width = rect.width;
                            const left = rect.left + window.scrollX;
                            const top = rect.bottom + window.scrollY;
                            this.menuStyle = `left: ${left}px; top: ${top}px; width: ${width}px; position: absolute;`;
                        }
                     }"
                     x-init="$watch('dropdownOpen', value => value && $nextTick(() => positionMenu()))"
                     @resize.window="dropdownOpen && positionMenu()"
                     @scroll.window="dropdownOpen && positionMenu()">
                    <span class="mb-1 block text-[10px] font-extrabold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Analizar un mes específico</span>
                    <button x-ref="trigger" type="button" @click="dropdownOpen = !dropdownOpen"
                            class="flex h-10 w-full items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white px-3.5 text-left text-xs font-bold text-zinc-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 hover:border-emerald-400 dark:hover:border-emerald-400"
                            :class="dropdownOpen && 'border-emerald-500 ring-2 ring-emerald-500/20'">
                        <span class="flex-1 min-w-0 truncate text-zinc-700 dark:text-zinc-200" x-text="selectedPeriod === '' ? 'Todo el periodo visible' : (visibleMonths.find(m => m.period === selectedPeriod)?.fullLabel || 'Todo el periodo visible')">Todo el periodo visible</span>
                        <svg class="h-4 w-4 shrink-0 text-zinc-400 transition-transform" :class="dropdownOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>
                    <template x-teleport="body">
                        <div x-show="dropdownOpen" @click.outside="dropdownOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             :style="menuStyle"
                             class="z-50 mt-1.5 max-h-60 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1.5 shadow-xl shadow-zinc-900/15 dark:border-zinc-800 dark:bg-zinc-900"
                             style="display: none;">
                            <button type="button" @click="selectPeriod(''); dropdownOpen = false"
                                    :class="selectedPeriod === '' ? 'bg-emerald-600 text-white dark:bg-emerald-400/20 dark:text-emerald-50' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900'"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-bold transition">
                                <span>Todo el periodo visible</span>
                                <svg x-show="selectedPeriod === ''" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>
                            </button>
                            <template x-for="month in [...visibleMonths].reverse()" :key="`option-${month.period}`">
                                <button type="button" @click="selectPeriod(month.period); dropdownOpen = false"
                                        :class="selectedPeriod === month.period ? 'bg-emerald-600 text-white dark:bg-emerald-400/20 dark:text-emerald-50' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900'"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-bold transition">
                                    <span x-text="`${month.fullLabel}${month.movements === 0 ? ' · sin movimientos' : ''}`"></span>
                                    <svg x-show="selectedPeriod === month.period" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Range switcher -->
                <div class="inline-flex items-center gap-1 rounded-2xl border border-zinc-200/90 bg-white/90 p-1.5 shadow-sm backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/90" aria-label="Alcance temporal del dashboard">
                    <template x-for="item in [{v: 6, l: '6 meses'}, {v: 12, l: '12 meses'}]" :key="item.v">
                        <button type="button" @click="setRange(item.v)"
                                :class="range === item.v 
                                    ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/25 ring-1 ring-emerald-400/30 dark:bg-emerald-400 dark:text-emerald-950 font-extrabold scale-[1.02]' 
                                    : 'text-zinc-600 hover:bg-zinc-100/80 dark:text-zinc-400 dark:hover:bg-zinc-800/80 hover:text-zinc-900 dark:hover:text-white font-bold'"
                                class="relative inline-flex items-center justify-center gap-1.5 rounded-xl px-3.5 py-2 text-xs transition-all duration-200 ease-out focus:outline-none">
                            <span class="h-1.5 w-1.5 rounded-full transition-all duration-300" 
                                  :class="range === item.v ? 'bg-white dark:bg-zinc-950 animate-pulse' : 'bg-transparent'"></span>
                            <span x-text="item.l"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4 p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-emerald-200/80 bg-emerald-50/70 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/[.07]">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-emerald-800/70 dark:text-emerald-300/70" x-text="selectedPeriod ? 'Ingresos del mes' : 'Ingresos del periodo'"></p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-900 dark:text-emerald-50" x-text="formatMoney(totalIncome)"></p>
                <p class="mt-2 text-xs text-emerald-800/65 dark:text-emerald-300/60"><span x-text="range"></span> meses analizados</p>
            </article>
            <article class="rounded-2xl border border-rose-200/80 bg-rose-50/70 p-4 dark:border-rose-500/20 dark:bg-rose-500/[.07]">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-rose-800/70 dark:text-rose-300/70" x-text="selectedPeriod ? 'Egresos del mes' : 'Egresos del periodo'"></p>
                <p class="mt-2 text-2xl font-extrabold text-rose-900 dark:text-rose-50" x-text="formatMoney(totalExpenses)"></p>
                <p class="mt-2 text-xs text-rose-800/65 dark:text-rose-300/60">Salidas registradas en caja</p>
            </article>
            <article class="rounded-2xl border p-4" :class="totalBalance >= 0 ? 'border-sky-200/80 bg-sky-50/70 dark:border-sky-500/20 dark:bg-sky-500/[.07]' : 'border-amber-200/80 bg-amber-50/70 dark:border-amber-500/20 dark:bg-amber-500/[.07]'">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-zinc-600 dark:text-zinc-400" x-text="selectedPeriod ? 'Balance del mes' : 'Balance acumulado'"></p>
                <p class="mt-2 text-2xl font-extrabold" :class="totalBalance >= 0 ? 'text-sky-900 dark:text-sky-50' : 'text-amber-900 dark:text-amber-50'" x-text="formatMoney(totalBalance)"></p>
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400" x-text="totalBalance >= 0 ? 'Resultado favorable' : 'Egresos por encima de ingresos'"></p>
            </article>
            <article class="rounded-2xl border border-violet-200/80 bg-violet-50/70 p-4 dark:border-violet-500/20 dark:bg-violet-500/[.07]">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-violet-800/70 dark:text-violet-300/70" x-text="selectedPeriod ? 'Asignación del mes' : 'Asignación familiar'"></p>
                <p class="mt-2 text-2xl font-extrabold text-violet-900 dark:text-violet-50" x-text="formatMoney(totalAssignments)"></p>
                <p class="mt-2 text-xs text-violet-800/65 dark:text-violet-300/60">Entregas del periodo</p>
            </article>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <article class="rounded-2xl border border-zinc-200 bg-zinc-50/60 p-4 xl:col-span-8 dark:border-zinc-800 dark:bg-zinc-900/55 sm:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.12)]"></span>
                            <h3 class="font-bold text-zinc-900 dark:text-white">Evolución de caja</h3>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Compara ingresos y egresos mes a mes.</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-bold text-zinc-600 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-full bg-emerald-500"></i>Ingresos</span>
                        <span class="inline-flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-full bg-rose-500"></i>Egresos</span>
                    </div>
                </div>
                <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 bg-white px-2 pt-2 dark:border-zinc-800 dark:bg-zinc-900/70">
                    <svg class="h-56 w-full" viewBox="0 0 1000 230" preserveAspectRatio="none" role="img" aria-label="Gráfico de ingresos y egresos mensuales">
                        <defs>
                            <linearGradient id="incomeTrendArea" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.01" />
                            </linearGradient>
                            <linearGradient id="expensesTrendArea" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#f43f5e" stop-opacity="0.01" />
                            </linearGradient>
                        </defs>
                        <g stroke="currentColor" class="text-zinc-100 dark:text-zinc-800/80" stroke-width="1">
                            <line x1="30" x2="970" y1="40" y2="40"/><line x1="30" x2="970" y1="95" y2="95"/><line x1="30" x2="970" y1="150" y2="150"/><line x1="30" x2="970" y1="205" y2="205"/>
                        </g>
                        <polygon :points="`30,205 ${trendPoints('income')} 970,205`" fill="url(#incomeTrendArea)" />
                        <polygon :points="`30,205 ${trendPoints('expenses')} 970,205`" fill="url(#expensesTrendArea)" />

                        <polyline :points="trendPoints('income')" fill="none" stroke="#10b981" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                        <polyline :points="trendPoints('expenses')" fill="none" stroke="#f43f5e" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                    </svg>
                    <div class="grid border-t border-zinc-100 px-1 py-2 dark:border-zinc-800" :style="`grid-template-columns: repeat(${visibleMonths.length}, minmax(0, 1fr))`">
                        <template x-for="(month, index) in visibleMonths" :key="`label-${month.period}`">
                            <span class="truncate text-center text-[9px] font-semibold text-zinc-400" x-text="showLabel(index) ? month.label : ''"></span>
                        </template>
                    </div>
                </div>
            </article>

            <div class="grid gap-4 sm:grid-cols-2 xl:col-span-4 xl:grid-cols-1">
                <article class="rounded-2xl border p-4 transition-colors"
                         :class="categoryType === 'egreso' ? 'border-rose-200/80 bg-rose-50/55 dark:border-rose-500/20 dark:bg-rose-500/[.055]' : 'border-emerald-200/80 bg-emerald-50/55 dark:border-emerald-500/20 dark:bg-emerald-500/[.055]'">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-zinc-900 dark:text-white" x-text="categoryType === 'egreso' ? 'Principales egresos' : 'Principales ingresos'">Principales egresos</h3>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-text="categoryType === 'egreso' ? 'Categorías con mayor salida.' : 'Categorías con mayor entrada.'">Categorías con mayor salida.</p>
                        </div>
                        <div class="flex items-center gap-1 rounded-lg border border-zinc-200/80 bg-white/50 p-1 shadow-sm dark:border-zinc-700/50 dark:bg-zinc-800/50">
                            <button type="button" @click="categoryType = 'ingreso'" 
                                    class="rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-wider transition"
                                    :class="categoryType === 'ingreso' ? 'bg-emerald-700 text-white shadow-sm dark:bg-emerald-400 dark:text-emerald-950' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Ingresos
                            </button>
                            <button type="button" @click="categoryType = 'egreso'"
                                    class="rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-wider transition"
                                    :class="categoryType === 'egreso' ? 'bg-rose-500 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Egresos
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 space-y-3" x-show="categoryRows.length > 0">
                        <template x-for="row in categoryRows" :key="row.label">
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs"><span class="truncate font-semibold capitalize text-zinc-700 dark:text-zinc-300" x-text="row.label"></span><strong class="shrink-0" :class="categoryType === 'egreso' ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300'" x-text="formatMoney(row.amount)"></strong></div>
                                <div class="h-1.5 overflow-hidden rounded-full" :class="categoryType === 'egreso' ? 'bg-rose-100 dark:bg-zinc-800' : 'bg-emerald-100 dark:bg-zinc-800'"><div class="h-full rounded-full" :class="categoryType === 'egreso' ? 'bg-rose-500' : 'bg-emerald-500'" :style="`width:${row.width}`"></div></div>
                            </div>
                        </template>
                    </div>
                    <p class="mt-4 text-xs text-zinc-500" x-show="categoryRows.length === 0" x-text="categoryType === 'egreso' ? 'Sin egresos en el periodo.' : 'Sin ingresos en el periodo.'">Sin movimientos en el periodo.</p>
                </article>
                <article class="rounded-2xl border border-violet-200/80 bg-violet-50/55 p-4 dark:border-violet-500/20 dark:bg-violet-500/[.055]">
                    <h3 class="font-bold text-zinc-900 dark:text-white">Destinos familiares</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Propósitos con mayor entrega.</p>
                    <div class="mt-4 space-y-3" x-show="purposeRows.length > 0">
                        <template x-for="row in purposeRows" :key="row.label">
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs"><span class="truncate font-semibold capitalize text-zinc-700 dark:text-zinc-300" x-text="row.label"></span><strong class="shrink-0 text-violet-700 dark:text-violet-300" x-text="formatMoney(row.amount)"></strong></div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-violet-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-violet-500" :style="`width:${row.width}`"></div></div>
                            </div>
                        </template>
                    </div>
                    <p class="mt-4 text-xs text-zinc-500" x-show="purposeRows.length === 0">Sin asignaciones en el periodo.</p>
                </article>
            </div>
        </div>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs border-t border-zinc-100 pt-3 dark:border-zinc-800/80">
            <span class="text-zinc-500 dark:text-zinc-400">Mes analizado: <strong class="text-zinc-800 dark:text-zinc-200" x-text="selectedPeriod ? (visibleMonths.find(m => m.period === selectedPeriod)?.fullLabel || 'Todo el periodo') : 'Todo el periodo'"></strong></span>
            <button x-show="selectedPeriod !== ''" type="button" @click="selectPeriod('')" class="font-bold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">Ver todo el periodo</button>
        </div>
    </div>
</section>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/finance-dashboard.blade.php ENDPATH**/ ?>