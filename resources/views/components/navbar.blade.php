@php
    $currentUser = auth()->user();
    $ganaderiaActive = request()->routeIs('animal.*', 'engorde.*', 'monitoreo.*', 'medicamentos.*', 'insumos.*');
    $produccionActive = request()->routeIs('leche.*', 'queso.*', 'finanzas.*');
    $sistemaActive = request()->routeIs('ajustes.*', 'auditoria.*');

    $canGanaderia = $currentUser->tienePermiso('animal', 'leer')
        || $currentUser->tienePermiso('engorde', 'leer')
        || $currentUser->tienePermiso('monitoreo', 'leer')
        || $currentUser->tienePermiso('medicamentos', 'leer');

    $canProduccion = $currentUser->tienePermiso('leche', 'leer')
        || $currentUser->tienePermiso('queso', 'leer')
        || $currentUser->tienePermiso('finanzas', 'leer');

    $canSistema = $currentUser->tienePermiso('ajustes', 'leer')
        || $currentUser->tienePermiso('auditoria', 'leer')
        || $currentUser->tienePermiso('gestion_web', 'actualizar');

    $navItem = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition';
    $navIdle = 'text-emerald-950/65 hover:bg-emerald-50 hover:text-emerald-800 dark:text-emerald-100/60 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-100';
    $navActive = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-200';
@endphp

