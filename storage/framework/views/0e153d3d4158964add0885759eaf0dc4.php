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

<div x-data="engordeDashboard(<?php echo \Illuminate\Support\Js::from($data)->toHtml() ?>)" class="space-y-6">
    <!-- Header with Last Update -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Panorama General de Engorde</h2>
        <div class="flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-1 text-xs font-bold text-zinc-500 shadow-inner dark:bg-zinc-800/50 dark:text-zinc-400">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Actualizado <?php echo e($data['generatedAt']); ?></span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Lotes Totales -->
        <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-indigo-500/10 blur-2xl transition-all group-hover:bg-indigo-500/20"></div>
            <div class="relative flex h-full flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100/80 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </div>
                    <span class="rounded-lg bg-indigo-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-300">Lotes</span>
                </div>
                <div class="mt-4">
                    <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white"><?php echo e(number_format($data['totalLotes'])); ?></p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span> <?php echo e($data['lotesActivos']); ?> Activos
                    </p>
                </div>
            </div>
        </article>

        <!-- Card 2: Animales Activos -->
        <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl transition-all group-hover:bg-emerald-500/20"></div>
            <div class="relative flex h-full flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100/80 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path>
                        </svg>
                    </div>
                    <span class="rounded-lg bg-emerald-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">Animales</span>
                </div>
                <div class="mt-4">
                    <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white"><?php echo e(number_format($data['animalesActivos'])); ?></p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-indigo-500"></span> En engorde activo
                    </p>
                </div>
            </div>
        </article>

        <!-- Card 3: Animales Cerrados/Historicos -->
        <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl transition-all group-hover:bg-amber-500/20"></div>
            <div class="relative flex h-full flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100/80 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <span class="rounded-lg bg-amber-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">Finalizados</span>
                </div>
                <div class="mt-4">
                    <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white"><?php echo e(number_format($data['animalesCerrados'])); ?></p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        Histórico / Terminados
                    </p>
                </div>
            </div>
        </article>

        <!-- Card 4: Tasa de Finalización -->
        <article class="group relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-gradient-to-b from-white/80 to-white/40 p-5 shadow-sm backdrop-blur-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-200/40 dark:border-zinc-700/50 dark:from-zinc-800/80 dark:to-zinc-800/40 dark:hover:shadow-black/40">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-rose-500/10 blur-2xl transition-all group-hover:bg-rose-500/20"></div>
            <div class="relative flex h-full flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100/80 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <span class="rounded-lg bg-rose-100/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-rose-800 dark:bg-rose-500/10 dark:text-rose-300">Tasa</span>
                </div>
                <div class="mt-4">
                    <p class="text-4xl font-black tracking-tight text-zinc-900 dark:text-white">
                        <?php echo e(($data['animalesCerrados'] + $data['animalesActivos']) > 0 ? round(($data['animalesCerrados'] / ($data['animalesCerrados'] + $data['animalesActivos'])) * 100, 1) : 0); ?>%
                    </p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                        Tasa global de salida
                    </p>
                </div>
            </div>
        </article>
    </div>

    <!-- Trend Chart Section -->
    <div class="relative overflow-hidden rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-6 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-black tracking-tight text-zinc-900 dark:text-white">Ingresos a Engorde</h3>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Evolución de ingresos de animales en los últimos <span x-text="range"></span> meses.</p>
            </div>

            <!-- Month & Range Controls -->
            <div class="flex items-center gap-2">
                <!-- Dropdown -->
                <div class="relative">
                    <button type="button" @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false"
                            class="flex h-12 items-center gap-3 rounded-2xl border border-zinc-200/80 bg-white/80 px-4 text-sm font-bold text-zinc-700 shadow-sm backdrop-blur-md transition-all hover:bg-zinc-50 dark:border-zinc-700/80 dark:bg-zinc-800/80 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <span x-text="selectedPeriod ? (visibleMonths.find(m => m.period === selectedPeriod)?.fullLabel || selectedPeriod) : 'Todo el historial'"></span>
                        <svg class="h-4 w-4 shrink-0 transition-transform duration-300" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <!-- Menú Desplegable -->
                    <template x-if="dropdownOpen">
                        <div x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute right-0 z-50 mt-2 max-h-72 w-56 overflow-y-auto rounded-2xl border border-zinc-200 bg-white/95 p-2 shadow-2xl backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/95">
                            <button type="button" @click="selectPeriod(''); dropdownOpen = false"
                                    :class="selectedPeriod === '' ? 'bg-indigo-50 text-indigo-900 dark:bg-indigo-500/20 dark:text-indigo-100' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'"
                                    class="mb-1 flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-left text-sm font-bold transition-colors">
                                <span>Todo el historial</span>
                                <svg x-show="selectedPeriod === ''" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </button>
                            <template x-for="month in [...visibleMonths].reverse()" :key="`opt-${month.period}`">
                                <button type="button" @click="selectPeriod(month.period); dropdownOpen = false"
                                        :class="selectedPeriod === month.period ? 'bg-indigo-50 text-indigo-900 dark:bg-indigo-500/20 dark:text-indigo-100' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'"
                                        class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-left text-sm font-bold transition-colors">
                                    <span x-text="`${month.fullLabel} (${month.count})`"></span>
                                    <svg x-show="selectedPeriod === month.period" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
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
                                    ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-500/25 ring-1 ring-indigo-400/30 dark:bg-indigo-500 dark:text-indigo-950 font-extrabold scale-[1.02]' 
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

        <!-- SVG Chart Canvas -->
        <div class="relative mt-6 w-full" wire:ignore>
            <div class="h-64 w-full">
                <svg class="h-full w-full overflow-visible" viewBox="0 0 1000 240" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="chartGradientEngorde" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#6366f1" stop-opacity="0.5" />
                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0.0" />
                        </linearGradient>
                        <filter id="glowEngorde" x="-20%" y="-20%" width="140%" height="140%">
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
                    <polygon :points="areaPoints" fill="url(#chartGradientEngorde)" />

                    <!-- Trend Line -->
                    <polyline :points="trendPoints" fill="none" stroke="#6366f1" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" filter="url(#glowEngorde)" />

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
                                  class="stroke-indigo-500 dark:stroke-indigo-400" stroke-dasharray="4 4" stroke-width="2" />

                            <circle :cx="pointX(index)" :cy="pointY(month.count)"
                                    :r="activePoint === index || selectedPeriod === month.period ? 8 : 5"
                                    class="fill-white stroke-indigo-500 transition-all duration-300 dark:fill-zinc-900 dark:stroke-indigo-400"
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
                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400"><span x-text="activeMonth?.count"></span> ingresos</span>
                </div>
                <div class="h-8 w-px bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">M</span>
                        <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300" x-text="activeMonth?.machos"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-600 dark:bg-pink-900/30 dark:text-pink-400">H</span>
                        <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300" x-text="activeMonth?.hembras"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Breakdowns Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <!-- Breakdown: Estado del Animal en Engorde -->
        <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-6 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
            <h3 class="mb-4 text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Estado de Animales (Histórico)</h3>
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['estadosAnimales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $colorClasses = [
                            0 => 'bg-emerald-500',
                            1 => 'bg-indigo-500',
                            2 => 'bg-amber-500',
                            3 => 'bg-rose-500'
                        ][$index % 4];
                    ?>
                    <div>
                        <div class="flex justify-between text-sm font-bold text-zinc-700 dark:text-zinc-300">
                            <span><?php echo e($item['label']); ?></span>
                            <span class="text-zinc-500"><?php echo e($item['count']); ?> (<?php echo e($item['percentage']); ?>%)</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                            <div class="h-full rounded-full <?php echo e($colorClasses); ?> transition-all duration-1000" style="width: <?php echo e($item['percentage']); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 font-medium">No hay datos suficientes.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Breakdown: Sexo -->
        <div class="rounded-[1.5rem] border border-zinc-200/60 bg-white/60 p-6 shadow-sm backdrop-blur-md dark:border-zinc-700/50 dark:bg-zinc-800/50">
            <h3 class="mb-4 text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Distribución por Género</h3>
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['sexoAnimales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $colorClass = strtolower($item['label']) === 'macho' ? 'bg-blue-500' : (strtolower($item['label']) === 'hembra' ? 'bg-pink-500' : 'bg-zinc-500');
                    ?>
                    <div>
                        <div class="flex justify-between text-sm font-bold text-zinc-700 dark:text-zinc-300">
                            <span><?php echo e($item['label']); ?></span>
                            <span class="text-zinc-500"><?php echo e($item['count']); ?> (<?php echo e($item['percentage']); ?>%)</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700/50">
                            <div class="h-full rounded-full <?php echo e($colorClass); ?> transition-all duration-1000" style="width: <?php echo e($item['percentage']); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 font-medium">No hay datos suficientes.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/engorde-dashboard.blade.php ENDPATH**/ ?>