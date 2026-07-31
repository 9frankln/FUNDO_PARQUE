<?php if (isset($component)) { $__componentOriginal2017cf16d1c406a6112effea164a232e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2017cf16d1c406a6112effea164a232e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.recent-record-host','data' => ['active' => $recentRecord !== null,'class' => 'space-y-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('recent-record-host'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentRecord !== null),'class' => 'space-y-6']); ?>
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">Finanzas y contabilidad</h1>
            <p class="mt-1 text-sm text-zinc-400">Movimientos de caja y asignaciones familiares, cada uno en su flujo.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto sm:justify-end">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('finanzas', 'exportar')): ?>
                <button type="button" wire:click="openReportModal"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-zinc-700 bg-zinc-900 px-4 text-sm font-bold text-zinc-200 shadow-sm transition hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-300 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Exportar PDF
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('finanzas', 'crear')): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'movimientos'): ?>
                    <a href="<?php echo e(route('finanzas.movimiento.create')); ?>" class="agro-button w-full sm:w-auto">
                        <span aria-hidden="true">+</span> Nuevo movimiento
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('finanzas.asignacion.create')); ?>" class="agro-button w-full sm:w-auto">
                        <span aria-hidden="true">+</span> Nueva asignación
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginaldbc7a57f277f6eace2b7ee997f6b804b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldbc7a57f277f6eace2b7ee997f6b804b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.finance-dashboard','data' => ['data' => $dashboardData]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('finance-dashboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dashboardData)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldbc7a57f277f6eace2b7ee997f6b804b)): ?>
<?php $attributes = $__attributesOriginaldbc7a57f277f6eace2b7ee997f6b804b; ?>
<?php unset($__attributesOriginaldbc7a57f277f6eace2b7ee997f6b804b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldbc7a57f277f6eace2b7ee997f6b804b)): ?>
<?php $component = $__componentOriginaldbc7a57f277f6eace2b7ee997f6b804b; ?>
<?php unset($__componentOriginaldbc7a57f277f6eace2b7ee997f6b804b); ?>
<?php endif; ?>

    <div class="flex gap-1 overflow-x-auto border-b border-zinc-800" role="tablist" aria-label="Secciones de finanzas">
        <button type="button" wire:click="$set('tab', 'movimientos')" role="tab" aria-selected="<?php echo e($tab === 'movimientos' ? 'true' : 'false'); ?>"
                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold transition <?php echo e($tab === 'movimientos' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-zinc-500 hover:text-zinc-300'); ?>">
            Movimientos de caja
        </button>
        <button type="button" wire:click="$set('tab', 'asignaciones')" role="tab" aria-selected="<?php echo e($tab === 'asignaciones' ? 'true' : 'false'); ?>"
                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold transition <?php echo e($tab === 'asignaciones' ? 'border-violet-500 text-violet-400' : 'border-transparent text-zinc-500 hover:text-zinc-300'); ?>">
            Asignación familiar
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'movimientos'): ?>
        <?php if (isset($component)) { $__componentOriginal1d5c68e966293d924b0903e2b6a1abaf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.collapsible-filters','data' => ['active' => $hasMovementFilters,'title' => 'Filtros de movimientos','description' => 'Búsqueda precisa por periodo, categoría, monto o comprobante.','id' => 'movimientos-filter-content','reset' => 'resetMovimientoFilters','loadingTarget' => 'searchMovimiento,tipoMovimiento,categoriaMovimiento,periodoMovimiento,fechaDesdeMovimiento,fechaHastaMovimiento,montoMinMovimiento,montoMaxMovimiento,conComprobante']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('collapsible-filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasMovementFilters),'title' => 'Filtros de movimientos','description' => 'Búsqueda precisa por periodo, categoría, monto o comprobante.','id' => 'movimientos-filter-content','reset' => 'resetMovimientoFilters','loading-target' => 'searchMovimiento,tipoMovimiento,categoriaMovimiento,periodoMovimiento,fechaDesdeMovimiento,fechaHastaMovimiento,montoMinMovimiento,montoMaxMovimiento,conComprobante']); ?>
            <div class="border-t border-zinc-800 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                    <div class="relative w-full">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500" aria-hidden="true">&#x1F50D;</span>
                        <input type="search" wire:model.live.debounce.300ms="searchMovimiento"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20"
                               placeholder="Buscar descripción o categoría...">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Periodo</label>
                    <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'periodoMovimiento','options' => ['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'anio_actual' => 'Año actual'],'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'periodoMovimiento','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'anio_actual' => 'Año actual']),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                    <input type="date" wire:model.live="fechaDesdeMovimiento" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                    <input type="date" wire:model.live="fechaHastaMovimiento" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Tipo</label>
                    <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'tipoMovimiento','options' => ['' => 'Ingresos y egresos', 'ingreso' => 'Ingresos', 'egreso' => 'Egresos'],'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'tipoMovimiento','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Ingresos y egresos', 'ingreso' => 'Ingresos', 'egreso' => 'Egresos']),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Categoría</label>
                    <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'categoriaMovimiento','options' => ['' => 'Todas las categorías'] + $categorias->mapWithKeys(fn ($category) => [(string) $category->id => $category->nombre])->all(),'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'categoriaMovimiento','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todas las categorías'] + $categorias->mapWithKeys(fn ($category) => [(string) $category->id => $category->nombre])->all()),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Comprobante</label>
                    <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'conComprobante','options' => ['' => 'Todos', '1' => 'Con comprobante', '0' => 'Sin comprobante'],'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'conComprobante','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos', '1' => 'Con comprobante', '0' => 'Sin comprobante']),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto mínimo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMinMovimiento" placeholder="0.00" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto máximo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMaxMovimiento" placeholder="Sin límite" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-emerald-500/60 focus:ring-2 focus:ring-emerald-500/20">
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf)): ?>
<?php $attributes = $__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf; ?>
<?php unset($__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1d5c68e966293d924b0903e2b6a1abaf)): ?>
<?php $component = $__componentOriginal1d5c68e966293d924b0903e2b6a1abaf; ?>
<?php unset($__componentOriginal1d5c68e966293d924b0903e2b6a1abaf); ?>
<?php endif; ?>

        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950 text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                            <th class="p-4 whitespace-nowrap">Fecha</th>
                            <th class="p-4 whitespace-nowrap">Categoría</th>
                            <th class="p-4">Descripción</th>
                            <th class="p-4 whitespace-nowrap">Comprobante</th>
                            <th class="p-4 whitespace-nowrap">Monto</th>
                            <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/70 text-sm text-zinc-300">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $isRecent = $this->isRecentRecord('finanzas.movimientos', $movimiento->id);
                                $receiptUrl = $movimiento->comprobante_ruta
                                    ? route('movimiento.comprobante', $movimiento).'?v='.sha1($movimiento->comprobante_ruta)
                                    : null;
                            ?>
                            <tr class="transition <?php echo e($isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-800/25'); ?>">
                                <td class="p-4 font-semibold text-zinc-100 whitespace-nowrap">
                                    <?php echo e($movimiento->fecha->format('d/m/Y')); ?>

                                    <?php if (isset($component)) { $__componentOriginale01dc785c600eaefe0dd96fbf206880a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale01dc785c600eaefe0dd96fbf206880a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.recent-record-badge','data' => ['show' => $isRecent,'action' => $recentRecord['action'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('recent-record-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isRecent),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentRecord['action'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale01dc785c600eaefe0dd96fbf206880a)): ?>
