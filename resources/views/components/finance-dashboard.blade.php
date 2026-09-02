@props(['data'])

<section wire:ignore x-data="financeDashboard(@js($data))"
         class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
    <div class="relative overflow-hidden border-b border-zinc-200 bg-zinc-50/70 px-4 py-5 dark:border-zinc-800 dark:bg-zinc-900/60 sm:px-6">
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-emerald-600/20 bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">Panel financiero</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Actualizado {{ $data['generatedAt'] }}</span>
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
                            class="flex h-10 w-full items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white px-3.5 text-left text-xs font-bold text-zinc-700 shadow-xs transition focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-200 hover:border-emerald-400 dark:hover:border-zinc-700"
                            :class="dropdownOpen && 'border-emerald-500 ring-2 ring-emerald-500/20 dark:border-emerald-500'">
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
                             class="z-50 mt-1.5 max-h-60 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1.5 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
                             style="display: none;">
                            <button type="button" @click="selectPeriod(''); dropdownOpen = false"
                                    :class="selectedPeriod === '' ? 'bg-emerald-600 text-white dark:bg-emerald-500/20 dark:text-emerald-400 font-bold' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-semibold transition">
                                <span>Todo el periodo visible</span>
                                <svg x-show="selectedPeriod === ''" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>
                            </button>
                            <template x-for="month in [...visibleMonths].reverse()" :key="`option-${month.period}`">
                                <button type="button" @click="selectPeriod(month.period); dropdownOpen = false"
                                        :class="selectedPeriod === month.period ? 'bg-emerald-600 text-white dark:bg-emerald-500/20 dark:text-emerald-400 font-bold' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-semibold transition">
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
                <div class="inline-flex items-center gap-1 rounded-xl border border-zinc-200 bg-zinc-100 p-1 dark:border-zinc-800 dark:bg-zinc-950" aria-label="Alcance temporal del dashboard">
                    <template x-for="item in [{v: 6, l: '6 meses'}, {v: 12, l: '12 meses'}]" :key="item.v">
                        <button type="button" @click="setRange(item.v)"
                                :class="range === item.v 
                                    ? 'bg-white text-emerald-700 shadow-xs dark:bg-zinc-800 dark:text-emerald-400 font-bold' 
                                    : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white font-medium'"
                                class="relative inline-flex items-center justify-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs transition focus:outline-none">
                            <span class="h-1.5 w-1.5 rounded-full" 
                                  :class="range === item.v ? 'bg-emerald-600 dark:bg-emerald-400' : 'bg-transparent'"></span>
                            <span x-text="item.l"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4 p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Card 1: Ingresos -->
            <article class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950 sm:p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400" x-text="selectedPeriod ? 'Ingresos del mes' : 'Ingresos del periodo'"></p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-zinc-900 dark:text-white" x-text="formatMoney(totalIncome)"></p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"><span x-text="range"></span> meses analizados</p>
            </article>

            <!-- Card 2: Egresos -->
            <article class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950 sm:p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400" x-text="selectedPeriod ? 'Egresos del mes' : 'Egresos del periodo'"></p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-zinc-900 dark:text-white" x-text="formatMoney(totalExpenses)"></p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Salidas registradas en caja</p>
            </article>

            <!-- Card 3: Balance -->
            <article class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950 sm:p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400" x-text="selectedPeriod ? 'Balance del mes' : 'Balance acumulado'"></p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.071.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.22 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold" :class="totalBalance >= 0 ? 'text-zinc-900 dark:text-white' : 'text-rose-600 dark:text-rose-400'" x-text="formatMoney(totalBalance)"></p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-text="totalBalance >= 0 ? 'Resultado favorable' : 'Egresos por encima de ingresos'"></p>
            </article>

            <!-- Card 4: Asignaciones -->
            <article class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950 sm:p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400" x-text="selectedPeriod ? 'Asignación del mes' : 'Asignación familiar'"></p>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400 border border-violet-200/60 dark:border-violet-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-zinc-900 dark:text-white" x-text="formatMoney(totalAssignments)"></p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Entregas del periodo</p>
            </article>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <!-- Evolución de caja -->
            <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs xl:col-span-8 dark:border-zinc-800 dark:bg-zinc-950 sm:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="font-bold text-zinc-900 dark:text-white">Evolución de caja</h3>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Compara ingresos y egresos mes a mes.</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-bold text-zinc-600 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-full bg-emerald-500"></i>Ingresos</span>
                        <span class="inline-flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-full bg-rose-500"></i>Egresos</span>
                    </div>
                </div>
                <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50/50 px-2 pt-2 dark:border-zinc-800/80 dark:bg-zinc-900/60">
                    <svg class="h-56 w-full" viewBox="0 0 1000 230" preserveAspectRatio="none" role="img" aria-label="Gráfico de ingresos y egresos mensuales">
                        <g stroke="currentColor" class="text-zinc-200 dark:text-zinc-800" stroke-width="1">
                            <line x1="30" x2="970" y1="40" y2="40"/><line x1="30" x2="970" y1="95" y2="95"/><line x1="30" x2="970" y1="150" y2="150"/><line x1="30" x2="970" y1="205" y2="205"/>
                        </g>

                        <polyline :points="trendPoints('income')" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                        <polyline :points="trendPoints('expenses')" fill="none" stroke="#f43f5e" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                    </svg>
                    <div class="grid border-t border-zinc-100 px-1 py-2 dark:border-zinc-800" :style="`grid-template-columns: repeat(${visibleMonths.length}, minmax(0, 1fr))`">
                        <template x-for="(month, index) in visibleMonths" :key="`label-${month.period}`">
                            <span class="truncate text-center text-[9px] font-semibold text-zinc-400" x-text="showLabel(index) ? month.label : ''"></span>
                        </template>
                    </div>
                </div>
            </article>

            <div class="grid gap-4 sm:grid-cols-2 xl:col-span-4 xl:grid-cols-1">
                <!-- Principales ingresos / egresos -->
                <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-zinc-900 dark:text-white" x-text="categoryType === 'egreso' ? 'Principales egresos' : 'Principales ingresos'">Principales egresos</h3>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-text="categoryType === 'egreso' ? 'Categorías con mayor salida.' : 'Categorías con mayor entrada.'">Categorías con mayor salida.</p>
                        </div>
                        <div class="flex items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-100 p-1 dark:border-zinc-800 dark:bg-zinc-900">
                            <button type="button" @click="categoryType = 'ingreso'" 
                                    class="rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider transition"
                                    :class="categoryType === 'ingreso' ? 'bg-white text-emerald-700 shadow-xs dark:bg-zinc-800 dark:text-emerald-400' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white'">
                                Ingresos
                            </button>
                            <button type="button" @click="categoryType = 'egreso'"
                                    class="rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider transition"
                                    :class="categoryType === 'egreso' ? 'bg-white text-rose-700 shadow-xs dark:bg-zinc-800 dark:text-rose-400' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white'">
                                Egresos
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 space-y-3" x-show="categoryRows.length > 0">
                        <template x-for="row in categoryRows" :key="row.label">
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                    <span class="truncate font-semibold capitalize text-zinc-700 dark:text-zinc-300" x-text="row.label"></span>
                                    <strong class="shrink-0" :class="categoryType === 'egreso' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="formatMoney(row.amount)"></strong>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded-full" :class="categoryType === 'egreso' ? 'bg-rose-500' : 'bg-emerald-500'" :style="`width:${row.width}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <p class="mt-4 text-xs text-zinc-500" x-show="categoryRows.length === 0" x-text="categoryType === 'egreso' ? 'Sin egresos en el periodo.' : 'Sin ingresos en el periodo.'">Sin movimientos en el periodo.</p>
                </article>

                <!-- Destinos familiares -->
                <article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950 sm:p-5">
                    <h3 class="font-bold text-zinc-900 dark:text-white">Destinos familiares</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Propósitos con mayor entrega.</p>
                    <div class="mt-4 space-y-3" x-show="purposeRows.length > 0">
                        <template x-for="row in purposeRows" :key="row.label">
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                    <span class="truncate font-semibold capitalize text-zinc-700 dark:text-zinc-300" x-text="row.label"></span>
                                    <strong class="shrink-0 text-violet-600 dark:text-violet-400" x-text="formatMoney(row.amount)"></strong>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded-full bg-violet-500" :style="`width:${row.width}`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <p class="mt-4 text-xs text-zinc-500" x-show="purposeRows.length === 0">Sin asignaciones en el periodo.</p>
                </article>
            </div>
        </div>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs border-t border-zinc-100 pt-3 dark:border-zinc-800">
            <span class="text-zinc-500 dark:text-zinc-400">Mes analizado: <strong class="text-zinc-800 dark:text-zinc-200" x-text="selectedPeriod ? (visibleMonths.find(m => m.period === selectedPeriod)?.fullLabel || 'Todo el periodo') : 'Todo el periodo'"></strong></span>
            <button x-show="selectedPeriod !== ''" type="button" @click="selectPeriod('')" class="font-bold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">Ver todo el periodo</button>
        </div>
    </div>
</section>
