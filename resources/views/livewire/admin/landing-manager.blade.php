<div
    class="mx-auto max-w-[96rem] space-y-5"
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
    <header class="agro-card overflow-hidden p-4 sm:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4V5Zm0 4h16M8 13h3m-3 3h8"/></svg>
                </span>
                <div><p class="agro-kicker">Administración</p><h1 class="agro-title mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">Gestión Web Pública</h1><p class="mt-1 max-w-2xl text-sm text-zinc-500">Controla identidad, textos, visibilidad y galería. Solo publica información institucional.</p></div>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <a href="{{ route('home') }}#inicio" target="_blank" rel="noopener" class="agro-button-secondary w-full sm:w-auto"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M14 5h5v5m0-5-8 8M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>Vista pública</a>
                <button type="button" x-on:click="keepScroll(() => $wire.saveBlock('hero'))" x-bind:disabled="$store.imageUploads.busy" wire:loading.attr="disabled" wire:target="saveBlock('hero')" class="agro-button w-full sm:w-auto"><span wire:loading.remove wire:target="saveBlock('hero')">Guardar identidad</span><span wire:loading wire:target="saveBlock('hero')">Guardando...</span></button>
            </div>
        </div>
    </header>

    <section class="grid gap-3 sm:grid-cols-3">
        <article class="agro-card p-4"><span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Fundo publicado</span><strong class="mt-2 block truncate text-sm">{{ $publicFundo?->nombre ?? 'Sin fundo activo' }}</strong><small class="mt-1 block truncate text-zinc-500">{{ !empty($blocks['hero']['settings']['show_location']) && filled($blocks['hero']['settings']['custom_location'] ?? null) ? $blocks['hero']['settings']['custom_location'] : 'Ubicación pública oculta' }}</small></article>
        <article class="agro-card p-4"><span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Secciones visibles</span><strong class="mt-2 block text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ collect($blocks)->where('is_active', true)->count() }} / {{ count($sections) }}</strong><small class="mt-1 block text-zinc-500">Cambios visibles al guardar</small></article>
        <article class="agro-card p-4"><span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Biblioteca visual</span><strong class="mt-2 block text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ collect($blocks)->sum(fn ($block) => count($block['media'] ?? [])) }}</strong><small class="mt-1 block text-zinc-500">Imágenes optimizadas para web</small></article>
    </section>

    <div class="grid gap-5 xl:grid-cols-[17rem_minmax(0,1fr)]">
        <aside class="h-fit xl:sticky xl:top-24">
            <nav class="agro-card grid grid-cols-2 gap-1.5 p-2 xl:grid-cols-1" aria-label="Secciones de la web">
                @foreach($sections as $key => $label)
                    <button type="button" @click="selectTab('{{ $key }}')" :class="activeTab === '{{ $key }}' ? 'bg-emerald-700 text-white shadow-md dark:bg-emerald-400 dark:text-emerald-950' : 'text-zinc-600 hover:bg-emerald-50 hover:text-emerald-800 dark:text-zinc-300 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-200'" class="flex min-w-0 items-center gap-3 rounded-xl px-3 py-3 text-left transition">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-700/10 text-[10px] font-black text-emerald-900 dark:bg-emerald-300/15 dark:text-emerald-100">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="min-w-0 flex-1"><strong class="block truncate text-xs">{{ $label }}</strong><small class="mt-0.5 block text-[9px] opacity-70">{{ !empty($blocks[$key]['is_active']) ? 'Visible' : 'Oculta' }} · {{ count($blocks[$key]['media'] ?? []) }} fotos</small></span>
                        <span class="h-2 w-2 shrink-0 rounded-full {{ !empty($blocks[$key]['is_active']) ? 'bg-emerald-400 ring-2 ring-emerald-400/20' : 'bg-zinc-400' }}"></span>
                    </button>
                @endforeach
            </nav>

            <div class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs leading-5 text-emerald-900 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
                <strong class="block">Información pública segura</strong>
                <span class="mt-1 block opacity-80">No publiques RUC, datos financieros, credenciales, documentos privados ni información personal.</span>
            </div>
        </aside>

        <main class="min-w-0">
            @foreach($sections as $key => $label)
                <section x-cloak x-show="activeTab === '{{ $key }}'" x-transition.opacity.duration.150ms class="agro-card overflow-hidden">
                    <header class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div><p class="agro-kicker">Sección {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p><h2 class="mt-1 text-xl font-extrabold">{{ $label }}</h2><p class="mt-1 text-xs text-zinc-500">{{ $key === 'hero' ? 'Identidad, llamada principal y portada.' : ($key === 'galeria' ? 'Presentación y fotografías exclusivas de galería.' : 'Texto, puntos clave y fotografías del área.') }}</p></div>
                        <button type="button" x-on:click="keepScroll(() => $wire.toggleActive('{{ $key }}'))" class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 text-xs font-bold dark:border-zinc-700 dark:bg-zinc-900">
                            <span class="relative h-5 w-9 rounded-full transition {{ !empty($blocks[$key]['is_active']) ? 'bg-emerald-600' : 'bg-zinc-300 dark:bg-zinc-600' }}"><span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition {{ !empty($blocks[$key]['is_active']) ? 'left-[1.125rem]' : 'left-0.5' }}"></span></span>
                            {{ !empty($blocks[$key]['is_active']) ? 'Visible' : 'Oculta' }}
                        </button>
                    </header>

                    <div class="space-y-6 p-4 sm:p-6">
                        @if($key === 'hero')
                            <section class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-400/20 dark:bg-emerald-400/[.06] sm:p-5">
                                <div class="mb-4"><p class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Identidad pública</p><h3 class="mt-1 text-base font-extrabold">Fundo mostrado al público</h3><p class="mt-1 text-xs text-zinc-500">El nombre viene del fundo. La ubicación pública se escribe manualmente y no usa GPS.</p></div>
                                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,.8fr)]">
                                    <label class="block"><span class="mb-1.5 block text-xs font-bold">Seleccionar fundo</span><x-filter-select model="blocks.hero.settings.public_fundo_id" :options="$fundos->pluck('nombre', 'id')->prepend('Primer fundo activo', '')->toArray()" tone="emerald" live="true" />@error('blocks.hero.settings.public_fundo_id')<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror</label>
                                    <div class="rounded-xl border border-emerald-200 bg-white p-3 dark:border-emerald-400/20 dark:bg-zinc-800/80"><small class="text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Nombre desde la base de datos</small><strong class="mt-1 block text-sm text-zinc-900 dark:text-zinc-100">{{ $publicFundo?->nombre ?? 'Sin selección' }}</strong><span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">La ubicación interna no se publica.</span></div>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="space-y-2 rounded-xl border border-emerald-950/10 bg-white p-3 dark:border-emerald-400/20 dark:bg-zinc-800/80 sm:col-span-2">
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input type="checkbox" wire:model="blocks.hero.settings.show_owner" class="agro-checkbox h-4 w-4 rounded">
                                            <strong class="text-xs font-bold text-zinc-900 dark:text-emerald-100">Mostrar Propietario / Familia en Portada</strong>
                                        </label>
                                        <input type="text" wire:model="blocks.hero.settings.owner_name" placeholder="Ej: Familia Choquenaira" class="agro-input font-bold">
                                        <small class="block text-[10px] text-zinc-500 dark:text-zinc-400">Aparecerá en un destacado especial y elegante en la portada principal (#inicio).</small>
                                    </div>
                                    <div class="space-y-2 rounded-xl border border-emerald-950/10 bg-white p-3 dark:border-emerald-400/20 dark:bg-zinc-800/80">
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input type="checkbox" wire:model="blocks.hero.settings.show_location" class="agro-checkbox h-4 w-4 rounded">
                                            <strong class="text-xs font-bold text-zinc-900 dark:text-emerald-100">Mostrar Ubicación</strong>
                                        </label>
                                        <input type="text" wire:model="blocks.hero.settings.custom_location" placeholder="Ej: Cusco - Canas - Kunturkanki" class="agro-input">
                                        @error('blocks.hero.settings.custom_location')<span class="block text-[10px] text-rose-500">{{ $message }}</span>@enderror
                                        <small class="block text-[10px] text-zinc-500 dark:text-zinc-400">Texto manual. Si desactivas la opción, no se mostrará ubicación.</small>
                                    </div>
                                    <div class="space-y-2 rounded-xl border border-emerald-950/10 bg-white p-3 dark:border-emerald-400/20 dark:bg-zinc-800/80">
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input type="checkbox" wire:model="blocks.hero.settings.show_address" class="agro-checkbox h-4 w-4 rounded">
                                            <strong class="text-xs font-bold text-zinc-900 dark:text-emerald-100">Mostrar Dirección Exacta</strong>
                                        </label>
                                        <input type="text" wire:model="blocks.hero.settings.custom_address" placeholder="Ej: Predio Fundo Ccolqque Parque Texas" class="agro-input">
                                        @error('blocks.hero.settings.custom_address')<span class="block text-[10px] text-rose-500">{{ $message }}</span>@enderror
                                        <small class="block text-[10px] text-zinc-500 dark:text-zinc-400">Dirección visible al público.</small>
                                    </div>
                                    <div class="space-y-3 rounded-xl border border-emerald-200 bg-white p-3 dark:border-emerald-400/20 dark:bg-zinc-800/80 sm:col-span-2">
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input type="checkbox" wire:model="blocks.hero.settings.show_whatsapp" class="agro-checkbox h-4 w-4 rounded">
                                            <strong class="text-xs font-bold text-zinc-900 dark:text-emerald-100">Mostrar botón flotante de WhatsApp</strong>
                                        </label>
                                        <div class="grid gap-3 lg:grid-cols-[minmax(14rem,.65fr)_minmax(0,1fr)]">
                                            <label class="block"><span class="mb-1 block text-[10px] font-bold text-zinc-600 dark:text-zinc-300">Número con código de país</span><input type="tel" wire:model="blocks.hero.settings.whatsapp_number" inputmode="tel" maxlength="25" placeholder="Ej: 51987654321" class="agro-input">@error('blocks.hero.settings.whatsapp_number')<span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span>@enderror</label>
                                            <label class="block"><span class="mb-1 block text-[10px] font-bold text-zinc-600 dark:text-zinc-300">Mensaje inicial</span><input type="text" wire:model="blocks.hero.settings.whatsapp_message" maxlength="180" placeholder="Hola, deseo recibir información..." class="agro-input">@error('blocks.hero.settings.whatsapp_message')<span class="mt-1 block text-[10px] text-rose-500">{{ $message }}</span>@enderror</label>
                                        </div>
                                        <small class="block text-[10px] text-zinc-500 dark:text-zinc-400">Puedes cambiar el número cuando quieras. Usa el código internacional, por ejemplo 51 para Perú.</small>
                                    </div>
                                </div>
                            </section>
                        @endif

                        <div class="grid gap-6 2xl:grid-cols-[minmax(20rem,.75fr)_minmax(0,1.25fr)]">
                            <section class="space-y-4">
                                <div class="flex items-center justify-between gap-3"><h3 class="text-sm font-extrabold">Contenido</h3><button type="button" x-on:click="confirmDelete('¿Usar textos sugeridos?', 'Reemplazará título, descripción y etiquetas sin borrar fotos. Guarda después para publicar.').then((res) => { if (res.isConfirmed) keepScroll(() => $wire.resetSectionDefaults('{{ $key }}')) })" class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300">Usar sugerencia</button></div>

                                <label class="block"><span class="mb-1.5 block text-xs font-bold">Etiqueta superior</span><input type="text" maxlength="60" wire:model="blocks.{{ $key }}.settings.eyebrow" placeholder="Ej: Manejo responsable" class="agro-input">@error("blocks.$key.settings.eyebrow")<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror</label>
                                <label class="block"><span class="mb-1.5 block text-xs font-bold">Título público</span><input type="text" maxlength="140" wire:model="blocks.{{ $key }}.title" placeholder="{{ $blocks[$key]['suggested_title'] }}" class="agro-input">@error("blocks.$key.title")<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror</label>
                                <label class="block"><span class="mb-1.5 block text-xs font-bold">Descripción</span><textarea rows="6" maxlength="2500" wire:model="blocks.{{ $key }}.content" placeholder="{{ $blocks[$key]['suggested_content'] }}" class="agro-input resize-y leading-6"></textarea><span class="mt-1 block text-[10px] text-zinc-500 dark:text-zinc-400">Texto informativo. No incluyas datos internos.</span>@error("blocks.$key.content")<span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>@enderror</label>

                                @if($key === 'hero')
                                    <div class="grid gap-3 sm:grid-cols-2"><label><span class="mb-1.5 block text-xs font-bold">Texto del botón</span><input type="text" maxlength="40" wire:model="blocks.hero.settings.cta_label" class="agro-input"></label><fieldset><legend class="mb-1.5 text-xs font-bold">Portada</legend><div class="grid grid-cols-2 gap-2">@foreach(['carousel' => 'Carrusel', 'single' => 'Foto fija'] as $mode => $modeLabel)<label class="cursor-pointer"><input type="radio" wire:model="blocks.hero.settings.hero_mode" value="{{ $mode }}" class="peer sr-only"><span class="flex min-h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 px-2 text-xs font-bold peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-400/15 dark:peer-checked:text-emerald-200">{{ $modeLabel }}</span></label>@endforeach</div></fieldset></div>
                                @elseif($key === 'galeria')
                                    <label class="block"><span class="mb-1.5 block text-xs font-bold">Máximo de fotos públicas</span><input type="number" min="8" max="48" wire:model="blocks.galeria.settings.max_images" class="agro-input"><small class="mt-1 block text-[10px] text-zinc-500">Entre 8 y 48. Incluye fotos de todas las áreas.</small></label>
                                @else
                                    <fieldset><legend class="mb-2 text-xs font-bold">Tres puntos destacados</legend><div class="space-y-2">@foreach(range(1, 3) as $feature)<label class="flex items-center gap-2"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-[10px] font-black text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">{{ $feature }}</span><input type="text" maxlength="70" wire:model="blocks.{{ $key }}.settings.feature_{{ $feature }}" class="agro-input"></label>@endforeach</div></fieldset>
                                @endif
                            </section>

                            <section class="min-w-0" x-data="directUploadProgress">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h3 class="text-sm font-extrabold">Biblioteca visual</h3><p class="mt-1 text-[10px] text-zinc-500">Primera foto funciona como portada. Orden afecta web pública.</p></div><div><button type="button" x-on:click="$refs.landingFiles.click()" x-bind:disabled="busy || $store.imageUploads.busy" class="agro-button-secondary text-xs"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 4v12m-5-7 5-5 5 5M5 15v4h14v-4"/></svg>Seleccionar imágenes</button><input x-ref="landingFiles" wire:model="uploads.{{ $key }}" x-on:change="captureFiles($event)" type="file" multiple accept="image/jpeg,image/png,image/webp" tabindex="-1" class="sr-only" x-bind:disabled="busy"></div></div>

                                <div x-cloak x-show="busy" class="mt-3 w-full rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200" role="status" aria-live="polite">
                                    <div class="flex items-center justify-between gap-3"><span>Subiendo imágenes...</span><span x-text="`${progress}%`"></span></div>
                                    <progress max="100" x-bind:value="progress" class="mt-2 block h-1.5 w-full overflow-hidden rounded-full"></progress>
                                    <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
                                        <template x-for="url in previewUrls" :key="url"><img :src="url" alt="Vista previa local" class="aspect-[4/3] w-full rounded-lg object-contain"></template>
                                    </div>
                                </div>
                                <p x-cloak x-show="error" x-text="error" class="mt-2 text-xs text-rose-500" role="alert"></p>
                                @if(count($uploads[$key] ?? []) > 0)
                                    <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 text-xs text-emerald-900 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200"><strong>{{ count($uploads[$key]) }} archivo(s) listo(s).</strong> Ajusta cada foto y guarda sección para publicar.</div>
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($uploads[$key] as $uploadIndex => $upload)
                                            @if(!$errors->has("uploads.$key.$uploadIndex"))
                                                @php
                                                    $pendingUrl = $upload->temporaryUrl();
                                                    $pendingFrame = \App\Support\ImageFrame::normalize($uploadFrames[$key][$uploadIndex] ?? null);
                                                @endphp
                                                <div wire:key="landing-pending-{{ $key }}-{{ $uploadIndex }}-{{ $upload->getFilename() }}" class="relative aspect-[4/3] overflow-hidden rounded-xl border border-emerald-300 bg-emerald-50 dark:border-emerald-400/25 dark:bg-emerald-950/30">
                                                    <img src="{{ $pendingUrl }}" alt="Imagen nueva pendiente" class="h-full w-full object-cover" style="object-position: {{ $pendingFrame['x'] }}% {{ $pendingFrame['y'] }}%; transform: scale({{ $pendingFrame['zoom'] }}); transform-origin: {{ $pendingFrame['x'] }}% {{ $pendingFrame['y'] }}%;">
                                                    <span class="absolute left-2 top-2 rounded-md bg-emerald-800/85 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-white">Nueva</span>
                                                    <x-image-frame-editor id="landing-pending-frame-{{ $key }}-{{ $uploadIndex }}" :src="$pendingUrl" x-model="uploadFrames.{{ $key }}.{{ $uploadIndex }}.x" y-model="uploadFrames.{{ $key }}.{{ $uploadIndex }}.y" zoom-model="uploadFrames.{{ $key }}.{{ $uploadIndex }}.zoom" />
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                @error("uploads.$key")<p class="mt-2 text-xs text-rose-500">{{ $message }}</p>@enderror
                                @error("uploads.$key.*")<p class="mt-2 text-xs text-rose-500">{{ $message }}</p>@enderror

                                @if(count($blocks[$key]['media']) > 0)
                                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($blocks[$key]['media'] as $index => $media)
                                            <article wire:key="landing-media-{{ $key }}-{{ $media['id'] }}" class="overflow-hidden rounded-2xl border {{ $index === 0 ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-zinc-200 dark:border-zinc-700' }} bg-zinc-50 dark:bg-zinc-900">
                                                <div class="relative aspect-[4/3] overflow-hidden">
                                                    <img src="{{ $media['preview_url'] }}" alt="" class="h-full w-full object-cover" style="object-position: {{ $media['focus_x'] }}% {{ $media['focus_y'] }}%; transform: scale({{ $media['zoom'] }}); transform-origin: {{ $media['focus_x'] }}% {{ $media['focus_y'] }}%">
                                                    @if($index === 0)<span class="absolute left-2 top-2 rounded-lg bg-emerald-700 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-white">Portada</span>@endif
                                                    <button type="button" x-on:click="keepScroll(() => $wire.openFrameEditor('{{ $key }}', {{ $media['id'] }}), false)" class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-lg border border-white/25 bg-black/65 px-2 py-1 text-[9px] font-black text-white backdrop-blur-md" aria-label="Ajustar encuadre de imagen"><svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8V5a1 1 0 0 1 1-1h3m8 0h3a1 1 0 0 1 1 1v3m0 8v3a1 1 0 0 1-1 1h-3m-8 0H5a1 1 0 0 1-1-1v-3M8 12h8m-4-4v8"/></svg>Ajustar</button>
                                                    <span class="absolute bottom-2 right-2 rounded-md bg-black/60 px-1.5 py-0.5 text-[9px] text-white">{{ number_format($media['size'] / 1024 / 1024, 1) }} MB</span>
                                                </div>
                                                <div class="space-y-2 p-3">
                                                    <label><span class="sr-only">Descripción de imagen</span><input type="text" maxlength="120" wire:model="blocks.{{ $key }}.media.{{ $index }}.caption" placeholder="Descripción breve de foto" class="agro-input py-1.5 text-[11px]"></label>
                                                    <div class="flex items-center gap-1.5">
                                                        <button type="button" x-on:click="keepScroll(() => $wire.moveMedia('{{ $key }}', {{ $media['id'] }}, 'up'))" @disabled($index === 0) class="agro-icon-button !h-8 !w-8 disabled:opacity-30" aria-label="Mover antes"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 15 6-6 6 6"/></svg></button>
                                                        <button type="button" x-on:click="keepScroll(() => $wire.moveMedia('{{ $key }}', {{ $media['id'] }}, 'down'))" @disabled($index === count($blocks[$key]['media']) - 1) class="agro-icon-button !h-8 !w-8 disabled:opacity-30" aria-label="Mover después"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg></button>
                                                        @if($index !== 0)
                                                            <button type="button" x-on:click="keepScroll(() => $wire.setAsCover('{{ $key }}', {{ $media['id'] }}))" wire:loading.attr="disabled" wire:target="setAsCover('{{ $key }}', {{ $media['id'] }})" class="flex-1 rounded-lg border border-emerald-200 px-2 py-2 text-[9px] font-bold text-emerald-700 disabled:opacity-60 dark:border-emerald-400/20 dark:text-emerald-300">
                                                                 <span>Hacer portada</span>
                                                            </button>
                                                        @endif
                                                        <button type="button" x-on:click="confirmDelete('¿Eliminar imagen?', 'Archivo y conversiones se eliminarán definitivamente.').then((res) => { if (res.isConfirmed) keepScroll(() => $wire.deleteMedia('{{ $key }}', {{ $media['id'] }})) })" class="agro-icon-button !h-8 !w-8 !text-rose-600 dark:!text-rose-300" aria-label="Eliminar imagen"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h14m-9 4v5m4-5v5M8 7l1 12h6l1-12m-6 0V4h4v3"/></svg></button>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-4 grid min-h-48 place-items-center rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-900"><div><svg class="mx-auto h-8 w-8 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.5" d="M4 19V5h16v14H4Zm3-4 3-3 2.5 2.5L16 10l3 4M8 9h.01"/></svg><strong class="mt-3 block text-sm">Sin fotos propias</strong><span class="mt-1 block text-xs text-zinc-500">Sube imágenes JPG, PNG o WebP.</span></div></div>
                                @endif
                            </section>
                        </div>
                    </div>

                    <footer class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/70 sm:flex-row sm:items-center sm:justify-between sm:px-6"><span class="text-[10px] text-zinc-500">Máximo 40 fotos por sección, 8 por carga, 15 MB por archivo.</span><button type="button" x-on:click="keepScroll(() => $wire.saveBlock('{{ $key }}'))" x-bind:disabled="$store.imageUploads.busy" wire:loading.attr="disabled" wire:target="saveBlock('{{ $key }}'),uploads.{{ $key }}" class="agro-button w-full sm:w-auto"><span wire:loading.remove wire:target="saveBlock('{{ $key }}')">Guardar {{ strtolower($label) }}</span><span wire:loading wire:target="saveBlock('{{ $key }}')">Guardando...</span></button></footer>
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
        >
            <div class="space-y-1" aria-live="polite">
                @error('frameX')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                @error('frameY')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                @error('frameZoom')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>
        </x-image-frame-editor>
    @endif
</div>