<?php $attributes = $__attributesOriginale01dc785c600eaefe0dd96fbf206880a; ?>
<?php unset($__attributesOriginale01dc785c600eaefe0dd96fbf206880a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale01dc785c600eaefe0dd96fbf206880a)): ?>
<?php $component = $__componentOriginale01dc785c600eaefe0dd96fbf206880a; ?>
<?php unset($__componentOriginale01dc785c600eaefe0dd96fbf206880a); ?>
<?php endif; ?>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <span class="font-medium text-zinc-300"><?php echo e($movimiento->categoria?->nombre ?? 'Sin categoría'); ?></span>
                                    <span class="mt-0.5 block text-[10px] uppercase tracking-wider <?php echo e($movimiento->tipo === 'ingreso' ? 'text-emerald-400' : 'text-rose-400'); ?>"><?php echo e($movimiento->tipo); ?></span>
                                </td>
                                <td class="max-w-sm p-4 text-zinc-400"><?php echo e($movimiento->descripcion ?: 'Sin descripción'); ?></td>
                                <td class="p-4 whitespace-nowrap">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($movimiento->comprobante_ruta && $movimiento->comprobanteEsImagen()): ?>
                                        <?php
                                            $receiptTableFrame = \App\Support\ImageFrame::normalize($movimiento->comprobante_encuadre);
                                        ?>
                                        <a href="<?php echo e($receiptUrl); ?>" target="_blank" rel="noopener" title="Abrir comprobante" class="group inline-flex overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50">
                                            <img src="<?php echo e($receiptUrl); ?>"
                                                 alt="Comprobante del movimiento <?php echo e($movimiento->id); ?>"
                                                 width="48" height="48" loading="lazy" decoding="async"
                                                 class="h-12 w-12 rounded-xl border border-zinc-700 object-cover shadow-sm transition group-hover:border-emerald-400"
                                                 style="object-position: <?php echo e($receiptTableFrame['x']); ?>% <?php echo e($receiptTableFrame['y']); ?>%; transform: scale(<?php echo e($receiptTableFrame['zoom']); ?>); transform-origin: <?php echo e($receiptTableFrame['x']); ?>% <?php echo e($receiptTableFrame['y']); ?>%;">
                                        </a>
                                    <?php elseif($movimiento->comprobante_ruta): ?>
                                        <a href="<?php echo e($receiptUrl); ?>" target="_blank" rel="noopener" title="Abrir comprobante PDF" aria-label="Abrir comprobante PDF" class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-300 transition hover:border-rose-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M9 15h6M9 11h2" /></svg>
                                        </a>
                                    <?php else: ?>
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-zinc-700 text-zinc-600" title="Sin comprobante">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" d="m5 19 14-14" /></svg>
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="p-4 text-base font-extrabold whitespace-nowrap <?php echo e($movimiento->tipo === 'ingreso' ? 'text-emerald-400' : 'text-rose-400'); ?>">
                                    <?php echo e($movimiento->tipo === 'ingreso' ? '+' : '-'); ?> S/. <?php echo e(number_format((float) $movimiento->monto, 2)); ?>

                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'view','href' => route('finanzas.movimiento.show', $movimiento->id),'label' => 'Ver movimiento']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'view','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('finanzas.movimiento.show', $movimiento->id)),'label' => 'Ver movimiento']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $attributes = $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $component = $__componentOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('finanzas', 'actualizar')): ?>
                                            <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'edit','href' => route('finanzas.movimiento.edit', $movimiento->id)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'edit','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('finanzas.movimiento.edit', $movimiento->id))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $attributes = $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $component = $__componentOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('finanzas', 'eliminar')): ?>
                                            <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'delete','wire:click' => 'solicitarEliminacionMovimiento('.e($movimiento->id).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'delete','wire:click' => 'solicitarEliminacionMovimiento('.e($movimiento->id).')']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $attributes = $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $component = $__componentOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="p-12 text-center text-sm text-zinc-500">No hay movimientos con estos filtros.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="agro-table-footer">
            <div class="agro-table-size"><span>Mostrar</span><?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'perPage','options' => ['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros'],'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'perPage','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']),'tone' => 'emerald','live' => true,'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?></div>
            <div class="min-w-0"><?php echo e($movimientos->links('components.pagination')); ?></div>
        </div>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal1d5c68e966293d924b0903e2b6a1abaf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.collapsible-filters','data' => ['active' => $hasAssignmentFilters,'title' => 'Filtros de asignaciones','description' => 'Encuentra una entrega por beneficiario, fecha, propósito, foto o monto.','id' => 'asignaciones-filter-content','reset' => 'resetAsignacionFilters','loadingTarget' => 'searchAsignacion,propositoAsignacion,periodoAsignacion,fechaDesdeAsignacion,fechaHastaAsignacion,montoMinAsignacion,montoMaxAsignacion,conFoto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('collapsible-filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasAssignmentFilters),'title' => 'Filtros de asignaciones','description' => 'Encuentra una entrega por beneficiario, fecha, propósito, foto o monto.','id' => 'asignaciones-filter-content','reset' => 'resetAsignacionFilters','loading-target' => 'searchAsignacion,propositoAsignacion,periodoAsignacion,fechaDesdeAsignacion,fechaHastaAsignacion,montoMinAsignacion,montoMaxAsignacion,conFoto']); ?>
            <div class="border-t border-zinc-800 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                    <div class="relative w-full">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500" aria-hidden="true">&#x1F50D;</span>
                        <input type="search" wire:model.live.debounce.300ms="searchAsignacion"
                               class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20"
                               placeholder="Buscar beneficiario o detalle...">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Periodo</label>
                    <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'periodoAsignacion','options' => ['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'anio_actual' => 'Año actual'],'tone' => 'violet','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'periodoAsignacion','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'anio_actual' => 'Año actual']),'tone' => 'violet','live' => true,'compact' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                    <input type="date" wire:model.live="fechaDesdeAsignacion" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                    <input type="date" wire:model.live="fechaHastaAsignacion" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>

                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Propósito</label>
                    <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'propositoAsignacion','options' => ['' => 'Todos los propósitos', 'estudio' => 'Estudios', 'salud' => 'Salud', 'alimentacion' => 'Alimentación', 'vivienda' => 'Vivienda', 'transporte' => 'Transporte', 'ropa' => 'Ropa', 'gastos_personales' => 'Gastos personales', 'emergencia' => 'Emergencia', 'otros' => 'Otros'],'tone' => 'violet','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'propositoAsignacion','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos los propósitos', 'estudio' => 'Estudios', 'salud' => 'Salud', 'alimentacion' => 'Alimentación', 'vivienda' => 'Vivienda', 'transporte' => 'Transporte', 'ropa' => 'Ropa', 'gastos_personales' => 'Gastos personales', 'emergencia' => 'Emergencia', 'otros' => 'Otros']),'tone' => 'violet','live' => true,'compact' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Foto</label>
                    <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'conFoto','options' => ['' => 'Con y sin foto', '1' => 'Con foto', '0' => 'Sin foto'],'tone' => 'violet','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'conFoto','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Con y sin foto', '1' => 'Con foto', '0' => 'Sin foto']),'tone' => 'violet','live' => true,'compact' => true]); ?>
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
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto mínimo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMinAsignacion" placeholder="0.00" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Monto máximo</label>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="montoMaxAsignacion" placeholder="Sin límite" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition placeholder:text-zinc-600 focus:border-violet-500/60 focus:ring-2 focus:ring-violet-500/20">
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf)): ?>
<?php $attributes = $__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf; ?>
<?php unset($__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1d5c68e966293d924b0903e2b6a1abaf)): ?>
<?php $component = $__componentOriginal1d5c68e966293d924b0903e2b6a1abaf; ?>
<?php unset($__componentOriginal1d5c68e966293d924b0903e2b6a1abaf); ?>
<?php endif; ?>

        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-800 bg-zinc-950 text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                            <th class="p-4 whitespace-nowrap">Fecha</th>
                            <th class="p-4 whitespace-nowrap">Beneficiario</th>
                            <th class="p-4 whitespace-nowrap">Propósito</th>
                            <th class="p-4 whitespace-nowrap">Foto</th>
                            <th class="p-4 whitespace-nowrap">Monto</th>
                            <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/70 text-sm text-zinc-300">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $asignaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asignacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $isRecent = $this->isRecentRecord('finanzas.asignaciones', $asignacion->id);
                            ?>
                            <tr class="transition <?php echo e($isRecent ? 'bg-violet-500/10' : 'hover:bg-zinc-800/25'); ?>">
                                <td class="p-4 font-semibold text-zinc-100 whitespace-nowrap">
                                    <?php echo e($asignacion->fecha->format('d/m/Y')); ?>

                                    <?php if (isset($component)) { $__componentOriginale01dc785c600eaefe0dd96fbf206880a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale01dc785c600eaefe0dd96fbf206880a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.recent-record-badge','data' => ['show' => $isRecent,'action' => $recentRecord['action'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('recent-record-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isRecent),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentRecord['action'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale01dc785c600eaefe0dd96fbf206880a)): ?>
