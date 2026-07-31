<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['pdf' => false]));

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

foreach (array_filter((['pdf' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $logo = $pdf ? $branding->logoDataUri() : $branding->logoUrl();
    $frame = \App\Support\ImageFrame::normalize($branding->logoFrame());
    $frameStyle = 'object-position: '.$frame['x'].'% '.$frame['y'].'%; transform: scale('.$frame['zoom'].'); transform-origin: '.$frame['x'].'% '.$frame['y'].'%;';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pdf): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo): ?>
        <img src="<?php echo e($logo); ?>" alt="Logo de <?php echo e($branding->name()); ?>" <?php echo e($attributes); ?>>
    <?php else: ?>
        <svg viewBox="0 0 64 64" role="img" aria-label="Logo de <?php echo e($branding->name()); ?>" xmlns="http://www.w3.org/2000/svg" <?php echo e($attributes); ?>>
            <path d="M32 57V27" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
            <path d="M32 35C18 35 10 27 9 15c13 0 22 6 23 20Zm0 9c14 0 22-8 23-20-13 0-22 6-23 20Z" fill="currentColor" opacity=".85"/>
            <path d="M17 57h30" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
        </svg>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php else: ?>
    <img data-brand-logo-image src="<?php echo e($logo ?? ''); ?>" alt="Logo de <?php echo e($branding->name()); ?>" <?php echo e($attributes->class(['hidden' => ! $logo])->merge(['style' => $frameStyle])); ?>>
    <svg data-brand-logo-fallback viewBox="0 0 64 64" role="img" aria-label="Logo de <?php echo e($branding->name()); ?>" xmlns="http://www.w3.org/2000/svg" <?php echo e($attributes->class(['hidden' => (bool) $logo])); ?>>
        <path d="M32 57V27" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
        <path d="M32 35C18 35 10 27 9 15c13 0 22 6 23 20Zm0 9c14 0 22-8 23-20-13 0-22 6-23 20Z" fill="currentColor" opacity=".85"/>
        <path d="M17 57h30" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
    </svg>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/brand-logo.blade.php ENDPATH**/ ?>