<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'active' => false,
    'title' => 'Filtros',
    'description' => null,
    'id' => 'filter-content',
    'reset' => null,
    'loadingTarget' => null,
]));

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

foreach (array_filter(([
    'active' => false,
    'title' => 'Filtros',
    'description' => null,
    'id' => 'filter-content',
    'reset' => null,
    'loadingTarget' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{ filtersOpen: <?php echo \Illuminate\Support\Js::from((bool) $active)->toHtml() ?> }"
     <?php echo e($attributes->class(['space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-colors sm:p-6 dark:border-slate-800 dark:bg-slate-900'])); ?>>
    <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo e($title); ?></h2>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"><?php echo e($description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="flex w-full flex-wrap items-center gap-2 md:w-auto md:justify-end">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadingTarget): ?>
                <span wire:loading.class.remove="hidden" wire:target="<?php echo e($loadingTarget); ?>"
                      class="hidden items-center gap-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 inline-flex">
                    <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></span>
                    Actualizando
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($active): ?>
                <span class="rounded-full border border-emerald-500/25 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Activos
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reset): ?>
                <button type="button" wire:click="<?php echo e($reset); ?>" <?php if(! $active): echo 'disabled'; endif; ?>
                        class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white">
                    Limpiar filtros
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button type="button" x-on:click="filtersOpen = ! filtersOpen"
                    x-bind:aria-expanded="filtersOpen"
                    aria-controls="<?php echo e($id); ?>"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-emerald-600 bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700 sm:flex-none dark:border-emerald-500 dark:bg-emerald-500 dark:text-white dark:hover:bg-emerald-400 dark:hover:text-emerald-950">
                <span x-text="filtersOpen ? 'Ocultar filtros' : 'Mostrar filtros'"><?php echo e($active ? 'Ocultar filtros' : 'Mostrar filtros'); ?></span>
                <svg class="h-4 w-4 transition-transform duration-200" x-bind:class="filtersOpen && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
            </button>
        </div>
    </div>

    <div id="<?php echo e($id); ?>"
         x-bind:style="filtersOpen ? 'display: block;' : 'display: none;'"
         style="<?php echo e($active ? 'display: block;' : 'display: none;'); ?>"
         class="space-y-4">
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/collapsible-filters.blade.php ENDPATH**/ ?>