<?php $attributes = $__attributesOriginale01dc785c600eaefe0dd96fbf206880a; ?>
<?php unset($__attributesOriginale01dc785c600eaefe0dd96fbf206880a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale01dc785c600eaefe0dd96fbf206880a)): ?>
<?php $component = $__componentOriginale01dc785c600eaefe0dd96fbf206880a; ?>
<?php unset($__componentOriginale01dc785c600eaefe0dd96fbf206880a); ?>
<?php endif; ?>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <a href="<?php echo e(route('finanzas.asignacion.show', $asignacion->id)); ?>" class="font-bold text-zinc-200 transition hover:text-violet-300"><?php echo e($asignacion->beneficiario); ?></a>
                                </td>
                                <td class="p-4 whitespace-nowrap"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['value' => $asignacion->proposito,'label' => ucfirst(str_replace('_', ' ', $asignacion->proposito)),'tone' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($asignacion->proposito),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst(str_replace('_', ' ', $asignacion->proposito))),'tone' => 'violet']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></td>
                                <td class="p-4 whitespace-nowrap">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asignacion->foto_ruta): ?>
                                        <?php
                                            $assignmentTableFrame = \App\Support\ImageFrame::normalize($asignacion->foto_encuadre);
                                        ?>
                                        <a href="<?php echo e(route('asignacion.foto', $asignacion)); ?>" target="_blank" rel="noopener" title="Abrir foto" class="group inline-flex overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/50">
                                            <img src="<?php echo e(route('asignacion.foto', $asignacion)); ?>" alt="Foto de <?php echo e($asignacion->beneficiario); ?>" width="48" height="48" loading="lazy" decoding="async" class="h-12 w-12 rounded-xl border border-violet-500/20 bg-zinc-950 object-cover transition group-hover:border-violet-400" style="object-position: <?php echo e($assignmentTableFrame['x']); ?>% <?php echo e($assignmentTableFrame['y']); ?>%; transform: scale(<?php echo e($assignmentTableFrame['zoom']); ?>); transform-origin: <?php echo e($assignmentTableFrame['x']); ?>% <?php echo e($assignmentTableFrame['y']); ?>%;">
                                        </a>
                                    <?php else: ?>
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-zinc-700 bg-zinc-950 text-zinc-600" title="Sin foto">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" /><path stroke-linecap="round" d="m5 19 14-14" /></svg>
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="p-4 text-base font-extrabold text-violet-300 whitespace-nowrap">S/. <?php echo e(number_format((float) $asignacion->monto, 2)); ?></td>
                                <td class="p-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'view','href' => route('finanzas.asignacion.show', $asignacion->id),'label' => 'Ver asignación']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'view','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('finanzas.asignacion.show', $asignacion->id)),'label' => 'Ver asignación']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $attributes = $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $component = $__componentOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('finanzas', 'actualizar')): ?>
                                            <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'edit','href' => route('finanzas.asignacion.edit', $asignacion->id)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'edit','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('finanzas.asignacion.edit', $asignacion->id))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $attributes = $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $component = $__componentOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('finanzas', 'eliminar')): ?>
                                            <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'delete','wire:click' => 'solicitarEliminacionAsignacion('.e($asignacion->id).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'delete','wire:click' => 'solicitarEliminacionAsignacion('.e($asignacion->id).')']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $attributes = $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__attributesOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4)): ?>
