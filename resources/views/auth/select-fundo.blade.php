<!DOCTYPE html>
<html lang="es"
      class="scroll-smooth"
      x-data="{ 
          darkMode: window.getTheme ? (window.getTheme() === 'dark') : document.documentElement.classList.contains('dark') 
      }" 
      x-init="$watch('darkMode', val => window.setTheme(val ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleccionar Fundo | {{ $branding->name }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-brand-theme />
    <x-theme-init />
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-x-hidden p-4 text-zinc-800 antialiased transition-colors duration-200 dark:text-zinc-100 sm:p-6">
    <!-- Ambient glowing backgrounds -->
    <div class="pointer-events-none absolute left-[-10%] top-[-10%] h-[50%] w-[50%] rounded-full bg-emerald-500/10 blur-[120px] dark:bg-emerald-500/5"></div>
    <div class="pointer-events-none absolute bottom-[-10%] right-[-10%] h-[50%] w-[50%] rounded-full bg-teal-500/10 blur-[120px] dark:bg-teal-500/5"></div>

    <!-- Top bar controls -->
    <div class="absolute right-4 top-4 z-20 flex items-center gap-2 sm:right-6 sm:top-6">
        <x-theme-toggle
            btn-class="agro-navbar__control flex h-9 w-9 items-center justify-center rounded-xl transition text-emerald-800 hover:bg-emerald-50 dark:text-emerald-200 dark:hover:bg-emerald-400/10 border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm"
            action="darkMode = !darkMode"
            icon-size="h-4.5 w-4.5"
        />
    </div>

    <!-- Main Modal Card -->
    <div class="relative z-10 w-full max-w-md space-y-6 rounded-[2rem] border border-zinc-200/80 bg-white/95 p-6 text-center shadow-2xl backdrop-blur-xl transition-all dark:border-zinc-800/80 dark:bg-zinc-900/90 sm:p-8">
        <div class="flex flex-col items-center gap-2">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl border-[2.5px] border-emerald-600 bg-zinc-950/10 shadow-md overflow-hidden dark:border-emerald-500 shrink-0">
                <x-brand-logo class="h-full w-full" />
            </span>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Seleccionar Fundo</h1>
            <p class="text-xs sm:text-sm font-medium text-zinc-500 dark:text-zinc-400">Elige el fundo o predio en el que deseas operar hoy:</p>
        </div>

        <div class="space-y-3 pt-2">
            @forelse($fundos as $fundo)
                <form method="POST" action="{{ route('select-fundo', $fundo->id) }}">
                    @csrf
                    <button type="submit"
                            class="group flex w-full items-center justify-between rounded-2xl border border-zinc-200/90 bg-zinc-50/80 p-4 text-left shadow-xs transition duration-200 hover:-translate-y-0.5 hover:border-emerald-500/50 hover:bg-emerald-50/80 dark:border-zinc-800 dark:bg-zinc-800/50 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10">
                        <div class="flex items-center gap-3.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-lg text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-300">
                                🏡
                            </span>
                            <div>
                                <div class="font-bold text-zinc-900 transition-colors group-hover:text-emerald-700 dark:text-zinc-100 dark:group-hover:text-emerald-400">{{ $fundo->nombre }}</div>
                                <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $fundo->distrito }}{{ $fundo->provincia ? ', '.$fundo->provincia : '' }}</div>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-zinc-400 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-emerald-600 dark:text-zinc-500 dark:group-hover:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </form>
            @empty
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-semibold text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                    No tienes fundos activos asignados. Contacta al administrador.
                </div>
            @endforelse
        </div>

        <div class="flex items-center justify-between border-t border-zinc-100 pt-4 text-xs font-semibold text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
            <span class="truncate max-w-[180px]">Usuario: {{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="font-bold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</body>
</html>
