<x-recent-record-host :active="$recentRecord !== null" class="space-y-6">
    <!-- Breadcrumbs / Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                Producción de Queso
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Registra y analiza la elaboración diaria de queso y el rendimiento semanal.</p>
        </div>
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            @if(auth()->user()->tienePermiso('queso', 'exportar'))
                <button type="button" wire:click="openQuesoReportModal"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-zinc-700 bg-zinc-900 px-4 text-sm font-bold text-zinc-200 shadow-sm transition hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-300 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Generar reporte PDF
                </button>
            @endif
            @if(auth()->user()->tienePermiso('queso', 'crear'))
                <a href="{{ route('queso.create') }}" class="agro-button w-full sm:w-auto">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Registrar Elaboración</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Productive dashboard: summaries only, interactions run locally in Alpine. -->
    <section wire:ignore x-data="cheeseDashboard(@js($dashboardData))" x-cloak
             class="overflow-hidden rounded-[1.75rem] border border-emerald-950/10 bg-white shadow-[0_24px_70px_-38px_rgba(6,78,59,0.35)] dark:border-emerald-200/10 dark:bg-zinc-950/75 dark:shadow-black/30">
        <div class="relative overflow-hidden border-b border-emerald-950/10 bg-gradient-to-br from-emerald-50 via-white to-sky-50 px-4 py-5 dark:border-emerald-200/10 dark:from-emerald-950/70 dark:via-zinc-950 dark:to-sky-950/50 sm:px-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-52 w-52 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-400/10"></div>
            <div class="relative flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-emerald-600/15 bg-emerald-600/10 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-emerald-800 dark:border-emerald-300/15 dark:bg-emerald-300/10 dark:text-emerald-300">Panel productivo</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Actualizado {{ $dashboardData['generatedAt'] }}</span>
                    </div>
                    <h2 class="mt-2 text-xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">Radiografía de la producción de queso</h2>
                    <p class="mt-1 max-w-3xl text-sm text-zinc-600 dark:text-zinc-400">Tendencias, mezcla de presentaciones y comportamiento histórico sin consultas adicionales al cambiar de gráfico.</p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-end xl:w-auto">
                    <div class="block min-w-52 relative" 
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
                                class="flex h-10 w-full items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white px-3.5 text-left text-xs font-bold text-zinc-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 hover:border-emerald-400 dark:hover:border-emerald-400"
                                :class="dropdownOpen && 'border-emerald-500 ring-2 ring-emerald-500/20'">
                            <span class="truncate" x-text="selectedPeriod === '' ? 'Todo el periodo visible' : (visibleMonths.find(m => m.period === selectedPeriod)?.fullLabel || 'Todo el periodo visible')"></span>
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
                                 class="z-50 mt-1.5 max-h-60 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1.5 shadow-xl shadow-zinc-950/15 dark:border-zinc-700 dark:bg-zinc-900"
                                 style="display: none;">
                                <button type="button" @click="selectPeriod(''); dropdownOpen = false"
                                        :class="selectedPeriod === '' ? 'bg-emerald-600 text-white dark:bg-emerald-400/20 dark:text-emerald-50' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-bold transition">
                                    <span>Todo el periodo visible</span>
                                    <svg x-show="selectedPeriod === ''" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                    </svg>
                                </button>
                                <template x-for="month in [...visibleMonths].reverse()" :key="`option-${month.period}`">
                                    <button type="button" @click="selectPeriod(month.period); dropdownOpen = false"
                                            :class="selectedPeriod === month.period ? 'bg-emerald-600 text-white dark:bg-emerald-400/20 dark:text-emerald-50' : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-bold transition">
                                        <span x-text="`${month.fullLabel}${Number(month.records) === 0 ? ' · sin producción' : ''}`"></span>
                                        <svg x-show="selectedPeriod === month.period" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="inline-flex items-center gap-1 rounded-2xl border border-zinc-200/90 bg-white/90 p-1.5 shadow-sm backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/90" aria-label="Alcance temporal del dashboard">
                        <template x-for="item in [{v: 6, l: '6 meses'}, {v: 12, l: '12 meses'}, {v: 24, l: '24 meses'}]" :key="item.v">
                            <button type="button" @click="setRange(item.v)"
                                    :class="range === item.v 
                                        ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/25 ring-1 ring-emerald-400/30 dark:from-emerald-500 dark:to-teal-400 dark:text-zinc-950 font-extrabold scale-[1.02]' 
                                        : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-800/80 font-bold'"
                                    class="relative inline-flex items-center justify-center gap-1.5 rounded-xl px-3.5 py-2 text-xs transition-all duration-200 ease-out focus:outline-none xl:flex-none">
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
                <article class="group relative overflow-hidden rounded-2xl border border-emerald-200/80 bg-emerald-50/70 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/[.07]">
                    <div class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600/10 text-emerald-700 dark:bg-emerald-300/10 dark:text-emerald-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.071.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.22 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                    <p class="pr-10 text-[10px] font-extrabold uppercase tracking-[0.14em] text-emerald-800/70 dark:text-emerald-300/70" x-text="selectedMonth ? 'Peso del mes' : 'Peso del periodo'"></p>
                    <p class="mt-2 text-2xl font-extrabold text-emerald-950 dark:text-emerald-50"><span x-text="format(totalWeight, 2)"></span> <small class="text-sm font-bold opacity-60">kg</small></p>
                    <p class="mt-2 text-xs text-emerald-800/65 dark:text-emerald-300/60"><span x-text="totalRecords"></span> elaboraciones consideradas</p>
                </article>

                <article class="relative overflow-hidden rounded-2xl border border-sky-200/80 bg-sky-50/70 p-4 dark:border-sky-500/20 dark:bg-sky-500/[.07]">
                    <div class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl bg-sky-600/10 text-sky-700 dark:bg-sky-300/10 dark:text-sky-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75c0-1.036 3.694-1.875 8.25-1.875s8.25.84 8.25 1.875S16.556 8.625 12 8.625s-8.25-.84-8.25-1.875Zm0 0v4.5c0 1.036 3.694 1.875 8.25 1.875s8.25-.84 8.25-1.875v-4.5m-16.5 4.5v4.5c0 1.036 3.694 1.875 8.25 1.875s8.25-.84 8.25-1.875v-4.5" /></svg>
                    </div>
                    <p class="pr-10 text-[10px] font-extrabold uppercase tracking-[0.14em] text-sky-800/70 dark:text-sky-300/70" x-text="selectedMonth ? 'Moldes del mes' : 'Moldes elaborados'"></p>
                    <p class="mt-2 text-2xl font-extrabold text-sky-950 dark:text-sky-50" x-text="format(totalUnits)"></p>
                    <p class="mt-2 text-xs text-sky-800/65 dark:text-sky-300/60"><span x-text="selectedMonth ? `${totalDays} día(s) con producción` : `${activeMonths.length} meses con actividad`"></span></p>
                </article>

                <article class="relative overflow-hidden rounded-2xl border border-violet-200/80 bg-violet-50/70 p-4 dark:border-violet-500/20 dark:bg-violet-500/[.07]">
                    <div class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl bg-violet-600/10 text-violet-700 dark:bg-violet-300/10 dark:text-violet-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Zm6.75-4.5c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625Zm6.75-4.5C16.5 3.504 17.004 3 17.625 3h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                    </div>
                    <p class="pr-10 text-[10px] font-extrabold uppercase tracking-[0.14em] text-violet-800/70 dark:text-violet-300/70">Promedio por jornada</p>
                    <p class="mt-2 text-2xl font-extrabold text-violet-950 dark:text-violet-50"><span x-text="format(averageDailyWeight, 2)"></span> <small class="text-sm font-bold opacity-60">kg</small></p>
                    <p class="mt-2 text-xs text-violet-800/65 dark:text-violet-300/60"><span x-text="totalDays"></span> día(s) productivos analizados</p>
                </article>

                <article class="relative overflow-hidden rounded-2xl border border-amber-200/80 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-500/[.07]">
                    <div class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-600/10 text-amber-700 dark:bg-amber-300/10 dark:text-amber-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    </div>
                    <p class="pr-10 text-[10px] font-extrabold uppercase tracking-[0.14em] text-amber-800/70 dark:text-amber-300/70" x-text="selectedMonth ? 'Mes seleccionado' : 'Mejor mes del periodo'"></p>
                    <template x-if="bestMonth && Number(bestMonth.weight) > 0">
                        <div>
                            <p class="mt-2 truncate text-xl font-extrabold text-amber-950 dark:text-amber-50" x-text="bestMonth.fullLabel"></p>
                            <p class="mt-2 text-xs font-semibold text-amber-800/70 dark:text-amber-300/65"><span x-text="format(bestMonth.weight, 2)"></span> kg · <span x-text="format(bestMonth.units)"></span> moldes</p>
                        </div>
                    </template>
                    <template x-if="!bestMonth || Number(bestMonth.weight) === 0">
                        <p class="mt-2 text-lg font-bold text-amber-900/60 dark:text-amber-200/60">Sin producción</p>
                    </template>
                </article>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                <article class="rounded-2xl border border-emerald-200/90 bg-emerald-50/40 p-4 shadow-sm xl:col-span-8 dark:border-emerald-500/20 dark:bg-emerald-500/[.055] sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.12)]"></span>
                                <h3 class="font-bold text-zinc-900 dark:text-white">Evolución mensual</h3>
                            </div>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                <span x-show="!activeMonth">Pasa sobre un punto para inspeccionarlo.</span>
                                <span x-show="activeMonth"><strong x-text="activeMonth?.fullLabel"></strong>: <span x-text="metricText(value(activeMonth))"></span></span>
                            </p>
                        </div>
                        <div class="inline-flex w-fit rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-950">
                            <button type="button" @click="metric = 'weight'; activePoint = null"
                                    :class="metric === 'weight' ? 'bg-emerald-600 text-white dark:bg-emerald-400 dark:text-emerald-950' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                                    class="rounded-md px-3 py-1.5 text-[11px] font-bold transition">Kilogramos</button>
                            <button type="button" @click="metric = 'units'; activePoint = null"
                                    :class="metric === 'units' ? 'bg-sky-600 text-white dark:bg-sky-400 dark:text-sky-950' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                                    class="rounded-md px-3 py-1.5 text-[11px] font-bold transition">Moldes</button>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200/80 bg-white px-2 pt-2 dark:border-zinc-800 dark:bg-zinc-950/70">
                        <svg class="h-56 w-full" viewBox="0 0 1000 230" preserveAspectRatio="none" role="img" aria-label="Gráfico de evolución mensual de queso">
                            <defs>
                                <linearGradient id="quesoTrendArea" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.28" />
                                    <stop offset="100%" stop-color="#10b981" stop-opacity="0.01" />
                                </linearGradient>
                                <linearGradient id="quesoTrendAreaUnits" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#0ea5e9" stop-opacity="0.28" />
                                    <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0.01" />
                                </linearGradient>
                            </defs>
                            <g class="stroke-zinc-200 dark:stroke-zinc-800" stroke-width="1">
                                <line x1="25" x2="975" y1="35" y2="35" />
                                <line x1="25" x2="975" y1="93" y2="93" />
                                <line x1="25" x2="975" y1="151" y2="151" />
                                <line x1="25" x2="975" y1="210" y2="210" />
                            </g>
                            <polygon :points="areaPoints" :fill="metric === 'weight' ? 'url(#quesoTrendArea)' : 'url(#quesoTrendAreaUnits)'" />
                            <polyline :points="trendPoints" fill="none" :stroke="metric === 'weight' ? '#10b981' : '#0ea5e9'" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                            <template x-for="(month, index) in visibleMonths" :key="month.period">
                                <circle :cx="pointX(index)" :cy="pointY(typeof month !== 'undefined' ? month : null)" r="7"
                                        :fill="activePoint === index || selectedPeriod === (typeof month !== 'undefined' ? month.period : '') ? (metric === 'weight' ? '#059669' : '#0284c7') : '#ffffff'"
                                        :stroke="metric === 'weight' ? '#10b981' : '#0ea5e9'" stroke-width="4" vector-effect="non-scaling-stroke"
                                        class="cursor-crosshair transition" tabindex="0"
                                        @mouseenter="activePoint = index" @mouseleave="activePoint = null"
                                        @focus="activePoint = index" @blur="activePoint = null">
                                    <title x-text="typeof month !== 'undefined' ? `${month.fullLabel}: ${metricText(value(month))}` : ''"></title>
                                </circle>
                            </template>
                        </svg>
                        <div class="grid pb-2" :style="`grid-template-columns: repeat(${visibleMonths.length}, minmax(0, 1fr))`">
                            <template x-for="(month, index) in visibleMonths" :key="`label-${month.period}`">
                                <span class="truncate text-center text-[9px] font-semibold text-zinc-400" x-text="showMonthLabel(index) ? month.label : ''"></span>
                            </template>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="text-zinc-500 dark:text-zinc-400">Mes de referencia: <strong class="text-zinc-800 dark:text-zinc-200" x-text="currentMonth?.fullLabel || 'Sin datos'"></strong></span>
                        <span :class="monthlyChange !== null && monthlyChange > 0 ? 'text-emerald-700 dark:text-emerald-300' : (monthlyChange !== null && monthlyChange < 0 ? 'text-rose-700 dark:text-rose-300' : 'text-zinc-500')"
                              class="rounded-full bg-zinc-100 px-2.5 py-1 font-bold dark:bg-zinc-800" x-text="changeText()"></span>
                    </div>
                </article>

                <article class="rounded-2xl border border-sky-200/90 bg-sky-50/45 p-4 shadow-sm xl:col-span-4 dark:border-sky-500/20 dark:bg-sky-500/[.055] sm:p-5">
                    <h3 class="font-bold text-zinc-900 dark:text-white">Mezcla de presentaciones</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Cantidad de moldes por tamaño en el periodo.</p>
                    <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row xl:flex-col 2xl:flex-row">
                        <div class="relative h-40 w-40 shrink-0 rounded-full shadow-inner" :style="`background: ${donutBackground}`">
                            <div class="absolute inset-[18%] flex flex-col items-center justify-center rounded-full border border-zinc-200 bg-white text-center dark:border-zinc-700 dark:bg-zinc-900">
                                <strong class="text-2xl text-zinc-900 dark:text-white" x-text="format(presentationTotal)"></strong>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">moldes</span>
                            </div>
                        </div>
                        <div class="w-full min-w-0 space-y-2.5">
                            <template x-for="item in presentationData" :key="item.weight">
                                <div>
                                    <div class="flex items-center justify-between gap-3 text-xs">
                                        <span class="flex min-w-0 items-center gap-2 font-semibold text-zinc-700 dark:text-zinc-300"><i class="h-2.5 w-2.5 shrink-0 rounded-full" :style="`background:${item.color}`"></i><span class="truncate" x-text="item.label"></span></span>
                                        <span class="shrink-0 font-bold text-zinc-900 dark:text-white"><span x-text="format(item.quantity)"></span> · <span x-text="`${format(item.percentage, 1)}%`"></span></span>
                                    </div>
                                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800"><div class="h-full rounded-full" :style="`width:${item.percentage}%; background:${item.color}`"></div></div>
                                </div>
                            </template>
                            <p x-show="presentationData.length === 0" class="rounded-xl border border-dashed border-zinc-300 p-4 text-center text-xs text-zinc-500 dark:border-zinc-700">Sin desglose de presentaciones en este periodo.</p>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-amber-200/90 bg-amber-50/45 p-4 shadow-sm xl:col-span-7 dark:border-amber-500/20 dark:bg-amber-500/[.055] sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-zinc-900 dark:text-white">Comparación anual</h3>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Hasta seis años de historia productiva.</p>
                        </div>
                        <div class="flex rounded-lg bg-zinc-200/70 p-1 dark:bg-zinc-800">
                            <button type="button" @click="annualMetric = 'weight'" :class="annualMetric === 'weight' ? 'bg-white text-emerald-700 shadow-sm dark:bg-zinc-700 dark:text-emerald-300' : 'text-zinc-500'" class="rounded-md px-2.5 py-1 text-[10px] font-bold">kg</button>
                            <button type="button" @click="annualMetric = 'units'" :class="annualMetric === 'units' ? 'bg-white text-sky-700 shadow-sm dark:bg-zinc-700 dark:text-sky-300' : 'text-zinc-500'" class="rounded-md px-2.5 py-1 text-[10px] font-bold">moldes</button>
                        </div>
                    </div>
                    <div class="mt-5 space-y-3">
                        <template x-for="year in annualRows" :key="year.year">
                            <div class="grid grid-cols-[3.5rem_minmax(0,1fr)_6.5rem] items-center gap-3">
                                <span class="text-sm font-extrabold text-zinc-700 dark:text-zinc-300" x-text="year.year"></span>
                                <div class="h-7 overflow-hidden rounded-lg bg-zinc-200/80 dark:bg-zinc-800">
                                    <div class="flex h-full items-center rounded-lg px-2 transition-all duration-500"
                                         :class="annualMetric === 'weight' ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-sky-500 to-cyan-400'"
                                         :style="`width:${annualWidth(year)}`">
                                        <span class="truncate text-[9px] font-bold text-white" x-text="`${year.months} meses activos`"></span>
                                    </div>
                                </div>
                                <strong class="text-right text-xs text-zinc-800 dark:text-zinc-200" x-text="metricText(annualValue(year), annualMetric)"></strong>
                            </div>
                        </template>
                        <p x-show="annualRows.length === 0" class="py-8 text-center text-xs text-zinc-500">Aún no hay información anual.</p>
                    </div>
                </article>

                <article class="rounded-2xl border border-violet-200/90 bg-violet-50/45 p-4 shadow-sm xl:col-span-5 dark:border-violet-500/20 dark:bg-violet-500/[.055] sm:p-5">
                    <h3 class="font-bold text-zinc-900 dark:text-white">Ritmo por día de semana</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Kilos elaborados y jornadas activas según el día.</p>
                    <div class="mt-5 flex h-48 items-end justify-between gap-2 rounded-xl border border-zinc-200 bg-white px-3 pb-3 pt-6 dark:border-zinc-800 dark:bg-zinc-950/70">
                        <template x-for="day in weekdayRows" :key="day.key">
                            <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2" :title="`${day.label}: ${format(day.weight, 2)} kg en ${day.days} jornada(s)`">
                                <span class="text-[9px] font-bold text-zinc-500" x-text="day.weight > 0 ? format(day.weight, 1) : ''"></span>
                                <div class="flex h-32 w-full items-end justify-center">
                                    <div class="w-full max-w-10 rounded-t-lg bg-gradient-to-t from-violet-600 to-fuchsia-400 shadow-sm transition-all duration-500" :style="`height:${day.height}`"></div>
                                </div>
                                <span class="text-[10px] font-extrabold text-zinc-600 dark:text-zinc-400" x-text="day.label"></span>
                            </div>
                        </template>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Navigation Tab and Filter -->
    <div class="overflow-x-auto border-b border-zinc-800">
        <div class="flex min-w-max gap-1">
            <button wire:click="$set('tab', 'diario')" 
                    class="px-4 py-3 border-b-2 text-sm font-semibold transition outline-none {{ $tab === 'diario' ? 'border-emerald-600 text-emerald-700 font-bold dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}">
                📋 Reporte Diario
            </button>
            <button wire:click="$set('tab', 'semanal')" 
                    class="px-4 py-3 border-b-2 text-sm font-semibold transition outline-none {{ $tab === 'semanal' ? 'border-sky-600 text-sky-700 font-bold dark:border-sky-400 dark:text-sky-300' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}">
                📋 Resumen Semanal
            </button>
            <button wire:click="$set('tab', 'mensual')"
                    class="px-4 py-3 border-b-2 text-sm font-semibold transition outline-none {{ $tab === 'mensual' ? 'border-violet-600 text-violet-700 font-bold dark:border-violet-400 dark:text-violet-300' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}">
                Resumen Mensual
            </button>
            <button wire:click="$set('tab', 'anual')"
                    class="px-4 py-3 border-b-2 text-sm font-semibold transition outline-none {{ $tab === 'anual' ? 'border-amber-600 text-amber-700 font-bold dark:border-amber-400 dark:text-amber-300' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}">
                Resumen Anual
            </button>
        </div>
    </div>

    <x-collapsible-filters :active="$hasActiveFilters"
                           title="Filtros de producción de queso"
                           description="Aplica el periodo y la búsqueda a la vista diaria, semanal, mensual o anual."
                           id="queso-filter-content"
                           reset="resetFilters"
                           loading-target="periodo,anio,mes,fechaDesde,fechaHasta,search,tab">
        <div class="hidden" aria-hidden="true">Periodo de elaboración</div>
        <div class="grid grid-cols-1 items-end gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800/60 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm text-zinc-400 dark:text-zinc-500">&#x1F50D;</span>
                    <input type="search" wire:model.live.debounce.300ms="search"
                           placeholder="Escribe un dato para buscar..."
                           class="w-full rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-800 dark:text-zinc-300 outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20 focus:ring-2">
                </div>
            </div>
            @if($tab === 'diario')
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-500">Acceso rápido</label>
                    <x-filter-select model="periodo" :options="['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']" tone="emerald" live compact />
                </div>
            @endif
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-500">Año</label>
                <x-filter-select model="anio" :options="['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all()" tone="emerald" live compact />
            </div>
            @if($tab !== 'anual')
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-500">Mes</label>
                    <x-filter-select model="mes" :options="['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre']" tone="emerald" live :disabled="$anio === ''" compact />
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-500">Desde</label>
                    <x-date-picker model="fechaDesde" placeholder="dd/mm/aaaa" compact />
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-500">Hasta</label>
                    <x-date-picker model="fechaHasta" placeholder="dd/mm/aaaa" compact />
                </div>
            @endif
        </div>
    </x-collapsible-filters>

    <!-- TAB 1: DIARIO -->
    @if($tab === 'diario')
        <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                        <th class="p-4 whitespace-nowrap">Fecha</th>
                        <th class="p-4 whitespace-nowrap">Foto</th>
                        <th class="p-4 whitespace-nowrap">Moldes Elaborados</th>
                        <th class="p-4 whitespace-nowrap">Presentaciones</th>
                        <th class="p-4 whitespace-nowrap">Peso Total (Kg)</th>
                        <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                    @forelse($produccionesDiarias as $prod)
                        @php
                            $isRecent = $this->isRecentRecord('queso.producciones', $prod->id);
                        @endphp
                        <tr class="transition duration-500 {{ $isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/20' }}">
                            <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                                {{ $prod->fecha->format('d/m/Y') }}
                                <x-recent-record-badge :show="$isRecent" :action="$recentRecord['action'] ?? null" />
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <x-table-photo :path="$prod->foto_ruta" :frame="$prod->foto_encuadre" :alt="'Foto de producción de queso del '.$prod->fecha->format('d/m/Y')" />
                            </td>
                            <td class="p-4 font-semibold text-teal-400 whitespace-nowrap">{{ $prod->unidades }} moldes</td>
                            <td class="p-4 min-w-48">
                                @if($prod->presentaciones->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($prod->presentaciones as $presentacion)
                                            <span class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 text-[11px] font-bold text-emerald-400">
                                                {{ \App\Models\ProduccionQuesoPresentacion::pesoLabel($presentacion->peso_gramos) }} · {{ $presentacion->cantidad }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-500">Sin desglose</span>
                                @endif
                            </td>
                            <td class="p-4 font-bold text-zinc-200 whitespace-nowrap">{{ $prod->peso_total_kg }} Kg</td>
                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                <x-table-action type="view" :href="route('queso.show', $prod->id)" label="Ver registro" />
                                @if(auth()->user()->tienePermiso('queso', 'actualizar'))
                                    <x-table-action type="edit" :href="route('queso.edit', $prod->id)" />
                                @endif
                                @if(auth()->user()->tienePermiso('queso', 'eliminar'))
                                    <x-table-action type="delete" wire:click="solicitarEliminacion({{ $prod->id }})" />
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-zinc-500">
                                <div class="text-3xl">📄</div>
                                <div class="mt-2 font-bold text-sm">No se encontraron registros de producción de queso</div>
                                <div class="text-xs text-zinc-500 mt-1">Registra la primera producción haciendo clic en "Registrar Elaboración".</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="agro-table-footer">
            <div class="agro-table-size">
                <span>Mostrar</span>
                <x-filter-select model="perPage" :options="['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']" tone="amber" live compact />
            </div>
            <div class="min-w-0">
                {{ $produccionesDiarias->links('components.pagination') }}
            </div>
        </div>
    @endif

    <!-- TAB 2: SEMANAL -->
    @if($tab === 'semanal')
        <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                        <th class="p-4 whitespace-nowrap">Semana</th>
                        <th class="p-4 whitespace-nowrap">Periodo</th>
                        <th class="p-4 whitespace-nowrap">Días de Producción</th>
                        <th class="p-4 whitespace-nowrap">Moldes Elaborados</th>
                        <th class="p-4 whitespace-nowrap">Peso Total (Total)</th>
                        <th class="p-4 whitespace-nowrap">Promedio Diario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                    @forelse($produccionesSemanales as $sem)
                        <tr class="hover:bg-zinc-800/20 transition duration-200">
                            <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">Semana {{ substr($sem->semana, 4) }} · {{ substr($sem->semana, 0, 4) }}</td>
                            <td class="p-4 text-zinc-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($sem->inicio_semana)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($sem->fin_semana)->format('d/m/Y') }}
                            </td>
                            <td class="p-4 text-zinc-500 whitespace-nowrap">{{ $sem->dias_producidos }} días</td>
                            <td class="p-4 font-bold text-teal-400 whitespace-nowrap">{{ $sem->total_unidades }} moldes</td>
                            <td class="p-4 font-extrabold text-zinc-200 whitespace-nowrap">{{ $sem->total_peso }} Kg</td>
                            <td class="p-4 whitespace-nowrap">
                                {{ number_format($sem->promedio_unidades, 1, ',', '.') }} moldes/día
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-zinc-500">
                                <div class="text-3xl">📄</div>
                                <div class="mt-2 font-bold text-sm">No se encontraron registros de producción</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- TAB 3: MENSUAL -->
    @if($tab === 'mensual')
        <div class="overflow-x-auto rounded-2xl border border-violet-200 bg-white shadow-md dark:border-violet-500/25 dark:bg-zinc-950/70">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-violet-800 bg-violet-700 text-xs font-bold uppercase tracking-wider text-white dark:border-violet-700 dark:bg-violet-950 dark:text-violet-100">
                        <th class="p-4 whitespace-nowrap">Mes</th>
                        <th class="p-4 whitespace-nowrap">Elaboraciones</th>
                        <th class="p-4 whitespace-nowrap">Días de Producción</th>
                        <th class="p-4 whitespace-nowrap">Moldes Elaborados</th>
                        <th class="p-4 whitespace-nowrap">Peso Total</th>
                        <th class="p-4 whitespace-nowrap">Promedio de Moldes</th>
                        <th class="p-4 whitespace-nowrap">Promedio de Peso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-violet-100 text-sm text-zinc-700 dark:divide-violet-500/15 dark:text-zinc-300">
                    @forelse($produccionesMensuales as $mesProduccion)
                        <tr class="transition odd:bg-white even:bg-violet-50/70 hover:bg-violet-100/70 dark:odd:bg-zinc-950/20 dark:even:bg-violet-500/[.055] dark:hover:bg-violet-500/10">
                            <td class="p-4 whitespace-nowrap">
                                <span class="block font-bold text-violet-700 dark:text-violet-300">{{ $mesProduccion->mes_nombre }}</span>
                                <span class="mt-0.5 block text-xs text-zinc-500">{{ $mesProduccion->anio }}</span>
                            </td>
                            <td class="p-4 whitespace-nowrap">{{ $mesProduccion->registros }} registro(s)</td>
                            <td class="p-4 whitespace-nowrap">{{ $mesProduccion->dias_producidos }} día(s)</td>
                            <td class="p-4 font-bold text-teal-400 whitespace-nowrap">{{ number_format($mesProduccion->total_unidades, 0, ',', '.') }} moldes</td>
                            <td class="p-4 font-extrabold text-zinc-100 whitespace-nowrap">{{ number_format($mesProduccion->total_peso, 2, ',', '.') }} kg</td>
                            <td class="p-4 whitespace-nowrap">{{ number_format($mesProduccion->promedio_unidades, 1, ',', '.') }} moldes/día</td>
                            <td class="p-4 whitespace-nowrap">{{ number_format($mesProduccion->promedio_peso, 2, ',', '.') }} kg/día</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-zinc-500">
                                <div class="text-sm font-bold">No hay meses con producción para los filtros seleccionados.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- TAB 4: ANUAL -->
    @if($tab === 'anual')
        <div class="overflow-x-auto rounded-2xl border border-amber-200 bg-white shadow-md dark:border-amber-500/25 dark:bg-zinc-950/70">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-amber-800 bg-amber-700 text-xs font-bold uppercase tracking-wider text-white dark:border-amber-700 dark:bg-amber-950 dark:text-amber-100">
                        <th class="p-4 whitespace-nowrap">Año</th>
                        <th class="p-4 whitespace-nowrap">Meses Activos</th>
                        <th class="p-4 whitespace-nowrap">Elaboraciones</th>
                        <th class="p-4 whitespace-nowrap">Días de Producción</th>
                        <th class="p-4 whitespace-nowrap">Moldes Elaborados</th>
                        <th class="p-4 whitespace-nowrap">Peso Total</th>
                        <th class="p-4 whitespace-nowrap">Promedio Mensual de Moldes</th>
                        <th class="p-4 whitespace-nowrap">Promedio Mensual de Peso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100 text-sm text-zinc-700 dark:divide-amber-500/15 dark:text-zinc-300">
                    @forelse($produccionesAnuales as $anioProduccion)
                        <tr class="transition odd:bg-white even:bg-amber-50/70 hover:bg-amber-100/70 dark:odd:bg-zinc-950/20 dark:even:bg-amber-500/[.055] dark:hover:bg-amber-500/10">
                            <td class="p-4 text-lg font-extrabold text-amber-700 whitespace-nowrap dark:text-amber-300">{{ $anioProduccion->anio }}</td>
                            <td class="p-4 whitespace-nowrap">{{ $anioProduccion->meses_producidos }} mes(es)</td>
                            <td class="p-4 whitespace-nowrap">{{ $anioProduccion->registros }} registro(s)</td>
                            <td class="p-4 whitespace-nowrap">{{ $anioProduccion->dias_producidos }} día(s)</td>
                            <td class="p-4 font-bold text-teal-400 whitespace-nowrap">{{ number_format($anioProduccion->total_unidades, 0, ',', '.') }} moldes</td>
                            <td class="p-4 font-extrabold text-zinc-100 whitespace-nowrap">{{ number_format($anioProduccion->total_peso, 2, ',', '.') }} kg</td>
                            <td class="p-4 whitespace-nowrap">{{ number_format($anioProduccion->promedio_unidades, 1, ',', '.') }} moldes/mes</td>
                            <td class="p-4 whitespace-nowrap">{{ number_format($anioProduccion->promedio_peso, 2, ',', '.') }} kg/mes</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-zinc-500">
                                <div class="text-sm font-bold">No hay años con producción para los filtros seleccionados.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if($showReportModal)
        @php
            $reportOptions = \App\Livewire\Queso\Index::reportSectionOptions();
            $columnOptions = \App\Livewire\Queso\Index::reportColumnOptions();
            $sectionThemes = [
                'summary' => [
                    'selected' => 'border-emerald-300 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-200 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'panel' => 'border-emerald-200 bg-emerald-50/55 dark:border-emerald-500/30 dark:bg-emerald-500/[.07]',
                    'title' => 'text-emerald-900 dark:text-emerald-100',
                    'action' => 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-300 dark:hover:text-emerald-200',
                    'field' => 'border-emerald-300 bg-white/90 text-emerald-950 dark:border-emerald-500/55 dark:bg-emerald-500/10 dark:text-emerald-50',
                    'check' => 'border-emerald-600 bg-emerald-600 dark:border-emerald-400 dark:bg-emerald-400',
                    'icon' => 'text-white dark:text-emerald-950',
                ],
                'daily' => [
                    'selected' => 'border-amber-300 bg-amber-50 text-amber-950 ring-1 ring-amber-200 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-50',
                    'panel' => 'border-amber-200 bg-amber-50/55 dark:border-amber-500/30 dark:bg-amber-500/[.07]',
                    'title' => 'text-amber-900 dark:text-amber-100',
                    'action' => 'text-amber-700 hover:text-amber-600 dark:text-amber-300 dark:hover:text-amber-200',
                    'field' => 'border-amber-300 bg-white/90 text-amber-950 dark:border-amber-500/55 dark:bg-amber-500/10 dark:text-amber-50',
                    'check' => 'border-amber-600 bg-amber-600 dark:border-amber-400 dark:bg-amber-400',
                    'icon' => 'text-white dark:text-amber-950',
                ],
                'weekly' => [
                    'selected' => 'border-sky-300 bg-sky-50 text-sky-950 ring-1 ring-sky-200 dark:border-sky-500/60 dark:bg-sky-500/10 dark:text-sky-50',
                    'panel' => 'border-sky-200 bg-sky-50/55 dark:border-sky-500/30 dark:bg-sky-500/[.07]',
                    'title' => 'text-sky-900 dark:text-sky-100',
                    'action' => 'text-sky-700 hover:text-sky-600 dark:text-sky-300 dark:hover:text-sky-200',
                    'field' => 'border-sky-300 bg-white/90 text-sky-950 dark:border-sky-500/55 dark:bg-sky-500/10 dark:text-sky-50',
                    'check' => 'border-sky-600 bg-sky-600 dark:border-sky-400 dark:bg-sky-400',
                    'icon' => 'text-white dark:text-sky-950',
                ],
                'monthly' => [
                    'selected' => 'border-violet-300 bg-violet-50 text-violet-950 ring-1 ring-violet-200 dark:border-violet-500/60 dark:bg-violet-500/10 dark:text-violet-50',
                    'panel' => 'border-violet-200 bg-violet-50/55 dark:border-violet-500/30 dark:bg-violet-500/[.07]',
                    'title' => 'text-violet-900 dark:text-violet-100',
                    'action' => 'text-violet-700 hover:text-violet-600 dark:text-violet-300 dark:hover:text-violet-200',
                    'field' => 'border-violet-300 bg-white/90 text-violet-950 dark:border-violet-500/55 dark:bg-violet-500/10 dark:text-violet-50',
                    'check' => 'border-violet-600 bg-violet-600 dark:border-violet-400 dark:bg-violet-400',
                    'icon' => 'text-white dark:text-violet-950',
                ],
                'annual' => [
                    'selected' => 'border-rose-300 bg-rose-50 text-rose-950 ring-1 ring-rose-200 dark:border-rose-500/60 dark:bg-rose-500/10 dark:text-rose-50',
                    'panel' => 'border-rose-200 bg-rose-50/55 dark:border-rose-500/30 dark:bg-rose-500/[.07]',
                    'title' => 'text-rose-900 dark:text-rose-100',
                    'action' => 'text-rose-700 hover:text-rose-600 dark:text-rose-300 dark:hover:text-rose-200',
                    'field' => 'border-rose-300 bg-white/90 text-rose-950 dark:border-rose-500/55 dark:bg-rose-500/10 dark:text-rose-50',
                    'check' => 'border-rose-600 bg-rose-600 dark:border-rose-400 dark:bg-rose-400',
                    'icon' => 'text-white dark:text-rose-950',
                ],
            ];
        @endphp
        <div x-data="{
                open: true,
                selectedSections: @js($selectedReportSections),
                reportColumns: @js($reportColumns),
                sectionOptions: @js($reportOptions),
                columnOptions: @js($columnOptions),
                
                toggleSection(key) {
                    if (this.selectedSections.includes(key)) {
                        this.selectedSections = this.selectedSections.filter(s => s !== key);
                    } else {
                        this.selectedSections.push(key);
                    }
                },
                toggleAllSections() {
                    const keys = Object.keys(this.sectionOptions);
                    if (this.selectedSections.length === keys.length) {
                        this.selectedSections = [];
                    } else {
                        this.selectedSections = [...keys];
                    }
                },
                toggleColumn(sectionKey, fieldKey) {
                    if (!this.reportColumns[sectionKey]) {
                        this.reportColumns[sectionKey] = [];
                    }
                    if (this.reportColumns[sectionKey].includes(fieldKey)) {
                        this.reportColumns[sectionKey] = this.reportColumns[sectionKey].filter(c => c !== fieldKey);
                    } else {
                        this.reportColumns[sectionKey].push(fieldKey);
                    }
                },
                toggleAllColumns(sectionKey) {
                    const fields = Object.keys(this.columnOptions[sectionKey] || {});
                    if (!this.reportColumns[sectionKey]) {
                        this.reportColumns[sectionKey] = [];
                    }
                    if (this.reportColumns[sectionKey].length === fields.length) {
                        this.reportColumns[sectionKey] = [];
                    } else {
                        this.reportColumns[sectionKey] = [...fields];
                    }
                },
                generate() {
                    $wire.downloadQuesoReport(this.selectedSections, this.reportColumns);
                },
                close() {
                    this.open = false;
                    document.body.classList.remove('overflow-hidden');
                    setTimeout(() => {
                        $wire.closeQuesoReportModal();
                    }, 50);
                }
             }"
             x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
             x-on:keydown.escape.window="close()"
             @click.self="close()"
             class="agro-dialog-overlay agro-dialog-overlay--full">
            <div role="dialog" aria-modal="true" aria-labelledby="queso-report-title"
                 class="agro-dialog agro-dialog--full h-[calc(100dvh-0.5rem)] sm:h-[calc(100dvh-1.5rem)] sm:w-[calc(100vw-1.5rem)]">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-700">
                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Exportación PDF</span>
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <span x-text="selectedSections.length"></span> de {{ count($reportOptions) }} secciones
                            </span>
                        </div>
                        <h3 id="queso-report-title" class="text-lg font-bold text-zinc-900 dark:text-white sm:text-xl">Generar reporte PDF de producción de queso</h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Selecciona contenido y campos. Se aplicarán los filtros activos en la tabla.</p>
                    </div>
                    <button type="button" @click="close()"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                            aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto xl:grid xl:grid-cols-[21rem_minmax(0,1fr)] xl:overflow-hidden">
                    <aside class="border-b border-zinc-200 bg-zinc-50/70 p-4 xl:overflow-y-auto xl:border-b-0 xl:border-r dark:border-zinc-700 dark:bg-zinc-950/25">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-300">Secciones del reporte</span>
                            <button type="button" @click="toggleAllSections()"
                                    class="text-xs font-bold text-emerald-700 transition hover:text-emerald-600 focus:outline-none focus:underline dark:text-emerald-400 dark:hover:text-emerald-300">
                                <span x-text="selectedSections.length === {{ count($reportOptions) }} ? 'Limpiar todas' : 'Seleccionar todas'"></span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-1">
                            @foreach($reportOptions as $key => $option)
                                @php
                                    $theme = $sectionThemes[$key];
                                @endphp
                                <label wire:key="queso-report-section-{{ $key }}"
                                       :class="selectedSections.includes('{{ $key }}') ? '{{ $theme['selected'] }}' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-300 dark:hover:border-zinc-600'"
                                       class="flex min-h-16 cursor-pointer items-center gap-3 rounded-xl border px-3 py-3 transition focus-within:ring-2 focus-within:ring-emerald-500/50">
                                    <input type="checkbox" :checked="selectedSections.includes('{{ $key }}')" @change="toggleSection('{{ $key }}')" class="sr-only">
                                    <span :class="selectedSections.includes('{{ $key }}') ? '{{ $theme['check'] }}' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-900'"
                                          class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 transition">
                                        <svg x-show="selectedSections.includes('{{ $key }}')" class="h-3 w-3 {{ $theme['icon'] }}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                    </span>
                                    <span class="min-w-0 leading-tight">
                                        <strong class="block text-sm">{{ $option['label'] }}</strong>
                                        <small class="mt-0.5 block text-[11px] font-normal opacity-65">{{ $option['description'] }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedReportSections') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        @error('selectedReportSections.*') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </aside>

                    <div class="p-4 sm:p-5 xl:overflow-y-auto 2xl:p-6">
                        <div x-show="selectedSections.length === 0" class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 text-center dark:border-zinc-700 dark:bg-zinc-950/30">
                            <div class="max-w-sm">
                                <strong class="block text-sm text-zinc-700 dark:text-zinc-200">Sin secciones seleccionadas</strong>
                                <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Activa una sección para elegir sus campos.</span>
                            </div>
                        </div>
                        <div class="grid content-start gap-4 xl:grid-cols-2 2xl:gap-5">
                            @foreach($reportOptions as $sectionKey => $sectionOption)
                                @php
                                    $fields = $columnOptions[$sectionKey] ?? [];
                                    $theme = $sectionThemes[$sectionKey];
                                @endphp
                                <section x-show="selectedSections.includes('{{ $sectionKey }}')"
                                         wire:key="queso-report-panel-{{ $sectionKey }}"
                                         class="rounded-2xl border p-4 {{ $theme['panel'] }}">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <strong class="block text-sm {{ $theme['title'] }}">Campos: {{ $sectionOption['label'] }}</strong>
                                            <small class="mt-0.5 block text-[11px] text-zinc-500 dark:text-zinc-400">Elige información incluida en esta sección.</small>
                                        </div>
                                        <button type="button" @click="toggleAllColumns('{{ $sectionKey }}')"
                                                class="shrink-0 text-[11px] font-bold transition focus:outline-none focus:underline {{ $theme['action'] }}">
                                            <span x-text="(reportColumns['{{ $sectionKey }}'] || []).length === {{ count($fields) }} ? 'Limpiar todos' : 'Seleccionar todos'"></span>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 2xl:grid-cols-3">
                                        @foreach($fields as $fieldKey => $fieldLabel)
                                            <label wire:key="queso-report-field-{{ $sectionKey }}-{{ $fieldKey }}"
                                                   :class="(reportColumns['{{ $sectionKey }}'] || []).includes('{{ $fieldKey }}') ? '{{ $theme['field'] }}' : 'border-zinc-200 bg-white/75 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                                   class="flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border px-2.5 py-2 text-xs font-semibold leading-tight transition focus-within:ring-2 focus-within:ring-current/30">
                                                <input type="checkbox" :checked="(reportColumns['{{ $sectionKey }}'] || []).includes('{{ $fieldKey }}')" @change="toggleColumn('{{ $sectionKey }}', '{{ $fieldKey }}')" class="sr-only">
                                                <span :class="(reportColumns['{{ $sectionKey }}'] || []).includes('{{ $fieldKey }}') ? '{{ $theme['check'] }}' : 'border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-950'"
                                                      class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition">
                                                    <svg x-show="(reportColumns['{{ $sectionKey }}'] || []).includes('{{ $fieldKey }}')" class="h-3 w-3 {{ $theme['icon'] }}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                                </span>
                                                <span>{{ $fieldLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="hidden text-xs text-zinc-500 sm:block dark:text-zinc-400">A4 horizontal · Filtros actuales · Secciones y campos configurables</p>
                    <div class="flex gap-2 sm:justify-end">
                        <button type="button" @click="close()"
                                class="h-11 flex-1 rounded-xl border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 sm:flex-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            Cancelar
                        </button>
                        <button type="button" @click="generate()" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70" wire:target="downloadQuesoReport"
                                class="h-11 flex-1 rounded-xl bg-emerald-700 px-6 text-sm font-bold text-white shadow-md shadow-emerald-700/15 transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait sm:flex-none dark:bg-emerald-500 dark:text-emerald-950 dark:hover:bg-emerald-400 dark:focus:ring-offset-zinc-900">
                            <span wire:loading.remove wire:target="downloadQuesoReport">Generar reporte</span>
                            <span wire:loading wire:target="downloadQuesoReport">Generando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL LIVE PDF PREVIEW --}}
    <x-pdf-preview-modal
        :show-export-modal="$showExportModal && $exportStep === 'preview'"
        export-step="preview"
        :pdf-preview-data="$pdfPreviewData"
        :pdf-preview-token="$pdfPreviewToken"
        :pdf-preview-filename="$pdfPreviewFilename"
        :pdf-preview-title="$pdfPreviewTitle"
        :pdf-preview-row-count="$pdfPreviewRowCount"
        :pdf-preview-page-count="$pdfPreviewPageCount"
        :pdf-include-signatures="$pdfIncludeSignatures"
        :pdf-scale="$pdfScale"
        :pdf-signature-scale="$pdfSignatureScale"
        :pdf-table-color-mode="$pdfTableColorMode"
        :pdf-table-preset="$pdfTablePreset"
        :pdf-table-radius="$pdfTableRadius"
        :has-pdf-customization="true"
        :back-action="'$set(\'showReportModal\', true); $set(\'showExportModal\', false)'"
    >
        {{-- Queso uses showReportModal for options --}}
    </x-pdf-preview-modal>
</x-recent-record-host>