<?php $component = $__componentOriginal3cb096f2e62c7df2672a776d39e07de4; ?>
<?php unset($__componentOriginal3cb096f2e62c7df2672a776d39e07de4); ?>
<?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="p-12 text-center text-sm text-zinc-500">No hay asignaciones con estos filtros.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="agro-table-footer">
            <div class="agro-table-size"><span>Mostrar</span><?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'perPage','options' => ['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros'],'tone' => 'violet','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'perPage','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']),'tone' => 'violet','live' => true,'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?></div>
            <div class="min-w-0"><?php echo e($asignaciones->links('components.pagination')); ?></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showReportModal): ?>
        <?php if (isset($component)) { $__componentOriginal98ab6345e235c0a681a351f065fcba80 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98ab6345e235c0a681a351f065fcba80 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.finance-report-modal','data' => ['title' => $reportType === 'movimientos' ? 'Exportar movimientos de caja' : 'Exportar asignación familiar','description' => 'Selecciona solo la información esencial. Se respetan los filtros activos.','sectionOptions' => $reportSectionOptions,'columnOptions' => $reportColumnOptions,'tone' => $reportType === 'movimientos' ? 'emerald' : 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('finance-report-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reportType === 'movimientos' ? 'Exportar movimientos de caja' : 'Exportar asignación familiar'),'description' => 'Selecciona solo la información esencial. Se respetan los filtros activos.','section-options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reportSectionOptions),'column-options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reportColumnOptions),'tone' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reportType === 'movimientos' ? 'emerald' : 'violet')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98ab6345e235c0a681a351f065fcba80)): ?>
<?php $attributes = $__attributesOriginal98ab6345e235c0a681a351f065fcba80; ?>
<?php unset($__attributesOriginal98ab6345e235c0a681a351f065fcba80); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98ab6345e235c0a681a351f065fcba80)): ?>
<?php $component = $__componentOriginal98ab6345e235c0a681a351f065fcba80; ?>
<?php unset($__componentOriginal98ab6345e235c0a681a351f065fcba80); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2017cf16d1c406a6112effea164a232e)): ?>
<?php $attributes = $__attributesOriginal2017cf16d1c406a6112effea164a232e; ?>
<?php unset($__attributesOriginal2017cf16d1c406a6112effea164a232e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2017cf16d1c406a6112effea164a232e)): ?>
<?php $component = $__componentOriginal2017cf16d1c406a6112effea164a232e; ?>
<?php unset($__componentOriginal2017cf16d1c406a6112effea164a232e); ?>
<?php endif; ?>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/livewire/finanzas/index.blade.php ENDPATH**/ ?>