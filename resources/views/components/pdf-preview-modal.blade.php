@props([
    'showExportModal' => false,
    'exportStep' => 'options',
    'pdfPreviewData' => null,
    'pdfPreviewToken' => null,
    'pdfPreviewFilename' => null,
    'pdfPreviewTitle' => 'Vista Previa PDF',
    'pdfPreviewRowCount' => null,
    'pdfPreviewPageCount' => null,
    'backAction' => null,
    'pdfIncludeSignatures' => true,
    'pdfScale' => '85',
    'pdfSignatureScale' => '100',
    'pdfTableColorMode' => null,
    'pdfTableRadius' => null,
    'pdfTablePreset' => null,
    'hasPdfCustomization' => true,
    'showSignModal' => false,
    'signPassword' => '',
    'signPasswordError' => null,
])
{{--
    PDF Preview Modal — Visor Ultra Fluido de Alta Velocidad (Zero-Flicker Double Buffering)
    - Hereda y sincroniza 100% la configuración institucional de Ajustes PDF (Modo y 16 Colores)
    - Modal de contraseña de firma digital con apertura instantánea (0ms)
    - Cero parpadeos en escritorio mediante doble buffer de iframes con tiempo de rasterización y cross-fade
    - Selección inteligente de color: 'Monocromático' despliega paleta instantánea en UI sin recargar hasta elegir color
    - Botón Opciones destacado en esmeralda
--}}
@if($showExportModal)
    @php
        $initialPdfUrl = $pdfPreviewToken ? route('pdf.preview', $pdfPreviewToken) : '';
        $initialIframeSrc = $initialPdfUrl ? ($initialPdfUrl.'#toolbar=0&navpanes=0&view=FitH&pagemode=none') : '';
        $pdfCfg = app(\App\Support\PdfReportConfig::class);
        $rawColorMode = (string) ($pdfTableColorMode ?? (isset($this) ? ($this->pdfTableColorMode ?? $pdfCfg->modoColorTablas()) : $pdfCfg->modoColorTablas()));
        $serverColorMode = in_array($rawColorMode, ['mono', 'monocromatico'], true) ? 'mono' : 'multi';
        $serverShowSign = (bool) ($showSignModal ?? (isset($this) ? ($this->showSignModal ?? false) : false));
        $currentPreset = (string) ($pdfTablePreset ?? (isset($this) ? ($this->pdfTablePreset ?? $pdfCfg->colorPreset()) : $pdfCfg->colorPreset()));
    @endphp
    <div
        wire:key="pdf-preview-modal-root"
        @click.self.stop
        x-data="{
            pdfUrl: '{{ $initialPdfUrl }}',
            lastProcessedUrl: '{{ $initialPdfUrl }}',
            currentToken: '{{ $pdfPreviewToken ?? '' }}',
            lastWidth: window.innerWidth,
            reqId: 0,
            isRenderingCanvas: false,
            renderBusy: false,
            pendingRenderUrl: null,
            activeLoadingTask: null,
            activeRenderTasks: [],
            openFormatMenu: false,
            openSpeedDial: false,
            openSignModal: {{ $serverShowSign ? 'true' : 'false' }},
            localColorMode: '{{ $serverColorMode }}',
            currentPage: 1,
            totalPages: 1,
            init() {
                this.lastWidth = window.innerWidth;
                window.addEventListener('resize', () => {
                    const currentWidth = window.innerWidth;
                    if (Math.abs(currentWidth - this.lastWidth) > 50) {
                        this.lastWidth = currentWidth;
                        if (this.pdfUrl) {
                            this.requestRender(this.pdfUrl);
                        }
                    }
                });
                if (this.pdfUrl) {
                    this.requestRender(this.pdfUrl);
                }
                if (window.Livewire) {
                    Livewire.on('pdf-preview-ready', (event) => {
                        const url = event?.url || (Array.isArray(event) && event[0]?.url) || (typeof event === 'string' ? event : '');
                        if (url) {
                            this.updatePdfUrl(url);
                        }
                    });
                }
                this.$watch('$wire.pdfPreviewToken', (token) => {
                    if (token && token !== this.currentToken) {
                        this.currentToken = token;
                        const url = '{{ url('/pdf-preview') }}/' + token;
                        this.updatePdfUrl(url);
                    }
                });
                this.$watch('$wire.showSignModal', (val) => {
                    this.openSignModal = Boolean(val);
                });
                this.$watch('$wire.pdfTableColorMode', (val) => {
                    if (val) {
                        this.localColorMode = (val === 'monocromatico' || val === 'mono') ? 'mono' : 'multi';
                    }
                });
            },
            updatePdfUrl(newUrl) {
                if (!newUrl || newUrl === this.lastProcessedUrl) return;
                this.lastProcessedUrl = newUrl;
                this.pdfUrl = newUrl;
                this.requestRender(newUrl);
            },
            // Cola de render: evita lanzar renders en paralelo que abortan el fetch del PDF.
            requestRender(url) {
                this.pendingRenderUrl = url;
                if (this.renderBusy) return;
                this.renderBusy = true;
                const run = async () => {
                    while (this.pendingRenderUrl) {
                        const u = this.pendingRenderUrl;
                        this.pendingRenderUrl = null;
                        await this.renderPdfPages(u, ++this.reqId);
                    }
                    this.renderBusy = false;
                };
                run();
            },
            async loadPdfJs() {
                if (window.pdfjsLib) return;
                return new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                    s.onload = () => {
                        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                        resolve();
                    };
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            },
            async renderPdfPages(url, thisReq) {
                if (!url) return;
                this.isRenderingCanvas = true;
                try {
                    await this.loadPdfJs();
                    if (this.reqId !== thisReq) return;
                    
                    if (this.activeLoadingTask) {
                        try { this.activeLoadingTask.destroy(); } catch(e) {}
                        this.activeLoadingTask = null;
                    }
                    for (const task of this.activeRenderTasks) {
                        try { task.cancel(); } catch(e) {}
                    }
                    this.activeRenderTasks = [];

                    const loadingTask = window.pdfjsLib.getDocument({ url, withCredentials: true });
                    this.activeLoadingTask = loadingTask;
                    const pdf = await loadingTask.promise;
                    if (this.reqId !== thisReq) return;
                    
                    this.totalPages = pdf.numPages || 1;
                    const container = this.$refs.pdfCanvasContainer;
                    const scroller = this.$refs.pdfScrollContainer;
                    if (!container) return;
                    
                    const fragment = document.createDocumentFragment();
                    const horizontalGutter = window.innerWidth < 640 ? 12 : 24;
                    const measuredWidth = scroller?.clientWidth || container.parentElement?.clientWidth || window.innerWidth;
                    const targetWidth = Math.max(320, measuredWidth - horizontalGutter);
                    const dpr = Math.min(window.devicePixelRatio || 1, 2);

                    const canvasList = [];

                    for (let i = 1; i <= pdf.numPages; i++) {
                        if (this.reqId !== thisReq) return;
                        const page = await pdf.getPage(i);
                        if (this.reqId !== thisReq) return;
                        
                        const unscaledViewport = page.getViewport({ scale: 1 });
                        const baseScale = targetWidth / unscaledViewport.width;
                        const viewport = page.getViewport({ scale: baseScale * dpr });

                        const wrapper = document.createElement('div');
                        wrapper.id = 'pdf-page-' + i;
                        wrapper.className = 'w-full flex justify-center mb-3.5 shrink-0';
                        wrapper.style.width = '100%';
                        wrapper.style.maxWidth = targetWidth + 'px';

                        const canvas = document.createElement('canvas');
                        canvas.className = 'rounded-lg shadow-2xl bg-white border border-zinc-700/60 w-full block mx-auto';
                        canvas.style.width = targetWidth + 'px';
                        canvas.style.maxWidth = '100%';
                        canvas.style.height = 'auto';
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const ctx = canvas.getContext('2d');
                        wrapper.appendChild(canvas);
                        fragment.appendChild(wrapper);

                        canvasList.push({ page, ctx, viewport });
                    }

                    if (this.reqId !== thisReq) return;
                    container.replaceChildren(fragment);

                    for (let i = 0; i < canvasList.length; i++) {
                        if (this.reqId !== thisReq) return;
                        const item = canvasList[i];
                        const renderTask = item.page.render({ canvasContext: item.ctx, viewport: item.viewport });
                        this.activeRenderTasks.push(renderTask);
                        await renderTask.promise;
                        if (i === 0 && this.reqId === thisReq) {
                            this.isRenderingCanvas = false;
                        }
                    }
                } catch (e) {
                    if (this.reqId === thisReq) {
                        console.warn('Visor PDF Error', e);
                    }
                } finally {
                    if (this.reqId === thisReq) {
                        this.isRenderingCanvas = false;
                    }
                }
            },
            destroy() {
                document.body.classList.remove('overflow-hidden');
            },
            scrollToTop() {
                const scroller = this.$refs.pdfScrollContainer;
                if (scroller) scroller.scrollTo({ top: 0, behavior: 'smooth' });
            },
            scrollToBottom() {
                const scroller = this.$refs.pdfScrollContainer;
                if (scroller) scroller.scrollTo({ top: scroller.scrollHeight, behavior: 'smooth' });
            },
            scrollPage(dir) {
                const scroller = this.$refs.pdfScrollContainer;
                if (scroller) {
                    const step = scroller.clientHeight * 0.82;
                    scroller.scrollBy({ top: dir * step, behavior: 'smooth' });
                }
            },
            openNative() {
                const target = $wire?.pdfPreviewToken ? ('{{ url('/pdf-preview') }}/' + $wire.pdfPreviewToken) : this.pdfUrl;
                if (target) {
                    window.open(target, '_blank');
                }
            }
        }"
        class="agro-dialog-overlay {{ $exportStep === 'preview' ? 'agro-dialog-overlay--pdf' : '' }}"
    >
        @if($exportStep === 'options')
            <div wire:key="pdf-modal-step-options" role="dialog" aria-modal="true"
                 class="agro-dialog agro-dialog--md agro-dialog--scroll space-y-6 p-4 sm:p-6"
                 @click.stop>
                {{ $slot }}
            </div>

        @elseif($exportStep === 'preview')
            @php
                $currentScale = (string) ($pdfScale ?? (isset($this) ? ($this->pdfScale ?? '85') : '85'));
                $currentSign = (bool) ($pdfIncludeSignatures ?? (isset($this) ? ($this->pdfIncludeSignatures ?? true) : true));
                $currentSigScale = (string) ($pdfSignatureScale ?? (isset($this) ? ($this->pdfSignatureScale ?? '100') : '100'));
                $currentRadius = (string) ($pdfTableRadius ?? (isset($this) ? ($this->pdfTableRadius ?? (string)$pdfCfg->tableBorderRadiusPx()) : (string)$pdfCfg->tableBorderRadiusPx()));
            @endphp
            <div
                wire:key="pdf-modal-step-preview"
                role="dialog" aria-modal="true" aria-label="Vista previa PDF"
                class="agro-dialog agro-dialog--pdf"
                @click.stop
            >
                {{-- BARRA SUPERIOR CONFORTABLE, ELEGANTE Y RESPONSIVE --}}
                <div class="flex items-center justify-between gap-2 border-b border-zinc-200/90 bg-white/95 px-4 py-2 sm:px-6 sm:py-2.5 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-950/95 shadow-2xs z-30 relative">
                    {{-- Grupo Izquierdo: Volver + Título del Reporte + Conteo --}}
                    <div class="flex items-center gap-2 sm:gap-2.5 shrink min-w-0">
                        {{-- Botón Volver / Opciones con color Esmeralda Genial --}}
                        <button type="button" wire:click="{{ $backAction ?? 'backToExportOptions' }}"
                                class="inline-flex h-9 sm:h-9.5 shrink-0 items-center gap-2 rounded-xl border-2 border-emerald-500/50 bg-emerald-50/90 px-3 sm:px-4 text-xs font-black text-emerald-900 shadow-xs transition hover:bg-emerald-100 hover:border-emerald-500 dark:border-emerald-500/40 dark:bg-emerald-950/70 dark:text-emerald-200 dark:hover:bg-emerald-900/80 cursor-pointer active:scale-95"
                                title="Volver a opciones de exportación">
                            <svg class="h-4 w-4 shrink-0 stroke-[2.8] text-emerald-700 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Opciones</span>
                        </button>

                        <div class="h-5 w-px shrink-0 bg-zinc-200 dark:bg-zinc-800 hidden sm:block"></div>

                        {{-- Título y Badges --}}
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="h-4.5 w-4.5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>
                            <span class="truncate text-xs sm:text-sm font-extrabold text-zinc-900 dark:text-zinc-100 max-w-[130px] xs:max-w-[190px] sm:max-w-[300px] md:max-w-[420px]">{{ $pdfPreviewTitle ?? 'Vista Previa' }}</span>
                            @if($pdfPreviewRowCount !== null)
                                <div class="hidden sm:inline-flex h-7 shrink-0 items-center gap-1.5 rounded-xl border border-emerald-500/20 bg-emerald-50/70 px-2.5 text-[11px] font-bold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-950/40 dark:text-emerald-300 shadow-2xs">
                                    <span>{{ $pdfPreviewRowCount }} {{ $pdfPreviewRowCount === 1 ? 'reg.' : 'regs.' }}</span>
                                    @if($pdfPreviewPageCount)
                                        <span class="opacity-40">&bull;</span>
                                        <span>{{ $pdfPreviewPageCount }} {{ $pdfPreviewPageCount === 1 ? 'pág.' : 'págs.' }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Grupo Derecho: Menú Desplegable Centralizado + Botón Cerrar --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if($hasPdfCustomization)
                            {{-- BOTÓN Y PANEL DESPLEGABLE CON COLORES SUAVES Y COMPATIBLES --}}
                            <div class="relative" @click.outside="openFormatMenu = false" @keydown.escape.window="openFormatMenu = false">
                                <button type="button" @click="openFormatMenu = !openFormatMenu"
                                        class="inline-flex h-9 sm:h-9.5 shrink-0 items-center justify-center gap-1.5 rounded-xl border-2 border-amber-500/60 bg-amber-50/90 px-3 sm:px-3.5 text-xs font-black text-amber-900 shadow-xs transition hover:bg-amber-100 active:scale-95 cursor-pointer dark:border-amber-500/50 dark:bg-amber-950/60 dark:text-amber-200 dark:hover:bg-amber-900/70"
                                        :class="{ 'ring-2 ring-amber-500/40 bg-amber-100/90 dark:bg-amber-900/80': openFormatMenu }"
                                        title="Opciones de formato, escala, colores y acciones del PDF">
                                    {{-- Ícono Sliders / Controles --}}
                                    <svg class="h-4.5 w-4.5 text-amber-700 dark:text-amber-300 stroke-[2.3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                    </svg>
                                    {{-- Chevron Up/Down --}}
                                    <svg class="h-4 w-4 transition-transform duration-200 text-amber-800 dark:text-amber-200 stroke-[2.5]" :class="{ 'rotate-180': openFormatMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                {{-- PANEL DESPLEGABLE CON COLORES SUAVES Y PALETA DE COLORES VISIBLE --}}
                                <div x-cloak x-show="openFormatMenu"
                                     x-transition:enter="transition ease-out duration-120"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                     class="fixed inset-x-3 top-14 sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:mt-2 sm:w-[360px] max-h-[85vh] overflow-y-auto rounded-2xl border border-zinc-200/90 bg-white/95 p-3.5 sm:p-4 shadow-2xl backdrop-blur-md dark:border-zinc-700/80 dark:bg-zinc-900/95 z-50 space-y-3.5">
                                    
                                    {{-- 1. Escala / Compactación de Tabla --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Escala de Tabla</span>
                                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md">{{ $currentScale }}%</span>
                                        </div>
                                        <div class="grid grid-cols-6 gap-1 bg-zinc-100/90 p-1 rounded-xl dark:bg-zinc-800/80">
                                            @foreach(['45' => '45%', '55' => '55%', '65' => '65%', '75' => '75%', '85' => '85%', '100' => '100%'] as $scVal => $scLabel)
                                                <button type="button" wire:click="setPdfScale('{{ $scVal }}')"
                                                        class="py-1.5 rounded-lg text-xs font-black text-center transition cursor-pointer active:scale-95 {{ in_array($currentScale, [$scVal, (string)((int)$scVal - 5)], true) || ($scVal === '100' && $currentScale === '100') || ($scVal === '85' && $currentScale === '85') || ($scVal === '75' && $currentScale === '75') || ($scVal === '65' && $currentScale === '65') ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-200/70 dark:text-zinc-300 dark:hover:bg-zinc-700/70 dark:hover:text-white' }}"
                                                        title="Escala {{ $scLabel }}">{{ $scLabel }}</button>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- 2. Coloración de Tablas (Multi / Mono + Paleta Visible Desplegable Sincronizada) --}}
                                    <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800/80">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Color de Tablas</span>
                                            <span class="text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-md"
                                                  x-text="localColorMode === 'multi' ? 'Multicolor' : 'Monocromático'"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <button type="button"
                                                    @click="localColorMode = 'multi'; $wire.setPdfTableColorMode('multi')"
                                                    class="flex-1 py-1.5 px-2.5 rounded-xl text-xs font-black transition cursor-pointer active:scale-95 flex items-center justify-center gap-1.5 border"
                                                    :class="localColorMode === 'multi' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'">
                                                <span class="flex gap-1 items-center">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                                    <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                                                </span>
                                                <span>Multicolor</span>
                                            </button>
                                            <button type="button"
                                                    @click="localColorMode = 'mono'"
                                                    class="flex-1 py-1.5 px-2.5 rounded-xl text-xs font-black transition cursor-pointer active:scale-95 text-center border"
                                                    :class="localColorMode === 'mono' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'">
                                                Monocromático
                                            </button>
                                        </div>

                                        {{-- PALETA MONOCROMÁTICA DESPLEGABLE EN TIEMPO REAL CON 16 COLORES RICOS Y LEGIBLES --}}
                                        <div x-show="localColorMode === 'mono'" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                            @php
                                                $presetsList = \App\Support\PdfReportConfig::COLOR_PRESETS;
                                            @endphp
                                            <div class="grid grid-cols-4 gap-2 p-2.5 rounded-xl border border-zinc-200/90 bg-zinc-50/80 dark:border-zinc-800 dark:bg-zinc-800/60 max-h-56 overflow-y-auto">
                                                @foreach($presetsList as $pKey => $pData)
                                                    <button type="button" wire:click="setPdfTablePreset('{{ $pKey }}')"
                                                            class="flex flex-col items-center justify-center p-1.5 rounded-xl border transition cursor-pointer active:scale-95 text-center gap-1 {{ $currentPreset === $pKey ? 'border-zinc-900 bg-white dark:border-white dark:bg-zinc-800 shadow-sm ring-1 ring-zinc-900/20' : 'border-transparent hover:bg-white/80 dark:hover:bg-zinc-800/80' }}"
                                                            title="Color {{ $pData['label'] ?? $pKey }}">
                                                        <span class="h-6 w-6 rounded-full shrink-0 shadow-sm flex items-center justify-center text-white text-[10px] font-black" style="background-color: {{ $pData['primary'] }};">
                                                            @if($currentPreset === $pKey)
                                                                ✓
                                                            @endif
                                                        </span>
                                                        <span class="text-[9.5px] font-extrabold text-zinc-800 dark:text-zinc-200 tracking-tight leading-none truncate max-w-full">{{ explode(' ', $pData['label'] ?? ucfirst($pKey))[0] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 3. Bloque de Firmas (CON TOGGLE SWITCH ESTILO IMAGEN 2) --}}
                                    <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800/80">
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <span class="block text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Bloque de Firmas</span>
                                                <span class="text-[11px] text-zinc-500">{{ $currentSign ? 'Firmas y sellos activados' : 'Sin firmas al final' }}</span>
                                            </div>
                                            {{-- Toggle Switch Maestro de Firmas --}}
                                            <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Activar / Desactivar bloque de firmas">
                                                <input type="checkbox" wire:click="togglePdfSignatures" {{ $currentSign ? 'checked' : '' }} class="peer sr-only">
                                                <span class="h-6 w-11 rounded-full bg-zinc-200 transition-colors duration-200 peer-checked:bg-emerald-600 dark:bg-zinc-700"></span>
                                                <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                            </label>
                                        </div>

                                        @if($currentSign)
                                            <div class="space-y-1.5 mt-2 bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-200/80 dark:border-zinc-800">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[11px] font-bold text-zinc-600 dark:text-zinc-400">Tamaño de firma</span>
                                                    <span class="text-[11px] font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-1.5 py-0.5 rounded-md">{{ $currentSigScale }}%</span>
                                                </div>
                                                <div class="grid grid-cols-7 gap-1 bg-zinc-100 p-1 rounded-lg dark:bg-zinc-800/80">
                                                    @foreach(['45' => '45%', '65' => '65%', '80' => '80%', '100' => '100%', '120' => '120%', '140' => '140%', '155' => '155%'] as $sigVal => $sigLabel)
                                                        <button type="button" wire:click="setPdfSignatureScale('{{ $sigVal }}')"
                                                                class="py-1 rounded-md text-[9px] font-black text-center transition cursor-pointer active:scale-95 {{ $currentSigScale === $sigVal || ($sigVal === '45' && in_array($currentSigScale, ['40', '45'])) || ($sigVal === '65' && in_array($currentSigScale, ['60', '65'])) ? 'bg-purple-600 text-white shadow-xs' : 'text-zinc-700 hover:bg-zinc-200/80 dark:text-zinc-300 dark:hover:bg-zinc-700 dark:hover:text-white' }}"
                                                                title="Firma {{ $sigLabel }}">{{ $sigLabel }}</button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- 4. Acciones Integradas del Reporte --}}
                                    <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800/80 space-y-2">
                                        <span class="block text-[10px] font-black uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Acciones del Reporte</span>
                                        <div class="grid grid-cols-1 gap-2">
                                            {{-- BOTÓN FIRMAR DIGITALMENTE PDF (APERTURA INSTANTÁNEA 0ms) --}}
                                            <button type="button"
                                                    @click="openSignModal = true; openFormatMenu = false; $wire.openSignModal()"
                                                    class="flex items-center justify-between p-2.5 rounded-xl border border-purple-500/20 bg-purple-50/70 text-purple-900 dark:border-purple-500/20 dark:bg-purple-950/40 dark:text-purple-200 text-xs font-bold transition hover:bg-purple-100 dark:hover:bg-purple-900/60 active:scale-98 cursor-pointer">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                    <span>Firmar Digitalmente PDF</span>
                                                </div>
                                                <span class="text-[9px] uppercase font-black tracking-wider text-purple-700 dark:text-purple-400 bg-purple-100/80 dark:bg-purple-900/60 px-1.5 py-0.5 rounded">X.509</span>
                                            </button>

                                            <button type="button" wire:click="downloadCurrentPdf" @click="openFormatMenu = false"
                                                    class="flex items-center justify-between p-2.5 rounded-xl border border-emerald-500/20 bg-emerald-50/70 text-emerald-900 dark:border-emerald-500/20 dark:bg-emerald-950/40 dark:text-emerald-200 text-xs font-bold transition hover:bg-emerald-100 dark:hover:bg-emerald-900/60 active:scale-98 cursor-pointer">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                    <span>Descargar PDF</span>
                                                </div>
                                                <span class="text-[9px] font-black text-emerald-700 dark:text-emerald-400 bg-emerald-100/80 dark:bg-emerald-900/60 px-1.5 py-0.5 rounded">Directo</span>
                                            </button>

                                            <a href="{{ route('ajustes.index', ['tab' => 'pdf']) }}" @click="openFormatMenu = false"
                                               class="flex items-center justify-between p-2.5 rounded-xl border border-zinc-200/90 bg-zinc-50 text-zinc-700 dark:border-zinc-700/80 dark:bg-zinc-800/80 dark:text-zinc-200 text-xs font-bold transition hover:bg-zinc-100 dark:hover:bg-zinc-700 active:scale-98 cursor-pointer">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span>Ajustes Generales de PDF</span>
                                                </div>
                                                <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Botón Cerrar (X) con grosor y proporción confortable --}}
                        <button type="button" wire:click="closeExportModal"
                                class="inline-flex h-9 w-9 sm:h-9.5 sm:w-9.5 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-50/80 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 dark:border-rose-500/30 dark:bg-rose-950/60 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white transition-all shadow-2xs active:scale-95 cursor-pointer"
                                title="Cerrar vista previa">
                            <svg class="h-4.5 w-4.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- VISOR DE PDF DE ALTA VELOCIDAD ZERO-FLICKER --}}
                <div class="relative flex-1 min-h-0 overflow-hidden bg-zinc-950 flex flex-col">
                    {{-- INDICADOR DE PROCESAMIENTO / RENDERIZADO FLOTANTE SUTIL EN ESQUINA SUPERIOR DERECHA --}}
                    <div x-cloak x-show="isRenderingCanvas"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute top-3 right-6 sm:right-8 z-30 pointer-events-none">
                        <div class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/40 bg-zinc-950/90 px-3 py-1.5 text-xs font-bold text-emerald-400 shadow-xl backdrop-blur-md">
                            <svg class="h-3.5 w-3.5 animate-spin text-emerald-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span class="text-[11px]">Procesando documento...</span>
                        </div>
                    </div>
                    <div wire:loading wire:target="setPdfScale, setPdfSignatureScale, togglePdfSignatures, setPdfTableColorMode, setPdfTablePreset, exportar, exportDetailedReport, signAndDownloadCurrentPdf"
                         class="absolute top-3 right-6 sm:right-8 z-30 pointer-events-none transition-opacity">
                        <div class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/40 bg-zinc-950/85 px-3 py-1.5 text-xs font-bold text-emerald-400 shadow-xl backdrop-blur-md">
                            <svg class="h-3.5 w-3.5 animate-spin text-emerald-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span class="text-[11px]">Actualizando...</span>
                        </div>
                    </div>

                    {{-- Línea de carga sutil ultra-delgada en borde superior --}}
                    <div x-show="isRenderingCanvas" class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-500 via-teal-300 to-emerald-500 animate-pulse z-30"></div>

                    @if($pdfPreviewToken || $pdfPreviewData)
                        {{-- VISOR PDF UNIVERSAL ALTA DEFINICIÓN (HTML5 CANVAS MULTI-PÁGINA CON AJUSTE MÁXIMO A BORDES) --}}
                        <div x-ref="pdfScrollContainer"
                             class="relative w-full flex-1 min-h-0 overflow-y-auto px-2 sm:px-3.5 py-2.5 bg-zinc-950 flex flex-col items-center select-none"
                             style="-webkit-overflow-scrolling: touch; overscroll-behavior-y: contain;">

                            {{-- Contenedor de páginas renderizadas directo al tope sin huecos negros --}}
                            <div wire:ignore x-ref="pdfCanvasContainer" class="w-full flex flex-col items-center"></div>
                        </div>

                        {{-- DOCK FLOTANTE COMPACTO Y DESPLEGABLE DE NAVEGACIÓN RÁPIDA (EXPANDIBLE AL CLIC / HOVER) --}}
                        <div class="absolute bottom-6 right-6 sm:bottom-8 sm:right-8 z-40 flex flex-col items-end gap-2"
                             @click.outside="openSpeedDial = false">
                            
                            {{-- Menú de botones desplegado verticalmente --}}
                            <div x-show="openSpeedDial"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-3 scale-90"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-3 scale-90"
                                 class="flex flex-col items-center gap-1.5 rounded-2xl border border-zinc-700/80 bg-zinc-950/95 p-2 shadow-2xl backdrop-blur-md">
                                
                                {{-- 1. Inicio (Página 1) --}}
                                <button type="button" @click="scrollToTop(); openSpeedDial = false"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl text-zinc-300 hover:bg-emerald-600 hover:text-white active:scale-95 transition cursor-pointer"
                                        title="Ir al inicio (Primera página)">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 11l7-7 7 7M5 19l7-7 7 7"/></svg>
                                </button>
                                
                                {{-- 2. Página anterior --}}
                                <button type="button" @click="scrollPage(-1)"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl text-zinc-300 hover:bg-emerald-600 hover:text-white active:scale-95 transition cursor-pointer"
                                        title="Subir una página">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                
                                <div class="h-px w-6 bg-zinc-800 my-0.5"></div>
                                
                                {{-- 3. Página siguiente --}}
                                <button type="button" @click="scrollPage(1)"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl text-zinc-300 hover:bg-emerald-600 hover:text-white active:scale-95 transition cursor-pointer"
                                        title="Bajar una página">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                
                                {{-- 4. Final (Última página) --}}
                                <button type="button" @click="scrollToBottom(); openSpeedDial = false"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl text-zinc-300 hover:bg-emerald-600 hover:text-white active:scale-95 transition cursor-pointer"
                                        title="Ir al final (Última página)">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 13l-7 7-7-7m0-8l7 7 7-7"/></svg>
                                </button>
                            </div>

                            {{-- Botón Principal Compacto de Activación --}}
                            <button type="button"
                                    @click="openSpeedDial = !openSpeedDial"
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-500/40 bg-zinc-950/90 text-emerald-400 shadow-2xl backdrop-blur-md transition hover:bg-emerald-600 hover:text-white hover:scale-105 active:scale-95 cursor-pointer"
                                    title="Navegación y salto rápido de páginas">
                                <template x-if="!openSpeedDial">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                </template>
                                <template x-if="openSpeedDial">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </template>
                            </button>
                        </div>
                    @else
                        <div class="flex h-full items-center justify-center">
                            <div class="flex items-center gap-3 text-zinc-400">
                                <svg class="h-5 w-5 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span class="text-sm font-medium">Cargando vista previa...</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- MODAL DE AUTENTICACIÓN Y CONFIRMACIÓN DE FIRMA DIGITAL CRIPTOGRÁFICA (APERTURA INSTANTÁNEA 0ms) --}}
        <div x-cloak x-show="openSignModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-md rounded-2xl border border-purple-500/30 bg-white p-5 shadow-2xl dark:border-purple-500/20 dark:bg-zinc-900 space-y-4"
                 @click.outside="openSignModal = false; $wire.closeSignModal()"
                 @click.stop>
                <div class="flex items-start justify-between gap-3 border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">Firma Digital Criptográfica</h3>
                            <p class="text-[11px] text-zinc-500">Estándar Firma Perú · X.509 PKI & DocMDP</p>
                        </div>
                    </div>
                    <button type="button" @click="openSignModal = false; $wire.closeSignModal()"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:border-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 transition cursor-pointer">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                    <div class="rounded-xl border border-purple-200 bg-purple-50/70 p-3 dark:border-purple-900/40 dark:bg-purple-950/30 space-y-1">
                        <div class="flex items-center gap-1.5 font-bold text-purple-900 dark:text-purple-200">
                            <svg class="h-4 w-4 text-purple-600 dark:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Garantía de Integridad y No Repudio</span>
                        </div>
                        <p class="text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-400">
                            Se aplicará una firma digital con clave RSA-2048 y protección <strong>DocMDP P=1</strong> (inmodificable).
                        </p>
                    </div>

                    <div>
                        <label for="sign-pwd-input" class="mb-1.5 block font-bold text-zinc-800 dark:text-zinc-200">
                            Ingresa tu contraseña para autorizar la firma:
                        </label>
                        <input type="password" id="sign-pwd-input" wire:model="signPassword"
                               wire:keydown.enter="signAndDownloadCurrentPdf"
                               x-ref="signPasswordInput"
                               x-init="$watch('openSignModal', val => { if(val) $nextTick(() => $refs.signPasswordInput?.focus()) })"
                               placeholder="Contraseña de usuario..."
                               autocomplete="current-password"
                               class="h-10 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs sm:text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        @php
                            $activePwdError = $signPasswordError ?: (isset($this) ? ($this->signPasswordError ?? null) : null);
                        @endphp
                        @if($activePwdError)
                            <p class="mt-1.5 text-[11px] font-semibold text-rose-500">{{ $activePwdError }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-3.5 dark:border-zinc-800">
                    <button type="button" @click="openSignModal = false; $wire.closeSignModal()"
                            class="h-10 rounded-xl border border-zinc-300 bg-white px-4 text-xs sm:text-sm font-bold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button" wire:click="signAndDownloadCurrentPdf"
                            wire:loading.attr="disabled" wire:target="signAndDownloadCurrentPdf"
                            class="inline-flex h-10 items-center gap-2 rounded-xl bg-purple-700 px-5 text-xs sm:text-sm font-black text-white shadow-md transition hover:bg-purple-600 active:scale-95 disabled:opacity-50 cursor-pointer">
                        <span wire:loading.remove wire:target="signAndDownloadCurrentPdf">Autorizar y Firmar PDF</span>
                        <span wire:loading wire:target="signAndDownloadCurrentPdf">Firmando documento...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
