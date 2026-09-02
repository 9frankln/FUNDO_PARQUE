<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          darkMode: window.getTheme ? (window.getTheme() === 'dark') : document.documentElement.classList.contains('dark') 
      }" 
      x-init="$watch('darkMode', val => window.setTheme(val ? 'dark' : 'light'))">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $branding->name }}</title>

        <!-- Google Fonts (carga no bloqueante: no retrasa el render) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript><link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

        <!-- Scripts -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-brand-theme />
        <x-theme-init />
        
        <style>
            body { font-family: 'Outfit', sans-serif; }
        </style>
    </head>
    <body x-data="{ mobileNavOpen: false }" class="min-h-screen overflow-x-hidden text-zinc-800 antialiased transition-colors duration-[250ms] dark:text-zinc-100">
        
        <div class="relative">
            <x-navbar />

            @if (isset($header))
                <header class="sticky top-16 z-30 border-b border-emerald-950/10 bg-white/75 px-3 py-3 backdrop-blur-xl transition-colors duration-200 dark:border-emerald-200/10 dark:bg-emerald-950/45 sm:px-6 sm:py-4 lg:top-[73px] lg:px-8">
                    <div class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-between gap-3">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="mx-auto w-full max-w-[1600px] p-3 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>

        @php
            $idleUser = auth()->user();
            $idleIsAdmin = (bool) ($idleUser?->esAdministrador());
            $idleTimeoutMs = 0;

            if ($idleIsAdmin) {
                if ($idleUser->session_idle_timeout_minutes !== null) {
                    $minutes = min(525600, max(5, (int) $idleUser->session_idle_timeout_minutes));
                    $idleTimeoutMs = $minutes >= 525600 ? 0 : $minutes * 60_000;
                }
            } else {
                $minutes = min((int) config('session.lifetime', 30), max(5, (int) ($idleUser?->session_idle_timeout_minutes ?: (int) config('session.lifetime', 30))));
                $idleTimeoutMs = $minutes * 60_000;
            }
        @endphp
        <form id="idle-logout-form" method="POST" action="{{ route('logout') }}" data-timeout="{{ $idleTimeoutMs }}" class="hidden" aria-hidden="true">
            @csrf
        </form>

        @livewireScripts
        @if(session('swal'))
            <script id="swal-flash" type="application/json">@json(session('swal'))</script>
        @endif
    </body>
</html>
