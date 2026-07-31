@props(['data'])

<div x-data="monitoreoDashboard(@js($data))" class="space-y-6">
    <!-- Header with Last Update -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Panel de Monitoreo</h2>
        <div class="flex items-center gap-2 rounded-full border border-rose-200/50 bg-rose-50 px-3 py-1 text-[10px] font-bold tracking-wider text-rose-600 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-400">
            <span class="flex h-2 w-2 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]"></span>
            Actualizado hoy a las {{ $data['generatedAt'] }}
        </div>
    </div>

    <!-- KPI Cards - Glassmorphism -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Animales Enfermos -->
        <div class="group relative overflow-hidden rounded-[2rem] border border-zinc-200/50 bg-white/40 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:shadow-rose-500/10 dark:border-zinc-700/50 dark:bg-zinc-800/40">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-rose-400/10 blur-2xl transition-all duration-500 group-hover:bg-rose-400/20"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Animales Enfermos</p>
                    <p class="mt-1 text-3xl font-black text-rose-600 dark:text-rose-400">{{ $data['animalesEnfermos'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100/50 text-xl shadow-inner dark:bg-rose-900/30">
                    🏥
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-bold text-zinc-600 dark:text-zinc-400">
                <span class="text-rose-500">En tratamiento actual</span>
            </div>
        </div>

        <!-- Alertas Activas -->
        <div class="group relative overflow-hidden rounded-[2rem] border border-zinc-200/50 bg-white/40 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:shadow-amber-500/10 dark:border-zinc-700/50 dark:bg-zinc-800/40">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-amber-400/10 blur-2xl transition-all duration-500 group-hover:bg-amber-400/20"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Alertas Activas</p>
                    <p class="mt-1 text-3xl font-black text-amber-500 dark:text-amber-400">{{ $data['alertasActivas'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100/50 text-xl shadow-inner dark:bg-amber-900/30">
                    ⚠️
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-bold text-zinc-600 dark:text-zinc-400">
                <span class="text-amber-500">Pendientes de leer</span>
            </div>
        </div>

        <!-- Partos del Mes -->
        <div class="group relative overflow-hidden rounded-[2rem] border border-zinc-200/50 bg-white/40 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:shadow-emerald-500/10 dark:border-zinc-700/50 dark:bg-zinc-800/40">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-emerald-400/10 blur-2xl transition-all duration-500 group-hover:bg-emerald-400/20"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Partos del Mes</p>
                    <p class="mt-1 text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $data['partosMes'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100/50 text-xl shadow-inner dark:bg-emerald-900/30">
                    🍼
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-bold text-zinc-600 dark:text-zinc-400">
                <span class="text-emerald-500">Nacimientos recientes</span>
            </div>
        </div>
        
        <!-- Casos Históricos -->
        <div class="group relative overflow-hidden rounded-[2rem] border border-zinc-200/50 bg-white/40 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:shadow-rose-500/10 dark:border-zinc-700/50 dark:bg-zinc-800/40">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-rose-400/10 blur-2xl transition-all duration-500 group-hover:bg-rose-400/20"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Histórico Clínico</p>
                    <p class="mt-1 text-3xl font-black text-zinc-900 dark:text-white">{{ $data['totalSanidad'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100/50 text-xl shadow-inner dark:bg-rose-900/30">
                    📋
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-bold text-zinc-600 dark:text-zinc-400">
                <span class="text-zinc-500">Atenciones totales</span>
            </div>
        </div>
    </div>

    <!-- Trend Chart Section -->
    <div class="relative overflow-hidden rounded-[2.5rem] border border-zinc-200/60 bg-white/60 p-8 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black tracking-tight text-zinc-900 dark:text-white">Incidencias de Sanidad</h3>
                <p class="mt-1 text-sm font-semibold text-zinc-500">Casos registrados agrupados por mes</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Data Highlights -->
                <div class="hidden items-center gap-6 rounded-2xl bg-zinc-100/50 px-4 py-2 dark:bg-zinc-900/50 sm:flex">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold uppercase text-zinc-500">Total</span>
                        <span class="text-sm font-black text-rose-600 dark:text-rose-400"><span x-text="periodCount"></span> casos</span>
                    </div>
                </div>

                <!-- Range Switcher -->
                <div class="inline-flex items-center gap-1 rounded-2xl border border-zinc-200/90 bg-white/90 p-1.5 shadow-sm backdrop-blur-md dark:border-zinc-700/80 dark:bg-zinc-800/80">
                    <template x-for="item in [{v: 6, l: '6 meses'}, {v: 12, l: '12 meses'}]" :key="item.v">
                        <button type="button" @click="setRange(item.v)" 
                                :class="range === item.v 
                                    ? 'bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow-md shadow-rose-500/25 ring-1 ring-rose-400/30 dark:bg-rose-500 dark:text-rose-50 font-extrabold scale-[1.02]' 
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

        <!-- Chart Container -->
        <div class="relative mt-6 w-full">
            <div class="h-64 w-full">
                <svg class="h-full w-full overflow-visible" viewBox="0 0 1000 240" preserveAspectRatio="none">
                    <defs>
                        <!-- Glow effect -->
                        <filter id="glowMonitoreo" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="4" result="blur" />
                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                        </filter>
                        
                        <!-- Gradient Fill -->
                        <linearGradient id="chartGradientMonitoreo" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.25" />
                            <stop offset="100%" stop-color="#f43f5e" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>

                    <!-- Grid Lines -->
                    <line x1="20" y1="40" x2="980" y2="40" class="stroke-zinc-200/50 dark:stroke-zinc-700/50" stroke-width="1" stroke-dasharray="4 4" />
                    <line x1="20" y1="95" x2="980" y2="95" class="stroke-zinc-200/50 dark:stroke-zinc-700/50" stroke-width="1" stroke-dasharray="4 4" />
                    <line x1="20" y1="150" x2="980" y2="150" class="stroke-zinc-200/50 dark:stroke-zinc-700/50" stroke-width="1" stroke-dasharray="4 4" />
                    <line x1="20" y1="205" x2="980" y2="205" class="stroke-zinc-300 dark:stroke-zinc-600" stroke-width="2" />

                    <!-- Filled Area -->
                    <polygon :points="areaPoints" fill="url(#chartGradientMonitoreo)" />

                    <!-- Trend Line -->
                    <polyline :points="trendPoints" fill="none" stroke="#f43f5e" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" filter="url(#glowMonitoreo)" />

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
                                  class="stroke-rose-500 dark:stroke-rose-400" stroke-dasharray="4 4" stroke-width="2" />

                            <circle :cx="pointX(index)" :cy="pointY(month.count)"
                                    :r="activePoint === index || selectedPeriod === month.period ? 8 : 5"
                                    class="fill-white stroke-rose-500 transition-all duration-300 dark:fill-zinc-900 dark:stroke-rose-400"
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

            <!-- Active Point Context -->
            <div x-show="activeMonth" x-transition.opacity class="absolute top-4 left-1/2 -translate-x-1/2 flex items-center gap-6 rounded-2xl border border-zinc-200/80 bg-white/95 px-6 py-3 shadow-2xl backdrop-blur-xl dark:border-zinc-700/80 dark:bg-zinc-800/95 pointer-events-none">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400" x-text="activeMonth?.fullLabel"></span>
                    <span class="text-xl font-black text-rose-600 dark:text-rose-400"><span x-text="activeMonth?.count ? Number(activeMonth.count) : 0"></span> casos</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Breakdowns Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <!-- Breakdown: Clasificaciones de Sanidad -->
        <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-6 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
            <h3 class="mb-4 text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Clasificación de Sanidad</h3>
            <div class="space-y-4">
                @forelse($data['clasificaciones'] as $index => $item)
                    @php
                        $colorClasses = [
                            0 => 'bg-rose-500',
                            1 => 'bg-amber-500',
                            2 => 'bg-emerald-500',
                            3 => 'bg-indigo-500',
                        ][$index % 4];
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm font-bold text-zinc-700 dark:text-zinc-300">
                            <span>{{ $item['label'] }}</span>
                            <span class="text-zinc-500">{{ $item['count'] }} ({{ $item['percentage'] }}%)</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                            <div class="h-full rounded-full {{ $colorClasses }} transition-all duration-1000" style="width: {{ $item['percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 font-medium">No hay datos suficientes.</p>
                @endforelse
            </div>
        </div>

        <!-- Breakdown: Tipos de Partos -->
        <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-6 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
            <h3 class="mb-4 text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Tipos de Partos Histórico</h3>
            <div class="space-y-4">
                @forelse($data['partos'] as $index => $item)
                    @php
                        $colorClasses = [
                            0 => 'bg-emerald-500',
                            1 => 'bg-amber-500',
                            2 => 'bg-rose-500',
                        ][$index % 3];
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm font-bold text-zinc-700 dark:text-zinc-300">
                            <span>{{ $item['label'] }}</span>
                            <span class="text-zinc-500">{{ $item['count'] }} ({{ $item['percentage'] }}%)</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                            <div class="h-full rounded-full {{ $colorClasses }} transition-all duration-1000" style="width: {{ $item['percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 font-medium">No hay datos suficientes.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
