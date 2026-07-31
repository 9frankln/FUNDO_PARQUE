<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleccionar Fundo | {{ $branding->name }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-brand-theme />
    <x-theme-init />
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-x-hidden p-3 text-zinc-100 sm:p-6">
    <!-- Ambient lights -->
    <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-emerald-500/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-teal-500/10 blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-md space-y-6 rounded-2xl border border-zinc-800/80 bg-zinc-900/60 p-5 text-center shadow-2xl backdrop-blur-md sm:p-8">
        <div class="flex flex-col items-center gap-2">
            <div class="p-3 bg-gradient-to-tr from-emerald-500 to-emerald-400 rounded-2xl shadow-lg shadow-emerald-500/20">
                <x-brand-logo class="w-8 h-8 text-zinc-950" />
            </div>
            <h1 class="agro-title mt-2 text-2xl font-bold tracking-tight">Seleccionar Fundo</h1>
            <p class="text-zinc-400 text-sm">Selecciona el fundo/campo en el que deseas operar hoy:</p>
        </div>

        <div class="space-y-3 pt-2">
            @foreach($fundos as $fundo)
                <form method="POST" action="{{ route('select-fundo', $fundo->id) }}">
                    @csrf
                    <button type="submit"
                   class="flex w-full items-center justify-between p-4 rounded-xl bg-zinc-800/40 hover:bg-emerald-500/10 border border-zinc-700/50 hover:border-emerald-500/40 transition duration-300 group">
                    <div class="flex items-center gap-3">
                        <span class="text-lg">🏡</span>
                        <div class="text-left">
                            <div class="font-bold text-zinc-100 group-hover:text-emerald-400 transition-colors">{{ $fundo->nombre }}</div>
                            <div class="text-xs text-zinc-400">{{ $fundo->distrito }}, {{ $fundo->provincia }}</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-zinc-500 group-hover:text-emerald-400 transition-colors transform group-hover:translate-x-1 duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
                    </button>
                </form>
            @endforeach
        </div>

        <div class="pt-4 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-500">
            <span>Usuario: {{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="hover:text-red-400 transition">Cerrar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