<nav x-data="{ ganaderiaOpen: false, produccionOpen: false, sistemaOpen: false, userOpen: false, mobileNavOpen: false }"
     @keydown.escape.window="ganaderiaOpen = produccionOpen = sistemaOpen = userOpen = mobileNavOpen = false"
     class="agro-navbar sticky top-0 z-50 border-b backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-[1600px] items-center justify-between gap-2 px-3 sm:px-4 lg:h-[70px] lg:px-6">
        
        {{-- Left: Brand + Desktop Menus --}}
        <div class="flex min-w-0 items-center gap-2 lg:gap-3 xl:gap-4">
            {{-- Brand Logo --}}
            <a wire:navigate href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2 sm:gap-2.5" aria-label="Ir al dashboard">
                <span class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border-[2.5px] border-emerald-600 dark:border-emerald-500 bg-zinc-950/10 shadow-sm overflow-hidden shrink-0">
                    <x-brand-logo class="h-full w-full" />
                </span>
                <span class="hidden min-[420px]:block">
                    <span data-brand-name class="agro-navbar__brand block text-sm sm:text-base font-extrabold leading-none tracking-tight">{{ $branding->name }}</span>
                    <span data-brand-tagline class="mt-0.5 hidden xl:block text-[9px] font-bold uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">{{ $branding->tagline }}</span>
                </span>
            </a>

            {{-- Desktop Navigation Links (Visible on lg and above) --}}
            <div class="hidden lg:flex items-center gap-1">
                {{-- Dashboard --}}
                <a wire:navigate href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-2 text-xs xl:text-sm font-semibold transition {{ request()->routeIs('dashboard') ? $navActive : $navIdle }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z" /></svg>
                    <span>Dashboard</span>
                </a>

                {{-- 1. Ganadería --}}
                @if($canGanaderia)
                <div class="relative" @click.outside="ganaderiaOpen = false">
                    <button type="button" @click="ganaderiaOpen = !ganaderiaOpen; produccionOpen = false; sistemaOpen = false; userOpen = false"
                            :aria-expanded="ganaderiaOpen" aria-haspopup="true"
                            class="inline-flex items-center gap-1 rounded-xl px-2.5 py-2 text-xs xl:text-sm font-semibold transition {{ $ganaderiaActive ? $navActive : $navIdle }}">
                        <span>Ganadería</span>
                        <svg class="h-3.5 w-3.5 shrink-0 transition" :class="ganaderiaOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                    </button>

                    <div x-cloak x-show="ganaderiaOpen" x-transition.origin.top.left
                         class="agro-navbar__surface absolute left-0 top-full mt-2 w-64 rounded-2xl border p-2 shadow-2xl shadow-emerald-950/15">
                        <p class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Manejo animal</p>
                        @if($currentUser->tienePermiso('animal', 'leer'))
                            <a wire:navigate href="{{ route('animal.index') }}" class="{{ $navItem }} {{ request()->routeIs('animal.*') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span><strong class="block">Animal</strong><small class="font-normal opacity-65">Inventario y fichas</small></span>
                            </a>
                        @endif
                        @if($currentUser->tienePermiso('engorde', 'leer'))
                            <a wire:navigate href="{{ route('engorde.index') }}" class="{{ $navItem }} {{ request()->routeIs('engorde.*') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-lime-500"></span>
                                <span><strong class="block">Engorde</strong><small class="font-normal opacity-65">Lotes y pesajes</small></span>
                            </a>
                        @endif
                        @if($currentUser->tienePermiso('monitoreo', 'leer'))
                            <a wire:navigate href="{{ route('monitoreo.index') }}" class="{{ $navItem }} {{ request()->routeIs('monitoreo.*') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                                <span><strong class="block">Monitoreo</strong><small class="font-normal opacity-65">Sanidad, partos y celos</small></span>
                            </a>
                        @endif
                        @if($currentUser->tienePermiso('medicamentos', 'leer'))
                            <a wire:navigate href="{{ route('medicamentos.index') }}" class="{{ $navItem }} {{ (request()->routeIs('medicamentos.*') || request()->routeIs('insumos.*')) ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span><strong class="block">Botiquín</strong><small class="font-normal opacity-65">Fármacos e insumos</small></span>
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- 2. Producción --}}
                @if($canProduccion)
                <div class="relative" @click.outside="produccionOpen = false">
                    <button type="button" @click="produccionOpen = !produccionOpen; ganaderiaOpen = false; sistemaOpen = false; userOpen = false"
                            :aria-expanded="produccionOpen" aria-haspopup="true"
                            class="inline-flex items-center gap-1 rounded-xl px-2.5 py-2 text-xs xl:text-sm font-semibold transition {{ $produccionActive ? $navActive : $navIdle }}">
                        <span>Producción</span>
                        <svg class="h-3.5 w-3.5 shrink-0 transition" :class="produccionOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                    </button>

                    <div x-cloak x-show="produccionOpen" x-transition.origin.top.left
                         class="agro-navbar__surface absolute left-0 top-full mt-2 w-64 rounded-2xl border p-2 shadow-2xl shadow-emerald-950/15">
                        <p class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Rendimiento y finanzas</p>
                        @if($currentUser->tienePermiso('leche', 'leer'))
                            <a wire:navigate href="{{ route('leche.index') }}" class="{{ $navItem }} {{ request()->routeIs('leche.*') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                <span><strong class="block">Leche</strong><small class="font-normal opacity-65">Ordeño diario</small></span>
                            </a>
                        @endif
                        @if($currentUser->tienePermiso('queso', 'leer'))
                            <a wire:navigate href="{{ route('queso.index') }}" class="{{ $navItem }} {{ request()->routeIs('queso.*') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                <span><strong class="block">Queso</strong><small class="font-normal opacity-65">Elaboración y derivados</small></span>
                            </a>
                        @endif
                        @if($currentUser->tienePermiso('finanzas', 'leer'))
                            <a wire:navigate href="{{ route('finanzas.index') }}" class="{{ $navItem }} {{ request()->routeIs('finanzas.*') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span><strong class="block">Finanzas</strong><small class="font-normal opacity-65">Ingresos y egresos</small></span>
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- 3. Sistema --}}
                @if($canSistema)
                <div class="relative" @click.outside="sistemaOpen = false">
                    <button type="button" @click="sistemaOpen = !sistemaOpen; ganaderiaOpen = false; produccionOpen = false; userOpen = false"
                            :aria-expanded="sistemaOpen" aria-haspopup="true"
                            class="inline-flex items-center gap-1 rounded-xl px-2.5 py-2 text-xs xl:text-sm font-semibold transition {{ $sistemaActive ? $navActive : $navIdle }}">
                        <span>Sistema</span>
                        <svg class="h-3.5 w-3.5 shrink-0 transition" :class="sistemaOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                    </button>

                    <div x-cloak x-show="sistemaOpen" x-transition.origin.top.left
                         class="agro-navbar__surface absolute left-0 top-full mt-2 w-64 rounded-2xl border p-2 shadow-2xl shadow-emerald-950/15">
                        <p class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Configuración y control</p>
                        @if($currentUser->tienePermiso('ajustes', 'leer'))
                            <a wire:navigate href="{{ route('ajustes.index') }}" class="{{ $navItem }} {{ request()->routeIs('ajustes.index') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-violet-400"></span>
                                <span><strong class="block">Ajustes</strong><small class="font-normal opacity-65">Usuarios y configuración</small></span>
                            </a>
                        @endif
                        @if($currentUser->tienePermiso('auditoria', 'leer'))
                            <a wire:navigate href="{{ route('auditoria.index') }}" class="{{ $navItem }} {{ request()->routeIs('auditoria.*') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                                <span><strong class="block">Auditoría</strong><small class="font-normal opacity-65">Trazabilidad y logs</small></span>
                            </a>
                        @endif
                        @if($currentUser->tienePermiso('gestion_web', 'actualizar'))
                            <a wire:navigate href="{{ route('ajustes.web') }}" class="{{ $navItem }} {{ request()->routeIs('ajustes.web') ? $navActive : $navIdle }}">
                                <span class="h-2 w-2 rounded-full bg-pink-400"></span>
                                <span><strong class="block">Gestión Web</strong><small class="font-normal opacity-65">Landing pública</small></span>
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Right Controls: Search, Fundo, Web, Theme, User, Hamburger --}}
        <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
            {{-- Buscador Global Directo --}}
            @if($currentUser->tienePermiso('buscador', 'leer'))
                <a wire:navigate href="{{ route('buscador') }}"
                   title="Buscador global (Presiona / para buscar)"
                   aria-label="Buscador global"
                   class="agro-navbar__control flex h-9 sm:h-10 items-center gap-1.5 rounded-xl px-2.5 sm:px-3 transition text-emerald-800 hover:bg-emerald-50 dark:text-emerald-200 dark:hover:bg-emerald-400/10 {{ request()->routeIs('buscador') ? 'bg-emerald-100 dark:bg-emerald-400/15' : '' }}">
                    <svg class="h-4 w-4 sm:h-4.5 sm:w-4.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.5-4.5m2-5.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                    </svg>
                    <span class="hidden 2xl:inline text-xs font-bold">Buscar</span>
                    <kbd class="hidden 2xl:inline rounded border border-emerald-300/50 bg-emerald-100/60 px-1 py-0.2 text-[10px] font-mono text-emerald-700 dark:border-emerald-700/50 dark:bg-emerald-950/60 dark:text-emerald-300">/</kbd>
                </a>
            @endif

            {{-- Fundo Badge (Compacto en xl, completo en 2xl) --}}
            <div class="hidden xl:flex max-w-[150px] 2xl:max-w-xs items-center gap-1.5 rounded-xl border border-emerald-200/70 bg-emerald-50 px-2.5 py-1.5 dark:border-emerald-400/15 dark:bg-emerald-400/5" title="{{ $currentUser->fundoActivo()?->nombre ?? 'Sin fundo' }}">
                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,.12)]"></span>
                <span class="truncate text-xs font-bold text-emerald-900 dark:text-emerald-100">{{ $currentUser->fundoActivo()?->nombre ?? 'Sin fundo' }}</span>
            </div>

            {{-- Sitio Web Público (Landing) --}}
            <a wire:navigate href="{{ route('home') }}"
               title="Ver sitio web público (Landing)"
               aria-label="Ver sitio web público"
               class="agro-navbar__control flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl transition text-emerald-800 hover:bg-emerald-50 dark:text-emerald-200 dark:hover:bg-emerald-400/10">
                <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a14.25 14.25 0 00-3.5 9 14.25 14.25 0 003.5 9 14.25 14.25 0 003.5-9A14.25 14.25 0 0012 3z" />
                </svg>
            </a>

            {{-- Dark / Light Mode --}}
            <x-theme-toggle
                btn-class="agro-navbar__control hidden sm:flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl transition"
                action="darkMode = !darkMode"
                icon-size="h-4.5 w-4.5 sm:h-5 sm:w-5"
            />

            {{-- User Menu --}}
            <div class="relative hidden lg:block" @click.outside="userOpen = false">
                <button type="button" @click="userOpen = !userOpen; ganaderiaOpen = produccionOpen = sistemaOpen = false"
                        class="flex items-center gap-1.5 rounded-xl p-1 transition hover:bg-emerald-50 dark:hover:bg-emerald-400/10" aria-label="Abrir menú de usuario">
                    <span class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-xl bg-emerald-700 text-xs sm:text-sm font-extrabold text-white shrink-0">
                        {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                    </span>
                    <span class="hidden 2xl:block max-w-24 text-left">
                        <strong class="block truncate text-xs text-emerald-950 dark:text-emerald-50">{{ $currentUser->name }}</strong>
                        <small class="block text-[9px] text-emerald-800/55 dark:text-emerald-200/55 leading-none">Mi cuenta</small>
                    </span>
                    <svg class="h-3 w-3 text-emerald-800/50 dark:text-emerald-200/50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>

                <div x-cloak x-show="userOpen" x-transition.origin.top.right class="agro-navbar__surface absolute right-0 top-full mt-2 w-64 rounded-2xl border p-2 shadow-2xl shadow-emerald-950/15">
                    <div class="border-b border-emerald-950/10 px-3 py-3 dark:border-emerald-200/10">
                        <p class="truncate text-sm font-bold text-emerald-950 dark:text-emerald-50">{{ $currentUser->name }}</p>
                        <p class="truncate text-xs text-emerald-800/55 dark:text-emerald-200/55">{{ $currentUser->email }}</p>
                    </div>
                    <a wire:navigate href="{{ route('profile') }}" class="mt-2 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-emerald-950/70 transition hover:bg-emerald-50 dark:text-emerald-100/70 dark:hover:bg-emerald-400/10">Mi perfil</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-400/10">Cerrar sesión</button></form>
                </div>
            </div>

            {{-- Mobile Nav Hamburger Toggle (Visible on < lg) --}}
            <button type="button" @click="mobileNavOpen = !mobileNavOpen" :aria-expanded="mobileNavOpen" aria-label="Abrir navegación"
                    class="agro-navbar__control flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl transition lg:hidden">
                <svg x-show="!mobileNavOpen" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" /></svg>
                <svg x-cloak x-show="mobileNavOpen" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>
    </div>

    {{-- Mobile Navigation Drawer --}}
    <div x-cloak x-show="mobileNavOpen" x-transition class="agro-navbar__mobile max-h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain border-t px-3 pb-5 pt-4 sm:px-4 lg:hidden">
        <div class="mx-auto max-w-2xl space-y-4">
            {{-- Fundo badge móvil --}}
            <div class="flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-3 dark:bg-emerald-400/5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Fundo activo</p>
                    <p class="text-sm font-bold text-emerald-950 dark:text-emerald-50">{{ $currentUser->fundoActivo()?->nombre ?? 'Sin selección' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="darkMode = !darkMode" type="button" class="rounded-xl border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-800 shadow-sm dark:border-emerald-400/20 dark:bg-zinc-900 dark:text-emerald-200">
                        <span x-text="darkMode ? 'Modo claro' : 'Modo oscuro'"></span>
                    </button>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="grid grid-cols-2 gap-2">
                <a wire:navigate href="{{ route('dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('dashboard') ? $navActive : $navIdle }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z" /></svg>
                    <span>Dashboard</span>
                </a>
                @if($currentUser->tienePermiso('buscador', 'leer'))
                    <a wire:navigate href="{{ route('buscador') }}" class="{{ $navItem }} {{ request()->routeIs('buscador') ? $navActive : $navIdle }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.5-4.5m2-5.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" /></svg>
                        <span>Buscador</span>
                    </a>
                @endif
            </div>

            {{-- Ganadería Móvil --}}
            @if($canGanaderia)
            <div>
                <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Ganadería</p>
                <div class="grid grid-cols-1 gap-2 min-[360px]:grid-cols-2">
                    @if($currentUser->tienePermiso('animal', 'leer'))<a wire:navigate href="{{ route('animal.index') }}" class="{{ $navItem }} {{ request()->routeIs('animal.*') ? $navActive : $navIdle }}">Animal</a>@endif
                    @if($currentUser->tienePermiso('engorde', 'leer'))<a wire:navigate href="{{ route('engorde.index') }}" class="{{ $navItem }} {{ request()->routeIs('engorde.*') ? $navActive : $navIdle }}">Engorde</a>@endif
                    @if($currentUser->tienePermiso('monitoreo', 'leer'))<a wire:navigate href="{{ route('monitoreo.index') }}" class="{{ $navItem }} {{ request()->routeIs('monitoreo.*') ? $navActive : $navIdle }}">Monitoreo</a>@endif
                    @if($currentUser->tienePermiso('medicamentos', 'leer'))<a wire:navigate href="{{ route('medicamentos.index') }}" class="{{ $navItem }} {{ (request()->routeIs('medicamentos.*') || request()->routeIs('insumos.*')) ? $navActive : $navIdle }}">Botiquín</a>@endif
                </div>
            </div>
            @endif

            {{-- Producción Móvil --}}
            @if($canProduccion)
            <div>
                <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Producción</p>
                <div class="grid grid-cols-1 gap-2 min-[360px]:grid-cols-2">
                    @if($currentUser->tienePermiso('leche', 'leer'))<a wire:navigate href="{{ route('leche.index') }}" class="{{ $navItem }} {{ request()->routeIs('leche.*') ? $navActive : $navIdle }}">Leche</a>@endif
                    @if($currentUser->tienePermiso('queso', 'leer'))<a wire:navigate href="{{ route('queso.index') }}" class="{{ $navItem }} {{ request()->routeIs('queso.*') ? $navActive : $navIdle }}">Queso</a>@endif
                    @if($currentUser->tienePermiso('finanzas', 'leer'))<a wire:navigate href="{{ route('finanzas.index') }}" class="{{ $navItem }} {{ request()->routeIs('finanzas.*') ? $navActive : $navIdle }}">Finanzas</a>@endif
                </div>
            </div>
            @endif

            {{-- Sistema Móvil --}}
            @if($canSistema)
            <div>
                <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">Sistema</p>
                <div class="grid grid-cols-1 gap-2 min-[360px]:grid-cols-2">
                    @if($currentUser->tienePermiso('ajustes', 'leer'))<a wire:navigate href="{{ route('ajustes.index') }}" class="{{ $navItem }} {{ request()->routeIs('ajustes.index') ? $navActive : $navIdle }}">Ajustes</a>@endif
                    @if($currentUser->tienePermiso('auditoria', 'leer'))<a wire:navigate href="{{ route('auditoria.index') }}" class="{{ $navItem }} {{ request()->routeIs('auditoria.*') ? $navActive : $navIdle }}">Auditoría</a>@endif
                    @if($currentUser->tienePermiso('gestion_web', 'actualizar'))<a wire:navigate href="{{ route('ajustes.web') }}" class="{{ $navItem }} {{ request()->routeIs('ajustes.web') ? $navActive : $navIdle }}">Gestión Web</a>@endif
                </div>
            </div>
            @endif

            {{-- User footer --}}
            <div class="flex items-center gap-2 border-t border-emerald-950/10 pt-4 dark:border-emerald-200/10">
                <a wire:navigate href="{{ route('profile') }}" class="flex-1 rounded-xl bg-emerald-50 px-4 py-2.5 text-center text-sm font-bold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200">Mi perfil</a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">@csrf<button type="submit" class="w-full rounded-xl bg-red-50 px-4 py-2.5 text-sm font-bold text-red-600 dark:bg-red-400/10 dark:text-red-300">Cerrar sesión</button></form>
            </div>
        </div>
    </div>
</nav>
