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
    <!-- Breadcrumbs / Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                Lotes de Engorde
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Administra lotes de cualquier especie y controla evolución de peso.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('engorde', 'exportar')): ?>
                <button wire:click="$set('showExportModal', true)"
                        class="flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-zinc-700 hover:bg-zinc-800">
                    <span>&#x1F4E5;</span> Resumen PDF
                </button>
                <button wire:click="openDetailedReportModal"
                        class="flex items-center gap-2 rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300">
                    Reporte detallado
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('engorde', 'crear')): ?>
                <a href="<?php echo e(route('engorde.lote.create')); ?>" class="agro-button">
                    <span>➕</span> Crear Lote de Engorde
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Engorde Dashboard -->
    <?php if (isset($component)) { $__componentOriginal611948195ce69097bf74a8143eff2474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal611948195ce69097bf74a8143eff2474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.engorde-dashboard','data' => ['data' => $dashboardData]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('engorde-dashboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dashboardData)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal611948195ce69097bf74a8143eff2474)): ?>
<?php $attributes = $__attributesOriginal611948195ce69097bf74a8143eff2474; ?>
<?php unset($__attributesOriginal611948195ce69097bf74a8143eff2474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal611948195ce69097bf74a8143eff2474)): ?>
<?php $component = $__componentOriginal611948195ce69097bf74a8143eff2474; ?>
<?php unset($__componentOriginal611948195ce69097bf74a8143eff2474); ?>
<?php endif; ?>

    <!-- Filters and Search Bar -->
    <?php if (isset($component)) { $__componentOriginal1d5c68e966293d924b0903e2b6a1abaf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.collapsible-filters','data' => ['active' => $hasActiveFilters,'title' => 'Filtros de lotes','description' => 'Consulta lotes por periodo de inicio, código, nombre o estado.','id' => 'engorde-filter-content','reset' => 'resetFilters','loadingTarget' => 'periodo,anio,mes,fechaDesde,fechaHasta,search,estado,perPage']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('collapsible-filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasActiveFilters),'title' => 'Filtros de lotes','description' => 'Consulta lotes por periodo de inicio, código, nombre o estado.','id' => 'engorde-filter-content','reset' => 'resetFilters','loading-target' => 'periodo,anio,mes,fechaDesde,fechaHasta,search,estado,perPage']); ?>
        <div class="border-t border-zinc-850/60 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500 text-sm">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Buscar por código o nombre...">
                </div>
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Estado</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'estado','options' => ['' => 'Todos los estados', 'activo' => 'Activos', 'cerrado' => 'Cerrados'],'tone' => 'amber','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'estado','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos los estados', 'activo' => 'Activos', 'cerrado' => 'Cerrados']),'tone' => 'amber','live' => true,'compact' => true]); ?>
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
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Acceso rápido</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'periodo','options' => ['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual'],'tone' => 'amber','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'periodo','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']),'tone' => 'amber','live' => true,'compact' => true]); ?>
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
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Año</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'anio','options' => ['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all(),'tone' => 'amber','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'anio','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all()),'tone' => 'amber','live' => true,'compact' => true]); ?>
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
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Mes</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'mes','options' => ['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'],'tone' => 'amber','live' => true,'disabled' => $anio === '','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'mes','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre']),'tone' => 'amber','live' => true,'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($anio === ''),'compact' => true]); ?>
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
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Desde</label>
                <input type="date" wire:model.live="fechaDesde"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-amber-500/50 focus:ring-2 focus:ring-amber-500/20">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="fechaHasta"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-amber-500/50 focus:ring-2 focus:ring-amber-500/20">
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

    <!-- Lotes Table -->
    <div class="overflow-x-auto rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                    <th class="p-4 whitespace-nowrap">Código</th>
                    <th class="p-4 whitespace-nowrap">Foto</th>
                    <th class="p-4 whitespace-nowrap">Nombre del Lote</th>
                    <th class="p-4 whitespace-nowrap">Fecha Inicio</th>
                    <th class="p-4 whitespace-nowrap">Fecha Fin</th>
                    <th class="p-4 whitespace-nowrap">Animales</th>
                    <th class="p-4 whitespace-nowrap">Estado</th>
                    <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isRecent = $this->isRecentRecord('engorde.lotes', $lote->id);
                    ?>
                    <tr class="transition duration-500 <?php echo e($isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20'); ?>">
                        <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                            <?php echo e($lote->codigo); ?>

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
                            <?php if (isset($component)) { $__componentOriginald2dfeb7b2a0846e7004a57cacdbd2b2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald2dfeb7b2a0846e7004a57cacdbd2b2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-photo','data' => ['path' => $lote->foto_ruta,'frame' => $lote->foto_encuadre,'alt' => 'Foto del lote '.$lote->codigo]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-photo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lote->foto_ruta),'frame' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lote->foto_encuadre),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Foto del lote '.$lote->codigo)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald2dfeb7b2a0846e7004a57cacdbd2b2f)): ?>
