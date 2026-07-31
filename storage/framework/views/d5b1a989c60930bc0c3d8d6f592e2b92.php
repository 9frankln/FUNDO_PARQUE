<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'inputId',
    'handler' => 'selectPhoto',
    'multiple' => false,
    'accept' => 'image/jpeg,image/png,image/webp',
    'galleryLabel' => null,
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
    'inputId',
    'handler' => 'selectPhoto',
    'multiple' => false,
    'accept' => 'image/jpeg,image/png,image/webp',
    'galleryLabel' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->class(['grid grid-cols-1 gap-2 min-[360px]:grid-cols-2'])); ?>>
    <button type="button" @click="openCamera($event)" :disabled="busy" class="agro-image-action">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.38a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
        </svg>
        Tomar foto
    </button>
    <input
        x-ref="captureInput"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        capture="environment"
        class="sr-only"
        @change="<?php echo e($handler); ?>($event)"
        :disabled="busy"
    >

    <button type="button" @click="$refs.galleryInput?.click()" :disabled="busy" class="agro-image-action">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5M3.75 15v3.75A2.25 2.25 0 0 0 6 21h12a2.25 2.25 0 0 0 2.25-2.25V15" />
        </svg>
        <?php echo e($galleryLabel ?? ($multiple ? 'Elegir fotos' : 'Elegir imagen')); ?>

    </button>
    <input
        id="<?php echo e($inputId); ?>"
        x-ref="galleryInput"
        type="file"
        accept="<?php echo e($accept); ?>"
        <?php if($multiple): ?> multiple <?php endif; ?>
        tabindex="-1"
        class="sr-only"
        @change="<?php echo e($handler); ?>($event)"
        :disabled="busy"
    >
</div>

<?php if (isset($component)) { $__componentOriginal071b46241508c66a1c84114f4aaa7e25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal071b46241508c66a1c84114f4aaa7e25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.camera-capture','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('camera-capture'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal071b46241508c66a1c84114f4aaa7e25)): ?>
<?php $attributes = $__attributesOriginal071b46241508c66a1c84114f4aaa7e25; ?>
<?php unset($__attributesOriginal071b46241508c66a1c84114f4aaa7e25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal071b46241508c66a1c84114f4aaa7e25)): ?>
<?php $component = $__componentOriginal071b46241508c66a1c84114f4aaa7e25; ?>
<?php unset($__componentOriginal071b46241508c66a1c84114f4aaa7e25); ?>
<?php endif; ?>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/image-source-actions.blade.php ENDPATH**/ ?>