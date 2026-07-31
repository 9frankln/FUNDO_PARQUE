<div>
    <?php if (isset($component)) { $__componentOriginal9f22e7742a20ce0778f428641f367b17 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f22e7742a20ce0778f428641f367b17 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.global-dashboard','data' => ['data' => $dashboardData]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('global-dashboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dashboardData)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f22e7742a20ce0778f428641f367b17)): ?>
<?php $attributes = $__attributesOriginal9f22e7742a20ce0778f428641f367b17; ?>
<?php unset($__attributesOriginal9f22e7742a20ce0778f428641f367b17); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f22e7742a20ce0778f428641f367b17)): ?>
<?php $component = $__componentOriginal9f22e7742a20ce0778f428641f367b17; ?>
<?php unset($__componentOriginal9f22e7742a20ce0778f428641f367b17); ?>
<?php endif; ?>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>