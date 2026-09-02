<div
    class="mx-auto max-w-[96rem] space-y-6"
    x-data="{
        activeTab: sessionStorage.getItem('landing-manager-tab') || 'hero',
        selectTab(tab) { this.activeTab = tab; sessionStorage.setItem('landing-manager-tab', tab) },
        keepScroll(action, blurActive = true) {
            const top = window.scrollY;
            if (blurActive) document.activeElement?.blur();
            return Promise.resolve(action()).finally(() => this.$nextTick(() => window.scrollTo({ top, left: 0, behavior: 'auto' })));
        }
    }"
>
    {{-- Header Principal --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 shadow-sm">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4V5Zm0 4h16M8 13h3m-3 3h8"/></svg>
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Administración · Web Pública</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-zinc-900 sm:text-3xl dark:text-white">Gestión Web Pública</h1>
                <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">Personaliza la identidad, textos informativos, puntos clave y fotografías del fundo.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('home') }}#inicio" target="_blank" rel="noopener"
               class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-xs sm:text-sm font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M14 5h5v5m0-5-8 8M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>
                <span>Ver página web</span>
            </a>
            <button type="button" x-on:click="keepScroll(() => $wire.saveBlock('hero'))" x-bind:disabled="$store?.imageUploads?.busy" wire:loading.attr="disabled" wire:target="saveBlock('hero')"
                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-xs sm:text-sm font-black text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                <span wire:loading.remove wire:target="saveBlock('hero')">Guardar identidad</span>
                <span wire:loading wire:target="saveBlock('hero')">Guardando...</span>
            </button>
        </div>
    </header>

    {{-- Métricas Resumen --}}
    <section class="grid gap-3 sm:grid-cols-3">
        <article class="rounded-2xl border border-zinc-200/90 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fundo publicado</span>
            <strong class="mt-2 block truncate text-base font-black text-zinc-900 dark:text-white">{{ $publicFundo?->nombre ?? 'Sin fundo activo' }}</strong>
            <small class="mt-1 block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ !empty($blocks['hero']['settings']['show_location']) && filled($blocks['hero']['settings']['custom_location'] ?? null) ? $blocks['hero']['settings']['custom_location'] : 'Ubicación pública oculta' }}</small>
        </article>
        <article class="rounded-2xl border border-zinc-200/90 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Secciones visibles</span>
            <strong class="mt-2 block text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ collect($blocks)->where('is_active', true)->count() }} / {{ count($sections) }}</strong>
            <small class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Públicamente accesibles</small>
        </article>
        <article class="rounded-2xl border border-zinc-200/90 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Biblioteca visual</span>
            <strong class="mt-2 block text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ collect($blocks)->sum(fn ($block) => count($block['media'] ?? [])) }}</strong>
            <small class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Fotografías optimizadas</small>
        </article>
    </section>

    {{-- Layout Principal: Sidebar + Contenido --}}
    <div class="grid gap-6 xl:grid-cols-[17rem_minmax(0,1fr)]">
        {{-- Sidebar de Navegación --}}
        <aside class="h-fit xl:sticky xl:top-24 space-y-4">
            <nav class="rounded-2xl border border-zinc-200/90 bg-white p-2.5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 grid grid-cols-2 gap-1.5 xl:grid-cols-1" aria-label="Secciones de la web">
                @foreach($sections as $key => $label)
                    <button type="button" @click="selectTab('{{ $key }}')"
                            :class="activeTab === '{{ $key }}'
                                ? 'bg-emerald-600 text-white font-bold shadow-sm'
                                : 'border border-transparent text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white font-medium'"
                            class="flex min-w-0 items-center gap-3 rounded-xl px-3 py-2.5 text-left transition">
                        <span :class="activeTab === '{{ $key }}'
                                    ? 'bg-white/20 text-white font-black'
                                    : 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-400 font-black'"
                              class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-[10px]">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <strong class="block truncate text-xs">{{ $label }}</strong>
                            <small class="mt-0.5 block text-[10px] opacity-80">{{ !empty($blocks[$key]['is_active']) ? 'Visible' : 'Oculta' }} · {{ count($blocks[$key]['media'] ?? []) }} fotos</small>
                        </span>
                        <span class="h-2 w-2 shrink-0 rounded-full {{ !empty($blocks[$key]['is_active']) ? 'bg-emerald-400 ring-2 ring-emerald-400/30' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>
                    </button>
                @endforeach
            </nav>

            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-50/50 p-4 text-xs leading-relaxed text-emerald-950 dark:border-emerald-500/20 dark:bg-emerald-950/30 dark:text-emerald-200">
                <strong class="block font-bold text-emerald-900 dark:text-emerald-300">💡 Información pública institucional</strong>
                <span class="mt-1 block opacity-90">Los datos que configures aquí son los que verán los visitantes de tu página web. No publiques datos sensibles o privados.</span>
            </div>
        </aside>

        {{-- Panel de la Sección Activa --}}
        <main class="min-w-0">
            @foreach($sections as $key => $label)
                <section x-cloak x-show="activeTab === '{{ $key }}'" x-transition.opacity.duration.150ms
                         class="rounded-2xl border border-zinc-200/90 bg-white p-5 sm:p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-6">
                    {{-- Encabezado de la Sección --}}
                    <div class="flex flex-col gap-4 border-b border-zinc-100 pb-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-emerald-500/10 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-400">
                                    Sección {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                            <h2 class="mt-1 text-xl sm:text-2xl font-black text-zinc-900 dark:text-white">{{ $label }}</h2>
                            <p class="mt-0.5 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $key === 'hero' ? 'Identidad principal, ubicación y llamada de bienvenida.' : ($key === 'galeria' ? 'Presentación visual y fotografías destacadas.' : 'Textos informativos, puntos clave y galería de fotos.') }}
                            </p>
                        </div>
                        <button type="button" x-on:click="keepScroll(() => $wire.toggleActive('{{ $key }}'))"
                                class="inline-flex items-center gap-3 rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-xs font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            <span class="relative h-5 w-9 rounded-full transition {{ !empty($blocks[$key]['is_active']) ? 'bg-emerald-600' : 'bg-zinc-300 dark:bg-zinc-700' }}">
                                <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition {{ !empty($blocks[$key]['is_active']) ? 'left-[1.125rem]' : 'left-0.5' }}"></span>
                            </span>
                            <span>{{ !empty($blocks[$key]['is_active']) ? 'Sección Visible' : 'Sección Oculta' }}</span>
                        </button>
                    </div>

                    {{-- Bloque Hero Especial --}}
                    @if($key === 'hero')
                        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-50/40 p-4 sm:p-5 dark:border-emerald-500/20 dark:bg-emerald-950/20 space-y-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Identidad del Fundo</p>
                                <h3 class="mt-0.5 text-base font-extrabold text-zinc-900 dark:text-white">Datos de Portada y Contacto</h3>
                                <p class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">Configura el nombre público, ubicación geográfica y botón flotante de WhatsApp.</p>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,.8fr)]">
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Fundo institucional</label>
                                    <x-filter-select model="blocks.hero.settings.public_fundo_id" :options="$fundos->pluck('nombre', 'id')->prepend('Primer fundo activo', '')->toArray()" tone="emerald" live="true" />
                                    @error('blocks.hero.settings.public_fundo_id')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                                </div>
                                <div class="rounded-xl border border-zinc-200/90 bg-white p-3.5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                                    <small class="text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Nombre cargado desde el sistema</small>
                                    <strong class="mt-1 block text-sm font-black text-zinc-900 dark:text-white">{{ $publicFundo?->nombre ?? 'Sin selección' }}</strong>
                                    <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">Se mostrará como título principal en la web.</span>
                                </div>
                            </div>

                            <div class="grid gap-3.5 sm:grid-cols-2">
                                {{-- Propietario --}}
                                <div class="space-y-2 rounded-xl border border-zinc-200/90 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:col-span-2">
                                    <label class="flex cursor-pointer items-center gap-2.5">
                                        <input type="checkbox" wire:model="blocks.hero.settings.show_owner" class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">
                                        <strong class="text-xs font-bold text-zinc-900 dark:text-white">Mostrar Propietario / Familia en Portada</strong>
                                    </label>
                                    <input type="text" wire:model="blocks.hero.settings.owner_name" placeholder="Ej: Familia Choquenaira"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm font-bold text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                    <small class="block text-[11px] text-zinc-500 dark:text-zinc-400">Aparecerá en un destacado especial y elegante en la portada principal (#inicio).</small>
                                </div>

                                {{-- Ubicación --}}
                                <div class="space-y-2 rounded-xl border border-zinc-200/90 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                                    <label class="flex cursor-pointer items-center gap-2.5">
                                        <input type="checkbox" wire:model="blocks.hero.settings.show_location" class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">
                                        <strong class="text-xs font-bold text-zinc-900 dark:text-white">Mostrar Ubicación General</strong>
                                    </label>
                                    <input type="text" wire:model="blocks.hero.settings.custom_location" placeholder="Ej: Cusco - Canas - Kunturkanki"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                    @error('blocks.hero.settings.custom_location')<span class="block text-[11px] text-rose-500">{{ $message }}</span>@enderror
                                    <small class="block text-[11px] text-zinc-500 dark:text-zinc-400">Texto manual legible para visitantes.</small>
                                </div>

                                {{-- Dirección --}}
                                <div class="space-y-2 rounded-xl border border-zinc-200/90 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                                    <label class="flex cursor-pointer items-center gap-2.5">
                                        <input type="checkbox" wire:model="blocks.hero.settings.show_address" class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">
                                        <strong class="text-xs font-bold text-zinc-900 dark:text-white">Mostrar Dirección / Predio</strong>
                                    </label>
                                    <input type="text" wire:model="blocks.hero.settings.custom_address" placeholder="Ej: Predio Fundo Ccolqque Parque Texas"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                    @error('blocks.hero.settings.custom_address')<span class="block text-[11px] text-rose-500">{{ $message }}</span>@enderror
                                    <small class="block text-[11px] text-zinc-500 dark:text-zinc-400">Dirección visible al público.</small>
                                </div>

                                {{-- WhatsApp --}}
                                <div class="space-y-3 rounded-xl border border-zinc-200/90 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:col-span-2">
                                    <label class="flex cursor-pointer items-center gap-2.5">
                                        <input type="checkbox" wire:model="blocks.hero.settings.show_whatsapp" class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-950">
                                        <strong class="text-xs font-bold text-zinc-900 dark:text-white">Mostrar botón flotante de WhatsApp</strong>
                                    </label>
                                    <div class="grid gap-3 lg:grid-cols-[minmax(14rem,.65fr)_minmax(0,1fr)]">
                                        <div>
                                            <span class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Número con código de país</span>
                                            <input type="tel" wire:model="blocks.hero.settings.whatsapp_number" inputmode="tel" maxlength="25" placeholder="Ej: 51987654321"
                                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                            @error('blocks.hero.settings.whatsapp_number')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                                        </div>
                                        <div>
                                            <span class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Mensaje de bienvenida predeterminado</span>
                                            <input type="text" wire:model="blocks.hero.settings.whatsapp_message" maxlength="180" placeholder="Hola, deseo recibir información sobre los productos del fundo..."
                                                   class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                            @error('blocks.hero.settings.whatsapp_message')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <small class="block text-[11px] text-zinc-500 dark:text-zinc-400">Permite a los clientes contactarse con un solo clic.</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Formulario de Textos + Multimedia --}}
                    <div class="grid gap-6 2xl:grid-cols-[minmax(20rem,.8fr)_minmax(0,1.2fr)]">
                        {{-- Textos y Redacción --}}
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-black text-zinc-900 dark:text-white">Textos de la Sección</h3>
                                <button type="button" x-on:click="confirmDelete('¿Usar textos sugeridos?', 'Reemplazará título, descripción y etiquetas sin borrar fotos. Guarda después para publicar.').then((res) => { if (res.isConfirmed) keepScroll(() => $wire.resetSectionDefaults('{{ $key }}')) })"
                                        class="text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 underline">
                                    Usar sugerencia
                                </button>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Etiqueta superior (Kicker)</label>
                                <input type="text" maxlength="60" wire:model="blocks.{{ $key }}.settings.eyebrow" placeholder="Ej: Manejo responsable"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                @error("blocks.$key.settings.eyebrow")<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Título público</label>
                                <input type="text" maxlength="140" wire:model="blocks.{{ $key }}.title" placeholder="{{ $blocks[$key]['suggested_title'] }}"
                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                @error("blocks.$key.title")<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Descripción o mensaje</label>
                                <textarea rows="6" maxlength="2500" wire:model="blocks.{{ $key }}.content" placeholder="{{ $blocks[$key]['suggested_content'] }}"
                                          class="w-full rounded-xl border border-zinc-300 bg-white p-3.5 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500"></textarea>
                                <span class="mt-1 block text-[11px] text-zinc-500 dark:text-zinc-400">Texto informativo e institucional redactado para el público general.</span>
                                @error("blocks.$key.content")<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror
                            </div>

                            @if($key === 'hero')
                                <div class="grid gap-3.5 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Texto del botón principal</label>
                                        <input type="text" maxlength="40" wire:model="blocks.hero.settings.cta_label"
                                               class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Estilo de Portada</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach(['carousel' => 'Carrusel', 'single' => 'Foto fija'] as $mode => $modeLabel)
                                                <label class="cursor-pointer">
                                                    <input type="radio" wire:model="blocks.hero.settings.hero_mode" value="{{ $mode }}" class="peer sr-only">
                                                    <span class="flex h-11 items-center justify-center rounded-xl border border-zinc-300 bg-white px-2 text-xs font-bold text-zinc-700 transition peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300 dark:peer-checked:border-emerald-500 dark:peer-checked:bg-emerald-500/15 dark:peer-checked:text-emerald-300">
                                                        {{ $modeLabel }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @elseif($key === 'galeria')
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Máximo de fotos públicas en galería</label>
                                    <input type="number" min="8" max="48" wire:model="blocks.galeria.settings.max_images"
                                           class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                    <small class="mt-1 block text-[11px] text-zinc-500 dark:text-zinc-400">Entre 8 y 48. Reúne fotos de todas las áreas del fundo.</small>
                                </div>
                            @else
                                <div>
                                    <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Tres puntos clave destacados</label>
                                    <div class="space-y-2">
                                        @foreach(range(1, 3) as $feature)
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-xs font-black text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-400">
                                                    {{ $feature }}
                                                </span>
                                                <input type="text" maxlength="70" wire:model="blocks.{{ $key }}.settings.feature_{{ $feature }}"
                                                       class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder-zinc-500">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Biblioteca Visual y Fotografías --}}
                        <div class="min-w-0" x-data="directUploadProgress">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-sm font-black text-zinc-900 dark:text-white">Fotografías de la Sección</h3>
                                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">La primera foto actúa como portada principal de esta área.</p>
                                </div>
                                <div>
                                    <button type="button" x-on:click="$refs.landingFiles.click()" x-bind:disabled="busy || $store?.imageUploads?.busy"
                                            class="inline-flex h-11 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-xs sm:text-sm font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 4v12m-5-7 5-5 5 5M5 15v4h14v-4"/></svg>
                                        <span>Subir imágenes</span>
                                    </button>
                                    <input x-ref="landingFiles" wire:model="uploads.{{ $key }}" x-on:change="captureFiles($event)" type="file" multiple accept="image/jpeg,image/png,image/webp" tabindex="-1" class="sr-only" x-bind:disabled="busy">
                                </div>
                            </div>

                            <div x-cloak x-show="busy" class="mt-3 w-full rounded-xl border border-emerald-500/30 bg-emerald-50/50 p-3 text-xs font-bold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-950/30 dark:text-emerald-200" role="status" aria-live="polite">
                                <div class="flex items-center justify-between gap-3"><span>Subiendo imágenes...</span><span x-text="`${progress}%`"></span></div>
                                <progress max="100" x-bind:value="progress" class="mt-2 block h-1.5 w-full overflow-hidden rounded-full"></progress>
                                <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
                                    <template x-for="url in previewUrls" :key="url"><img :src="url" alt="Vista previa local" class="aspect-[4/3] w-full rounded-lg object-contain"></template>
                                </div>
                            </div>
                            <p x-cloak x-show="error" x-text="error" class="mt-2 text-xs text-rose-500" role="alert"></p>

                            @if(count($uploads[$key] ?? []) > 0)
                                <div class="mt-3 rounded-xl border border-emerald-500/30 bg-emerald-50/50 p-3 text-xs text-emerald-900 dark:border-emerald-500/20 dark:bg-emerald-950/30 dark:text-emerald-200">
                                    <strong>{{ count($uploads[$key]) }} archivo(s) listo(s).</strong> Ajusta el encuadre si lo deseas y presiona guardar para publicar.
                                </div>
                                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($uploads[$key] as $uploadIndex => $upload)
                                        @if(!$errors->has("uploads.$key.$uploadIndex"))
                                            @php
                                                $pendingUrl = $upload->temporaryUrl();
                                                $pendingFrame = \App\Support\ImageFrame::normalize($uploadFrames[$key][$uploadIndex] ?? null);
                                            @endphp
                                            <div wire:key="landing-pending-{{ $key }}-{{ $uploadIndex }}-{{ $upload->getFilename() }}" class="relative aspect-[4/3] overflow-hidden rounded-xl border border-emerald-400 bg-emerald-50 shadow-sm dark:border-emerald-500/40 dark:bg-emerald-950/40">
                                                <img src="{{ $pendingUrl }}" alt="Imagen nueva pendiente" class="h-full w-full object-cover" style="object-position: {{ $pendingFrame['x'] }}% {{ $pendingFrame['y'] }}%; transform: scale({{ $pendingFrame['zoom'] }}); transform-origin: {{ $pendingFrame['x'] }}% {{ $pendingFrame['y'] }}%;">
                                                <span class="absolute left-2 top-2 rounded-md bg-emerald-700 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white shadow-sm">Nueva</span>
                                                <x-image-frame-editor id="landing-pending-frame-{{ $key }}-{{ $uploadIndex }}" :src="$pendingUrl" x-model="uploadFrames.{{ $key }}.{{ $uploadIndex }}.x" y-model="uploadFrames.{{ $key }}.{{ $uploadIndex }}.y" zoom-model="uploadFrames.{{ $key }}.{{ $uploadIndex }}.zoom" mode="simple" title="Ajustar imagen" description="Arrastra la imagen y usa el zoom para encuadrar." />
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            @error("uploads.$key")<p class="mt-2 text-xs text-rose-500">{{ $message }}</p>@enderror
                            @error("uploads.$key.*")<p class="mt-2 text-xs text-rose-500">{{ $message }}</p>@enderror

                            @if(count($blocks[$key]['media']) > 0)
                                @php
                                    $coverId = null;
                                    foreach ($blocks[$key]['media'] as $mid => $m) {
                                        if (! empty($m['portada'])) { $coverId = $mid; break; }
                                    }
                                    if ($coverId === null) { $coverId = array_key_first($blocks[$key]['media']); }
                                @endphp
                                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($blocks[$key]['media'] as $id => $media)
                                        @php($isCover = (string) $id === (string) $coverId)
                                        <article wire:key="landing-media-{{ $key }}-{{ $id }}" class="overflow-hidden rounded-2xl border {{ $isCover ? 'border-emerald-500 ring-2 ring-emerald-500/25' : 'border-zinc-200/90 dark:border-zinc-800' }} bg-white dark:bg-zinc-950 shadow-sm">
                                            <div class="relative aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-900">
                                                <img src="{{ $media['preview_url'] }}" alt="" class="h-full w-full object-cover" style="object-position: {{ $media['focus_x'] }}% {{ $media['focus_y'] }}%; transform: scale({{ $media['zoom'] }}); transform-origin: {{ $media['focus_x'] }}% {{ $media['focus_y'] }}%">
                                                @if($isCover)
                                                    <span class="absolute left-2 top-2 rounded-lg bg-emerald-600 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-white shadow-sm">Portada</span>
                                                @endif
                                                <button type="button" x-on:click="keepScroll(() => $wire.openFrameEditor('{{ $key }}', {{ $media['id'] }}), false)" class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-lg border border-white/30 bg-black/70 px-2 py-1 text-[9px] font-black text-white backdrop-blur-md transition hover:bg-black/85" aria-label="Ajustar encuadre de imagen">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8V5a1 1 0 0 1 1-1h3m8 0h3a1 1 0 0 1 1 1v3m0 8v3a1 1 0 0 1-1 1h-3m-8 0H5a1 1 0 0 1-1-1v-3M8 12h8m-4-4v8"/></svg>
                                                    <span>Ajustar</span>
                                                </button>
                                                <span class="absolute bottom-2 right-2 rounded-md bg-black/60 px-1.5 py-0.5 text-[9px] font-medium text-white">{{ number_format($media['size'] / 1024 / 1024, 1) }} MB</span>
                                            </div>
                                            <div class="space-y-2 p-3">
                                                <input wire:key="landing-caption-{{ $key }}-{{ $id }}" type="text" maxlength="120" wire:model.defer="blocks.{{ $key }}.media.{{ $id }}.caption" placeholder="Descripción breve de foto"
                                                       class="h-8 w-full rounded-lg border border-zinc-300 bg-white px-2.5 text-xs text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500">
                                                <div class="flex items-center gap-1.5">
                                                    <button type="button" x-on:click="keepScroll(() => $wire.moveMedia('{{ $key }}', {{ $media['id'] }}, 'up'))" @disabled($loop->first)
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-600 shadow-sm transition hover:bg-zinc-50 disabled:opacity-30 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800" aria-label="Mover antes">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 15 6-6 6 6"/></svg>
                                                    </button>
                                                    <button type="button" x-on:click="keepScroll(() => $wire.moveMedia('{{ $key }}', {{ $media['id'] }}, 'down'))" @disabled($loop->last)
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-600 shadow-sm transition hover:bg-zinc-50 disabled:opacity-30 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800" aria-label="Mover después">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                                                    </button>
                                                    @if(! $isCover)
                                                        <button type="button" x-on:click="keepScroll(() => $wire.setAsCover('{{ $key }}', {{ $media['id'] }}))" wire:loading.attr="disabled" wire:target="setAsCover('{{ $key }}', {{ $media['id'] }})"
                                                                class="flex-1 h-8 rounded-lg border border-emerald-200 px-2 text-[10px] font-bold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-400 dark:hover:bg-emerald-950/30 disabled:opacity-60">
                                                            <span>Hacer portada</span>
                                                        </button>
                                                    @endif
                                                    <button type="button" x-on:click="confirmDelete('¿Eliminar imagen?', 'Archivo y conversiones se eliminarán definitivamente.').then((res) => { if (res.isConfirmed) keepScroll(() => $wire.deleteMedia('{{ $key }}', {{ $media['id'] }})) })"
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-600 shadow-sm transition hover:bg-rose-50 dark:border-rose-900/40 dark:bg-zinc-900 dark:text-rose-400 dark:hover:bg-rose-950/30" aria-label="Eliminar imagen">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h14m-9 4v5m4-5v5M8 7l1 12h6l1-12m-6 0V4h4v3"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4 grid min-h-44 place-items-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 p-6 text-center dark:border-zinc-800 dark:bg-zinc-950/50">
                                    <div>
                                        <svg class="mx-auto h-8 w-8 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.5" d="M4 19V5h16v14H4Zm3-4 3-3 2.5 2.5L16 10l3 4M8 9h.01"/></svg>
                                        <strong class="mt-3 block text-sm font-bold text-zinc-800 dark:text-zinc-200">Sin fotografías para esta sección</strong>
                                        <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">Sube imágenes en formato JPG, PNG o WebP.</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer de Guardar Sección --}}
                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Máximo 40 fotos por sección, 8 por carga, 15 MB por archivo.</span>
                        <button type="button" x-on:click="keepScroll(() => $wire.saveBlock('{{ $key }}'))" x-bind:disabled="$store?.imageUploads?.busy" wire:loading.attr="disabled" wire:target="saveBlock('{{ $key }}'),uploads.{{ $key }}"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 text-xs sm:text-sm font-black text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 w-full sm:w-auto">
                            <span wire:loading.remove wire:target="saveBlock('{{ $key }}')">Guardar {{ strtolower($label) }}</span>
                            <span wire:loading wire:target="saveBlock('{{ $key }}')">Guardando sección...</span>
                        </button>
                    </div>
                </section>
            @endforeach
        </main>
    </div>

    @if($showFrameEditor)
        <x-image-frame-editor
            id="landing-frame-editor"
            :src="$framePreviewUrl"
            x-model="frameX"
            y-model="frameY"
            zoom-model="frameZoom"
            :initially-open="true"
            save-action="saveFrame"
            close-action="closeFrameEditor"
            apply-label="Guardar encuadre"
            mode="simple"
            title="Ajustar imagen"
            description="Arrastra la imagen para moverla y usa el zoom para encuadrar a tu gusto."
        >
            <div class="space-y-1" aria-live="polite">
                @error('frameX')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                @error('frameY')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                @error('frameZoom')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>
        </x-image-frame-editor>
    @endif
</div>
