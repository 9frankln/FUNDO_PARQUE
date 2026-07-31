@props(['data'])

<section wire:ignore x-data="animalDashboard(@js($data))" x-cloak
         class="overflow-hidden rounded-[2rem] border border-white/40 bg-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/60 dark:shadow-[0_8px_30px_rgb(0,0,0,0.4)]">
    
    <!-- Premium Header -->
    <div class="relative overflow-hidden border-b border-zinc-200/50 bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-transparent px-6 py-8 dark:border-zinc-800/50 dark:from-emerald-500/20 dark:via-teal-500/10 sm:px-8">
        <!-- Abstract background shapes -->
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-emerald-400/20 blur-[80px] dark:bg-emerald-500/20"></div>
        <div class="pointer-events-none absolute -bottom-32 left-10 h-64 w-64 rounded-full bg-teal-400/20 blur-[80px] dark:bg-teal-500/20"></div>
        
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest text-emerald-700 backdrop-blur-md dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Dashboard de Hato
                    </span>
                    <span class="flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Actualizado {{ $data['generatedAt'] }}
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black tracking-tight text-zinc-900 dark:text-white sm:text-3xl">Demografía y Estadísticas</h2>
                <p class="mt-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">Visualiza la evolución del hato, distribuciones biológicas y estado de la producción láctea en tiempo real.</p>
            </div>
            
            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                <!-- Dropdown Mes -->
                <div class="relative block w-full sm:w-64" 
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
                    <button x-ref="trigger" type="button" @click="dropdownOpen = !dropdownOpen"
                            class="group flex h-12 w-full items-center justify-between gap-3 rounded-2xl border border-zinc-200/80 bg-white/80 px-4 text-left text-sm font-bold text-zinc-700 shadow-sm backdrop-blur-md transition-all hover:border-emerald-400 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-500/10 dark:border-zinc-700/80 dark:bg-zinc-800/80 dark:text-zinc-200 dark:hover:border-emerald-500"
                            :class="dropdownOpen && 'border-emerald-500 ring-4 ring-emerald-500/10'">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="h-4 w-4 text-zinc-400 group-hover:text-emerald-500 dark:group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="flex-1 truncate text-zinc-700 dark:text-zinc-200" x-text="selectedPeriod === '' ? 'Ver todo el historial' : (visibleMonths.find(m => m.period === selectedPeriod)?.fullLabel || 'Ver todo el historial')">Ver todo el historial</span>
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-zinc-400 transition-transform duration-300" :class="dropdownOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <template x-teleport="body">
                        <div x-show="dropdownOpen" @click.outside="dropdownOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             :style="menuStyle"
                             class="z-50 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-zinc-200 bg-white/95 p-2 shadow-2xl backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/95"
                             style="display: none;">
                            <button type="button" @click="selectPeriod(''); dropdownOpen = false"
                                    :class="selectedPeriod === '' ? 'bg-emerald-50 text-emerald-900 dark:bg-emerald-500/20 dark:text-emerald-100' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'"
                                    class="mb-1 flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-left text-sm font-bold transition-colors">
                                <span>Todo el historial</span>
                                <svg x-show="selectedPeriod === ''" class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </button>
                            <template x-for="month in [...visibleMonths].reverse()" :key="`opt-${month.period}`">
                                <button type="button" @click="selectPeriod(month.period); dropdownOpen = false"
                                        :class="selectedPeriod === month.period ? 'bg-emerald-50 text-emerald-900 dark:bg-emerald-500/20 dark:text-emerald-100' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'"
                                        class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-left text-sm font-bold transition-colors">
                                    <span x-text="`${month.fullLabel} (${month.count})`"></span>
                                    <svg x-show="selectedPeriod === month.period" class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Range Switcher -->
                <div class="inline-flex items-center gap-1 rounded-2xl border border-zinc-200/90 bg-white/90 p-1.5 shadow-sm backdrop-blur-md dark:border-zinc-700/80 dark:bg-zinc-800/80">
                    <template x-for="item in [{v: 6, l: '6 meses'}, {v: 12, l: '12 meses'}]" :key="item.v">
                        <button type="button" @click="setRange(item.v)"
                                :class="range === item.v 
                                    ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/25 ring-1 ring-emerald-400/30 dark:bg-emerald-500 dark:text-emerald-950 font-extrabold scale-[1.02]' 
                                    : 'text-zinc-600 hover:bg-zinc-100/80 dark:text-zinc-400 dark:hover:bg-zinc-700/80 hover:text-zinc-900 dark:hover:text-white font-bold'"
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

    <!-- Body -->
    <div class="p-6 sm:p-8 space-y-8">
        
        <!-- KPI Cards Grid - Glassmorphism -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Card 1: Total -->
            <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl transition-all group-hover:bg-emerald-500/20"></div>
                <div class="relative flex flex-col h-full justify-between">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100/80 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <span class="rounded-lg bg-emerald-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">Total</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white">{{ number_format($data['total']) }}</p>
                        <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                            <span x-text="selectedPeriod ? `${periodCount} altas en este mes` : 'Inventario general activo'"></span>
                        </p>
                    </div>
                </div>
            </article>

            <!-- Card 2: Hembras -->
            <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-pink-500/10 blur-2xl transition-all group-hover:bg-pink-500/20"></div>
                <div class="relative flex flex-col h-full justify-between">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-100/80 text-pink-600 dark:bg-pink-500/20 dark:text-pink-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg> <!-- Female roughly -->
                        </div>
                        <span class="rounded-lg bg-pink-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-pink-800 dark:bg-pink-500/10 dark:text-pink-300">Hembras</span>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white">{{ number_format($data['hembras']) }}</p>
                            <span class="text-sm font-bold text-pink-600 dark:text-pink-400">{{ $data['total'] > 0 ? round(($data['hembras'] / $data['total']) * 100, 1) : 0 }}%</span>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                            <span x-text="selectedPeriod ? `${periodHembras} hembras altas` : 'Total plantel productivo'"></span>
                        </p>
                    </div>
                </div>
            </article>

            <!-- Card 3: Machos -->
            <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-500/10 blur-2xl transition-all group-hover:bg-blue-500/20"></div>
                <div class="relative flex flex-col h-full justify-between">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100/80 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4l-8 8h16l-8-8z"></path></svg>
                        </div>
                        <span class="rounded-lg bg-blue-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">Machos</span>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white">{{ number_format($data['machos']) }}</p>
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $data['total'] > 0 ? round(($data['machos'] / $data['total']) * 100, 1) : 0 }}%</span>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                            <span x-text="selectedPeriod ? `${periodMachos} machos altas` : 'Reproductores y engorde'"></span>
                        </p>
                    </div>
                </div>
            </article>

            <!-- Card 4: Ordeño -->
            <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl transition-all group-hover:bg-amber-500/20"></div>
                <div class="relative flex flex-col h-full justify-between">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100/80 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                        <span class="rounded-lg bg-amber-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">En Ordeño</span>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white">{{ number_format($data['aptos']) }}</p>
                            <span class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ $data['hembras'] > 0 ? round(($data['aptos'] / $data['hembras']) * 100, 1) : 0 }}% <span class="text-[10px] text-zinc-400">/ hembras</span></span>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Activas para producción láctea</p>
                    </div>
                </div>
            </article>
        </div>

        <!-- Trend Chart Section -->
        <div class="relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-6 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-black tracking-tight text-zinc-900 dark:text-white">Flujo de Altas y Registros</h3>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Evolución de ingresos de animales en los últimos <span x-text="range"></span> meses.</p>
                </div>
            </div>

            <!-- SVG Chart Canvas -->
            <div class="relative mt-6 w-full">
                <div class="h-64 w-full">
                    <svg class="h-full w-full overflow-visible" viewBox="0 0 1000 240" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.5" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                            </linearGradient>
                            <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                                <feGaussianBlur stdDeviation="4" result="blur" />
                                <feComposite in="SourceGraphic" in2="blur" operator="over" />
                            </filter>
                        </defs>

                        <!-- Horizontal Gridlines -->
                        <line x1="20" y1="40" x2="980" y2="40" class="stroke-zinc-200 dark:stroke-zinc-700" stroke-dasharray="6 6" stroke-width="1.5" />
                        <line x1="20" y1="95" x2="980" y2="95" class="stroke-zinc-200 dark:stroke-zinc-700" stroke-dasharray="6 6" stroke-width="1.5" />
                        <line x1="20" y1="150" x2="980" y2="150" class="stroke-zinc-200 dark:stroke-zinc-700" stroke-dasharray="6 6" stroke-width="1.5" />
                        <line x1="20" y1="205" x2="980" y2="205" class="stroke-zinc-300 dark:stroke-zinc-600" stroke-width="2" />

                        <!-- Filled Area -->
                        <polygon :points="areaPoints" fill="url(#chartGradient)" />

                        <!-- Trend Line -->
                        <polyline :points="trendPoints" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)" />

                        <!-- Data Points -->
                        <template x-for="(month, index) in visibleMonths" :key="`pt-${month.period}`">
                            <g class="cursor-pointer transition-all duration-300"
                               @mouseenter="activePoint = index"
                               @mouseleave="activePoint = null"
                               @click="selectPeriod(selectedPeriod === month.period ? '' : month.period)">
                                
                                <!-- Highlight Area -->
                                <rect x-show="activePoint === index || selectedPeriod === month.period"
                                      :x="pointX(index) - 15" y="10" width="30" height="200"
                                      class="fill-zinc-100/50 dark:fill-zinc-700/30 rx-4" rx="4" />
                                
                                <line x-show="activePoint === index || selectedPeriod === month.period"
                                      :x1="pointX(index)" y1="20" :x2="pointX(index)" y2="205"
                                      class="stroke-emerald-500 dark:stroke-emerald-400" stroke-dasharray="4 4" stroke-width="2" />

                                <circle :cx="pointX(index)" :cy="pointY(month.count)"
                                        :r="activePoint === index || selectedPeriod === month.period ? 8 : 5"
                                        class="fill-white stroke-emerald-500 transition-all duration-300 dark:fill-zinc-900 dark:stroke-emerald-400"
                                        :stroke-width="activePoint === index || selectedPeriod === month.period ? 4 : 3" />
                            </g>
                        </template>
                    </svg>
                </div>

                <!-- Month Axis Labels -->
                <div class="mt-4 flex justify-between px-1 text-xs font-bold text-zinc-500 dark:text-zinc-400 pb-2">
                    <template x-for="(month, index) in visibleMonths" :key="`lbl-${month.period}`">
                        <button type="button" @click="selectPeriod(selectedPeriod === month.period ? '' : month.period)"
                                x-show="showMonthLabel(index)"
                                :class="selectedPeriod === month.period ? 'text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-700 px-3 py-1 rounded-full' : 'hover:text-zinc-800 dark:hover:text-zinc-200'"
                                class="transition-all transform">
                            <span x-text="month.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Active Point Context -->
            <div x-show="activeMonth" x-transition.opacity class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-4 rounded-full border border-emerald-500/30 bg-emerald-50/90 px-6 py-2.5 shadow-xl backdrop-blur-md dark:border-emerald-400/20 dark:bg-zinc-800/90">
                <span class="text-sm font-black text-zinc-900 dark:text-white" x-text="activeMonth?.fullLabel"></span>
                <div class="h-4 w-px bg-zinc-300 dark:bg-zinc-600"></div>
                <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400"><strong class="text-base" x-text="activeMonth?.count"></strong> altas</span>
                <div class="flex items-center gap-3 text-xs font-bold">
                    <span class="text-pink-600 dark:text-pink-400" x-text="`${activeMonth?.hembras || 0}H`"></span>
                    <span class="text-blue-600 dark:text-blue-400" x-text="`${activeMonth?.machos || 0}M`"></span>
                </div>
            </div>
        </div>

        <!-- Breakdown Section -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            
            <!-- Breakdown 1: Especies -->
            <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-5 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
                <div class="mb-5 flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Por Especie</h4>
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ count($data['especies']) }}</span>
                </div>
                <div class="space-y-4">
                    @forelse($data['especies'] as $esp)
                        <div class="group">
                            <div class="mb-1.5 flex justify-between text-sm">
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $esp['label'] }}</span>
                                <span class="font-bold text-zinc-500">{{ $esp['count'] }} <span class="text-xs font-medium opacity-70">({{ $esp['percentage'] }}%)</span></span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                                <div class="h-full rounded-full bg-emerald-500 transition-all duration-700 ease-out group-hover:bg-emerald-400" style="width: {{ $esp['percentage'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm font-medium text-zinc-500">Sin datos registrados.</p>
                    @endforelse
                </div>
            </div>

            <!-- Breakdown 2: Estado Reproductivo -->
            <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-5 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
                <div class="mb-5 flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Reproductivo</h4>
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ count($data['estados']) }}</span>
                </div>
                <div class="space-y-4">
                    @forelse($data['estados'] as $est)
                        <div class="group">
                            <div class="mb-1.5 flex justify-between text-sm">
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $est['label'] }}</span>
                                <span class="font-bold text-zinc-500">{{ $est['count'] }} <span class="text-xs font-medium opacity-70">({{ $est['percentage'] }}%)</span></span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                                <div class="h-full rounded-full bg-pink-500 transition-all duration-700 ease-out group-hover:bg-pink-400" style="width: {{ $est['percentage'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm font-medium text-zinc-500">Sin datos registrados.</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Breakdown 3: Estado Productivo -->
            <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-5 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
                <div class="mb-5 flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Productivo</h4>
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ count($data['productivo']) }}</span>
                </div>
                <div class="space-y-4">
                    @forelse($data['productivo'] as $prod)
                        <div class="group">
                            <div class="mb-1.5 flex justify-between text-sm">
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $prod['label'] }}</span>
                                <span class="font-bold text-zinc-500">{{ $prod['count'] }} <span class="text-xs font-medium opacity-70">({{ $prod['percentage'] }}%)</span></span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                                <div class="h-full rounded-full bg-amber-500 transition-all duration-700 ease-out group-hover:bg-amber-400" style="width: {{ $prod['percentage'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm font-medium text-zinc-500">Sin datos registrados.</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Breakdown 4: Origen / Tipo de Alta -->
            <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-5 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
                <div class="mb-5 flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Orígenes</h4>
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ count($data['altas']) }}</span>
                </div>
                <div class="space-y-4">
                    @forelse($data['altas'] as $alt)
                        <div class="group">
                            <div class="mb-1.5 flex justify-between text-sm">
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $alt['label'] }}</span>
                                <span class="font-bold text-zinc-500">{{ $alt['count'] }} <span class="text-xs font-medium opacity-70">({{ $alt['percentage'] }}%)</span></span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                                <div class="h-full rounded-full bg-blue-500 transition-all duration-700 ease-out group-hover:bg-blue-400" style="width: {{ $alt['percentage'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm font-medium text-zinc-500">Sin datos registrados.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</section>
