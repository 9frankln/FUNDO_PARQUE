<?php
    $currentUser = auth()->user();
    $operationsActive = request()->routeIs('animal.*', 'engorde.*', 'leche.*', 'queso.*', 'monitoreo.*');
    $managementActive = request()->routeIs('finanzas.*', 'buscador', 'ajustes.*', 'auditoria.*');
    $canOperations = $currentUser->tienePermiso('animal', 'leer') || $currentUser->tienePermiso('engorde', 'leer') || $currentUser->tienePermiso('leche', 'leer') || $currentUser->tienePermiso('queso', 'leer') || $currentUser->tienePermiso('monitoreo', 'leer');
    $canManagement = $currentUser->tienePermiso('finanzas', 'leer') || $currentUser->tienePermiso('buscador', 'leer') || $currentUser->tienePermiso('ajustes', 'leer') || $currentUser->tienePermiso('auditoria', 'leer') || $currentUser->tienePermiso('gestion_web', 'actualizar');
    $navItem = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition';
    $navIdle = 'text-emerald-950/65 hover:bg-emerald-50 hover:text-emerald-800 dark:text-emerald-100/60 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-100';
    $navActive = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-200';
?>

<nav x-data="{ operationsOpen: false, managementOpen: false, userOpen: false }"
     @keydown.escape.window="operationsOpen = managementOpen = userOpen = mobileNavOpen = false"
     class="agro-navbar sticky top-0 z-50 border-b backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-[1600px] items-center gap-4 px-4 sm:px-6 lg:h-[73px] lg:px-8">
        <a href="<?php echo e(route('dashboard')); ?>" class="flex shrink-0 items-center gap-3" aria-label="Ir al dashboard">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-700 text-white shadow-lg shadow-emerald-700/20">
                <?php if (isset($component)) { $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-logo','data' => ['class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $attributes = $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $component = $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
            </span>
            <span class="hidden sm:block">
                <span data-brand-name class="agro-navbar__brand block text-base font-extrabold leading-none tracking-tight"><?php echo e($branding->name); ?></span>
                <span data-brand-tagline class="mt-1 block text-[10px] font-bold uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300"><?php echo e($branding->tagline); ?></span>
            </span>
        </a>

        <div class="ml-4 hidden h-full items-center gap-1 lg:flex">
            <a href="<?php echo e(route('dashboard')); ?>"
               class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition <?php echo e(request()->routeIs('dashboard') ? $navActive : $navIdle); ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z" /></svg>
                Dashboard
            </a>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canOperations): ?>
            <div class="relative" @click.outside="operationsOpen = false">
                <button type="button" @click="operationsOpen = !operationsOpen; managementOpen = false; userOpen = false"
                        :aria-expanded="operationsOpen" aria-haspopup="true"
                        class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition <?php echo e($operationsActive ? $navActive : $navIdle); ?>">
                    Operaciones
                    <svg class="h-3.5 w-3.5 transition" :class="operationsOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>

                <div x-cloak x-show="operationsOpen" x-transition.origin.top.left
                     class="agro-navbar__surface absolute left-0 top-full mt-3 w-64 rounded-2xl border p-2 shadow-2xl shadow-emerald-950/15">
                    <p class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Producción e inventario</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('animal', 'leer')): ?>
                        <a href="<?php echo e(route('animal.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('animal.*') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-emerald-500"></span><span><strong class="block">Animal</strong><small class="font-normal opacity-65">Inventario y fichas</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('engorde', 'leer')): ?>
                        <a href="<?php echo e(route('engorde.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('engorde.*') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-lime-500"></span><span><strong class="block">Engorde</strong><small class="font-normal opacity-65">Lotes y pesajes</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('leche', 'leer')): ?>
                        <a href="<?php echo e(route('leche.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('leche.*') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-cyan-500"></span><span><strong class="block">Leche</strong><small class="font-normal opacity-65">Ordeños diarios</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('queso', 'leer')): ?>
                        <a href="<?php echo e(route('queso.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('queso.*') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-amber-400"></span><span><strong class="block">Queso</strong><small class="font-normal opacity-65">Producción y rendimiento</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('monitoreo', 'leer')): ?>
                        <a href="<?php echo e(route('monitoreo.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('monitoreo.*') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-rose-400"></span><span><strong class="block">Monitoreo</strong><small class="font-normal opacity-65">Sanidad y alertas</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManagement): ?>
            <div class="relative" @click.outside="managementOpen = false">
                <button type="button" @click="managementOpen = !managementOpen; operationsOpen = false; userOpen = false"
                        :aria-expanded="managementOpen" aria-haspopup="true"
                        class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition <?php echo e($managementActive ? $navActive : $navIdle); ?>">
                    Administración
                    <svg class="h-3.5 w-3.5 transition" :class="managementOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>

                <div x-cloak x-show="managementOpen" x-transition.origin.top.left
                     class="agro-navbar__surface absolute left-0 top-full mt-3 w-64 rounded-2xl border p-2 shadow-2xl shadow-emerald-950/15">
                    <p class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Control del fundo</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('finanzas', 'leer')): ?>
                        <a href="<?php echo e(route('finanzas.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('finanzas.*') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-emerald-500"></span><span><strong class="block">Finanzas</strong><small class="font-normal opacity-65">Ingresos y egresos</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('buscador', 'leer')): ?>
                        <a href="<?php echo e(route('buscador')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('buscador') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-sky-400"></span><span><strong class="block">Buscador</strong><small class="font-normal opacity-65">Consulta global</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('ajustes', 'leer')): ?>
                        <a href="<?php echo e(route('ajustes.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('ajustes.index') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-violet-400"></span><span><strong class="block">Ajustes</strong><small class="font-normal opacity-65">Usuarios y configuración</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('auditoria', 'leer')): ?>
                        <a href="<?php echo e(route('auditoria.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('auditoria.*') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-indigo-400"></span><span><strong class="block">Auditoría</strong><small class="font-normal opacity-65">Actividad y sesiones</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('gestion_web', 'actualizar')): ?>
                        <a href="<?php echo e(route('ajustes.web')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('ajustes.web') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-pink-400"></span><span><strong class="block">Gestión Web</strong><small class="font-normal opacity-65">Landing pública</small></span></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <div class="hidden max-w-[180px] items-center gap-2 rounded-xl border border-emerald-200/70 bg-emerald-50 px-3 py-2 lg:flex lg:max-w-xs xl:max-w-sm dark:border-emerald-400/15 dark:bg-emerald-400/5" title="<?php echo e($currentUser->fundoActivo()?->nombre ?? 'Sin fundo'); ?>">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,.12)]"></span>
                <span class="truncate text-xs font-bold text-emerald-900 dark:text-emerald-100"><?php echo e($currentUser->fundoActivo()?->nombre ?? 'Sin fundo'); ?></span>
            </div>

            <button @click="darkMode = !darkMode" type="button" aria-label="Alternar tema"
                    class="agro-navbar__control hidden h-10 w-10 items-center justify-center rounded-xl transition sm:flex">
                <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.4-6.4-.7.7M6.3 17.7l-.7.7m0-12.8.7.7m11.4 11.4.7.7M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                <svg x-cloak x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.5 15.8A8.5 8.5 0 0 1 8.2 3.5 8.5 8.5 0 1 0 20.5 15.8Z" /></svg>
            </button>

            <div class="relative hidden lg:block" @click.outside="userOpen = false">
                <button type="button" @click="userOpen = !userOpen; operationsOpen = managementOpen = false"
                        class="flex items-center gap-2 rounded-xl p-1.5 pr-2 transition hover:bg-emerald-50 dark:hover:bg-emerald-400/10" aria-label="Abrir menú de usuario">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-700 text-sm font-extrabold text-white"><?php echo e(strtoupper(substr($currentUser->name, 0, 1))); ?></span>
                    <span class="hidden max-w-28 text-left xl:block"><strong class="block truncate text-xs text-emerald-950 dark:text-emerald-50"><?php echo e($currentUser->name); ?></strong><small class="block text-[10px] text-emerald-800/55 dark:text-emerald-200/55">Mi cuenta</small></span>
                    <svg class="h-3.5 w-3.5 text-emerald-800/50 dark:text-emerald-200/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>

                <div x-cloak x-show="userOpen" x-transition.origin.top.right class="agro-navbar__surface absolute right-0 top-full mt-3 w-64 rounded-2xl border p-2 shadow-2xl shadow-emerald-950/15">
                    <div class="border-b border-emerald-950/10 px-3 py-3 dark:border-emerald-200/10"><p class="truncate text-sm font-bold text-emerald-950 dark:text-emerald-50"><?php echo e($currentUser->name); ?></p><p class="truncate text-xs text-emerald-800/55 dark:text-emerald-200/55"><?php echo e($currentUser->email); ?></p></div>
                    <a href="<?php echo e(route('profile')); ?>" class="mt-2 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-emerald-950/70 transition hover:bg-emerald-50 dark:text-emerald-100/70 dark:hover:bg-emerald-400/10">Mi perfil</a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-400/10">Cerrar sesión</button></form>
                </div>
            </div>

            <button type="button" @click="mobileNavOpen = !mobileNavOpen" :aria-expanded="mobileNavOpen" aria-label="Abrir navegación"
                    class="agro-navbar__control flex h-10 w-10 items-center justify-center rounded-xl transition lg:hidden">
                <svg x-show="!mobileNavOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" /></svg>
                <svg x-cloak x-show="mobileNavOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>
    </div>

    <div x-cloak x-show="mobileNavOpen" x-transition class="agro-navbar__mobile max-h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain border-t px-3 pb-5 pt-4 sm:px-4 lg:hidden">
        <div class="mx-auto max-w-2xl">
            <div class="mb-4 flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-3 dark:bg-emerald-400/5">
                <div><p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Fundo activo</p><p class="text-sm font-bold text-emerald-950 dark:text-emerald-50"><?php echo e($currentUser->fundoActivo()?->nombre ?? 'Sin selección'); ?></p></div>
                <button @click="darkMode = !darkMode" type="button" class="rounded-xl border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-800 dark:border-emerald-400/20 dark:text-emerald-200"><span x-text="darkMode ? 'Modo claro' : 'Modo oscuro'"></span></button>
            </div>

            <a href="<?php echo e(route('dashboard')); ?>" class="<?php echo e($navItem); ?> mb-3 <?php echo e(request()->routeIs('dashboard') ? $navActive : $navIdle); ?>"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Dashboard</a>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canOperations): ?>
            <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Operaciones</p>
            <div class="grid grid-cols-1 gap-2 min-[360px]:grid-cols-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('animal', 'leer')): ?><a href="<?php echo e(route('animal.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('animal.*') ? $navActive : $navIdle); ?>">Animal</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('engorde', 'leer')): ?><a href="<?php echo e(route('engorde.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('engorde.*') ? $navActive : $navIdle); ?>">Engorde</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('leche', 'leer')): ?><a href="<?php echo e(route('leche.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('leche.*') ? $navActive : $navIdle); ?>">Leche</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('queso', 'leer')): ?><a href="<?php echo e(route('queso.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('queso.*') ? $navActive : $navIdle); ?>">Queso</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('monitoreo', 'leer')): ?><a href="<?php echo e(route('monitoreo.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('monitoreo.*') ? $navActive : $navIdle); ?>">Monitoreo</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManagement): ?>
            <p class="mb-2 mt-5 px-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Administración</p>
            <div class="grid grid-cols-1 gap-2 min-[360px]:grid-cols-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('finanzas', 'leer')): ?><a href="<?php echo e(route('finanzas.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('finanzas.*') ? $navActive : $navIdle); ?>">Finanzas</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('buscador', 'leer')): ?><a href="<?php echo e(route('buscador')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('buscador') ? $navActive : $navIdle); ?>">Buscador</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('ajustes', 'leer')): ?><a href="<?php echo e(route('ajustes.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('ajustes.index') ? $navActive : $navIdle); ?>">Ajustes</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('auditoria', 'leer')): ?><a href="<?php echo e(route('auditoria.index')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('auditoria.*') ? $navActive : $navIdle); ?>">Auditoría</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser->tienePermiso('gestion_web', 'actualizar')): ?><a href="<?php echo e(route('ajustes.web')); ?>" class="<?php echo e($navItem); ?> <?php echo e(request()->routeIs('ajustes.web') ? $navActive : $navIdle); ?>">Gestión Web</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-5 flex items-center gap-2 border-t border-emerald-950/10 pt-4 dark:border-emerald-200/10">
                <a href="<?php echo e(route('profile')); ?>" class="flex-1 rounded-xl bg-emerald-50 px-4 py-2.5 text-center text-sm font-bold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">Mi perfil</a>
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="flex-1"><?php echo csrf_field(); ?><button type="submit" class="w-full rounded-xl bg-red-50 px-4 py-2.5 text-sm font-bold text-red-600 dark:bg-red-400/10 dark:text-red-300">Cerrar sesión</button></form>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/navbar.blade.php ENDPATH**/ ?>