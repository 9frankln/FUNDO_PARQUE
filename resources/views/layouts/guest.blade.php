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
    <body class="min-h-screen font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center bg-[var(--bg-primary)] px-3 pt-6 sm:justify-center sm:px-0 sm:pt-0">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-20 h-20 fill-current text-zinc-500" />
                </a>
            </div>

            <div class="mt-6 w-full overflow-hidden rounded-2xl border border-emerald-950/10 bg-white px-5 py-5 shadow-xl dark:border-emerald-200/10 dark:bg-emerald-950/35 sm:max-w-md sm:px-6">
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