<?php $attributes = $__attributesOriginald2dfeb7b2a0846e7004a57cacdbd2b2f; ?>
<?php unset($__attributesOriginald2dfeb7b2a0846e7004a57cacdbd2b2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald2dfeb7b2a0846e7004a57cacdbd2b2f)): ?>
<?php $component = $__componentOriginald2dfeb7b2a0846e7004a57cacdbd2b2f; ?>
<?php unset($__componentOriginald2dfeb7b2a0846e7004a57cacdbd2b2f); ?>
<?php endif; ?>
                        </td>
                        <td class="p-4 whitespace-nowrap"><?php echo e($lote->nombre ?? '-'); ?></td>
                        <td class="p-4 whitespace-nowrap"><?php echo e($lote->fecha_inicio->format('d/m/Y')); ?></td>
                        <td class="p-4 whitespace-nowrap"><?php echo e($lote->fecha_fin ? $lote->fecha_fin->format('d/m/Y') : 'En Curso'); ?></td>
                        <td class="p-4 font-semibold text-emerald-400 whitespace-nowrap"><?php echo e($lote->animales_count); ?> animales</td>
                        <td class="p-4 whitespace-nowrap">
                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['value' => $lote->estado]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lote->estado)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                            <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'view','href' => route('engorde.lote.show', $lote->id),'label' => 'Ver lote']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'view','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('engorde.lote.show', $lote->id)),'label' => 'Ver lote']); ?>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('engorde', 'actualizar')): ?>
                                <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'edit','href' => route('engorde.lote.edit', $lote->id)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'edit','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('engorde.lote.edit', $lote->id))]); ?>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('engorde', 'eliminar')): ?>
                                <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'delete','wire:click' => 'solicitarEliminacion('.e($lote->id).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'delete','wire:click' => 'solicitarEliminacion('.e($lote->id).')']); ?>
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
                    <tr>
                        <td colspan="8" class="p-12 text-center text-zinc-550">
                            <div class="text-3xl">📭</div>
                            <div class="mt-2 font-bold text-sm">No se encontraron lotes de engorde</div>
                            <div class="text-xs text-zinc-500 mt-1">Crea un nuevo lote para comenzar el registro.</div>
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'perPage','options' => ['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros'],'tone' => 'amber','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'perPage','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['10' => '10 registros', '25' => '25 registros', '50' => '50 registros', '100' => '100 registros']),'tone' => 'amber','live' => true,'compact' => true]); ?>
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
        <div class="min-w-0">
            <?php echo e($lotes->links('components.pagination')); ?>

        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showExportModal): ?>
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ columns: <?php echo \Illuminate\Support\Js::from($selectedColumns)->toHtml() ?> }"
                 role="dialog" aria-modal="true" aria-label="Exportar lotes de engorde"
                 class="agro-dialog agro-dialog--md agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Exportar lotes de engorde</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">PDF respetará filtros y orden actuales.</p>
                    </div>
                    <button wire:click="$set('showExportModal', false)" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div>
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Formato</span>
                    <div class="flex items-center gap-3 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm font-semibold text-rose-900 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-600 text-xs font-black text-white">PDF</span>
                        <span>Documento PDF (.pdf)</span>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Columnas</span>
                        <button type="button" x-on:click="columns = columns.length === <?php echo e(count($availableColumns)); ?> ? [] : <?php echo \Illuminate\Support\Js::from(array_keys($availableColumns))->toHtml() ?>" class="text-xs font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">Seleccionar todas</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label :class="columns.includes('<?php echo e($key); ?>') ? 'border-violet-500 bg-violet-100 text-violet-950 shadow-sm ring-1 ring-violet-500/20 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-slate-200 bg-white text-slate-600 hover:border-violet-300 hover:bg-violet-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-violet-400/10'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition">
                                <input type="checkbox" x-model="columns" value="<?php echo e($key); ?>" class="sr-only">
                                <span :class="columns.includes('<?php echo e($key); ?>') ? 'border-violet-700 bg-violet-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition">
                                    <svg x-cloak x-show="columns.includes('<?php echo e($key); ?>')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span><?php echo e($label); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedColumns'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedColumns.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-end">
                    <button wire:click="$set('showExportModal', false)" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300">Cancelar</button>
                    <button type="button" x-on:click="$wire.exportar(columns)" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-wait" wire:target="exportar"
                            class="rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:from-indigo-500 hover:to-blue-500 disabled:cursor-wait disabled:opacity-60">
                        Generar PDF
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDetailedReportModal): ?>
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ scope: <?php echo \Illuminate\Support\Js::from($detailedReportScope)->toHtml() ?>, lots: <?php echo \Illuminate\Support\Js::from(collect($detailedReportLotIds)->map(fn ($id) => (string) $id)->values())->toHtml() ?>, columns: <?php echo \Illuminate\Support\Js::from($detailedReportColumns)->toHtml() ?> }"
                 role="dialog" aria-modal="true" aria-label="Reporte general detallado"
                 class="agro-dialog agro-dialog--lg agro-dialog--scroll space-y-5 p-4 sm:p-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Reporte general detallado</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Agrupa lotes elegidos y muestra datos completos de cada animal en PDF horizontal.</p>
                </div>

                <div>
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Lotes a incluir</span>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label :class="scope === 'filtered' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 dark:bg-emerald-400/15 dark:text-emerald-100' : 'border-slate-200 dark:border-slate-700'" class="cursor-pointer rounded-xl border p-3 text-sm font-semibold">
                            <input type="radio" x-model="scope" value="filtered" class="sr-only"> Todos los resultados filtrados
                            <span class="mt-1 block text-xs font-normal opacity-65"><?php echo e($detailedReportLots->count()); ?> lotes disponibles</span>
                        </label>
                        <label :class="scope === 'selected' ? 'border-rose-500 bg-rose-50 text-rose-900 dark:bg-rose-400/15 dark:text-rose-100' : 'border-slate-200 dark:border-slate-700'" class="cursor-pointer rounded-xl border p-3 text-sm font-semibold">
                            <input type="radio" x-model="scope" value="selected" class="sr-only"> Escoger lotes
                            <span class="mt-1 block text-xs font-normal opacity-65" x-text="`${lots.length} seleccionados`"></span>
                        </label>
                    </div>
                </div>

                <div x-cloak x-show="scope === 'selected'" class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Selección de lotes</span>
                        <button type="button" x-on:click="lots = lots.length === <?php echo e($detailedReportLots->count()); ?> ? [] : <?php echo \Illuminate\Support\Js::from($detailedReportLots->pluck('id')->map(fn ($id) => (string) $id)->values())->toHtml() ?>" class="text-xs font-semibold text-rose-600 dark:text-rose-400">Seleccionar todos / limpiar</button>
                    </div>
                    <div class="grid max-h-52 gap-2 overflow-y-auto sm:grid-cols-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detailedReportLots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reportLot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label :class="lots.includes('<?php echo e($reportLot->id); ?>') ? 'border-rose-400 bg-rose-50 dark:bg-rose-400/10' : 'border-slate-200 dark:border-slate-700'" class="flex cursor-pointer items-center gap-3 rounded-lg border p-3">
                                <input type="checkbox" x-model="lots" value="<?php echo e($reportLot->id); ?>" class="agro-checkbox h-4 w-4 rounded">
                                <span class="min-w-0"><strong class="block text-sm text-slate-900 dark:text-white"><?php echo e($reportLot->codigo); ?></strong><small class="block truncate text-slate-500"><?php echo e($reportLot->nombre ?: 'Sin nombre'); ?> · <?php echo e($reportLot->animales_count); ?> animales</small></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['detailedReportLotIds'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Datos por animal</span>
                        <button type="button" x-on:click="columns = columns.length === <?php echo e(count($detailedReportAvailableColumns)); ?> ? [] : <?php echo \Illuminate\Support\Js::from(array_keys($detailedReportAvailableColumns))->toHtml() ?>" class="text-xs font-semibold text-violet-600 dark:text-violet-400">Seleccionar todas</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detailedReportAvailableColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label :class="columns.includes('<?php echo e($key); ?>') ? 'border-violet-500 bg-violet-100 text-violet-950 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium">
                                <input type="checkbox" x-model="columns" value="<?php echo e($key); ?>" class="sr-only">
                                <span :class="columns.includes('<?php echo e($key); ?>') ? 'border-violet-700 bg-violet-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2">
                                    <svg x-cloak x-show="columns.includes('<?php echo e($key); ?>')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span><?php echo e($label); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['detailedReportColumns'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['detailedReportColumns.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button wire:click="$set('showDetailedReportModal', false)" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300">Cancelar</button>
                    <button type="button" x-on:click="$wire.exportDetailedReport(scope, lots, columns)" wire:loading.attr="disabled" wire:target="exportDetailedReport" class="rounded-xl bg-rose-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-500 disabled:opacity-60">Generar PDF detallado</button>
                </div>
            </div>
        </div>
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
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/livewire/engorde/index.blade.php ENDPATH**/ ?>