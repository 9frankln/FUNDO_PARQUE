<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description',
    'sectionOptions',
    'columnOptions',
    'downloadMethod' => 'downloadReport',
    'tone' => 'emerald',
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
    'title',
    'description',
    'sectionOptions',
    'columnOptions',
    'downloadMethod' => 'downloadReport',
    'tone' => 'emerald',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isViolet = $tone === 'violet';
    $accentText = $isViolet ? 'text-violet-600 dark:text-violet-400' : 'text-emerald-600 dark:text-emerald-400';
    $accentButton = $isViolet
        ? 'bg-violet-600 hover:bg-violet-500 focus:ring-violet-400 dark:bg-violet-600 dark:text-white'
        : 'bg-emerald-600 hover:bg-emerald-500 focus:ring-emerald-400 dark:bg-emerald-400 dark:text-emerald-950';

    $sectionThemes = [
        'summary' => [
            'selected' => $isViolet 
                ? 'border-violet-300 bg-violet-50/60 text-violet-950 ring-1 ring-violet-200 dark:border-violet-500/60 dark:bg-violet-500/10 dark:text-violet-50' 
                : 'border-emerald-300 bg-emerald-50/60 text-emerald-950 ring-1 ring-emerald-200 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-50',
            'panel' => $isViolet 
                ? 'border-violet-200 bg-violet-50/30 dark:border-violet-500/30 dark:bg-violet-500/[.07]' 
                : 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-500/30 dark:bg-emerald-500/[.07]',
            'title' => $isViolet ? 'text-violet-900 dark:text-violet-100' : 'text-emerald-900 dark:text-emerald-100',
            'action' => $isViolet ? 'text-violet-700 hover:text-violet-600 dark:text-violet-300 dark:hover:text-violet-200' : 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-300 dark:hover:text-emerald-200',
            'field' => $isViolet 
                ? 'border-violet-200 bg-white/90 text-violet-950 dark:border-violet-500/40 dark:bg-violet-500/15 dark:text-violet-200' 
                : 'border-emerald-200 bg-white/90 text-emerald-950 dark:border-emerald-500/40 dark:bg-emerald-500/15 dark:text-emerald-200',
            'check' => $isViolet ? 'border-violet-600 bg-violet-600 dark:border-violet-400 dark:bg-violet-400' : 'border-emerald-600 bg-emerald-600 dark:border-emerald-400 dark:bg-emerald-400',
            'icon' => 'text-white dark:text-zinc-950',
        ],
        'records' => [
            'selected' => 'border-amber-300 bg-amber-50/60 text-amber-950 ring-1 ring-amber-200 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-50',
            'panel' => 'border-amber-200 bg-amber-50/30 dark:border-amber-500/30 dark:bg-amber-500/[.07]',
            'title' => 'text-amber-900 dark:text-amber-100',
            'action' => 'text-amber-700 hover:text-amber-600 dark:text-amber-300 dark:hover:text-amber-200',
            'field' => 'border-amber-200 bg-white/90 text-amber-950 dark:border-amber-500/40 dark:bg-amber-500/15 dark:text-amber-200',
            'check' => 'border-amber-600 bg-amber-600 dark:border-amber-400 dark:bg-amber-400',
            'icon' => 'text-white dark:text-zinc-950',
        ],
        'categories' => [
            'selected' => 'border-sky-300 bg-sky-50/60 text-sky-950 ring-1 ring-sky-200 dark:border-sky-500/60 dark:bg-sky-500/10 dark:text-sky-50',
            'panel' => 'border-sky-200 bg-sky-50/30 dark:border-sky-500/30 dark:bg-sky-500/[.07]',
            'title' => 'text-sky-900 dark:text-sky-100',
            'action' => 'text-sky-700 hover:text-sky-600 dark:text-sky-300 dark:hover:text-sky-200',
            'field' => 'border-sky-200 bg-white/90 text-sky-950 dark:border-sky-500/40 dark:bg-sky-500/15 dark:text-sky-200',
            'check' => 'border-sky-600 bg-sky-600 dark:border-sky-400 dark:bg-sky-400',
            'icon' => 'text-white dark:text-zinc-950',
        ],
        'purposes' => [
            'selected' => 'border-sky-300 bg-sky-50/60 text-sky-950 ring-1 ring-sky-200 dark:border-sky-500/60 dark:bg-sky-500/10 dark:text-sky-50',
            'panel' => 'border-sky-200 bg-sky-50/30 dark:border-sky-500/30 dark:bg-sky-500/[.07]',
            'title' => 'text-sky-900 dark:text-sky-100',
            'action' => 'text-sky-700 hover:text-sky-600 dark:text-sky-300 dark:hover:text-sky-200',
            'field' => 'border-sky-200 bg-white/90 text-sky-950 dark:border-sky-500/40 dark:bg-sky-500/15 dark:text-sky-200',
            'check' => 'border-sky-600 bg-sky-600 dark:border-sky-400 dark:bg-sky-400',
            'icon' => 'text-white dark:text-zinc-950',
        ],
    ];
