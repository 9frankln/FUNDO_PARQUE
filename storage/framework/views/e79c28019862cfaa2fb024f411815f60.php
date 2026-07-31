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
    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="agro-title text-3xl font-extrabold tracking-tight">
                Inventario Animal
            </h1>
            <p class="text-zinc-400 text-sm mt-1">Monitorea y gestiona los animales registrados en tu fundo.</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('animal', 'exportar')): ?>
                <button wire:click="$set('showExportModal', true)"
                    class="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-300 text-sm font-semibold transition duration-200 flex items-center gap-2">
                <span>&#x1F4E5;</span> Exportar
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('animal', 'crear')): ?>
                <a href="<?php echo e(route('animal.create')); ?>" class="agro-button">
                    <span>&#x2795;</span> Nuevo Animal
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Dynamic Animal Dashboard -->
    <?php if (isset($component)) { $__componentOriginal860cc82b439d3b2143872d177a7ccd5c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal860cc82b439d3b2143872d177a7ccd5c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.animal-dashboard','data' => ['data' => $dashboardData]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('animal-dashboard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dashboardData)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal860cc82b439d3b2143872d177a7ccd5c)): ?>
<?php $attributes = $__attributesOriginal860cc82b439d3b2143872d177a7ccd5c; ?>
<?php unset($__attributesOriginal860cc82b439d3b2143872d177a7ccd5c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal860cc82b439d3b2143872d177a7ccd5c)): ?>
<?php $component = $__componentOriginal860cc82b439d3b2143872d177a7ccd5c; ?>
<?php unset($__componentOriginal860cc82b439d3b2143872d177a7ccd5c); ?>
<?php endif; ?>

    <!-- Filters -->
    <?php if (isset($component)) { $__componentOriginal1d5c68e966293d924b0903e2b6a1abaf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1d5c68e966293d924b0903e2b6a1abaf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.collapsible-filters','data' => ['active' => $hasActiveFilters,'title' => 'Filtros de inventario','description' => 'Consulta animales por fecha de alta y características.','id' => 'animal-filter-content','reset' => 'resetFilters','loadingTarget' => 'periodo,anio,mes,fechaDesde,fechaHasta,search,especieId,razaId,genero,activo,motivoBaja,perPage,sort']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('collapsible-filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasActiveFilters),'title' => 'Filtros de inventario','description' => 'Consulta animales por fecha de alta y características.','id' => 'animal-filter-content','reset' => 'resetFilters','loading-target' => 'periodo,anio,mes,fechaDesde,fechaHasta,search,especieId,razaId,genero,activo,motivoBaja,perPage,sort']); ?>
        <div class="border-t border-zinc-850/60 pt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 items-end">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Buscar</label>
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500 text-sm">&#x1F50D;</span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 py-1.5 pl-10 pr-4 text-sm text-zinc-100 outline-none transition placeholder:text-zinc-500 focus:border-emerald-500/50 focus:ring-emerald-500/20"
                           placeholder="Buscar código o nombre...">
                </div>
            </div>
            <div>
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Especie</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'especieId','options' => ['' => 'Todas'] + collect($especies)->pluck('nombre', 'id')->all(),'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'especieId','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todas'] + collect($especies)->pluck('nombre', 'id')->all()),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Raza</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'razaId','options' => ['' => 'Todas'] + collect($razas)->pluck('nombre', 'id')->all(),'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'razaId','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todas'] + collect($razas)->pluck('nombre', 'id')->all()),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Género</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'genero','options' => ['' => 'Todos', 'macho' => 'Macho', 'hembra' => 'Hembra'],'tone' => 'sky','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'genero','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos', 'macho' => 'Macho', 'hembra' => 'Hembra']),'tone' => 'sky','live' => true,'compact' => true]); ?>
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
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Estado</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'activo','options' => ['' => 'Todo el inventario', '1' => 'En inventario', '0' => 'Dados de baja'],'tone' => 'rose','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'activo','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todo el inventario', '1' => 'En inventario', '0' => 'Dados de baja']),'tone' => 'rose','live' => true,'compact' => true]); ?>
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
                <label class="block text-zinc-500 text-[10px] font-bold uppercase tracking-wider mb-1">Motivo de baja</label>
                <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'motivoBaja','options' => ['' => 'Todos los motivos'] + \App\Models\Animal::INACTIVE_REASONS,'tone' => 'amber','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'motivoBaja','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos los motivos'] + \App\Models\Animal::INACTIVE_REASONS),'tone' => 'amber','live' => true,'compact' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'periodo','options' => ['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual'],'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'periodo','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todo el historial', 'hoy' => 'Hoy', 'ultimos_7_dias' => 'Últimos 7 días', 'semana_actual' => 'Semana actual', 'mes_actual' => 'Mes actual', 'mes_anterior' => 'Mes anterior', 'trimestre_actual' => 'Trimestre actual', 'anio_actual' => 'Año actual']),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'anio','options' => ['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all(),'tone' => 'emerald','live' => true,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'anio','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos los años'] + collect($availableYears)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->all()),'tone' => 'emerald','live' => true,'compact' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['model' => 'mes','options' => ['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'],'tone' => 'emerald','live' => true,'disabled' => $anio === '','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['model' => 'mes','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['' => 'Todos los meses', '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre']),'tone' => 'emerald','live' => true,'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($anio === ''),'compact' => true]); ?>
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
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Hasta</label>
                <input type="date" wire:model.live="fechaHasta"
                       class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-200 outline-none transition focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20">
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

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl bg-zinc-900 border border-zinc-800 shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-950 text-zinc-400 text-xs font-bold uppercase tracking-wider border-b border-zinc-800">
                        <th class="p-4 cursor-pointer hover:text-zinc-200 transition whitespace-nowrap" wire:click="sort('arete')">
                            Código <?php echo $sortBy === 'arete' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : ''; ?>

                        </th>
                        <th class="p-4 whitespace-nowrap">Foto</th>
                        <th class="p-4 cursor-pointer hover:text-zinc-200 transition whitespace-nowrap" wire:click="sort('nombre')">
                            Nombre <?php echo $sortBy === 'nombre' ? ($sortDir === 'asc' ? '&#x25B4;' : '&#x25BE;') : ''; ?>

                        </th>
                        <th class="p-4 whitespace-nowrap">Tipo / raza</th>
                        <th class="p-4 whitespace-nowrap">Sexo</th>
                        <th class="p-4 whitespace-nowrap">Edad</th>
                        <th class="p-4 whitespace-nowrap">Estado del inventario</th>
                        <th class="p-4 text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850/60 text-sm text-zinc-300">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $animal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isRecent = $this->isRecentRecord('animal.animales', $animal->id);
                        ?>
                        <tr class="transition duration-500 <?php echo e($isRecent ? 'bg-emerald-500/10' : 'hover:bg-zinc-850/20'); ?>">
                            <td class="p-4 font-bold text-zinc-100 whitespace-nowrap">
                                <a href="<?php echo e(route('animal.show', $animal->id)); ?>"
                                   class="rounded font-bold text-zinc-100 transition hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:hover:text-emerald-300"
                                   title="Ver ficha de <?php echo e($animal->arete); ?>">
                                    <?php echo e($animal->arete); ?>

                                </a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-photo','data' => ['path' => $animal->foto_ruta,'frame' => $animal->foto_encuadre,'alt' => 'Foto de '.($animal->nombre ?: $animal->arete)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-photo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($animal->foto_ruta),'frame' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($animal->foto_encuadre),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Foto de '.($animal->nombre ?: $animal->arete))]); ?>
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
                            <td class="p-4 whitespace-nowrap">
                                <a href="<?php echo e(route('animal.show', $animal->id)); ?>"
                                   class="rounded font-semibold text-zinc-200 transition hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:hover:text-emerald-300"
                                   title="Ver ficha de <?php echo e($animal->nombre ?: $animal->arete); ?>">
                                    <?php echo e($animal->nombre ?? '-'); ?>

                                </a>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="block font-semibold text-zinc-200"><?php echo e($animal->especie->nombre ?? '-'); ?></span>
                                <span class="mt-0.5 block text-[10px] text-zinc-500"><?php echo e($animal->raza->nombre ?? '-'); ?></span>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['value' => $animal->genero]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($animal->genero)]); ?>
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
                                <span class="block font-semibold text-zinc-200"><?php echo e($animal->clasificacion_edad); ?></span>
                                <span class="mt-0.5 block text-[10px] text-zinc-500"><?php echo e($animal->edad_texto); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($animal->denticion_estimada): ?>
                                    <span class="mt-0.5 block text-[10px] text-amber-600 dark:text-amber-400"><?php echo e($animal->denticion_estimada); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="min-w-56 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['value' => $animal->activo ? 'activo' : 'inactivo','label' => $animal->activo ? 'En inventario' : 'Dado de baja']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($animal->activo ? 'activo' : 'inactivo'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($animal->activo ? 'En inventario' : 'Dado de baja')]); ?>
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
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($animal->activo)): ?>
                                            <p class="mt-1.5 text-xs font-bold text-zinc-300"><?php echo e($animal->motivo_baja_label); ?></p>
                                            <p class="mt-0.5 text-[10px] text-zinc-500">
                                                <?php echo e($animal->fecha_baja?->format('d/m/Y') ?? 'Fecha no registrada'); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($animal->comprador_baja): ?> · <?php echo e($animal->comprador_baja); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('animal', 'actualizar')): ?>
                                        <button type="button" wire:click="openStatusModal(<?php echo e($animal->id); ?>)"
                                                class="inline-flex h-8 shrink-0 items-center rounded-lg border px-2.5 text-[10px] font-black uppercase tracking-wide transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 <?php echo e($animal->activo ? 'border-amber-500/30 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20' : 'border-sky-500/30 bg-sky-500/10 text-sky-300 hover:bg-sky-500/20'); ?>">
                                            Gestionar
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'view','href' => route('animal.show', $animal->id),'label' => 'Ver ficha']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'view','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('animal.show', $animal->id)),'label' => 'Ver ficha']); ?>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('animal', 'actualizar')): ?>
                                    <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'edit','href' => route('animal.edit', $animal->id)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'edit','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('animal.edit', $animal->id))]); ?>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->tienePermiso('animal', 'eliminar')): ?>
                                    <?php if (isset($component)) { $__componentOriginal3cb096f2e62c7df2672a776d39e07de4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3cb096f2e62c7df2672a776d39e07de4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-action','data' => ['type' => 'delete','wire:click' => 'solicitarEliminacion('.e($animal->id).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'delete','wire:click' => 'solicitarEliminacion('.e($animal->id).')']); ?>
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
                            <td colspan="8" class="p-12 text-center text-zinc-500">
                                <div class="text-3xl">&#x1F4ED;</div>
                                <div class="mt-2 font-bold text-sm">No se encontraron animales</div>
                                <div class="text-xs text-zinc-500 mt-1">Intenta ajustando los filtros o añade uno nuevo.</div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="agro-table-footer">
        <div class="agro-table-size">
            <span>Mostrar</span>
            <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
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
<?php endif; ?>
        </div>
        <div class="min-w-0">
            <?php echo e($animales->links('components.pagination')); ?>

        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showStatusModal && $statusAnimal): ?>
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
             class="agro-dialog-overlay">
            <div role="dialog" aria-modal="true" aria-labelledby="animal-status-title"
                 class="agro-dialog agro-dialog--md agro-dialog--scroll">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-400">Control de inventario</p>
                        <h3 id="animal-status-title" class="mt-1 text-xl font-black text-zinc-900 dark:text-white">
                            <?php echo e($statusAnimal->activo ? 'Registrar baja' : 'Revisar baja'); ?>

                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            <?php echo e($statusAnimal->arete); ?><?php echo e($statusAnimal->nombre ? ' · '.$statusAnimal->nombre : ''); ?>

                        </p>
                    </div>
                    <button type="button" wire:click="closeStatusModal" aria-label="Cerrar"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAnimal->activo): ?>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
                            La baja retirará al animal del stock, ordeño y engorde activo. Su historial se conserva.
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Motivo de baja <span class="text-rose-500">*</span></label>
                            <select wire:model.live="statusReason"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                <option value="">Selecciona un motivo...</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\Animal::INACTIVE_REASONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($reason); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['statusReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1.5 text-xs font-semibold text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Fecha de baja <span class="text-rose-500">*</span></label>
                            <input type="date" wire:model="statusDate" max="<?php echo e(today()->format('Y-m-d')); ?>"
                                   class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['statusDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1.5 text-xs font-semibold text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusReason === 'venta'): ?>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <p class="text-sm font-black text-emerald-900 dark:text-emerald-100">Venta enlazada con Finanzas</p>
                                <p class="mt-1 text-xs leading-5 text-emerald-700 dark:text-emerald-300">Continuarás al formulario de ingreso con este animal y “Venta de Animales” preseleccionados. La baja se aplicará únicamente cuando el movimiento se guarde correctamente.</p>
                            </div>
                        <?php else: ?>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                    Detalle <?php echo e($statusReason === 'otro' ? '*' : '(opcional)'); ?>

                                </label>
                                <textarea wire:model="statusDetail" rows="3" maxlength="255"
                                          class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                          placeholder="Información breve para la trazabilidad"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['statusDetail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1.5 text-xs font-semibold text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-zinc-100 p-4 dark:bg-zinc-800/70">
                                <dt class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Motivo</dt>
                                <dd class="mt-1 font-bold text-zinc-900 dark:text-white"><?php echo e($statusAnimal->motivo_baja_label); ?></dd>
                            </div>
                            <div class="rounded-xl bg-zinc-100 p-4 dark:bg-zinc-800/70">
                                <dt class="text-[10px] font-black uppercase tracking-wider text-zinc-500">Fecha</dt>
                                <dd class="mt-1 font-bold text-zinc-900 dark:text-white"><?php echo e($statusAnimal->fecha_baja?->format('d/m/Y') ?? 'No registrada'); ?></dd>
                            </div>
                        </dl>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAnimal->detalle_baja): ?>
                            <p class="rounded-xl border border-zinc-200 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300"><?php echo e($statusAnimal->detalle_baja); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAnimal->motivo_baja === 'venta' && $statusAnimal->movimientoVenta): ?>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <p class="text-sm font-black text-emerald-900 dark:text-emerald-100">Venta financiera vinculada</p>
                                <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">
                                    Movimiento #<?php echo e(str_pad((string) $statusAnimal->movimientoVenta->id, 6, '0', STR_PAD_LEFT)); ?>

                                    · S/. <?php echo e(number_format((float) $statusAnimal->movimientoVenta->monto, 2)); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAnimal->comprador_baja): ?> · <?php echo e($statusAnimal->comprador_baja); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <p class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-800 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-200">Si la baja fue un error, puedes devolver el animal al inventario activo.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['statusReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs font-semibold text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:flex-row sm:justify-end sm:px-6">
                    <button type="button" wire:click="closeStatusModal"
                            class="h-11 rounded-xl border border-zinc-300 px-5 text-sm font-bold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Cancelar
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAnimal->activo): ?>
                        <button type="button" wire:click="confirmStatusChange" wire:loading.attr="disabled" wire:target="confirmStatusChange"
                                class="h-11 rounded-xl bg-amber-500 px-6 text-sm font-black text-zinc-950 transition hover:bg-amber-400 disabled:opacity-60">
                            <?php echo e($statusReason === 'venta' ? 'Continuar a Finanzas' : 'Confirmar baja'); ?>

                        </button>
                    <?php elseif(!($statusAnimal->motivo_baja === 'venta' && $statusAnimal->movimientoVenta)): ?>
                        <button type="button" wire:click="confirmStatusChange" wire:loading.attr="disabled" wire:target="confirmStatusChange"
                                class="h-11 rounded-xl bg-sky-500 px-6 text-sm font-black text-white transition hover:bg-sky-400 disabled:opacity-60">
                            Reactivar animal
                        </button>
                    <?php else: ?>
                        <a href="<?php echo e(route('finanzas.movimiento.show', $statusAnimal->movimientoVenta)); ?>"
                           class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-500 px-6 text-sm font-black text-zinc-950 transition hover:bg-emerald-400">
                            Ver movimiento
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Export Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showExportModal): ?>
        <div x-data x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')" class="agro-dialog-overlay">
            <div x-data="{ format: <?php echo \Illuminate\Support\Js::from($exportFormat)->toHtml() ?>, columns: <?php echo \Illuminate\Support\Js::from($selectedColumns)->toHtml() ?> }"
                role="dialog" aria-modal="true" aria-label="Exportar inventario animal"
                class="agro-dialog agro-dialog--md agro-dialog--scroll space-y-6 p-4 sm:p-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Exportar Inventario Animal</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">PDF horizontal o Excel. Ambos respetan filtros, orden y fundo activo.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Formato</span>
                        <div class="grid grid-cols-1 gap-3 min-[400px]:grid-cols-2">
                            <label :class="format === 'xlsx' ? 'border-sky-500 bg-sky-100 text-sky-950 shadow-sm shadow-sky-500/20 dark:border-sky-400 dark:bg-sky-400/20 dark:text-sky-50' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-sky-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'" class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 text-sm font-semibold transition">
                                <input type="radio" x-model="format" value="xlsx" class="sr-only">
                                <span :class="format === 'xlsx' ? 'border-sky-600 bg-sky-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2">
                                    <svg x-cloak x-show="format === 'xlsx'" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>Excel (.xlsx)</span>
                            </label>
                            <label :class="format === 'pdf' ? 'border-rose-500 bg-rose-100 text-rose-950 shadow-sm shadow-rose-500/20 dark:border-rose-400 dark:bg-rose-400/20 dark:text-rose-50' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-rose-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'" class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 text-sm font-semibold transition">
                                <input type="radio" x-model="format" value="pdf" class="sr-only">
                                <span :class="format === 'pdf' ? 'border-rose-600 bg-rose-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2">
                                    <svg x-cloak x-show="format === 'pdf'" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                </span>
                                <span>PDF (.pdf)</span>
                            </label>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['exportFormat'];
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
                            <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Columnas</span>
                            <button type="button" x-on:click="columns = columns.length === <?php echo e(count($availableColumns)); ?> ? [] : <?php echo \Illuminate\Support\Js::from(array_keys($availableColumns))->toHtml() ?>" class="text-xs font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">Seleccionar todas</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label :class="columns.includes('<?php echo e($key); ?>') ? 'border-violet-500 bg-violet-100 text-violet-950 shadow-sm ring-1 ring-violet-500/20 dark:border-violet-400 dark:bg-violet-400/20 dark:text-violet-50' : 'border-slate-200 bg-white text-slate-600 hover:border-violet-300 hover:bg-violet-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-violet-400/10'" class="flex cursor-pointer items-center gap-2 rounded-lg border p-2 text-xs font-medium transition">
                                    <input type="checkbox" x-model="columns" value="<?php echo e($key); ?>" class="sr-only">
                                    <span :class="columns.includes('<?php echo e($key); ?>') ? 'border-violet-700 bg-violet-600' : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition">
                                        <svg x-cloak x-show="columns.includes('<?php echo e($key); ?>')" class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                    </span>
                                    <span><?php echo e($label); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">Estado reproductivo y precio de compra son opcionales. Precio solo aplica a animales comprados.</p>
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
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-end">
                    <button wire:click="$set('showExportModal', false)"
                            class="px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:border-rose-300 hover:bg-rose-100 text-rose-700 dark:border-rose-400/30 dark:bg-rose-400/10 dark:hover:bg-rose-400/20 dark:text-rose-300 text-sm font-semibold transition">
                        Cancelar
                    </button>
                    <button type="button" x-on:click="$wire.exportar(format, columns)" wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-wait" wire:target="exportar"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/20 transition disabled:cursor-wait">
                        Generar Reporte
                    </button>
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
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/livewire/animal/index.blade.php ENDPATH**/ ?>