<div class="mx-auto max-w-6xl space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="<?php echo e(route('animal.index')); ?>"
           class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-100 transition duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                <?php echo e($isEdit ? 'Editar Animal' : 'Registrar Nuevo Animal'); ?>

            </h1>
            <p class="text-zinc-400 text-sm mt-1">Completa la ficha técnica para guardar en el inventario.</p>
        </div>
    </div>

    <!-- Form Layout -->
    <form wire:submit="save" x-data x-on:submit="if ($store.imageUploads.busy) $event.preventDefault()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section class="space-y-5 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
                <div class="border-b border-zinc-850 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Identificación</h2>
                    <p class="mt-1 text-xs text-zinc-500">Selecciona tipo y raza. Código se genera solo.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Tipo de animal <span class="text-red-500">*</span></label>
                        <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'especieId','options' => ['' => 'Selecciona tipo'] + collect($especies)->pluck('nombre', 'id')->all(),'tone' => 'emerald','live' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'especieId','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Selecciona tipo'] + collect($especies)->pluck('nombre', 'id')->all()),'tone' => 'emerald','live' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['especieId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Raza <span class="text-red-500">*</span></label>
                        <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'razaId','options' => ['' => 'Selecciona raza'] + collect($razas)->pluck('nombre', 'id')->all(),'tone' => 'emerald','disabled' => empty($especieId)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'razaId','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Selecciona raza'] + collect($razas)->pluck('nombre', 'id')->all()),'tone' => 'emerald','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(empty($especieId))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['razaId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Código del animal <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-[minmax(0,1fr)_6rem] overflow-hidden rounded-xl border border-zinc-800 bg-zinc-950 focus-within:border-emerald-500/60 focus-within:ring-2 focus-within:ring-emerald-500/15">
                            <div class="flex items-center border-r border-zinc-800 px-4 text-sm font-black tracking-wider text-emerald-700 dark:text-emerald-300">
                                <?php echo e($codigoPrefijo && $codigoAnio ? strtoupper($codigoPrefijo).str_pad((string) ((int) $codigoAnio % 100), 2, '0', STR_PAD_LEFT).'-' : 'TIPO-AÑO-'); ?>

                            </div>
                            <input type="text" inputmode="numeric" maxlength="3" wire:model.blur="codigoNumero"
                                   class="border-0 bg-transparent px-3 py-2.5 text-center font-mono text-sm font-black tracking-widest text-zinc-100 outline-none focus:ring-0"
                                   placeholder="001" <?php if(!$codigoPrefijo): echo 'disabled'; endif; ?>>
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                            <span class="text-zinc-500">Prefijo y año fijos. Editable: últimos 3 dígitos.</span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-400"><?php echo e($this->codigoPreview); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['codigoNumero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Nombre <span class="font-normal text-zinc-500">(opcional)</span></label>
                        <input type="text" wire:model="nombre" maxlength="100"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50"
                               placeholder="Ej: Estrella">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="space-y-5 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
                <div class="border-b border-zinc-850 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Clasificación y edad</h2>
                    <p class="mt-1 text-xs text-zinc-500">Edad se actualiza automáticamente con paso del tiempo.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Sexo <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-3 text-sm font-semibold transition <?php echo e($genero === 'hembra' ? 'border-pink-500/40 bg-pink-500/10 text-pink-700 dark:text-pink-300' : 'border-zinc-800 text-zinc-400'); ?>">
                            <input type="radio" wire:model.live="genero" value="hembra" class="sr-only">
                            <span class="text-lg">&#x2640;</span> Hembra
                        </label>
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-3 text-sm font-semibold transition <?php echo e($genero === 'macho' ? 'border-sky-500/40 bg-sky-500/10 text-sky-700 dark:text-sky-300' : 'border-zinc-800 text-zinc-400'); ?>">
                            <input type="radio" wire:model.live="genero" value="macho" class="sr-only">
                            <span class="text-lg">&#x2642;</span> Macho
                        </label>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['genero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Procedencia <span class="text-red-500">*</span></label>
                        <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'tipoAlta','options' => ['compra' => 'Compra', 'parto' => 'Nacimiento / parto', 'donacion' => 'Donación', 'traslado' => 'Traslado', 'prestamo' => 'Préstamo'],'tone' => 'sky','live' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'tipoAlta','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['compra' => 'Compra', 'parto' => 'Nacimiento / parto', 'donacion' => 'Donación', 'traslado' => 'Traslado', 'prestamo' => 'Préstamo']),'tone' => 'sky','live' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tipoAlta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400"><?php echo e($this->admissionDateLabel); ?> <span class="text-red-500">*</span></label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipoAlta === 'parto'): ?>
                            <input type="date" wire:model.live="fechaNacimiento" max="<?php echo e(now()->toDateString()); ?>"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fechaNacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <input type="date" wire:model.live="fechaAlta" max="<?php echo e(now()->toDateString()); ?>"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fechaAlta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipoAlta === 'compra'): ?>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Precio de compra <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-bold text-emerald-700 dark:text-emerald-400">S/</span>
                                <input type="number" min="0.01" max="9999999999.99" step="0.01" wire:model="precioCompra"
                                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-2.5 pl-11 pr-4 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50"
                                       placeholder="Ej: 2850.00">
                            </div>
                            <p class="mt-1 text-[10px] text-zinc-500">Monto pagado por animal en soles.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['precioCompra'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipoAlta !== 'parto'): ?>
                        <div class="sm:col-span-2 -mb-1">
                            <p class="text-xs font-semibold text-zinc-400">Edad aproximada</p>
                            <p class="mt-0.5 text-[10px] text-zinc-500">Completa al menos años o meses. Valores vacíos se consideran cero.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Años</label>
                            <input type="number" min="0" max="100" wire:model.live.debounce.250ms="edadEstimadaAnios"
                                   x-on:focus="if ($el.value === '0') { $el.value = ''; $el.dispatchEvent(new Event('input', { bubbles: true })) }"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50"
                                   placeholder="Ej: 2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edadEstimadaAnios'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Meses adicionales</label>
                            <input type="number" min="0" max="11" wire:model.live.debounce.250ms="edadEstimadaMeses"
                                   x-on:focus="if ($el.value === '0') { $el.value = ''; $el.dispatchEvent(new Event('input', { bubbles: true })) }"
                                   class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50"
                                   placeholder="Ej: 6">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edadEstimadaMeses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-400"><?php echo e($this->weightLabel); ?></label>
                        <input type="number" min="0" step="0.01" wire:model="peso"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-zinc-100 outline-none transition focus:border-emerald-500/50"
                               placeholder="Ej: 385.50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['peso'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($genero === 'hembra' && (!$this->showMilkingOption || $this->canEnableMilking)): ?>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-400">Estado reproductivo</label>
                            <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'estadoReproductivo','options' => ['' => 'Sin registrar'] + \App\Models\Animal::REPRODUCTIVE_STATES,'tone' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'estadoReproductivo','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Sin registrar'] + \App\Models\Animal::REPRODUCTIVE_STATES),'tone' => 'amber']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['estadoReproductivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="mt-1 block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="grid grid-cols-1 gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-50 p-4 dark:bg-emerald-950/20 sm:grid-cols-[auto_1fr] sm:items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 text-2xl font-black text-white dark:bg-emerald-400 dark:text-emerald-950">
                        <?php echo e($genero === 'hembra' ? '♀' : '♂'); ?>

                    </div>
                    <div>
                        <div class="text-sm font-black text-emerald-900 dark:text-emerald-100"><?php echo e($this->clasificacionPreview); ?></div>
                        <div class="mt-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300"><?php echo e($this->edadPreview); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->denticionPreview): ?>
                            <div class="mt-1 text-[10px] font-medium text-amber-700 dark:text-amber-300">Estimación por edad, no observada: <?php echo e($this->denticionPreview); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col gap-5 border-t border-zinc-850/60 pt-4 sm:flex-row">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showMilkingOption): ?>
                        <label class="flex items-center gap-3 <?php echo e($this->canEnableMilking ? 'cursor-pointer' : 'cursor-not-allowed opacity-65'); ?>">
                            <input type="checkbox" wire:model="aptaOrdeno" class="agro-checkbox h-5 w-5 rounded border focus:ring-0" <?php if(!$this->canEnableMilking): echo 'disabled'; endif; ?>>
                            <div class="text-left">
                                <div class="text-sm font-semibold text-zinc-250">Apta para ordeño</div>
                                <div class="text-[10px] text-zinc-500"><?php echo e($this->milkingEligibilityMessage); ?></div>
                            </div>
                        </label>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="activo" class="agro-checkbox h-5 w-5 rounded border focus:ring-0">
                        <div class="text-left">
                            <div class="text-sm font-semibold text-zinc-250">Animal Activo</div>
                            <div class="text-[10px] text-zinc-500">Permitir su uso en subsistemas</div>
                        </div>
                    </label>
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
                <h2 class="border-b border-zinc-850 pb-2 text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Observaciones</h2>
                <textarea wire:model="observaciones" rows="4"
                          class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 focus:border-emerald-500/50 text-zinc-100 placeholder-zinc-600 text-sm transition outline-none"
                          placeholder="Agrega anotaciones clínicas o de procedencia..."></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        </div>

        <!-- Right / Photo + Save -->
        <?php
            $animalPhotoFrame = \App\Support\ImageFrame::normalize($fotoEncuadre);
        ?>
        <div x-data="optimizedImageUpload" class="space-y-6">
            <div class="space-y-4 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3 border-b border-zinc-800 pb-3">
                    <div>
                        <h3 class="text-left text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Foto del animal</h3>
                        <p class="mt-1 text-left text-xs text-zinc-500">Completa, proporcional y optimizada.</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-zinc-800 bg-zinc-950 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                        <?php echo e($existingFoto && !$removeFoto && !$foto ? 'Existente' : 'Opcional'); ?>

                    </span>
                </div>

                <label for="animal-photo-input" class="group relative flex aspect-[4/3] w-full cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50 transition hover:border-emerald-500 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:hover:border-emerald-400 lg:aspect-square">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Vista previa de foto nueva" class="absolute inset-0 h-full w-full bg-zinc-100 object-contain dark:bg-zinc-950">
                    </template>

                    <span x-show="!previewUrl" class="absolute inset-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($foto && !$errors->has('foto')): ?>
                            <img src="<?php echo e($foto->temporaryUrl()); ?>" alt="Vista previa de foto nueva" class="h-full w-full bg-zinc-100 object-cover dark:bg-zinc-950" style="object-position: <?php echo e($animalPhotoFrame['x']); ?>% <?php echo e($animalPhotoFrame['y']); ?>%; transform: scale(<?php echo e($animalPhotoFrame['zoom']); ?>); transform-origin: <?php echo e($animalPhotoFrame['x']); ?>% <?php echo e($animalPhotoFrame['y']); ?>%;">
                            <span class="absolute left-3 top-3 rounded-lg bg-zinc-950/80 px-3 py-1.5 text-xs font-semibold text-white">Nueva imagen</span>
                        <?php elseif($existingFoto && !$removeFoto): ?>
                            <img src="<?php echo e('/storage/'.ltrim($existingFoto, '/')); ?>" alt="Foto actual del animal" class="h-full w-full bg-zinc-100 object-cover dark:bg-zinc-950" style="object-position: <?php echo e($animalPhotoFrame['x']); ?>% <?php echo e($animalPhotoFrame['y']); ?>%; transform: scale(<?php echo e($animalPhotoFrame['zoom']); ?>); transform-origin: <?php echo e($animalPhotoFrame['x']); ?>% <?php echo e($animalPhotoFrame['y']); ?>%;">
                            <span class="absolute left-3 top-3 rounded-lg bg-zinc-950/80 px-3 py-1.5 text-xs font-semibold text-white">Foto actual</span>
                        <?php elseif($removeFoto): ?>
                            <span class="flex h-full flex-col items-center justify-center px-5 text-center">
                                <svg class="h-10 w-10 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                <span class="mt-3 text-sm font-semibold text-rose-700 dark:text-rose-300">Eliminación preparada</span>
                                <span class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">Clic para elegir reemplazo.</span>
                            </span>
                        <?php else: ?>
                            <span class="flex h-full flex-col items-center justify-center px-5 text-center">
                                <svg class="h-10 w-10 text-emerald-700 transition group-hover:scale-105 dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.379a2.25 2.25 0 0 0 1.59-.659l.622-.622A2.25 2.25 0 0 1 10.432 4h3.136a2.25 2.25 0 0 1 1.591.659l.622.622A2.25 2.25 0 0 0 17.371 6h1.379A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.375a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                </svg>
                                <span class="mt-3 text-sm font-semibold text-[#183c2c] dark:text-[#ecfdf5]">Cargar foto</span>
                                <span class="mt-1 text-xs text-[#567365] dark:text-[#a7c9b5]">JPG, PNG o WebP</span>
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </span>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($foto && !$errors->has('foto')): ?>
                        <?php if (isset($component)) { $__componentOriginal283b837347bc9693891d12fbf0eaa9f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283b837347bc9693891d12fbf0eaa9f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.image-frame-editor','data' => ['id' => 'animal-photo-frame','src' => $foto->temporaryUrl(),'xModel' => 'fotoEncuadre.x','yModel' => 'fotoEncuadre.y','zoomModel' => 'fotoEncuadre.zoom']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('image-frame-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'animal-photo-frame','src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($foto->temporaryUrl()),'x-model' => 'fotoEncuadre.x','y-model' => 'fotoEncuadre.y','zoom-model' => 'fotoEncuadre.zoom']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283b837347bc9693891d12fbf0eaa9f3)): ?>
<?php $attributes = $__attributesOriginal283b837347bc9693891d12fbf0eaa9f3; ?>
<?php unset($__attributesOriginal283b837347bc9693891d12fbf0eaa9f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283b837347bc9693891d12fbf0eaa9f3)): ?>
<?php $component = $__componentOriginal283b837347bc9693891d12fbf0eaa9f3; ?>
<?php unset($__componentOriginal283b837347bc9693891d12fbf0eaa9f3); ?>
<?php endif; ?>
                    <?php elseif($existingFoto && !$removeFoto): ?>
                        <?php if (isset($component)) { $__componentOriginal283b837347bc9693891d12fbf0eaa9f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal283b837347bc9693891d12fbf0eaa9f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.image-frame-editor','data' => ['id' => 'animal-photo-frame','src' => '/storage/'.ltrim($existingFoto, '/'),'xModel' => 'fotoEncuadre.x','yModel' => 'fotoEncuadre.y','zoomModel' => 'fotoEncuadre.zoom']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('image-frame-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'animal-photo-frame','src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('/storage/'.ltrim($existingFoto, '/')),'x-model' => 'fotoEncuadre.x','y-model' => 'fotoEncuadre.y','zoom-model' => 'fotoEncuadre.zoom']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal283b837347bc9693891d12fbf0eaa9f3)): ?>
<?php $attributes = $__attributesOriginal283b837347bc9693891d12fbf0eaa9f3; ?>
<?php unset($__attributesOriginal283b837347bc9693891d12fbf0eaa9f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal283b837347bc9693891d12fbf0eaa9f3)): ?>
<?php $component = $__componentOriginal283b837347bc9693891d12fbf0eaa9f3; ?>
<?php unset($__componentOriginal283b837347bc9693891d12fbf0eaa9f3); ?>
<?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <span x-cloak x-show="busy" class="absolute inset-x-3 bottom-3 z-30 rounded-xl bg-zinc-950/90 p-3 shadow-lg" role="status" aria-live="polite">
                        <span class="flex items-center justify-between gap-3 text-[11px] font-semibold text-emerald-300">
                            <span x-text="processing ? 'Optimizando imagen...' : 'Subiendo imagen...'"></span>
                            <span x-show="uploading" x-text="`${progress}%`"></span>
                        </span>
                        <span class="mt-2 block h-1 overflow-hidden rounded-full bg-zinc-700">
                            <span class="block h-full bg-emerald-400 transition-all duration-200" :style="`width: ${processing ? 18 : progress}%`"></span>
                        </span>
                    </span>

                </label>

                <?php if (isset($component)) { $__componentOriginal100e636ee34a225780e6d745118b8a1d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100e636ee34a225780e6d745118b8a1d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.image-source-actions','data' => ['inputId' => 'animal-photo-input']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('image-source-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['input-id' => 'animal-photo-input']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100e636ee34a225780e6d745118b8a1d)): ?>