?>

<div x-data="{
        sections: $wire.entangle('selectedReportSections').live,
        columns: $wire.entangle('reportColumns').live,
        init() { document.body.classList.add('overflow-hidden') },
        destroy() { document.body.classList.remove('overflow-hidden') },
    }"
     x-on:keydown.escape.window="$wire.set('showReportModal', false)"
     x-on:click.self="$wire.set('showReportModal', false)"
     class="agro-dialog-overlay agro-dialog-overlay--full">
    <div role="dialog" aria-modal="true" aria-labelledby="finance-report-title"
         class="agro-dialog agro-dialog--full h-[calc(100dvh-0.5rem)] sm:h-[calc(100dvh-1.5rem)] sm:w-[calc(100vw-1.5rem)]">
        <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-4 py-4 sm:px-6 dark:border-zinc-800">
            <div>
                <div class="mb-1 flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-[0.18em] <?php echo e($accentText); ?>">Exportación PDF</span>
                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <span x-text="sections.length"></span> de <?php echo e(count($sectionOptions)); ?> secciones
                    </span>
                </div>
                <h3 id="finance-report-title" class="text-lg font-bold text-zinc-900 dark:text-white sm:text-xl"><?php echo e($title); ?></h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"><?php echo e($description); ?></p>
            </div>
            <button type="button" wire:click="$set('showReportModal', false)" aria-label="Cerrar"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto xl:grid xl:grid-cols-[21rem_minmax(0,1fr)] xl:overflow-hidden">
            <aside class="border-b border-zinc-200 bg-zinc-50/70 p-4 xl:overflow-y-auto xl:border-b-0 xl:border-r dark:border-zinc-800 dark:bg-zinc-950/25">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-300">Secciones del reporte</span>
                    <button type="button" x-on:click="sections = sections.length === <?php echo e(count($sectionOptions)); ?> ? [] : <?php echo \Illuminate\Support\Js::from(array_keys($sectionOptions))->toHtml() ?>"
                            class="text-xs font-bold <?php echo e($accentText); ?>">
                        <span x-text="sections.length === <?php echo e(count($sectionOptions)); ?> ? 'Limpiar todas' : 'Seleccionar todas'"></span>
                    </button>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sectionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionKey => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $theme = $sectionThemes[$sectionKey] ?? $sectionThemes['summary'];
                        ?>
                        <label :class="sections.includes('<?php echo e($sectionKey); ?>') ? '<?php echo e($theme['selected']); ?>' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/70 dark:text-zinc-300 dark:hover:border-zinc-700'"
                               class="flex min-h-16 cursor-pointer items-center gap-3 rounded-xl border px-3 py-3 transition focus-within:ring-2 focus-within:ring-emerald-500/50">
                            <input type="checkbox" x-model="sections" value="<?php echo e($sectionKey); ?>" class="sr-only">
                            <span :class="sections.includes('<?php echo e($sectionKey); ?>') ? '<?php echo e($theme['check']); ?>' : 'border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900'"
                                  class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 transition">
                                <svg x-cloak x-show="sections.includes('<?php echo e($sectionKey); ?>')" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                            </span>
                            <span class="min-w-0 leading-tight">
                                <strong class="block text-sm"><?php echo e($section['label']); ?></strong>
                                <small class="mt-0.5 block text-[11px] font-normal opacity-65"><?php echo e($section['description']); ?></small>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedReportSections'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedReportSections.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </aside>

            <div class="p-4 sm:p-5 xl:overflow-y-auto 2xl:p-6">
                <div x-cloak x-show="sections.length === 0" class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 text-center dark:border-zinc-800 dark:bg-zinc-950/30">
                    <div class="max-w-sm">
                        <strong class="block text-sm text-zinc-700 dark:text-zinc-200">Sin secciones seleccionadas</strong>
                        <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Activa una sección para elegir sus campos.</span>
                    </div>
                </div>
                <div class="grid content-start gap-4 xl:grid-cols-2 2xl:gap-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sectionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionKey => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $fields = $columnOptions[$sectionKey] ?? [];
                            $theme = $sectionThemes[$sectionKey] ?? $sectionThemes['summary'];
                        ?>
                        <section x-cloak x-show="sections.includes('<?php echo e($sectionKey); ?>')" class="rounded-2xl border p-4 <?php echo e($theme['panel']); ?>">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <strong class="block text-sm <?php echo e($theme['title']); ?>">Campos: <?php echo e($section['label']); ?></strong>
                                    <small class="mt-0.5 block text-[11px] text-zinc-500 dark:text-zinc-400">Elige información incluida en esta sección.</small>
                                </div>
                                <button type="button" x-on:click="columns['<?php echo e($sectionKey); ?>'] = columns['<?php echo e($sectionKey); ?>'].length === <?php echo e(count($fields)); ?> ? [] : <?php echo \Illuminate\Support\Js::from(array_keys($fields))->toHtml() ?>"
                                        class="shrink-0 text-[11px] font-bold transition focus:outline-none focus:underline <?php echo e($theme['action']); ?>">
                                    <span x-text="(columns['<?php echo e($sectionKey); ?>'] || []).length === <?php echo e(count($fields)); ?> ? 'Limpiar todos' : 'Seleccionar todos'"></span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 2xl:grid-cols-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $fieldLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label :class="(columns['<?php echo e($sectionKey); ?>'] || []).includes('<?php echo e($fieldKey); ?>') ? '<?php echo e($theme['field']); ?>' : 'border-zinc-200 bg-white/75 text-zinc-600 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/70 dark:text-zinc-400 dark:hover:border-zinc-700'"
                                           class="flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border px-2.5 py-2 text-xs font-semibold leading-tight transition focus-within:ring-2 focus-within:ring-current/30">
                                        <input type="checkbox" x-model="columns['<?php echo e($sectionKey); ?>']" value="<?php echo e($fieldKey); ?>" class="sr-only">
                                        <span :class="(columns['<?php echo e($sectionKey); ?>'] || []).includes('<?php echo e($fieldKey); ?>') ? '<?php echo e($theme['check']); ?>' : 'border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-950'"
                                              class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition">
                                            <svg x-cloak x-show="(columns['<?php echo e($sectionKey); ?>'] || []).includes('<?php echo e($fieldKey); ?>')" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                        </span>
                                        <span><?php echo e($fieldLabel); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["reportColumns.{$sectionKey}"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["reportColumns.{$sectionKey}.*"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </section>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-zinc-800 dark:bg-zinc-900">
            <p class="hidden text-xs text-zinc-500 sm:block">A4 horizontal · Secciones configurables · Diseño compacto</p>
            <div class="flex gap-2 sm:justify-end">
                <button type="button" wire:click="$set('showReportModal', false)" class="h-11 flex-1 rounded-xl border border-zinc-300 px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 sm:flex-none dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-900">Cancelar</button>
                <button type="button" wire:click="<?php echo e($downloadMethod); ?>" wire:loading.attr="disabled" wire:target="<?php echo e($downloadMethod); ?>"
                        class="h-11 flex-1 rounded-xl px-6 text-sm font-bold text-white shadow-md transition disabled:cursor-wait disabled:opacity-70 sm:flex-none <?php echo e($accentButton); ?>">
                    <span wire:loading.remove wire:target="<?php echo e($downloadMethod); ?>">Generar reporte</span>
                    <span wire:loading.class.remove="hidden" wire:target="<?php echo e($downloadMethod); ?>" class="hidden">Generando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/finance-report-modal.blade.php ENDPATH**/ ?>