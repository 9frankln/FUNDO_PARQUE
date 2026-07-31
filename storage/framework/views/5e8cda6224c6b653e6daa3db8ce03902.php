<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'path' => null,
    'url' => null,
    'alt' => 'Fotografía del registro',
    'size' => 'md',
    'frame' => null,
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
    'path' => null,
    'url' => null,
    'alt' => 'Fotografía del registro',
    'size' => 'md',
    'frame' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $boxClass = $size === 'sm' ? 'h-9 w-9 rounded-lg' : 'h-12 w-12 rounded-xl';
    $iconClass = $size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
    $source = $url ?: ($path ? '/storage/'.ltrim($path, '/') : null);
    $pixels = $size === 'sm' ? 36 : 48;
    $imageFrame = \App\Support\ImageFrame::normalize($frame);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($source): ?>
    <a href="<?php echo e($source); ?>" target="_blank" rel="noopener noreferrer"
       title="Abrir imagen completa"
       class="group inline-flex overflow-hidden rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-900">
         <img src="<?php echo e($source); ?>"
              alt="<?php echo e($alt); ?>"
              width="<?php echo e($pixels); ?>"
              height="<?php echo e($pixels); ?>"
             loading="lazy"
             decoding="async"
             class="<?php echo e($boxClass); ?> border border-emerald-900/10 bg-emerald-50 object-cover shadow-sm transition group-hover:border-emerald-500/40 dark:border-emerald-300/15 dark:bg-emerald-950/30"
             style="object-position: <?php echo e($imageFrame['x']); ?>% <?php echo e($imageFrame['y']); ?>%; transform: scale(<?php echo e($imageFrame['zoom']); ?>); transform-origin: <?php echo e($imageFrame['x']); ?>% <?php echo e($imageFrame['y']); ?>%;">
    </a>
<?php else: ?>
    <span title="Sin foto" class="<?php echo e($boxClass); ?> inline-flex items-center justify-center border border-dashed border-slate-300 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-600">
        <svg class="<?php echo e($iconClass); ?>" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.379a2.25 2.25 0 0 0 1.59-.659l.622-.622A2.25 2.25 0 0 1 10.432 4h3.136a2.25 2.25 0 0 1 1.591.659l.622.622A2.25 2.25 0 0 0 17.371 6h1.379A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.375a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            <path stroke-linecap="round" d="m5 19 14-14" />
        </svg>
        <span class="sr-only">Sin foto</span>
    </span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/table-photo.blade.php ENDPATH**/ ?>