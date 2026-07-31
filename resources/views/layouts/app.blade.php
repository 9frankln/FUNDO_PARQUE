<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $branding->name }}</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-brand-theme />
        
        <style>
            body { font-family: 'Outfit', sans-serif; }
        </style>

        <x-theme-init />
    </head>
    <body x-data="{ 
              mobileNavOpen: false,
              darkMode: document.documentElement.classList.contains('dark'),
           }" 
           x-init="$watch('darkMode', val => {
               window.setTheme(val ? 'dark' : 'light');
           })"
           class="min-h-screen overflow-x-hidden text-zinc-800 antialiased transition-colors duration-[250ms] dark:text-zinc-100">
        
        <div class="relative">
            <div class="hidden">
                <livewire:layout.navigation />
            </div>

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
            $idleTimeout = min(
                (int) config('session.lifetime', 30),
                max(5, (int) (auth()->user()->session_idle_timeout_minutes ?: config('session.lifetime', 30)))
            );
        @endphp
        <form id="idle-logout-form" method="POST" action="{{ route('logout') }}" data-timeout="{{ $idleTimeout * 60_000 }}" class="hidden" aria-hidden="true">
            @csrf
        </form>

        @livewireScripts
        @if(session('swal'))
            <script id="swal-flash" type="application/json">@json(session('swal'))</script>
        @endif
    </body>
</html>
