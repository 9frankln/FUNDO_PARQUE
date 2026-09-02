@php
    $isLegacyAuthRoute = request()->is('login', 'register', 'resiter');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#064e3b">
        <title>Ruta no encontrada | {{ $branding->name }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css'])
        <x-brand-theme />
        <x-theme-init />

        <style>body { font-family: 'Outfit', sans-serif; }</style>
    </head>
    <body class="min-h-screen overflow-x-hidden text-emerald-950 antialiased dark:text-emerald-50">
        <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute -left-40 -top-40 h-96 w-96 rounded-full bg-emerald-300/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-44 -right-32 h-[28rem] w-[28rem] rounded-full bg-lime-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-40 [background-image:linear-gradient(rgba(5,150,105,.06)_1px,transparent_1px),linear-gradient(90deg,rgba(5,150,105,.06)_1px,transparent_1px)] [background-size:32px_32px]"></div>

            <section class="relative grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-white/90 shadow-2xl shadow-emerald-950/10 backdrop-blur-xl dark:border-emerald-200/10 dark:bg-emerald-950/35 lg:grid-cols-[0.88fr_1.12fr]">
                <div class="relative flex min-h-64 items-center justify-center overflow-hidden bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-700 p-8 sm:min-h-80 lg:min-h-[34rem]">
                    <div class="absolute inset-0 opacity-15 [background-image:radial-gradient(circle_at_center,white_1px,transparent_1px)] [background-size:22px_22px]"></div>
                    <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full border-[24px] border-white/5"></div>
                    <div class="absolute -bottom-14 -left-14 h-52 w-52 rounded-full border-[30px] border-lime-300/10"></div>

                    <div class="relative text-center text-white">
                        <svg class="mx-auto h-40 w-52 sm:h-48 sm:w-64" viewBox="0 0 260 190" fill="none" aria-hidden="true">
                            <path d="M130 176V55" stroke="#A7F3D0" stroke-width="9" stroke-linecap="round" />
                            <path d="M130 169c-30 0-55 7-72 18h144c-17-11-42-18-72-18Z" fill="#065F46" />
                            <path d="M65 38h130l18 27-18 27H65L47 65l18-27Z" fill="#ECFDF5" />
                            <path d="m105 55 25 25m0-25-25 25" stroke="#F43F5E" stroke-width="8" stroke-linecap="round" />
                            <circle cx="130" cy="22" r="12" fill="#34D399" />
                            <path d="M218 132c-16-18-31-26-46-24 4 18 19 28 46 24Zm-176 4c15-20 30-30 46-29-2 18-17 30-46 29Z" fill="#34D399" fill-opacity=".65" />
                        </svg>
                        <p class="mt-2 text-7xl font-extrabold tracking-tighter text-white sm:text-8xl">404</p>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.25em] text-emerald-200">Camino no encontrado</p>
                    </div>
                </div>

                <div class="flex flex-col justify-center p-6 sm:p-10 lg:p-14">
                    <a href="{{ route('home') }}" class="mb-8 inline-flex w-fit items-center gap-3" aria-label="Volver al inicio">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-emerald-500/40 bg-emerald-950/80 shadow-md overflow-hidden shrink-0">
                            <x-brand-logo class="h-full w-full" />
                        </span>
                        <span><strong class="block text-base font-extrabold leading-none">{{ $branding->name }}</strong><small class="mt-1 block text-[9px] font-bold uppercase tracking-[.2em] text-emerald-600">{{ $branding->tagline }}</small></span>
                    </a>

                    <span class="mb-4 inline-flex w-fit items-center rounded-full border border-amber-300/70 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                        Ruta solicitada: /{{ request()->path() }}
                    </span>

                    @if($isLegacyAuthRoute)
                        <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 dark:text-emerald-50 sm:text-4xl">Esta ruta de acceso ya no está disponible</h1>
                        <p class="mt-4 max-w-xl text-base leading-relaxed text-emerald-950/60 dark:text-emerald-100/65">
                            Para ofrecerte una experiencia más rápida y segura, el inicio de sesión ahora está integrado en la página principal mediante un modal.
                        </p>
                    @else
                        <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 dark:text-emerald-50 sm:text-4xl">La ruta indicada no es correcta</h1>
                        <p class="mt-4 max-w-xl text-base leading-relaxed text-emerald-950/60 dark:text-emerald-100/65">
                            Es posible que la dirección esté mal escrita, que el contenido se haya movido o que el enlace ya no exista.
                        </p>
                    @endif

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ $isLegacyAuthRoute ? route('home', ['login' => 1]) : route('home') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-700 to-green-500 px-6 py-3.5 text-sm font-extrabold text-white shadow-xl shadow-emerald-700/20 transition hover:-translate-y-0.5 hover:from-emerald-600 hover:to-green-400">
                            {{ $isLegacyAuthRoute ? 'Ir al inicio de sesión' : 'Volver a la página principal' }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m9 18 6-6-6-6" /></svg>
                        </a>
                        <button type="button" onclick="history.back()"
                                class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-emerald-50 px-6 py-3.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100">
                            Regresar
                        </button>
                    </div>

                    <div class="mt-8 flex items-start gap-3 border-t border-emerald-950/10 pt-6 text-sm text-emerald-950/50">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 4.6 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.6a2 2 0 0 0-3.4 0Z" /></svg>
                        <p>Si llegaste aquí desde un enlace interno, regresa al inicio y continúa desde la navegación principal.</p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
