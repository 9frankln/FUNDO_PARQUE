<div
    x-data
    @keydown.window.slash="if (!['INPUT', 'TEXTAREA'].includes($event.target.tagName)) { $event.preventDefault(); $refs.globalSearch.focus() }"
    @keydown.escape.window="$wire.clearSearch(); $refs.globalSearch.focus()"
    class="mx-auto min-w-0 max-w-6xl space-y-5 overflow-x-hidden"
>
    <header class="relative isolate overflow-hidden rounded-[1.75rem] border border-emerald-200/80 bg-gradient-to-br from-emerald-50 via-zinc-50 to-teal-50/60 p-5 text-zinc-900 shadow-xl dark:border-emerald-800/60 dark:bg-gradient-to-br dark:from-zinc-950 dark:via-emerald-950/90 dark:to-zinc-950 dark:text-white sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-20 -z-10 h-56 w-56 rounded-full bg-emerald-400/15 blur-3xl dark:bg-emerald-500/10"></div>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-600/20 bg-emerald-600/10 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[.18em] text-emerald-800 dark:border-emerald-400/30 dark:bg-emerald-400/10 dark:text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                Consulta global
            </div>
            <h1 class="mt-4 text-2xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-4xl">Encuentra cualquier registro</h1>
            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-zinc-600 dark:text-zinc-300">
                Busca por arete, animal, especie, lote, fecha, diagnóstico, categoría financiera, monto o descripción.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <button type="button"
                    onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('dashboard') }}'"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-extrabold text-zinc-800 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Atrás</span>
            </button>
            <a wire:navigate href="{{ route('dashboard') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-extrabold text-zinc-800 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"/>
                </svg>
                Dashboard
            </a>
        </div>
        </div>
    </header>

    <section class="relative overflow-hidden rounded-[1.75rem] border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
        <div class="pointer-events-none absolute -right-20 -top-20 h-48 w-48 rounded-full bg-emerald-400/10 blur-3xl"></div>

        <div class="relative">
            <label for="global-search" class="mb-2 block text-xs font-extrabold text-zinc-700 dark:text-zinc-200">
                ¿Qué necesitas encontrar?
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.5-4.5m2-5.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/>
                    </svg>
                </span>
                <input
                    id="global-search"
                    x-ref="globalSearch"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    autocomplete="off"
                    autofocus
                    placeholder="Ej.: BOV26-001, Holstein, alimentos, 26/07/2026..."
                    class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 py-3.5 pl-12 pr-12 text-sm font-bold text-zinc-950 outline-none transition placeholder:font-medium placeholder:text-zinc-400 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-400 dark:focus:ring-2 dark:focus:ring-emerald-400/20 sm:py-4 sm:pr-28 sm:text-base [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none"
                >

                <span wire:loading.flex wire:target="search,categoria" class="absolute inset-y-0 right-12 hidden items-center text-emerald-600 dark:text-emerald-400">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-80" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/>
                    </svg>
                </span>

                @if($search !== '')
                    <button
                        type="button"
                        wire:click="clearSearch"
                        class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-zinc-400 transition hover:text-zinc-800 dark:hover:text-white"
                        aria-label="Limpiar búsqueda"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/>
                        </svg>
                    </button>
                @else
                    <span class="pointer-events-none absolute inset-y-0 right-4 hidden items-center sm:flex">
                        <kbd class="rounded-lg border border-zinc-200 bg-white px-2 py-1 text-[10px] font-extrabold text-zinc-400 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">/</kbd>
                    </span>
                @endif
            </div>

            @if(mb_strlen(trim($search)) === 1)
                <p class="mt-2 flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-amber-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Escribe al menos 2 caracteres.
                </p>
            @endif

            <div class="mt-5 flex items-center justify-between gap-3">
                <p class="text-[10px] font-black uppercase tracking-[.16em] text-zinc-500 dark:text-zinc-400">
                    Buscar en
                </p>
                <p class="hidden text-[10px] font-semibold text-zinc-400 sm:block">
                    Selecciona uno o consulta todos
                </p>
            </div>

            <div data-testid="search-category-grid" class="mt-2 flex flex-wrap gap-2 overflow-x-auto pt-1 pb-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                @foreach($availableCategories as $key => $category)
                    @php
                        $dotClass = match($key) {
                            'animal' => 'bg-sky-400',
                            'engorde' => 'bg-lime-400',
                            'leche' => 'bg-cyan-400',
                            'queso' => 'bg-amber-400',
                            'monitoreo' => 'bg-rose-400',
                            'finanzas' => 'bg-teal-400',
                            'auditoria' => 'bg-indigo-400',
                            default => 'bg-emerald-400',
                        };
                    @endphp
                    <button
                        type="button"
                        wire:click="setCategoria('{{ $key }}')"
                        title="{{ $category['description'] }}"
                        class="inline-flex shrink-0 min-w-0 items-center justify-center gap-2 rounded-xl px-3.5 py-2 text-xs font-extrabold transition-all duration-200 ease-out focus:outline-none
                            {{ $categoria === $key
                                ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-zinc-950 shadow-md shadow-emerald-500/20 ring-1 ring-emerald-400/40 dark:from-emerald-400 dark:to-teal-400 dark:text-zinc-950 scale-[1.02]'
                                : 'border border-zinc-200 bg-zinc-100/80 text-zinc-700 hover:bg-zinc-200/80 dark:border-zinc-800 dark:bg-zinc-950/80 dark:text-zinc-300 dark:hover:bg-zinc-800/80' }}"
                    >
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $dotClass }}"></span>
                        <span class="truncate">{{ $category['label'] }}</span>
                        @if($search !== '' && $key !== 'todos' && ($resultCounts[$key] ?? 0) > 0)
                            <span class="rounded-full px-1.5 py-0.5 text-[9px] {{ $categoria === $key ? 'bg-black/20 text-zinc-950' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-400' }}">
                                {{ $resultCounts[$key] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section aria-live="polite" aria-busy="false">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3 px-1">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-black text-zinc-900 dark:text-white">
                    @if(mb_strlen(trim($search)) >= 2)
                        Resultados para “{{ trim($search) }}”
                    @else
                        Resultados
                    @endif
                </h2>
                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-black text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300">
                    {{ count($resultados) }}
                </span>
            </div>
            @if(count($resultados) > 0)
                <p class="text-[11px] font-semibold text-zinc-400">Ordenados desde el registro más reciente</p>
            @endif
        </div>

        <div class="space-y-3">
            @forelse($resultados as $result)
                @php
                    $tone = match($result['type']) {
                        'animal' => [
                            'icon' => 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-300',
                            'badge' => 'bg-sky-50 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300',
                        ],
                        'engorde' => [
                            'icon' => 'border-lime-200 bg-lime-50 text-lime-700 dark:border-lime-400/20 dark:bg-lime-400/10 dark:text-lime-300',
                            'badge' => 'bg-lime-50 text-lime-700 dark:bg-lime-400/10 dark:text-lime-300',
                        ],
                        'leche' => [
                            'icon' => 'border-cyan-200 bg-cyan-50 text-cyan-700 dark:border-cyan-400/20 dark:bg-cyan-400/10 dark:text-cyan-300',
                            'badge' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300',
                        ],
                        'queso' => [
                            'icon' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300',
                            'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
                        ],
                        'finanzas' => [
                            'icon' => 'border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-400/20 dark:bg-teal-400/10 dark:text-teal-300',
                            'badge' => 'bg-teal-50 text-teal-700 dark:bg-teal-400/10 dark:text-teal-300',
                        ],
                        'monitoreo' => [
                            'icon' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300',
                            'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300',
                        ],
                        default => [
                            'icon' => 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-400/20 dark:bg-violet-400/10 dark:text-violet-300',
                            'badge' => 'bg-violet-50 text-violet-700 dark:bg-violet-400/10 dark:text-violet-300',
                        ],
                    };
                @endphp

                <a
                    href="{{ $result['url'] }}"
                    class="group flex flex-col gap-4 rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg dark:border-zinc-700/70 dark:bg-zinc-900/80 dark:hover:border-emerald-400/30 sm:flex-row sm:items-center sm:p-5"
                >
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border {{ $tone['icon'] }}">
                        @if($result['type'] === 'animal')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M6 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm12 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM9 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm6 0a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 15c-3.9 0-7-2.2-7-5s3.1-5 7-5 7 2.2 7 5-3.1 5-7 5Z"/></svg>
                        @elseif($result['type'] === 'finanzas')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16v12H4V6Zm0 4h16m-4 4h1"/></svg>
                        @elseif($result['type'] === 'monitoreo')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 3v18M3 12h18"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19V8l7-4 7 4v11H5Zm3-7h8M9 16h6"/></svg>
                        @endif
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $tone['badge'] }}">{{ $result['label'] }}</span>
                            <span class="text-[10px] font-bold text-zinc-400">{{ $result['date'] }}</span>
                        </span>
                        <strong class="mt-1.5 block truncate text-sm text-zinc-950 transition group-hover:text-emerald-700 dark:text-white dark:group-hover:text-emerald-300 sm:text-base">
                            {{ $result['title'] }}
                        </strong>
                        <span class="mt-1 block line-clamp-2 text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $result['description'] }}</span>
                        <span class="mt-1.5 block truncate text-[10px] font-bold text-zinc-400">{{ $result['meta'] }}</span>
                    </span>

                    <span class="flex shrink-0 items-center gap-2 self-end text-xs font-extrabold text-emerald-700 transition group-hover:translate-x-1 dark:text-emerald-300 sm:self-auto">
                        Abrir
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="m9 18 6-6-6-6"/>
                        </svg>
                    </span>
                </a>
            @empty
                <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white px-6 py-12 text-center shadow-sm dark:border-zinc-700/70 dark:bg-zinc-900/80">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="1.8" d="m21 21-4.5-4.5m2-5.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/>
                        </svg>
                    </span>
                    @if(mb_strlen(trim($search)) >= 2)
                        <h3 class="mt-4 text-base font-black text-zinc-950 dark:text-white">No encontramos coincidencias</h3>
                        <p class="mx-auto mt-1 max-w-md text-xs font-semibold leading-5 text-zinc-500">
                            Prueba con otro término, una parte del nombre, el arete, la fecha, la categoría o cambia el módulo seleccionado.
                        </p>
                    @else
                        <h3 class="mt-4 text-base font-black text-zinc-950 dark:text-white">Escribe para comenzar</h3>
                        <p class="mx-auto mt-1 max-w-md text-xs font-semibold leading-5 text-zinc-500">
                            El buscador consultará únicamente los módulos y datos que tienes autorizados dentro del fundo actual.
                        </p>
                    @endif
                </div>
            @endforelse
        </div>
    </section>
</div>

