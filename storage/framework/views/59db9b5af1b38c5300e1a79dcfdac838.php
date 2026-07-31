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

<div
    wire:key="global-dashboard-<?php echo e($data['refreshKey']); ?>"
    wire:poll.300s="loadStats"
    x-data="globalDashboard(<?php echo \Illuminate\Support\Js::from($data)->toHtml() ?>)"
    class="space-y-6"
>
    <!-- Hero Banner -->
    <section class="agro-dashboard-hero relative overflow-hidden rounded-[2rem] border border-emerald-200/80 bg-gradient-to-br from-emerald-50 via-slate-50 to-teal-50/60 text-zinc-900 shadow-xl shadow-emerald-950/5 dark:border-emerald-800/60 dark:bg-gradient-to-br dark:from-zinc-950 dark:via-emerald-950/90 dark:to-zinc-950 dark:text-white dark:shadow-2xl dark:shadow-black/50">
        <div class="pointer-events-none absolute -right-24 -top-32 h-80 w-80 rounded-full bg-emerald-400/15 blur-3xl dark:bg-emerald-500/10"></div>
        <div class="pointer-events-none absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-teal-400/15 blur-3xl dark:bg-teal-500/10"></div>

        <div class="relative grid gap-8 p-6 sm:p-8 xl:grid-cols-[1.45fr_.8fr] xl:p-10">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-600/20 bg-emerald-600/10 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[.2em] text-emerald-800 dark:border-emerald-400/30 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                        Centro de operaciones
                    </span>
                    <span class="agro-dashboard-hero__date text-xs font-semibold text-zinc-500 dark:text-zinc-400"><?php echo e($data['welcome']['date']); ?></span>
                </div>

                <h1 class="mt-5 text-3xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-4xl lg:text-5xl">
                    <?php echo e($data['welcome']['greeting']); ?>, <?php echo e($data['welcome']['name']); ?>

                </h1>
                <p class="agro-dashboard-hero__message mt-3 max-w-2xl text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-300 sm:text-base">
                    <?php echo e($data['welcome']['message']); ?>

                </p>

                <div class="mt-5 flex flex-wrap items-center gap-3 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                    <span class="agro-dashboard-hero__context inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white/90 px-3 py-2 text-zinc-700 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90 dark:text-emerald-300">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M8 9h.01M12 9h.01M16 9h.01"/>
                        </svg>
                        <?php echo e($data['welcome']['fundo']); ?>

                    </span>
                    <span class="agro-dashboard-hero__context inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white/90 px-3 py-2 text-zinc-700 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90 dark:text-zinc-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.15)] dark:bg-emerald-400"></span>
                        Datos actualizados a las <?php echo e($data['generatedAt']); ?>

                    </span>
                </div>

                <div class="mt-7 flex flex-wrap gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['createPermissions']['animal'] ?? false): ?>
                        <a href="<?php echo e(route('animal.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 dark:bg-emerald-500 dark:text-slate-950 dark:hover:bg-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                            Nuevo animal
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['createPermissions']['leche'] ?? false): ?>
                        <a href="<?php echo e(route('leche.create')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300/80 bg-white px-4 py-2.5 text-xs font-extrabold text-zinc-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-zinc-50 dark:border-emerald-500/40 dark:bg-zinc-900 dark:text-emerald-200 dark:hover:bg-zinc-800">
                            Registrar ordeño
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['createPermissions']['finanzas'] ?? false): ?>
                        <a href="<?php echo e(route('finanzas.movimiento.create')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300/80 bg-white px-4 py-2.5 text-xs font-extrabold text-zinc-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-zinc-50 dark:border-teal-500/40 dark:bg-zinc-900 dark:text-teal-200 dark:hover:bg-zinc-800">
                            Nuevo movimiento
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Lectura Rápida Card -->
            <div class="agro-dashboard-hero__quick self-stretch rounded-2xl border border-emerald-200/80 bg-white/90 p-5 shadow-md backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/95">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-400">Lectura rápida</p>
                        <h2 class="mt-1 text-lg font-black text-zinc-950 dark:text-white">Hoy en el fundo</h2>
                    </div>
                    <button
                        type="button"
                        wire:click="loadStats"
                        wire:loading.attr="disabled"
                        class="agro-dashboard-hero__refresh inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 dark:hover:bg-zinc-800 disabled:opacity-50"
                        aria-label="Actualizar dashboard"
                    >
                        <svg wire:loading.class="animate-spin" wire:target="loadStats" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 12a8 8 0 1 1-2.34-5.66M20 4v6h-6"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-2 xl:grid-cols-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['monitoreo'] ?? false): ?>
                        <a href="<?php echo e(route('monitoreo.index')); ?>" class="agro-dashboard-hero__metric rounded-xl border border-zinc-200/80 bg-slate-50/90 p-3 shadow-sm transition hover:bg-white dark:border-zinc-800 dark:bg-zinc-950/90 dark:hover:bg-zinc-950 xl:flex xl:items-center xl:justify-between">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Alertas pendientes</span>
                            <strong class="mt-1 block text-2xl font-black <?php echo e(($data['kpis']['alerts']['overdue'] ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-950 dark:text-white'); ?>">
                                <?php echo e($data['kpis']['alerts']['pending']); ?>

                            </strong>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['leche'] ?? false): ?>
                        <a href="<?php echo e(route('leche.index')); ?>" class="agro-dashboard-hero__metric rounded-xl border border-zinc-200/80 bg-slate-50/90 p-3 shadow-sm transition hover:bg-white dark:border-zinc-800 dark:bg-zinc-950/90 dark:hover:bg-zinc-950 xl:flex xl:items-center xl:justify-between">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ordeño Hoy</span>
                            <strong class="mt-1 block text-2xl font-black text-cyan-700 dark:text-cyan-400"><?php echo e(number_format($data['kpis']['milk']['today'], 1)); ?> <small class="text-xs">L</small></strong>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['finanzas'] ?? false): ?>
                        <a href="<?php echo e(route('finanzas.index')); ?>" class="agro-dashboard-hero__metric rounded-xl border border-zinc-200/80 bg-slate-50/90 p-3 shadow-sm transition hover:bg-white dark:border-zinc-800 dark:bg-zinc-950/90 dark:hover:bg-zinc-950 xl:flex xl:items-center xl:justify-between">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Saldo del mes</span>
                            <strong class="mt-1 block text-lg font-black <?php echo e($data['kpis']['finance']['balance'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?>">
                                S/ <?php echo e(number_format($data['kpis']['finance']['balance'], 0)); ?>

                            </strong>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Resumen Operativo -->
    <section aria-labelledby="dashboard-summary-title">
        <div class="mb-3 flex items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Resumen operativo</p>
                <h2 id="dashboard-summary-title" class="mt-1 text-xl font-black tracking-tight text-zinc-950 dark:text-white">Indicadores esenciales</h2>
            </div>
            <p class="hidden text-xs font-semibold text-zinc-500 dark:text-zinc-400 sm:block">Comparación contra el mes anterior</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['animal'] ?? false): ?>
                <a href="<?php echo e(route('animal.index')); ?>" class="group rounded-2xl border border-sky-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-sky-400/20 dark:bg-zinc-900/80">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 11c-1.5 0-2.5-1-2.5-2.5S3.5 6 5 6s2.5 1 2.5 2.5S6.5 11 5 11Zm14 0c-1.5 0-2.5-1-2.5-2.5S17.5 6 19 6s2.5 1 2.5 2.5S20.5 11 19 11ZM8 7c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3Zm8 0c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3Zm-4 15c-4 0-7-2.4-7-5.5S8 11 12 11s7 2.4 7 5.5S16 22 12 22Z"/></svg>
                        </span>
                        <span class="text-xs font-bold text-sky-700 dark:text-sky-300">+<?php echo e($data['kpis']['animals']['newThisMonth']); ?> este mes</span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-zinc-950 dark:text-white"><?php echo e($data['kpis']['animals']['total']); ?></p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400">Animales en inventario</p>
                    <p class="mt-3 text-[11px] font-semibold text-zinc-400 dark:text-zinc-500"><?php echo e($data['kpis']['animals']['inactive']); ?> dados de baja en el historial</p>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['leche'] ?? false): ?>
                <a href="<?php echo e(route('leche.index')); ?>" class="group rounded-2xl border border-cyan-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-cyan-400/20 dark:bg-zinc-900/80">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3h8l-1 4c3 2 4 5 4 8a7 7 0 0 1-14 0c0-3 1-6 4-8L8 3Z"/></svg>
                        </span>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold <?php echo e($data['kpis']['milk']['variation'] >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300'); ?>">
                            <?php echo e($data['kpis']['milk']['variation'] >= 0 ? '+' : ''); ?><?php echo e($data['kpis']['milk']['variation']); ?>%
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-zinc-950 dark:text-white"><?php echo e(number_format($data['kpis']['milk']['month'], 1)); ?> <small class="text-sm text-zinc-400">L</small></p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400">Leche producida este mes</p>
                    <p class="mt-3 text-[11px] font-semibold text-zinc-400 dark:text-zinc-500">Promedio 7 días: <?php echo e(number_format($data['kpis']['milk']['average7Days'], 1)); ?> L/día</p>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['queso'] ?? false): ?>
                <a href="<?php echo e(route('queso.index')); ?>" class="group rounded-2xl border border-amber-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-amber-400/20 dark:bg-zinc-900/80">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 9 8-5 8 5v10H4V9Zm0 0 8 4 8-4M9 11v8"/></svg>
                        </span>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold <?php echo e($data['kpis']['cheese']['variation'] >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300'); ?>">
                            <?php echo e($data['kpis']['cheese']['variation'] >= 0 ? '+' : ''); ?><?php echo e($data['kpis']['cheese']['variation']); ?>%
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-zinc-950 dark:text-white"><?php echo e(number_format($data['kpis']['cheese']['monthKg'], 1)); ?> <small class="text-sm text-zinc-400">kg</small></p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400">Queso producido este mes</p>
                    <p class="mt-3 text-[11px] font-semibold text-zinc-400 dark:text-zinc-500"><?php echo e($data['kpis']['cheese']['monthUnits']); ?> unidades registradas</p>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['finanzas'] ?? false): ?>
                <a href="<?php echo e(route('finanzas.index')); ?>" class="group rounded-2xl border border-emerald-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-emerald-400/20 dark:bg-zinc-900/80">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4V6Zm0 4h16m-4 4h1"/></svg>
                        </span>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold <?php echo e($data['kpis']['finance']['balance'] >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300'); ?>">
                            <?php echo e($data['kpis']['finance']['balance'] >= 0 ? 'Saldo positivo' : 'Saldo negativo'); ?>

                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-black <?php echo e($data['kpis']['finance']['balance'] >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'); ?>">S/ <?php echo e(number_format($data['kpis']['finance']['balance'], 0)); ?></p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400">Balance de caja del mes</p>
                    <p class="mt-3 text-[11px] font-semibold text-zinc-400 dark:text-zinc-500">Ingresos S/ <?php echo e(number_format($data['kpis']['finance']['income'], 0)); ?> · Egresos S/ <?php echo e(number_format($data['kpis']['finance']['expense'], 0)); ?></p>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['engorde'] ?? false): ?>
                <a href="<?php echo e(route('engorde.index')); ?>" class="group rounded-2xl border border-lime-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-lime-400/20 dark:bg-zinc-900/80">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-100 text-lime-700 dark:bg-lime-400/10 dark:text-lime-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 18V8l4-3 4 3 4-3 4 3v10M7 18v-4h10v4M8 9h.01M16 9h.01"/></svg>
                        </span>
                        <span class="text-xs font-bold text-lime-700 dark:text-lime-300"><?php echo e($data['kpis']['fattening']['lots']); ?> lotes</span>
                    </div>
                    <p class="mt-4 text-3xl font-black text-zinc-950 dark:text-white"><?php echo e($data['kpis']['fattening']['active']); ?></p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400">Animales en engorde activo</p>
                    <p class="mt-3 text-[11px] font-semibold text-zinc-400 dark:text-zinc-500"><?php echo e($data['kpis']['fattening']['ready']); ?> listos para venta</p>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['monitoreo'] ?? false): ?>
                <a href="<?php echo e(route('monitoreo.index')); ?>" class="group rounded-2xl border border-rose-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-rose-400/20 dark:bg-zinc-900/80">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10 3h4l7 17H3l7-17Z"/></svg>
                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['kpis']['alerts']['overdue'] > 0): ?>
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-extrabold text-rose-700 dark:bg-rose-400/10 dark:text-rose-300"><?php echo e($data['kpis']['alerts']['overdue']); ?> vencidas</span>
                        <?php else: ?>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">Al día</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <p class="mt-4 text-3xl font-black text-zinc-950 dark:text-white"><?php echo e($data['kpis']['alerts']['pending']); ?></p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400">Alertas sanitarias pendientes</p>
                    <p class="mt-3 text-[11px] font-semibold text-zinc-400 dark:text-zinc-500"><?php echo e($data['kpis']['alerts']['today']); ?> programadas para hoy</p>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    <!-- Tendencia Productiva -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($data['allowedModules']['leche'] ?? false) || ($data['allowedModules']['queso'] ?? false)): ?>
        <section class="overflow-hidden rounded-[1.75rem] border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/80">
            <div class="flex flex-col gap-4 border-b border-zinc-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-zinc-800">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-violet-600 dark:text-violet-400">Tendencia productiva</p>
                    <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Evolución de la producción</h2>
                    <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Selecciona el indicador, el rango y un mes para ver su detalle.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div class="flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['leche'] ?? false): ?>
                            <button type="button" @click="setPerformanceMetric('milk')" :class="performanceMetric === 'milk' ? 'bg-white text-cyan-700 shadow-sm dark:bg-zinc-700 dark:text-cyan-300' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">Leche</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['queso'] ?? false): ?>
                            <button type="button" @click="setPerformanceMetric('cheese')" :class="performanceMetric === 'cheese' ? 'bg-white text-amber-700 shadow-sm dark:bg-zinc-700 dark:text-amber-300' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">Queso</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        <button type="button" @click="setPerformanceRange(6)" :class="performanceRange === 6 ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">6 meses</button>
                        <button type="button" @click="setPerformanceRange(12)" :class="performanceRange === 12 ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">12 meses</button>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[1fr_230px]">
                <div>
                    <div class="relative h-64 rounded-2xl bg-zinc-50 px-3 pt-5 dark:bg-zinc-950/80">
                        <svg class="h-full w-full overflow-visible" viewBox="0 0 1000 240" preserveAspectRatio="none" role="img" aria-label="Tendencia mensual de producción">
                            <defs>
                                <linearGradient id="globalPerformanceArea" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#14b8a6" stop-opacity=".28"/>
                                    <stop offset="100%" stop-color="#14b8a6" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <line x1="30" y1="40" x2="970" y2="40" class="stroke-zinc-200 dark:stroke-zinc-800" stroke-dasharray="5 6"/>
                            <line x1="30" y1="95" x2="970" y2="95" class="stroke-zinc-200 dark:stroke-zinc-800" stroke-dasharray="5 6"/>
                            <line x1="30" y1="150" x2="970" y2="150" class="stroke-zinc-200 dark:stroke-zinc-800" stroke-dasharray="5 6"/>
                            <line x1="30" y1="205" x2="970" y2="205" class="stroke-zinc-300 dark:stroke-zinc-700"/>
                            <polygon :points="performanceAreaPoints" fill="url(#globalPerformanceArea)"/>
                            <polyline :points="performanceTrendPoints" fill="none" :stroke="performanceMetric === 'milk' ? '#06b6d4' : '#f59e0b'" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="mt-3 grid gap-1" :style="`grid-template-columns: repeat(${performanceVisible.length}, minmax(0, 1fr))`">
                        <template x-for="(month, index) in performanceVisible" :key="`${performanceMetric}-${month.period}`">
                            <button
                                type="button"
                                @mouseenter="performanceHover = index"
                                @mouseleave="performanceHover = null"
                                @click="selectPerformanceMonth(month.period)"
                                :class="performanceSelectedPeriod === month.period ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300' : 'text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                                class="truncate rounded-lg px-1 py-2 text-[10px] font-extrabold transition"
                                x-text="month.label"
                            ></button>
                        </template>
                    </div>
                </div>

                <aside class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950/80">
                    <p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-zinc-400" x-text="performanceActiveMonth ? performanceActiveMonth.label : `${performanceRange} meses`"></p>
                    <p class="mt-3 text-3xl font-black text-zinc-950 dark:text-white">
                        <span x-text="formatNumber(performanceSummary.total, 1)"></span>
                        <small class="text-sm text-zinc-400" x-text="performanceMetric === 'milk' ? 'L' : 'kg'"></small>
                    </p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400" x-text="performanceActiveMonth ? 'Producción del mes' : 'Producción acumulada'"></p>

                    <div class="mt-5 space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-zinc-500 dark:text-zinc-400">Promedio mensual</span>
                            <strong class="text-zinc-900 dark:text-white"><span x-text="formatNumber(performanceSummary.average, 1)"></span> <span x-text="performanceMetric === 'milk' ? 'L' : 'kg'"></span></strong>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-zinc-500 dark:text-zinc-400" x-text="performanceMetric === 'milk' ? 'Ordeños' : 'Unidades'"></span>
                            <strong class="text-zinc-900 dark:text-white" x-text="performanceSummary.secondary"></strong>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-zinc-500 dark:text-zinc-400">Mejor mes</span>
                            <strong class="text-zinc-900 dark:text-white" x-text="performanceSummary.bestLabel"></strong>
                        </div>
                    </div>

                    <button x-show="performanceSelectedPeriod" type="button" @click="selectPerformanceMonth('')" class="mt-5 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-[11px] font-extrabold text-zinc-600 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">Ver todo el periodo</button>
                </aside>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Control Financiero & Inventario -->
    <div class="grid gap-6 xl:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['finanzas'] ?? false): ?>
            <section class="overflow-hidden rounded-[1.75rem] border border-zinc-200/80 bg-white shadow-sm xl:col-span-2 dark:border-zinc-800 dark:bg-zinc-900/80">
                <div class="flex flex-col gap-4 border-b border-zinc-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-zinc-800">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Control financiero</p>
                        <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Ingresos frente a egresos</h2>
                    </div>
                    <div class="flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        <button type="button" @click="setFinanceRange(6)" :class="financeRange === 6 ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold">6 meses</button>
                        <button type="button" @click="setFinanceRange(12)" :class="financeRange === 12 ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold">12 meses</button>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="grid h-64 items-end gap-2 rounded-2xl bg-zinc-50 px-3 pb-3 pt-7 dark:bg-zinc-950/80" :style="`grid-template-columns: repeat(${financeVisible.length}, minmax(0, 1fr))`">
                        <template x-for="(month, index) in financeVisible" :key="month.period">
                            <button
                                type="button"
                                @mouseenter="financeHover = index"
                                @mouseleave="financeHover = null"
                                @click="selectFinanceMonth(month.period)"
                                class="group flex h-full min-w-0 flex-col justify-end"
                                :aria-label="`Ver finanzas de ${month.label}`"
                            >
                                <div class="flex flex-1 items-end justify-center gap-1.5">
                                    <span class="w-3 rounded-t-md bg-emerald-500 transition-all group-hover:bg-emerald-400 sm:w-4" :style="`height: ${financeBarPercent(month.income)}%`"></span>
                                    <span class="w-3 rounded-t-md bg-rose-400 transition-all group-hover:bg-rose-300 sm:w-4" :style="`height: ${financeBarPercent(month.expense)}%`"></span>
                                </div>
                                <span :class="financeSelectedPeriod === month.period ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-400'" class="mt-2 truncate text-[9px] font-extrabold sm:text-[10px]" x-text="month.label"></span>
                            </button>
                        </template>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/40 dark:border dark:border-emerald-800/40">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Ingresos</span>
                            <strong class="mt-1 block text-lg text-emerald-800 dark:text-emerald-200">S/ <span x-text="formatNumber(financeSummary.income, 0)"></span></strong>
                        </div>
                        <div class="rounded-xl bg-rose-50 p-3 dark:bg-rose-950/40 dark:border dark:border-rose-800/40">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300">Egresos</span>
                            <strong class="mt-1 block text-lg text-rose-800 dark:text-rose-200">S/ <span x-text="formatNumber(financeSummary.expense, 0)"></span></strong>
                        </div>
                        <div class="rounded-xl bg-zinc-100 p-3 dark:bg-zinc-950/80 dark:border dark:border-zinc-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400" x-text="financeActiveMonth ? `Balance · ${financeActiveMonth.label}` : 'Balance del periodo'"></span>
                            <strong :class="financeSummary.balance >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'" class="mt-1 block text-lg">S/ <span x-text="formatNumber(financeSummary.balance, 0)"></span></strong>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['animal'] ?? false): ?>
            <section class="rounded-[1.75rem] border border-zinc-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/80">
                <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-sky-600 dark:text-sky-400">Composición</p>
                <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Inventario por especie</h2>

                <div class="relative mx-auto mt-6 h-44 w-44">
                    <div class="h-full w-full rounded-full shadow-inner" :style="`background: ${inventoryGradient}`"></div>
                    <div class="absolute inset-9 flex flex-col items-center justify-center rounded-full bg-white text-center shadow-sm dark:bg-zinc-950 dark:border dark:border-zinc-800">
                        <strong class="text-3xl font-black text-zinc-950 dark:text-white" x-text="inventoryActive ? inventoryActive.count : inventoryTotal"></strong>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400" x-text="inventoryActive ? inventoryActive.label : 'Animales'"></span>
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <template x-for="(species, index) in species" :key="species.label">
                        <button
                            type="button"
                            @click="inventorySelected = inventorySelected === index ? null : index"
                            :class="inventorySelected === index ? 'bg-zinc-100 dark:bg-zinc-800' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/60'"
                            class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left transition"
                        >
                            <span class="flex items-center gap-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span class="h-2.5 w-2.5 rounded-full" :style="`background-color: ${inventoryColors[index % inventoryColors.length]}`"></span>
                                <span x-text="species.label"></span>
                            </span>
                            <span class="text-xs font-extrabold text-zinc-500 dark:text-zinc-400"><span x-text="species.count"></span> · <span x-text="species.percentage"></span>%</span>
                        </button>
                    </template>
                    <p x-show="species.length === 0" class="rounded-xl bg-zinc-50 p-4 text-center text-xs font-semibold text-zinc-500 dark:bg-zinc-800/50">Aún no hay animales activos.</p>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Prioridades Próximas & Áreas Disponibles -->
    <div class="grid gap-6 xl:grid-cols-[.9fr_1.35fr]">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['monitoreo'] ?? false): ?>
            <section class="rounded-[1.75rem] border border-zinc-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-rose-600 dark:text-rose-400">Atención requerida</p>
                        <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Prioridades próximas</h2>
                    </div>
                    <a href="<?php echo e(route('monitoreo.index')); ?>" class="text-[11px] font-extrabold text-emerald-700 hover:text-emerald-600 dark:text-emerald-300">Ver monitoreo →</a>
                </div>

                <div class="mt-5 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data['priorities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('monitoreo.index')); ?>" class="flex items-start gap-3 rounded-xl border border-zinc-100 p-3 transition hover:border-emerald-200 hover:bg-emerald-50/40 dark:border-zinc-800 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-950/30">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full <?php echo e($priority['status'] === 'overdue' ? 'bg-rose-500' : ($priority['status'] === 'today' ? 'bg-amber-400' : 'bg-sky-400')); ?>"></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-3">
                                    <strong class="truncate text-xs text-zinc-900 dark:text-white"><?php echo e($priority['type']); ?></strong>
                                    <small class="shrink-0 text-[10px] font-extrabold <?php echo e($priority['status'] === 'overdue' ? 'text-rose-600 dark:text-rose-300' : 'text-zinc-400'); ?>"><?php echo e($priority['date']); ?></small>
                                </span>
                                <span class="mt-1 block line-clamp-1 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400"><?php echo e($priority['message']); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($priority['animal']): ?>
                                    <span class="mt-1 block text-[10px] font-bold text-emerald-700 dark:text-emerald-300"><?php echo e($priority['animal']); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="rounded-2xl bg-emerald-50 p-5 text-center dark:bg-emerald-950/40 dark:border dark:border-emerald-800/40">
                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                            </span>
                            <p class="mt-3 text-sm font-extrabold text-emerald-900 dark:text-emerald-100">Sin alertas pendientes</p>
                            <p class="mt-1 text-xs font-semibold text-emerald-700/70 dark:text-emerald-200/60">El seguimiento sanitario está al día.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="rounded-[1.75rem] border border-zinc-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/80">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Navegación directa</p>
                <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Áreas disponibles</h2>
                <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Accede solo a los módulos habilitados para tu usuario.</p>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/30">
                    <p class="mb-2 text-[10px] font-extrabold uppercase tracking-[.16em] text-emerald-700 dark:text-emerald-300">Producción e inventario</p>
                    <div class="space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['animal'] ?? false): ?>
                            <a href="<?php echo e(route('animal.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Animal</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Inventario, fichas y bajas</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['engorde'] ?? false): ?>
                            <a href="<?php echo e(route('engorde.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-lime-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Engorde</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Lotes, pesos y evolución</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['leche'] ?? false): ?>
                            <a href="<?php echo e(route('leche.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-cyan-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Leche</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Ordeños diarios y rendimiento</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['queso'] ?? false): ?>
                            <a href="<?php echo e(route('queso.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Queso</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Producción y presentaciones</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['monitoreo'] ?? false): ?>
                            <a href="<?php echo e(route('monitoreo.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Monitoreo</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Sanidad, partos y alertas</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl border border-teal-100 bg-teal-50/50 p-4 dark:border-teal-900/40 dark:bg-teal-950/30">
                    <p class="mb-2 text-[10px] font-extrabold uppercase tracking-[.16em] text-teal-700 dark:text-teal-300">Control del fundo</p>
                    <div class="space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['allowedModules']['finanzas'] ?? false): ?>
                            <a href="<?php echo e(route('finanzas.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-teal-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Finanzas</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Ingresos, egresos y asignaciones</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('buscador', 'leer')): ?>
                            <a href="<?php echo e(route('buscador')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-violet-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Buscador general</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Encuentra registros del sistema</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('ajustes', 'leer')): ?>
                            <a href="<?php echo e(route('ajustes.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-zinc-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Ajustes</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Usuarios, permisos y respaldos</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('auditoria', 'leer')): ?>
                            <a href="<?php echo e(route('auditoria.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2.5 w-2.5 rounded-full bg-indigo-400"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Auditoría</strong><small class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">Accesos, cambios y sesiones</small></span>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/global-dashboard.blade.php ENDPATH**/ ?>