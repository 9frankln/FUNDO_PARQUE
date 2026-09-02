@props(['data' => []])

@if(empty($data) || empty($data['welcome']))
    <div class="rounded-3xl border border-zinc-200 bg-white p-12 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M8 9h.01M12 9h.01M16 9h.01"/>
            </svg>
        </div>
        <h2 class="mt-4 text-xl font-bold text-zinc-950 dark:text-white">Selecciona un fundo</h2>
        <p class="mt-2 text-sm text-zinc-500">Debes tener un fundo activo seleccionado para ver el centro de operaciones.</p>
    </div>
@else
<div
    wire:key="global-dashboard"
    wire:poll.300s.visible="loadStats"
    x-data="globalDashboard(@js($data))"
    class="space-y-6"
>
    <!-- Hero Banner -->
    <section class="agro-dashboard-hero relative overflow-hidden rounded-[2rem] border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8 xl:p-10">
        <div class="relative grid gap-8 xl:grid-cols-[1.45fr_.8fr]">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[.2em] text-emerald-700 dark:text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Centro de operaciones
                    </span>
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $data['welcome']['date'] }}</span>
                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-4xl lg:text-5xl">
                    {{ $data['welcome']['greeting'] }}, {{ $data['welcome']['name'] }}
                </h1>
                <p class="mt-2.5 max-w-2xl text-sm font-medium leading-relaxed text-zinc-600 dark:text-zinc-300 sm:text-base">
                    {{ $data['welcome']['message'] }}
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-3 text-xs font-semibold">
                    <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-zinc-700 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M8 9h.01M12 9h.01M16 9h.01"/>
                        </svg>
                        {{ $data['welcome']['fundo'] }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-zinc-600 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,.2)] dark:bg-emerald-400"></span>
                        Datos actualizados: {{ $data['generatedAt'] }}
                    </span>
                </div>

                <!-- Acciones Rápidas Unificadas -->
                <div class="mt-6 flex flex-wrap gap-2.5">
                    @if($data['createPermissions']['animal'] ?? false)
                        <a wire:navigate href="{{ route('animal.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500 dark:bg-emerald-500 dark:text-zinc-950 dark:hover:bg-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                            Nuevo animal
                        </a>
                    @endif
                    @if($data['createPermissions']['leche'] ?? false)
                        <a wire:navigate href="{{ route('leche.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-800 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            + Ordeño
                        </a>
                    @endif
                    @if($data['createPermissions']['finanzas'] ?? false)
                        <a wire:navigate href="{{ route('finanzas.movimiento.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-800 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            + Movimiento
                        </a>
                    @endif
                    @if($data['createPermissions']['monitoreo'] ?? false)
                        <a wire:navigate href="{{ route('monitoreo.sanidad.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-800 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            + Sanidad
                        </a>
                        <a wire:navigate href="{{ route('monitoreo.parto.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-800 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            + Parto
                        </a>
                    @endif
                    @if($data['createPermissions']['queso'] ?? false)
                        <a wire:navigate href="{{ route('queso.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-800 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            + Queso
                        </a>
                    @endif
                    @if($data['createPermissions']['medicamentos'] ?? false)
                        <a wire:navigate href="{{ route('medicamentos.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-800 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            + Medicamento
                        </a>
                        <a wire:navigate href="{{ route('insumos.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-800 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            + Insumo
                        </a>
                    @endif
                </div>
            </div>

            <!-- Lectura Rápida Card -->
            <div class="self-stretch rounded-2xl border border-zinc-200 bg-zinc-50 p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-center justify-between border-b border-zinc-200/80 pb-3 dark:border-zinc-800">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-400">Lectura rápida</p>
                        <h2 class="mt-0.5 text-base font-black text-zinc-950 dark:text-white">Hoy en el fundo</h2>
                    </div>
                    <button
                        type="button"
                        wire:click="loadStats"
                        wire:loading.attr="disabled"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 disabled:opacity-50"
                        aria-label="Actualizar dashboard"
                        title="Actualizar datos"
                    >
                        <svg wire:loading.class="animate-spin" wire:target="loadStats" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 12a8 8 0 1 1-2.34-5.66M20 4v6h-6"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-4 xl:grid-cols-2">
                    @if($data['allowedModules']['monitoreo'] ?? false)
                        <a href="{{ route('monitoreo.index') }}" class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-emerald-500/40 dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Alertas</span>
                            <strong class="mt-1 block text-xl font-black {{ ($data['kpis']['alerts']['overdue'] ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $data['kpis']['alerts']['pending'] }}
                            </strong>
                            <span class="mt-0.5 block text-[10px] font-semibold text-zinc-400">{{ ($data['kpis']['alerts']['overdue'] ?? 0) > 0 ? $data['kpis']['alerts']['overdue'].' vencidas' : 'Al día' }}</span>
                        </a>
                    @endif
                    @if($data['allowedModules']['leche'] ?? false)
                        <a href="{{ route('leche.index') }}" class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-cyan-500/40 dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ordeño Hoy</span>
                            <strong class="mt-1 block text-xl font-black text-cyan-700 dark:text-cyan-400">{{ number_format($data['kpis']['milk']['today'], 1) }} <small class="text-xs">L</small></strong>
                            <span class="mt-0.5 block text-[10px] font-semibold text-zinc-400">Prom: {{ number_format($data['kpis']['milk']['average7Days'], 1) }} L</span>
                        </a>
                    @endif
                    @if($data['allowedModules']['finanzas'] ?? false)
                        <a href="{{ route('finanzas.index') }}" class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-emerald-500/40 dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Saldo Mes</span>
                            <strong class="mt-1 block text-xl font-black {{ $data['kpis']['finance']['balance'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                S/ {{ number_format($data['kpis']['finance']['balance'], 0) }}
                            </strong>
                            <span class="mt-0.5 block text-[10px] font-semibold text-zinc-400">{{ $data['kpis']['finance']['balance'] >= 0 ? 'Positivo' : 'Déficit' }}</span>
                        </a>
                    @endif
                    @if($data['allowedModules']['medicamentos'] ?? false)
                        <a href="{{ route('medicamentos.index') }}" class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm transition hover:border-cyan-500/40 dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Botiquín</span>
                            <strong class="mt-1 block text-xl font-black {{ ($data['kpis']['botiquin']['lowStockCount'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ ($data['kpis']['botiquin']['totalMedicamentos'] ?? 0) + ($data['kpis']['botiquin']['totalInsumos'] ?? 0) }}
                            </strong>
                            <span class="mt-0.5 block text-[10px] font-semibold {{ ($data['kpis']['botiquin']['expiringSoonCount'] ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">
                                {{ ($data['kpis']['botiquin']['expiringSoonCount'] ?? 0) > 0 ? $data['kpis']['botiquin']['expiringSoonCount'].' por vencer' : 'Stock al día' }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Resumen Operativo (Indicadores Esenciales) -->
    <section aria-labelledby="dashboard-summary-title">
        <div class="mb-2.5 flex items-end justify-between gap-4">
            <div>
                <p class="text-[9px] font-extrabold uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Resumen operativo</p>
                <h2 id="dashboard-summary-title" class="mt-0.5 text-lg font-black tracking-tight text-zinc-950 dark:text-white">Indicadores esenciales</h2>
            </div>
            <p class="hidden text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 sm:block">Comparación contra el mes anterior</p>
        </div>

        <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <!-- 1. Animales -->
            @if($data['allowedModules']['animal'] ?? false)
                <a wire:navigate href="{{ route('animal.index') }}" class="group rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-500/50 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 11c-1.5 0-2.5-1-2.5-2.5S3.5 6 5 6s2.5 1 2.5 2.5S6.5 11 5 11Zm14 0c-1.5 0-2.5-1-2.5-2.5S17.5 6 19 6s2.5 1 2.5 2.5S20.5 11 19 11ZM8 7c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3Zm8 0c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3Zm-4 15c-4 0-7-2.4-7-5.5S8 11 12 11s7 2.4 7 5.5S16 22 12 22Z"/></svg>
                        </span>
                        <span class="rounded-full bg-sky-50 px-2 py-0.5 text-[9px] font-extrabold text-sky-700 dark:bg-sky-950 dark:text-sky-300 border border-sky-200 dark:border-sky-800">+{{ $data['kpis']['animals']['newThisMonth'] }} mes</span>
                    </div>
                    <p class="mt-2 text-xl sm:text-2xl font-black text-zinc-950 dark:text-white">{{ $data['kpis']['animals']['total'] }}</p>
                    <p class="mt-0.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Animales en inventario</p>
                    <p class="mt-2 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 truncate">{{ $data['kpis']['animals']['inactive'] }} bajas históricas</p>
                </a>
            @endif

            <!-- 2. Leche -->
            @if($data['allowedModules']['leche'] ?? false)
                <a wire:navigate href="{{ route('leche.index') }}" class="group rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-500/50 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3h8l-1 4c3 2 4 5 4 8a7 7 0 0 1-14 0c0-3 1-6 4-8L8 3Z"/></svg>
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold border {{ $data['kpis']['milk']['variation'] >= 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                            {{ $data['kpis']['milk']['variation'] >= 0 ? '+' : '' }}{{ $data['kpis']['milk']['variation'] }}%
                        </span>
                    </div>
                    <p class="mt-2 text-xl sm:text-2xl font-black text-zinc-950 dark:text-white">{{ number_format($data['kpis']['milk']['month'], 1) }} <small class="text-xs font-semibold text-zinc-400">L</small></p>
                    <p class="mt-0.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Leche este mes</p>
                    <p class="mt-2 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 truncate">Prom. 7d: {{ number_format($data['kpis']['milk']['average7Days'], 1) }} L/día</p>
                </a>
            @endif

            <!-- 3. Queso -->
            @if($data['allowedModules']['queso'] ?? false)
                <a wire:navigate href="{{ route('queso.index') }}" class="group rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-500/50 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 9 8-5 8 5v10H4V9Zm0 0 8 4 8-4M9 11v8"/></svg>
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold border {{ $data['kpis']['cheese']['variation'] >= 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                            {{ $data['kpis']['cheese']['variation'] >= 0 ? '+' : '' }}{{ $data['kpis']['cheese']['variation'] }}%
                        </span>
                    </div>
                    <p class="mt-2 text-xl sm:text-2xl font-black text-zinc-950 dark:text-white">{{ number_format($data['kpis']['cheese']['monthKg'], 1) }} <small class="text-xs font-semibold text-zinc-400">kg</small></p>
                    <p class="mt-0.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Queso este mes</p>
                    <p class="mt-2 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 truncate">{{ $data['kpis']['cheese']['monthUnits'] }} unidades prod.</p>
                </a>
            @endif

            <!-- 4. Finanzas -->
            @if($data['allowedModules']['finanzas'] ?? false)
                <a wire:navigate href="{{ route('finanzas.index') }}" class="group rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-500/50 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4V6Zm0 4h16m-4 4h1"/></svg>
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold border {{ $data['kpis']['finance']['balance'] >= 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                            {{ $data['kpis']['finance']['balance'] >= 0 ? 'Positivo' : 'Déficit' }}
                        </span>
                    </div>
                    <p class="mt-2 text-xl sm:text-2xl font-black {{ $data['kpis']['finance']['balance'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">S/ {{ number_format($data['kpis']['finance']['balance'], 0) }}</p>
                    <p class="mt-0.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Balance de caja</p>
                    <p class="mt-2 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 truncate">Ing: S/ {{ number_format($data['kpis']['finance']['income'], 0) }} · Egr: S/ {{ number_format($data['kpis']['finance']['expense'], 0) }}</p>
                </a>
            @endif

            <!-- 5. Botiquín e Insumos -->
            @if($data['allowedModules']['medicamentos'] ?? false)
                <a wire:navigate href="{{ route('medicamentos.index') }}" class="group rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-500/50 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold border {{ ($data['kpis']['botiquin']['lowStockCount'] ?? 0) > 0 ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'border-zinc-200 bg-zinc-50 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400' }}">
                            {{ ($data['kpis']['botiquin']['lowStockCount'] ?? 0) > 0 ? $data['kpis']['botiquin']['lowStockCount'].' bajo' : 'Normal' }}
                        </span>
                    </div>
                    <p class="mt-2 text-xl sm:text-2xl font-black text-zinc-950 dark:text-white">{{ ($data['kpis']['botiquin']['totalMedicamentos'] ?? 0) + ($data['kpis']['botiquin']['totalInsumos'] ?? 0) }}</p>
                    <p class="mt-0.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Medicamentos e Insumos</p>
                    <p class="mt-2 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 truncate">{{ $data['kpis']['botiquin']['totalMedicamentos'] ?? 0 }} fármacos · {{ $data['kpis']['botiquin']['totalInsumos'] ?? 0 }} insumos</p>
                </a>
            @endif

            <!-- 6. Engorde -->
            @if($data['allowedModules']['engorde'] ?? false)
                <a href="{{ route('engorde.index') }}" class="group rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-lime-500/50 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-lime-500/10 text-lime-600 dark:bg-lime-400/10 dark:text-lime-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 18V8l4-3 4 3 4-3 4 3v10M7 18v-4h10v4M8 9h.01M16 9h.01"/></svg>
                        </span>
                        <span class="rounded-full bg-lime-50 px-2 py-0.5 text-[9px] font-extrabold text-lime-700 dark:bg-lime-950 dark:text-lime-300 border border-lime-200 dark:border-lime-800">{{ $data['kpis']['fattening']['lots'] }} lotes</span>
                    </div>
                    <p class="mt-2 text-xl sm:text-2xl font-black text-zinc-950 dark:text-white">{{ $data['kpis']['fattening']['active'] }}</p>
                    <p class="mt-0.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Engorde activo</p>
                    <p class="mt-2 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 truncate">{{ $data['kpis']['fattening']['ready'] }} listos venta</p>
                </a>
            @endif

            <!-- 7. Monitoreo & Sanidad -->
            @if($data['allowedModules']['monitoreo'] ?? false)
                <a href="{{ route('monitoreo.index') }}" class="group rounded-xl border border-zinc-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-rose-500/50 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10 3h4l7 17H3l7-17Z"/></svg>
                        </span>
                        @if($data['kpis']['alerts']['overdue'] > 0)
                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[9px] font-extrabold text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800">{{ $data['kpis']['alerts']['overdue'] }} vencidas</span>
                        @else
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-extrabold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Al día</span>
                        @endif
                    </div>
                    <p class="mt-2 text-xl sm:text-2xl font-black text-zinc-950 dark:text-white">{{ $data['kpis']['alerts']['pending'] }}</p>
                    <p class="mt-0.5 text-[11px] font-bold text-zinc-500 dark:text-zinc-400">Alertas pendientes</p>
                    <p class="mt-2 text-[10px] font-medium text-zinc-400 dark:text-zinc-500 truncate">{{ $data['kpis']['alerts']['partosMonth'] ?? 0 }} partos · {{ $data['kpis']['alerts']['sanidadMonth'] ?? 0 }} atenciones</p>
                </a>
            @endif
        </div>
    </section>

    <!-- Tendencia Productiva -->
    @if(($data['allowedModules']['leche'] ?? false) || ($data['allowedModules']['queso'] ?? false))
        <section class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-zinc-800">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-cyan-600 dark:text-cyan-400">Tendencia productiva</p>
                    <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Evolución de la producción</h2>
                    <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Selecciona el indicador, el rango y un mes para ver su detalle.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div class="flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        @if($data['allowedModules']['leche'] ?? false)
                            <button type="button" @click="setPerformanceMetric('milk')" :class="performanceMetric === 'milk' ? 'bg-white text-cyan-700 shadow-sm dark:bg-zinc-700 dark:text-cyan-300' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">Leche</button>
                        @endif
                        @if($data['allowedModules']['queso'] ?? false)
                            <button type="button" @click="setPerformanceMetric('cheese')" :class="performanceMetric === 'cheese' ? 'bg-white text-amber-700 shadow-sm dark:bg-zinc-700 dark:text-amber-300' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">Queso</button>
                        @endif
                    </div>
                    <div class="flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        <button type="button" @click="setPerformanceRange(6)" :class="performanceRange === 6 ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">6 meses</button>
                        <button type="button" @click="setPerformanceRange(12)" :class="performanceRange === 12 ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">12 meses</button>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[1fr_240px]">
                <div>
                    <div class="relative h-64 rounded-2xl bg-zinc-50 px-3 pt-5 dark:bg-zinc-950">
                        <svg class="h-full w-full overflow-visible" viewBox="0 0 1000 240" preserveAspectRatio="none" role="img" aria-label="Tendencia mensual de producción">
                            <defs>
                                <linearGradient id="globalPerformanceArea" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#06b6d4" stop-opacity=".25"/>
                                    <stop offset="100%" stop-color="#06b6d4" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <line x1="30" y1="40" x2="970" y2="40" class="stroke-zinc-200 dark:stroke-zinc-800" stroke-dasharray="5 6"/>
                            <line x1="30" y1="95" x2="970" y2="95" class="stroke-zinc-200 dark:stroke-zinc-800" stroke-dasharray="5 6"/>
                            <line x1="30" y1="150" x2="970" y2="150" class="stroke-zinc-200 dark:stroke-zinc-800" stroke-dasharray="5 6"/>
                            <line x1="30" y1="205" x2="970" y2="205" class="stroke-zinc-300 dark:stroke-zinc-700"/>
                            <polygon :points="performanceAreaPoints" fill="url(#globalPerformanceArea)"/>
                            <polyline :points="performanceTrendPoints" fill="none" :stroke="performanceMetric === 'milk' ? '#06b6d4' : '#f59e0b'" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="mt-3 grid gap-1" :style="`grid-template-columns: repeat(${performanceVisible.length}, minmax(0, 1fr))`">
                        <template x-for="(month, index) in performanceVisible" :key="`${performanceMetric}-${month.period}`">
                            <button
                                type="button"
                                @mouseenter="performanceHover = index"
                                @mouseleave="performanceHover = null"
                                @click="selectPerformanceMonth(month.period)"
                                :class="performanceSelectedPeriod === month.period ? 'bg-cyan-50 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300 font-black' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                class="truncate rounded-lg px-1 py-2 text-[10px] font-bold transition"
                                x-text="month.label"
                            ></button>
                        </template>
                    </div>
                </div>

                <aside class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950">
                    <p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-zinc-400" x-text="performanceActiveMonth ? performanceActiveMonth.label : `${performanceRange} meses`"></p>
                    <p class="mt-3 text-3xl font-black text-zinc-950 dark:text-white">
                        <span x-text="formatNumber(performanceSummary.total, 1)"></span>
                        <small class="text-sm font-semibold text-zinc-400" x-text="performanceMetric === 'milk' ? 'L' : 'kg'"></small>
                    </p>
                    <p class="mt-1 text-xs font-bold text-zinc-500 dark:text-zinc-400" x-text="performanceActiveMonth ? 'Producción del mes' : 'Producción acumulada'"></p>

                    <div class="mt-5 space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-zinc-500 dark:text-zinc-400">Promedio mensual</span>
                            <strong class="text-zinc-900 dark:text-white"><span x-text="formatNumber(performanceSummary.average, 1)"></span> <span x-text="performanceMetric === 'milk' ? 'L' : 'kg'"></span></strong>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-zinc-500 dark:text-zinc-400" x-text="performanceMetric === 'milk' ? 'Ordeños' : 'Unidades'"></span>
                            <strong class="text-zinc-900 dark:text-white" x-text="performanceSummary.secondary"></strong>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-zinc-500 dark:text-zinc-400">Mejor mes</span>
                            <strong class="text-zinc-900 dark:text-white" x-text="performanceSummary.bestLabel"></strong>
                        </div>
                    </div>

                    <button x-show="performanceSelectedPeriod" type="button" @click="selectPerformanceMonth('')" class="mt-5 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-[11px] font-extrabold text-zinc-600 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">Ver todo el periodo</button>
                </aside>
            </div>
        </section>
    @endif

    <!-- Control Financiero & Inventario -->
    <div class="grid gap-6 xl:grid-cols-3">
        @if($data['allowedModules']['finanzas'] ?? false)
            <section class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm xl:col-span-2 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 border-b border-zinc-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-zinc-800">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Control financiero</p>
                        <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Ingresos frente a egresos</h2>
                    </div>
                    <div class="flex rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        <button type="button" @click="setFinanceRange(6)" :class="financeRange === 6 ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">6 meses</button>
                        <button type="button" @click="setFinanceRange(12)" :class="financeRange === 12 ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'" class="rounded-lg px-3 py-1.5 text-[11px] font-extrabold transition">12 meses</button>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="grid h-64 items-end gap-2 rounded-2xl bg-zinc-50 px-3 pb-3 pt-7 dark:bg-zinc-950" :style="`grid-template-columns: repeat(${financeVisible.length}, minmax(0, 1fr))`">
                        <template x-for="(month, index) in financeVisible" :key="month.period">
                            <button
                                type="button"
                                @mouseenter="financeHover = index"
                                @mouseleave="financeHover = null"
                                @click="selectFinanceMonth(month.period)"
                                class="group flex h-full min-w-0 flex-col justify-end"
                                :aria-label="`Ver finanzas de ${month.label}`"
                            >
                                <div class="flex flex-1 items-end justify-center gap-1.5">
                                    <span class="w-3 rounded-t-md bg-emerald-500 transition-all group-hover:bg-emerald-400 sm:w-4" :style="`height: ${financeBarPercent(month.income)}%`"></span>
                                    <span class="w-3 rounded-t-md bg-rose-500 transition-all group-hover:bg-rose-400 sm:w-4" :style="`height: ${financeBarPercent(month.expense)}%`"></span>
                                </div>
                                <span :class="financeSelectedPeriod === month.period ? 'text-emerald-700 dark:text-emerald-300 font-black' : 'text-zinc-400'" class="mt-2 truncate text-[9px] font-bold sm:text-[10px]" x-text="month.label"></span>
                            </button>
                        </template>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-zinc-200 bg-emerald-50/50 p-3 dark:border-zinc-800 dark:bg-emerald-950/20">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Ingresos</span>
                            <strong class="mt-1 block text-lg font-black text-emerald-800 dark:text-emerald-200">S/ <span x-text="formatNumber(financeSummary.income, 0)"></span></strong>
                        </div>
                        <div class="rounded-xl border border-zinc-200 bg-rose-50/50 p-3 dark:border-zinc-800 dark:bg-rose-950/20">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300">Egresos</span>
                            <strong class="mt-1 block text-lg font-black text-rose-800 dark:text-rose-200">S/ <span x-text="formatNumber(financeSummary.expense, 0)"></span></strong>
                        </div>
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400" x-text="financeActiveMonth ? `Balance · ${financeActiveMonth.label}` : 'Balance del periodo'"></span>
                            <strong :class="financeSummary.balance >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'" class="mt-1 block text-lg font-black">S/ <span x-text="formatNumber(financeSummary.balance, 0)"></span></strong>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- Inventario por especie -->
        @if($data['allowedModules']['animal'] ?? false)
            <section class="rounded-[2rem] border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-sky-600 dark:text-sky-400">Composición</p>
                <h2 class="mt-1 text-xl font-black text-zinc-950 dark:text-white">Inventario por especie</h2>

                <div class="relative mx-auto mt-6 h-44 w-44">
                    <div class="h-full w-full rounded-full shadow-inner" :style="`background: ${inventoryGradient}`"></div>
                    <div class="absolute inset-9 flex flex-col items-center justify-center rounded-full bg-white text-center shadow-sm dark:bg-zinc-950 dark:border dark:border-zinc-800">
                        <strong class="text-3xl font-black text-zinc-950 dark:text-white" x-text="inventoryActive ? inventoryActive.count : inventoryTotal"></strong>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400" x-text="inventoryActive ? inventoryActive.label : 'Animales'"></span>
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <template x-for="(species, index) in species" :key="species.label">
                        <button
                            type="button"
                            @click="inventorySelected = inventorySelected === index ? null : index"
                            :class="inventorySelected === index ? 'bg-zinc-100 dark:bg-zinc-800' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/60'"
                            class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left transition"
                        >
                            <span class="flex items-center gap-2 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                <span class="h-2.5 w-2.5 rounded-full" :style="`background-color: ${inventoryColors[index % inventoryColors.length]}`"></span>
                                <span x-text="species.label"></span>
                            </span>
                            <span class="text-xs font-extrabold text-zinc-500 dark:text-zinc-400"><span x-text="species.count"></span> · <span x-text="species.percentage"></span>%</span>
                        </button>
                    </template>
                    <p x-show="species.length === 0" class="rounded-xl bg-zinc-50 p-4 text-center text-xs font-semibold text-zinc-500 dark:bg-zinc-950">Aún no hay animales activos.</p>
                </div>
            </section>
        @endif
    </div>

    <!-- Prioridades Próximas & Áreas Disponibles -->
    <div class="grid gap-6 xl:grid-cols-[1fr_1.35fr]">
        <!-- 1. Atención requerida -->
        @if($data['allowedModules']['monitoreo'] ?? false)
            <section class="rounded-[2rem] border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-rose-600 dark:text-rose-400">Atención requerida</p>
                        <h2 class="mt-0.5 text-lg font-black text-zinc-950 dark:text-white">Prioridades próximas</h2>
                    </div>
                    <a href="{{ route('monitoreo.index') }}" class="text-[11px] font-extrabold text-emerald-700 hover:text-emerald-600 dark:text-emerald-400">Ver monitoreo →</a>
                </div>

                <div class="mt-4 space-y-2">
                    @forelse($data['priorities'] as $priority)
                        <a href="{{ route('monitoreo.index') }}" class="flex items-start gap-3 rounded-xl border border-zinc-100 bg-zinc-50/50 p-3 transition hover:border-emerald-300 hover:bg-emerald-50/30 dark:border-zinc-800 dark:bg-zinc-950/50 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-950/20">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $priority['status'] === 'overdue' ? 'bg-rose-500 animate-pulse' : ($priority['status'] === 'today' ? 'bg-amber-400' : 'bg-sky-400') }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-3">
                                    <strong class="truncate text-xs font-bold text-zinc-900 dark:text-white">{{ $priority['type'] }}</strong>
                                    <small class="shrink-0 text-[10px] font-black {{ $priority['status'] === 'overdue' ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400' }}">{{ $priority['date'] }}</small>
                                </span>
                                <span class="mt-1 block line-clamp-1 text-[11px] font-medium text-zinc-500 dark:text-zinc-400">{{ $priority['message'] }}</span>
                                @if($priority['animal'])
                                    <span class="mt-1 block text-[10px] font-bold text-emerald-700 dark:text-emerald-400">{{ $priority['animal'] }}</span>
                                @endif
                            </span>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 text-center dark:border-zinc-800 dark:bg-zinc-950">
                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                            </span>
                            <p class="mt-3 text-sm font-extrabold text-zinc-900 dark:text-zinc-100">Sin alertas pendientes</p>
                            <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">El seguimiento sanitario y reproductivo está al día.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif

        <!-- 2. Áreas Disponibles -->
        <section class="rounded-[2rem] border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Navegación directa</p>
                <h2 class="mt-0.5 text-lg font-black text-zinc-950 dark:text-white">Áreas disponibles</h2>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <!-- Bloque 1: Producción e inventario -->
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-950/70">
                    <p class="mb-2.5 text-[10px] font-extrabold uppercase tracking-[.16em] text-emerald-700 dark:text-emerald-400">Ganadería y Producción</p>
                    <div class="space-y-1">
                        @if($data['allowedModules']['animal'] ?? false)
                            <a href="{{ route('animal.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Animales</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Inventario y fichas</small></span>
                            </a>
                        @endif
                        @if($data['allowedModules']['engorde'] ?? false)
                            <a href="{{ route('engorde.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-lime-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Engorde</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Lotes y evolución de peso</small></span>
                            </a>
                        @endif
                        @if($data['allowedModules']['leche'] ?? false)
                            <a wire:navigate href="{{ route('leche.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Leche</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Ordeños diarios y rendimiento</small></span>
                            </a>
                        @endif
                        @if($data['allowedModules']['queso'] ?? false)
                            <a wire:navigate href="{{ route('queso.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Queso</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Producción y presentaciones</small></span>
                            </a>
                        @endif
                        @if($data['allowedModules']['monitoreo'] ?? false)
                            <a wire:navigate href="{{ route('monitoreo.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Monitoreo</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Sanidad, partos y alertas</small></span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Bloque 2: Suministros y Administración -->
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-950/70">
                    <p class="mb-2.5 text-[10px] font-extrabold uppercase tracking-[.16em] text-teal-700 dark:text-teal-400">Suministros y Control</p>
                    <div class="space-y-1">
                        @if($data['allowedModules']['medicamentos'] ?? false)
                            <a wire:navigate href="{{ route('medicamentos.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Medicamentos</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Fármacos, lotes y FEFO</small></span>
                            </a>
                            <a wire:navigate href="{{ route('insumos.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Insumos y Materiales</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Stock y suministros</small></span>
                            </a>
                        @endif
                        @if($data['allowedModules']['finanzas'] ?? false)
                            <a wire:navigate href="{{ route('finanzas.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Finanzas</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Ingresos, egresos y asignaciones</small></span>
                            </a>
                        @endif
                        @if(auth()->user()->tienePermiso('buscador', 'leer'))
                            <a wire:navigate href="{{ route('buscador') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Buscador global</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Búsqueda rápida en el fundo</small></span>
                            </a>
                        @endif
                        @if(auth()->user()->tienePermiso('ajustes', 'leer'))
                            <a wire:navigate href="{{ route('ajustes.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white dark:hover:bg-zinc-800">
                                <span class="h-2 w-2 rounded-full bg-zinc-500"></span>
                                <span><strong class="block text-xs text-zinc-900 dark:text-white">Ajustes del Sistema</strong><small class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400">Roles, branding y respaldos</small></span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endif