<?php $attributes = $__attributesOriginal100e636ee34a225780e6d745118b8a1d; ?>
<?php unset($__attributesOriginal100e636ee34a225780e6d745118b8a1d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100e636ee34a225780e6d745118b8a1d)): ?>
<?php $component = $__componentOriginal100e636ee34a225780e6d745118b8a1d; ?>
<?php unset($__componentOriginal100e636ee34a225780e6d745118b8a1d); ?>
<?php endif; ?>

                <p class="text-center text-xs font-medium text-zinc-500">
                    Fotos grandes se reducen a 1600 px y WebP. Ajusta foco y zoom sin modificar archivo original.
                </p>
                <span x-cloak x-show="clientError" x-text="clientError" class="block text-xs text-red-500" role="alert"></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['foto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="block text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="grid gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($foto): ?>
                        <button type="button" wire:click="cancelPhotoChange" x-on:click="releasePreview()"
                                class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900">
                            Descartar imagen nueva
                        </button>
                    <?php elseif($existingFoto && !$removeFoto): ?>
                        <button type="button" wire:click="requestPhotoRemoval"
                                class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-transparent dark:text-rose-400 dark:hover:bg-rose-500/10">
                            Eliminar imagen
                        </button>
                    <?php elseif($removeFoto): ?>
                        <button type="button" wire:click="cancelPhotoRemoval"
                                class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:bg-zinc-900">
                            Deshacer eliminación
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="rounded-xl border border-amber-500/25 bg-amber-50 p-3 text-xs leading-5 text-amber-800 dark:bg-amber-500/5 dark:text-amber-200/80">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingFoto): ?>
                        La foto actual permanece protegida hasta pulsar <strong>Actualizar animal</strong>.
                    <?php else: ?>
                        La imagen solo se almacenará al pulsar <strong>Guardar animal</strong>.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <button type="submit" x-bind:disabled="busy || $store.imageUploads.busy" wire:loading.attr="disabled" wire:target="save,foto"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 py-4 font-bold tracking-wide text-zinc-950 shadow-xl shadow-emerald-500/10 transition duration-300 hover:-translate-y-0.5 hover:from-emerald-400 hover:to-teal-400 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="save">&#x1F4BE;</span>
                <span wire:loading.remove wire:target="save"><?php echo e($isEdit ? 'Actualizar animal' : 'Guardar animal'); ?></span>
                <span wire:loading wire:target="save" class="h-5 w-5 animate-spin rounded-full border-2 border-zinc-950 border-t-transparent"></span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/livewire/animal/form.blade.php ENDPATH**/ ?>