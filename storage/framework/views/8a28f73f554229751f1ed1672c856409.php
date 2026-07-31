<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($branding->name); ?></title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php if (isset($component)) { $__componentOriginal6328591391b02ca7d72027c0d6027f6b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6328591391b02ca7d72027c0d6027f6b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-theme','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-theme'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6328591391b02ca7d72027c0d6027f6b)): ?>
<?php $attributes = $__attributesOriginal6328591391b02ca7d72027c0d6027f6b; ?>
<?php unset($__attributesOriginal6328591391b02ca7d72027c0d6027f6b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6328591391b02ca7d72027c0d6027f6b)): ?>
<?php $component = $__componentOriginal6328591391b02ca7d72027c0d6027f6b; ?>
<?php unset($__componentOriginal6328591391b02ca7d72027c0d6027f6b); ?>
<?php endif; ?>
        
        <style>
            body { font-family: 'Outfit', sans-serif; }
        </style>

        <?php if (isset($component)) { $__componentOriginalff3018920f514a2a3356c625e214876e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalff3018920f514a2a3356c625e214876e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.theme-init','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('theme-init'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalff3018920f514a2a3356c625e214876e)): ?>
<?php $attributes = $__attributesOriginalff3018920f514a2a3356c625e214876e; ?>
<?php unset($__attributesOriginalff3018920f514a2a3356c625e214876e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalff3018920f514a2a3356c625e214876e)): ?>
<?php $component = $__componentOriginalff3018920f514a2a3356c625e214876e; ?>
<?php unset($__componentOriginalff3018920f514a2a3356c625e214876e); ?>
<?php endif; ?>
    </head>
    <body x-data="{ 
              mobileNavOpen: false,
              darkMode: document.documentElement.classList.contains('dark'),
           }" 
           x-init="$watch('darkMode', val => {
               window.setTheme(val ? 'dark' : 'light');
           })"
           class="min-h-screen overflow-x-hidden text-zinc-800 antialiased transition-colors duration-[250ms] dark:text-zinc-100">
        
        <div class="relative">
            <div class="hidden">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.navigation', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2279461270-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>

            <?php if (isset($component)) { $__componentOriginala591787d01fe92c5706972626cdf7231 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala591787d01fe92c5706972626cdf7231 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $attributes = $__attributesOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__attributesOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $component = $__componentOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__componentOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                <header class="sticky top-16 z-30 border-b border-emerald-950/10 bg-white/75 px-3 py-3 backdrop-blur-xl transition-colors duration-200 dark:border-emerald-200/10 dark:bg-emerald-950/45 sm:px-6 sm:py-4 lg:top-[73px] lg:px-8">
                    <div class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-between gap-3">
                        <?php echo e($header); ?>

                    </div>
                </header>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <main class="mx-auto w-full max-w-[1600px] p-3 sm:p-6 lg:p-8">
                <?php echo e($slot); ?>

            </main>
        </div>

        <?php
            $idleTimeout = min(
                (int) config('session.lifetime', 30),
                max(5, (int) (auth()->user()->session_idle_timeout_minutes ?: config('session.lifetime', 30)))
            );
        ?>
        <form id="idle-logout-form" method="POST" action="<?php echo e(route('logout')); ?>" data-timeout="<?php echo e($idleTimeout * 60_000); ?>" class="hidden" aria-hidden="true">
            <?php echo csrf_field(); ?>
        </form>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('swal')): ?>
            <script id="swal-flash" type="application/json"><?php echo json_encode(session('swal'), 15, 512) ?></script>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </body>
</html>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/layouts/app.blade.php ENDPATH**/ ?>