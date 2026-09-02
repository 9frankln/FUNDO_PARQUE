<section class="space-y-6">
    {{-- Header Principal Estandarizado --}}
    <div class="agro-card relative overflow-hidden p-5 sm:p-6">
        <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-white shadow-lg shadow-emerald-800/20 dark:bg-emerald-400 dark:text-emerald-950">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="agro-kicker">Motor de reportes & Firma Digital</p>
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-extrabold text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-200">
                            10 PLANTILLAS · CRIPTOGRAFÍA X.509
                        </span>
                    </div>
                    <h2 class="mt-1 text-xl font-extrabold sm:text-2xl">Seguridad, firmas y estilos de PDF</h2>
                    <p class="mt-1 max-w-2xl text-xs leading-5 text-zinc-500 sm:text-sm">Personalización visual, certificados de firma digital criptográfica con protección DocMDP P=1 y validador de integridad Firma Perú.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <button type="button" wire:click="downloadSamplePdf" wire:loading.attr="disabled" wire:target="downloadSamplePdf"
                        class="agro-button w-full sm:w-auto">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span wire:loading.remove wire:target="downloadSamplePdf">Descargar muestra</span>
                    <span wire:loading wire:target="downloadSamplePdf">Generando...</span>
                </button>
                @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))
                    <button type="button" wire:click="resetPdfSettings"
                            x-on:click.prevent="confirmDelete('¿Restablecer ajustes de PDF?', 'Se volverá a los valores y formatos estándar recomendados.').then((res) => { if (res.isConfirmed) $wire.resetPdfSettings() })"
                            class="agro-button-secondary w-full sm:w-auto">
                        Restablecer
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Sub-navegación Modular de PDF --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-3 dark:border-zinc-800">
        <button type="button" wire:click="setPdfSubTab('diseno')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-extrabold transition cursor-pointer {{ $activePdfSubTab === 'diseno' ? 'bg-emerald-700 text-white shadow-sm dark:bg-emerald-500 dark:text-zinc-950' : 'bg-white text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white border border-zinc-200 dark:border-zinc-800' }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
            <span>1. Diseño & Firmas Visuales</span>
        </button>

        <button type="button" wire:click="setPdfSubTab('certificados')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-extrabold transition cursor-pointer {{ $activePdfSubTab === 'certificados' ? 'bg-emerald-700 text-white shadow-sm dark:bg-emerald-500 dark:text-zinc-950' : 'bg-white text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white border border-zinc-200 dark:border-zinc-800' }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>2. Certificados Digitales X.509</span>
        </button>

        <button type="button" wire:click="setPdfSubTab('validador')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-extrabold transition cursor-pointer {{ $activePdfSubTab === 'validador' ? 'bg-emerald-700 text-white shadow-sm dark:bg-emerald-500 dark:text-zinc-950' : 'bg-white text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white border border-zinc-200 dark:border-zinc-800' }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span>3. Validador de Firmas PDF (Firma Perú)</span>
        </button>
    </div>

    {{-- SUB-TAB 1: DISEÃâ€˜O Y ESTILOS VISUALES --}}
    @if($activePdfSubTab === 'diseno')
        @php
            $previewAccent = ($pdfSettings['estilo_color'] ?? '') === 'custom'
                ? ($pdfSettings['color_acento'] ?: '#047857')
                : (\App\Support\PdfReportConfig::COLOR_PRESETS[$pdfSettings['estilo_color'] ?? 'emerald']['primary'] ?? '#047857');
            $previewDark = ($pdfSettings['estilo_color'] ?? '') === 'custom'
                ? '#0f172a'
                : (\App\Support\PdfReportConfig::COLOR_PRESETS[$pdfSettings['estilo_color'] ?? 'emerald']['dark'] ?? '#064e3b');
            $previewSoft = ($pdfSettings['estilo_color'] ?? '') === 'custom'
                ? '#f8fafc'
                : (\App\Support\PdfReportConfig::COLOR_PRESETS[$pdfSettings['estilo_color'] ?? 'emerald']['soft'] ?? '#effaf2');
            $previewWatermarkText = trim($pdfSettings['texto_marca_agua'] ?? '') ?: (auth()->user()?->fundoActivo()?->nombre ?? 'AGROFUNDO').' • DOCUMENTO OFICIAL';
            $previewWatermarkOrientation = in_array(($pdfSettings['orientacion_marca_agua'] ?? 'diagonal'), ['horizontal', 'recto'], true) ? 'horizontal' : 'diagonal';
            $previewSigType = $pdfSettings['tipo_firma'] ?? 'digital';
            $previewShowMasterFirmas = filter_var($pdfSettings['mostrar_firmas'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $previewShowSig1 = $previewShowMasterFirmas && filter_var($pdfSettings['mostrar_firma_1'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $previewShowSig2 = $previewShowMasterFirmas && filter_var($pdfSettings['mostrar_firma_2'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $previewHasExternal = filter_var($pdfSettings['mostrar_sello_externo'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $previewActiveSigsCount = ($previewShowSig1 ? 1 : 0) + ($previewShowSig2 ? 1 : 0) + ($previewHasExternal ? 1 : 0);
            $previewWatermarkShow = filter_var($pdfSettings['mostrar_marca_agua'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $previewLogoShow = filter_var($pdfSettings['mostrar_logo'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $previewPieShow = filter_var($pdfSettings['mostrar_pie'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $previewNumPagShow = filter_var($pdfSettings['mostrar_num_pagina'] ?? true, FILTER_VALIDATE_BOOLEAN);
        @endphp

        {{-- Vista Previa Horizontal Plegable / Desplegable (A4 Landscape) --}}
        <div x-data="{ showPreview: false }" class="agro-card p-5 space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-2.5 w-2.5 rounded-full transition-colors"
                          :class="showPreview ? 'bg-emerald-500 animate-pulse' : 'bg-zinc-400 dark:bg-zinc-600'"></span>
                    <div>
                        <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">Vista previa en vivo (A4 Apaisado - 1:1 Dompdf)</h3>
                        <p class="text-xs text-zinc-500" x-text="showPreview ? 'Visualización activa en tiempo real con foliación Pag. 1 de 1.' : 'Despliega para ver el diseño final antes de exportar.'"></p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex h-8 items-center rounded-lg border border-emerald-500/25 bg-emerald-50 px-3 text-xs font-semibold text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-400/10 dark:text-emerald-300">
                        Marca de agua: {{ $previewWatermarkOrientation === 'horizontal' ? 'Recta (0°)' : 'Diagonal (-24°)' }}
                    </span>
                    <span class="inline-flex h-8 items-center rounded-lg border border-zinc-200 bg-zinc-100/80 px-3 text-xs font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-300">
                        Firmas activas: {{ $previewActiveSigsCount }}
                    </span>

                    <button type="button"
                            @click="showPreview = !showPreview"
                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 text-xs font-semibold text-zinc-700 shadow-xs transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-emerald-500/50 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-300 cursor-pointer">
                        <svg class="h-3.5 w-3.5 shrink-0 transition-transform duration-200"
                             :class="showPreview ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : 'rotate-0 text-zinc-400'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span x-text="showPreview ? 'Ocultar vista previa' : 'Mostrar vista previa'"></span>
                    </button>
                </div>
            </div>

            {{-- Hoja A4 Horizontal Simulada con Soporte Móvil --}}
            <div x-cloak x-show="showPreview"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="w-full overflow-x-auto rounded-2xl border border-zinc-200 bg-zinc-100/50 p-1 sm:p-0 dark:border-zinc-700 dark:bg-zinc-900/50">
                <div class="relative w-full min-w-[560px] sm:min-w-0 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 sm:p-6 shadow-xl dark:border-zinc-700 text-zinc-900 select-none flex flex-col justify-between"
                     style="aspect-ratio: 1.414 / 1; min-height: 380px;">

                {{-- Marca de Agua en Vivo --}}
                @if($previewWatermarkShow)
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center z-0 overflow-hidden">
                        <span class="inline-block font-black uppercase tracking-widest text-center whitespace-nowrap text-2xl sm:text-4xl select-none"
                              style="display: inline-block; transform: rotate({{ $previewWatermarkOrientation === 'horizontal' ? '0deg' : '-24deg' }}); -webkit-transform: rotate({{ $previewWatermarkOrientation === 'horizontal' ? '0deg' : '-24deg' }}); opacity: {{ $pdfSettings['opacidad_marca_agua'] ?? '0.04' }}; color: {{ $pdfSettings['color_marca_agua'] ?: $previewDark }}; transition: transform 0.25s ease, opacity 0.25s ease;">
                            {{ $previewWatermarkText }}
                        </span>
                    </div>
                @endif

                {{-- Cabecera Horizontal --}}
                <div class="relative z-10 pb-2.5 border-b-2" style="border-color: {{ $previewAccent }};">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-wider" style="color: {{ $previewAccent }};">{{ $branding->tagline }} &bull; REPORTE TÉCNICO OFICIAL</p>
                            <h4 class="text-base sm:text-lg font-black text-zinc-900">{{ $branding->name }} &mdash; Consolidado General de Ganado</h4>
                            <p class="text-[10px] text-zinc-600">Fundo: <strong>{{ auth()->user()?->fundoActivo()?->nombre ?? 'Fundo Modelo' }}</strong> &bull; Emisión: {{ now()->format('d/m/Y H:i') }} (Hora oficial de Perú)</p>
                        </div>
                        @if($previewLogoShow)
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="color: {{ $previewAccent }};">
                                <x-brand-logo class="h-9 w-9" />
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tabla Horizontal de Muestra --}}
                <div class="relative z-10 my-2 overflow-hidden border border-zinc-200"
                     style="border-radius: {{ ($pdfSettings['estilo_esquinas'] ?? 'redondeado') === 'clasico' ? '0px' : (($pdfSettings['radio_esquinas'] ?? 5) . 'px') }};">
                    <table class="w-full text-left text-[9px] sm:text-[10px]">
                        <thead>
                            <tr style="background-color: {{ $previewAccent }}; color: #ffffff;">
                                <th class="p-1.5 font-bold">Código / Arete</th>
                                <th class="p-1.5 font-bold">Nombre / Identificación</th>
                                <th class="p-1.5 font-bold">Especie / Raza</th>
                                <th class="p-1.5 font-bold">Estado Reproductivo</th>
                                <th class="p-1.5 text-center font-bold">Condición</th>
                                <th class="p-1.5 text-right font-bold">Peso Actual (Kg)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white/95 text-zinc-700">
                            <tr>
                                <td class="p-1.5 font-mono font-bold text-zinc-900">BOV26-001</td>
                                <td class="p-1.5 font-semibold">Estrella del Sur</td>
                                <td class="p-1.5">Bovino &bull; Holstein Friesian</td>
                                <td class="p-1.5">Preñada (6to mes)</td>
                                <td class="p-1.5 text-center"><span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[8px] font-bold text-emerald-800">Activo</span></td>
                                <td class="p-1.5 text-right font-mono font-bold">540.00 kg</td>
                            </tr>
                            <tr>
                                <td class="p-1.5 font-mono font-bold text-zinc-900">BOV26-002</td>
                                <td class="p-1.5 font-semibold">Coronel de Texas</td>
                                <td class="p-1.5">Bovino &bull; Brown Swiss</td>
                                <td class="p-1.5">Reproductor Semental</td>
                                <td class="p-1.5 text-center"><span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[8px] font-bold text-emerald-800">Activo</span></td>
                                <td class="p-1.5 text-right font-mono font-bold">610.50 kg</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Bloque de Firmas Horizontal en Vivo --}}
                @if($previewShowMasterFirmas && $previewActiveSigsCount > 0)
                    @php
                        $simScale = max(0.5, min(1.6, ((int) ($pdfSettings['escala_firmas'] ?? 100)) / 100));
                        $simBodySize = round(5.2 * $simScale, 1) . 'px';
                        $simTitleSize = round(6.0 * $simScale, 1) . 'px';
                        $simLabelSize = round(5.0 * $simScale, 1) . 'px';
                    @endphp
                    <div class="relative z-10 mt-auto pt-5 text-black">
                        @if($previewSigType === 'digital')
                            <div class="w-full {{ $previewActiveSigsCount === 3 ? 'grid grid-cols-3 gap-2.5 items-start' : ($previewActiveSigsCount === 2 ? 'flex justify-between items-start' : 'flex justify-center items-start') }}">
                                @if($previewShowSig1)
                                    <div class="{{ $previewActiveSigsCount === 2 ? 'w-[42%]' : ($previewActiveSigsCount === 1 ? 'max-w-xs mx-auto' : '') }} leading-none text-zinc-900 font-normal text-left"
                                         style="font-size: {{ $simBodySize }};">
                                        <div style="color: {{ $previewAccent }};">
                                            <span class="font-bold uppercase tracking-wider block" style="font-size: {{ $simLabelSize }}; line-height: 1.05;">Firmado digitalmente por:</span>
                                            <div class="font-black text-zinc-950 uppercase" style="font-size: {{ $simTitleSize }}; line-height: 1.08; margin-top: 0;">{{ $pdfSettings['firma_1_nombre'] ?: 'Noe Franklin Choquenaira Quispe' }}</div>
                                        </div>
                                        <div class="text-zinc-800" style="line-height: 1.1; margin-top: 1.5px;"><strong class="font-bold text-zinc-950">Cargo:</strong> {{ $pdfSettings['firma_1_cargo'] ?: 'Responsable de Fundo / Titular' }}</div>
                                        <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">DNI / RUC:</strong> {{ $pdfSettings['firma_1_documento'] ?: 'DNI 74056499' }}</div>
                                        <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">Motivo:</strong> {{ $pdfSettings['firma_motivo'] ?: 'Autorización y Conformidad del Documento' }}</div>
                                        <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">Fecha y hora:</strong> {{ now()->format('d/m/Y H:i:s') }} (Hora oficial de Perú)</div>
                                        <div class="font-bold" style="color: {{ $previewAccent }}; font-size: {{ $simLabelSize }}; line-height: 1.1; margin-top: 1.5px;">Validación: {{ $pdfSettings['firma_software'] ?: 'AGROFUNDO ERP v2.6 · Software Ganadero' }}</div>
                                    </div>
                                @endif

                                @if($previewShowSig2)
                                    <div class="{{ $previewActiveSigsCount === 2 ? 'w-[42%] flex justify-end' : ($previewActiveSigsCount === 3 ? 'flex justify-center' : 'max-w-xs mx-auto') }} leading-none text-zinc-900 font-normal"
                                         style="font-size: {{ $simBodySize }};">
                                        <div class="inline-block text-left max-w-[170px]">
                                            <div style="color: {{ $previewAccent }};">
                                                <span class="font-bold uppercase tracking-wider block" style="font-size: {{ $simLabelSize }}; line-height: 1.05;">Firmado digitalmente por:</span>
                                                <div class="font-black text-zinc-950 uppercase" style="font-size: {{ $simTitleSize }}; line-height: 1.08; margin-top: 0;">{{ $pdfSettings['firma_2_nombre'] ?: 'Supervisión Técnica y Sanitaria' }}</div>
                                            </div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 1.5px;"><strong class="font-bold text-zinc-950">Cargo:</strong> {{ $pdfSettings['firma_2_cargo'] ?: 'Médico Veterinario / Control Técnico' }}</div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">DNI / Colegiatura:</strong> {{ $pdfSettings['firma_2_documento'] ?: 'DNI / CMVP Colegiatura' }}</div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">Motivo:</strong> Certificación Técnica y Sanitaria Oficial</div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">Fecha y hora:</strong> {{ now()->format('d/m/Y H:i:s') }} (Hora oficial de Perú)</div>
                                            <div class="font-bold" style="color: {{ $previewAccent }}; font-size: {{ $simLabelSize }}; line-height: 1.1; margin-top: 1.5px;">Validación Técnica: Conforme</div>
                                        </div>
                                    </div>
                                @endif

                                @if($previewHasExternal)
                                    <div class="{{ $previewActiveSigsCount === 2 ? 'w-[42%] flex justify-end' : ($previewActiveSigsCount === 3 ? 'flex justify-end' : 'max-w-xs mx-auto') }} leading-none text-zinc-900 font-normal"
                                         style="font-size: {{ $simBodySize }};">
                                        <div class="inline-block text-left max-w-[170px]">
                                            <div style="color: {{ $previewAccent }};">
                                                <span class="font-bold uppercase tracking-wider block" style="font-size: {{ $simLabelSize }}; line-height: 1.05;">Evaluación Externa / Dictamen:</span>
                                                <div class="font-black text-zinc-950 uppercase" style="font-size: {{ $simTitleSize }}; line-height: 1.08; margin-top: 0;">{{ $pdfSettings['sello_externo_entidad'] ?: 'Entidad Financiera / Bancaria' }}</div>
                                            </div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 1.5px;"><strong class="font-bold text-zinc-950">Evaluador:</strong> {{ $pdfSettings['sello_externo_nombre'] ?: 'Evaluador Asignado' }} ({{ $pdfSettings['sello_externo_cargo'] ?: 'Analista de Crédito' }})</div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">DNI:</strong> {{ $pdfSettings['sello_externo_documento'] ?: 'DNI del Evaluador' }}</div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">N° Expediente:</strong> {{ $pdfSettings['sello_externo_expediente'] ?: 'EXP-2026-CRÉD-0041' }}</div>
                                            <div class="text-zinc-800" style="line-height: 1.1; margin-top: 0.5px;"><strong class="font-bold text-zinc-950">Fecha y hora:</strong> {{ now()->format('d/m/Y H:i:s') }} (Hora oficial de Perú)</div>
                                            <div class="font-bold" style="color: {{ $previewAccent }}; font-size: {{ $simLabelSize }}; line-height: 1.1; margin-top: 1.5px;">Dictamen: {{ $pdfSettings['sello_externo_estado'] ?: 'CONFORME / APROBADO' }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif($previewSigType === 'clasica')
                            <div class="w-full {{ $previewActiveSigsCount === 3 ? 'grid grid-cols-3 gap-6 items-end' : ($previewActiveSigsCount === 2 ? 'flex justify-between items-end' : 'flex justify-center items-end') }}">
                                @if($previewShowSig1)
                                    <div class="{{ $previewActiveSigsCount === 2 ? 'w-[42%]' : ($previewActiveSigsCount === 1 ? 'max-w-xs mx-auto' : '') }} text-center text-zinc-900 font-normal"
                                         style="font-size: {{ $simBodySize }};">
                                        <div class="border-t-2 border-zinc-800 pt-1.5 inline-block w-full">
                                            <div class="font-black text-zinc-950 uppercase" style="font-size: {{ $simTitleSize }};">{{ $pdfSettings['firma_1_nombre'] ?: 'Noe Franklin Choquenaira Quispe' }}</div>
                                            <div class="text-zinc-800 font-bold" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['firma_1_cargo'] ?: 'Responsable de Fundo / Titular' }}</div>
                                            <div class="text-zinc-600" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['firma_1_documento'] ?: 'DNI 74056499' }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if($previewShowSig2)
                                    <div class="{{ $previewActiveSigsCount === 2 ? 'w-[42%]' : ($previewActiveSigsCount === 1 ? 'max-w-xs mx-auto' : '') }} text-center text-zinc-900 font-normal"
                                         style="font-size: {{ $simBodySize }};">
                                        <div class="border-t-2 border-zinc-800 pt-1.5 inline-block w-full">
                                            <div class="font-black text-zinc-950 uppercase" style="font-size: {{ $simTitleSize }};">{{ $pdfSettings['firma_2_nombre'] ?: 'Supervisión Técnica y Sanitaria' }}</div>
                                            <div class="text-zinc-800 font-bold" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['firma_2_cargo'] ?: 'Médico Veterinario / Control Técnico' }}</div>
                                            <div class="text-zinc-600" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['firma_2_documento'] ?: 'DNI / CMVP Colegiatura' }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if($previewHasExternal)
                                    <div class="{{ $previewActiveSigsCount === 2 ? 'w-[42%]' : ($previewActiveSigsCount === 1 ? 'max-w-xs mx-auto' : '') }} text-center text-zinc-900 font-normal"
                                         style="font-size: {{ $simBodySize }};">
                                        <div class="border-t-2 border-sky-800 pt-1.5 inline-block w-full">
                                            <div class="font-black text-sky-950 uppercase" style="font-size: {{ $simTitleSize }};">{{ $pdfSettings['sello_externo_entidad'] ?: 'Entidad Financiera' }}</div>
                                            <div class="text-zinc-800 font-bold" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['sello_externo_nombre'] ?: 'Evaluador Asignado' }}</div>
                                            <div class="text-emerald-700 font-bold" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['sello_externo_estado'] ?: 'CONFORME / APROBADO' }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Doble Modelo: Líneas Clásicas + Sello Digital --}}
                            <div class="grid grid-cols-3 items-center gap-3 text-black">
                                <div>
                                    @if($previewShowSig1)
                                        <div class="border-t-2 border-zinc-800 pt-1 text-center" style="font-size: {{ $simBodySize }};">
                                            <strong class="block text-zinc-950 truncate" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['firma_1_cargo'] ?: 'Responsable' }}</strong>
                                            <span class="block text-zinc-700 truncate" style="font-size: {{ $simBodySize }};">{{ $pdfSettings['firma_1_nombre'] ?: 'Firma' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="rounded-lg border border-emerald-600 bg-emerald-50 p-1 text-center" style="font-size: {{ $simBodySize }};">
                                    <span class="block font-bold text-emerald-950" style="font-size: {{ $simLabelSize }};">Sello Digital Criptográfico</span>
                                    <span class="block text-zinc-700" style="font-size: {{ $simBodySize }};">{{ $pdfSettings['firma_software'] ?: 'AGROFUNDO ERP v2.6' }}</span>
                                </div>
                                <div>
                                    @if($previewShowSig2)
                                        <div class="border-t-2 border-zinc-800 pt-1 text-center" style="font-size: {{ $simBodySize }};">
                                            <strong class="block text-zinc-950 truncate" style="font-size: {{ $simLabelSize }};">{{ $pdfSettings['firma_2_cargo'] ?: 'Técnico' }}</strong>
                                            <span class="block text-zinc-700 truncate" style="font-size: {{ $simBodySize }};">{{ $pdfSettings['firma_2_nombre'] ?: 'Firma' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Pie de Página Horizontal --}}
                @if($previewPieShow)
                    <div class="relative z-10 mt-2 border-t border-zinc-300 pt-1.5 text-[8px] text-zinc-600 flex items-center justify-between">
                        <span class="truncate max-w-[75%]">
                            {{ trim($pdfSettings['texto_pie'] ?? '') ?: "Documento emitido por {$branding->name}. Información oficial y certificada del fundo activo." }}
                        </span>
                        @if($previewNumPagShow)
                            <span class="font-bold text-zinc-900 shrink-0">Pag. 1 de 1</span>
                        @endif
                    </div>
                @endif
                </div>
            </div>
        </div>

        {{-- Formulario de Ajustes Completo --}}
        <form wire:submit="savePdfSettings" class="grid gap-6 lg:grid-cols-2">

            {{-- 1. Firmas Digitales y Clásicas (Separadas por cada firmante) --}}
            <div class="agro-card p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">1. Firmas oficiales (individuales)</h3>
                            <p class="text-xs text-zinc-500">Configuración modular por cada firmante y modelo de firma.</p>
                        </div>
                    </div>

                    {{-- Toggle Switch Maestro de Firmas --}}
                    <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Activar / Desactivar bloque de firmas general">
                        <input type="checkbox" wire:model.live="pdfSettings.mostrar_firmas" class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-emerald-600 dark:bg-zinc-700"></span>
                        <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                    </label>
                </div>

                @if($previewShowMasterFirmas)
                    <div class="space-y-4">
                        {{-- Formato de Presentación Global --}}
                        <div>
                            <label class="mb-2 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Modelo de presentación</label>
                            <div class="grid gap-2 sm:grid-cols-3">
                                <label wire:key="tf-digital" wire:click="$set('pdfSettings.tipo_firma', 'digital')" class="cursor-pointer rounded-xl border p-2.5 text-center transition {{ ($pdfSettings['tipo_firma'] ?? '') === 'digital' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.tipo_firma" value="digital" class="sr-only">
                                    <strong class="block text-xs font-bold">Firma Digital</strong>
                                    <small class="text-[10px] text-zinc-500">Legal y Formal</small>
                                </label>
                                <label wire:key="tf-clasica" wire:click="$set('pdfSettings.tipo_firma', 'clasica')" class="cursor-pointer rounded-xl border p-2.5 text-center transition {{ ($pdfSettings['tipo_firma'] ?? '') === 'clasica' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.tipo_firma" value="clasica" class="sr-only">
                                    <strong class="block text-xs font-bold">Líneas Clásicas</strong>
                                    <small class="text-[10px] text-zinc-500">Firma física</small>
                                </label>
                                <label wire:key="tf-ambas" wire:click="$set('pdfSettings.tipo_firma', 'ambas')" class="cursor-pointer rounded-xl border p-2.5 text-center transition {{ ($pdfSettings['tipo_firma'] ?? '') === 'ambas' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.tipo_firma" value="ambas" class="sr-only">
                                    <strong class="block text-xs font-bold">Doble Modelo</strong>
                                    <small class="text-[10px] text-zinc-500">Líneas + Sello</small>
                                </label>
                            </div>
                        </div>

                        {{-- FIRMANTE 1: Tarjeta Individual Separada con Switch Propio --}}
                        @php $isSig1Active = filter_var($pdfSettings['mostrar_firma_1'] ?? true, FILTER_VALIDATE_BOOLEAN); @endphp
                        <div class="rounded-xl border {{ $isSig1Active ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-400/20 dark:bg-emerald-400/5' : 'border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/40 opacity-70' }} p-4 transition-all duration-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-md {{ $isSig1Active ? 'bg-emerald-700 text-white dark:bg-emerald-500 dark:text-zinc-950' : 'bg-zinc-300 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300' }} text-[10px] font-extrabold">1</span>
                                    <div>
                                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Firmante 1 (Titular / Responsable)</strong>
                                        <span class="block text-[10px] text-zinc-500">Responsable de Administración / Titular del Fundo</span>
                                    </div>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Activar / Desactivar Firmante 1">
                                    <input type="checkbox" wire:model.live="pdfSettings.mostrar_firma_1" class="peer sr-only">
                                    <span class="h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-emerald-600 dark:bg-zinc-700"></span>
                                    <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                </label>
                            </div>

                            @if($isSig1Active)
                                <div class="grid gap-3 sm:grid-cols-2 pt-2 border-t border-emerald-200/50 dark:border-emerald-800/40">
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Cargo oficial</label>
                                        <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_1_cargo" placeholder="ej. Responsable de Fundo / Titular" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Nombre completo del titular</label>
                                        <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_1_nombre" placeholder="ej. Noe Franklin Choquenaira Quispe" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">DNI / RUC del titular</label>
                                        <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_1_documento" placeholder="ej. DNI 74056499" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                </div>
                            @else
                                <p class="text-[11px] italic text-zinc-400">Firmante 1 desactivado. No se mostrará en los reportes.</p>
                            @endif
                        </div>

                        {{-- FIRMANTE 2: Tarjeta Individual Separada con Switch Propio --}}
                        @php $isSig2Active = filter_var($pdfSettings['mostrar_firma_2'] ?? true, FILTER_VALIDATE_BOOLEAN); @endphp
                        <div class="rounded-xl border {{ $isSig2Active ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-400/20 dark:bg-emerald-400/5' : 'border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/40 opacity-70' }} p-4 transition-all duration-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-md {{ $isSig2Active ? 'bg-emerald-700 text-white dark:bg-emerald-500 dark:text-zinc-950' : 'bg-zinc-300 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300' }} text-[10px] font-extrabold">2</span>
                                    <div>
                                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Firmante 2 (Técnico / Sanitario / Veterinario)</strong>
                                        <span class="block text-[10px] text-zinc-500">Supervisión, sanidad o control veterinario</span>
                                    </div>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Activar / Desactivar Firmante 2">
                                    <input type="checkbox" wire:model.live="pdfSettings.mostrar_firma_2" class="peer sr-only">
                                    <span class="h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-emerald-600 dark:bg-zinc-700"></span>
                                    <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                </label>
                            </div>

                            @if($isSig2Active)
                                <div class="grid gap-3 sm:grid-cols-2 pt-2 border-t border-emerald-200/50 dark:border-emerald-800/40">
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Cargo oficial</label>
                                        <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_2_cargo" placeholder="ej. Médico Veterinario / Control Técnico" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Nombre completo del profesional</label>
                                        <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_2_nombre" placeholder="ej. Dr. Carlos Mendoza Pari" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">DNI / Colegiatura CMVP</label>
                                        <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_2_documento" placeholder="ej. DNI 41235678 - CMVP 8492" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                    </div>
                                </div>
                            @else
                                <p class="text-[11px] italic text-zinc-400">Firmante 2 desactivado. No se mostrará en los reportes.</p>
                            @endif
                        </div>

                        {{-- Metadatos Generales de Firmas --}}
                        <div class="grid gap-3 sm:grid-cols-2 rounded-xl border border-zinc-200 bg-zinc-50/70 p-3.5 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Motivo legal de certificación</label>
                                <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_motivo" placeholder="ej. Autorización y Conformidad del Documento" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Sistema oficial de validación</label>
                                <input type="text" wire:model.live.debounce.300ms="pdfSettings.firma_software" placeholder="ej. AGROFUNDO ERP v2.6 · Software Ganadero" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. Validación Externa / Bancaria --}}
            @php $isSelloExternoActive = filter_var($pdfSettings['mostrar_sello_externo'] ?? false, FILTER_VALIDATE_BOOLEAN); @endphp
            <div class="agro-card p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">2. Sello bancario / evaluación externa</h3>
                            <p class="text-xs text-zinc-500">Para trámites de crédito bancario, analistas o auditoría.</p>
                        </div>
                    </div>

                    {{-- Toggle Switch --}}
                    <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Activar / Desactivar Sello Externo">
                        <input type="checkbox" wire:model.live="pdfSettings.mostrar_sello_externo" class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-sky-600 dark:bg-zinc-700"></span>
                        <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                    </label>
                </div>

                @if($isSelloExternoActive)
                    <div class="grid gap-3 sm:grid-cols-2 rounded-xl border border-sky-200/70 bg-sky-50/40 p-4 dark:border-sky-900/40 dark:bg-sky-950/20">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Entidad bancaria o evaluadora</label>
                            <input type="text" wire:model.live.debounce.300ms="pdfSettings.sello_externo_entidad" placeholder="ej. AGROBANCO / BCP / BBVA" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Cargo del evaluador</label>
                            <input type="text" wire:model.live.debounce.300ms="pdfSettings.sello_externo_cargo" placeholder="ej. Analista de Créditos / Auditor" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Nombre completo del evaluador</label>
                            <input type="text" wire:model.live.debounce.300ms="pdfSettings.sello_externo_nombre" placeholder="ej. Lic. Roberto Huamán Soto" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">DNI del evaluador</label>
                            <input type="text" wire:model.live.debounce.300ms="pdfSettings.sello_externo_documento" placeholder="ej. DNI 09876543" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">N° de Expediente / Operación</label>
                            <input type="text" wire:model.live.debounce.300ms="pdfSettings.sello_externo_expediente" placeholder="ej. EXP-2026-CRÉD-0041" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Dictamen o estado</label>
                            <input type="text" wire:model.live.debounce.300ms="pdfSettings.sello_externo_estado" placeholder="ej. CONFORME / APROBADO" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>
                    </div>
                @else
                    <p class="text-xs text-zinc-400 italic">Sello bancario externo inactivo. Actívalo cuando necesites presentar el documento a entidades financieras.</p>
                @endif
            </div>

            {{-- 3. Escala y Proporción de Firmas y Sellos --}}
            <div class="agro-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">3. Escala y proporción de firmas digitales</h3>
                            <p class="text-xs text-zinc-500">Ajusta el tamaño visual de firmas y sellos en paralelo en tiempo real.</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-violet-700 dark:text-violet-400 bg-violet-50 dark:bg-violet-950/50 px-2 py-0.5 rounded-md border border-violet-200 dark:border-violet-800">
                        {{ $pdfSettings['escala_firmas'] ?? 100 }}%
                    </span>
                </div>

                {{-- Selector con Botones de Check Numérico (40%, 50%, 60%, 70%, 80%, 90%, 100%, 110%, 120%, 130%, 140%) --}}
                <div class="space-y-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Nivel de escala rápida (Selección única con check):</label>
                        <div class="grid grid-cols-3 gap-1.5 sm:grid-cols-4 lg:grid-cols-7">
                            @php
                                $scaleOptions = [
                                    45 => '45% Mín',
                                    65 => '65% Comp',
                                    80 => '80% Med',
                                    100 => '100% Est',
                                    120 => '120% Dest',
                                    140 => '140% Gran',
                                    155 => '155% Máx',
                                ];
                                $currentScale = (int) ($pdfSettings['escala_firmas'] ?? 100);
                            @endphp
                            @foreach($scaleOptions as $sPercent => $sLabel)
                                @php $isSel = $currentScale === $sPercent; @endphp
                                <button type="button"
                                        wire:key="scale-opt-{{ $sPercent }}"
                                        wire:click="setSignatureScale({{ $sPercent }})"
                                        class="relative flex flex-col items-center justify-center rounded-xl border p-2 text-center transition cursor-pointer {{ $isSel ? 'border-violet-600 bg-violet-600 text-white shadow-xs ring-2 ring-violet-600/30 dark:border-violet-500 dark:bg-violet-600' : 'border-zinc-200 bg-white text-zinc-800 hover:border-violet-400 hover:bg-violet-50/50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700' }}">
                                    @if($isSel)
                                        <span class="absolute top-0.5 right-0.5 flex h-3 w-3 items-center justify-center rounded-full bg-white text-violet-700">
                                            <svg class="h-2 w-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                    @endif
                                    <span class="text-xs font-black font-mono leading-none">{{ $sPercent }}%</span>
                                    <span class="mt-0.5 text-[9px] font-semibold truncate max-w-full {{ $isSel ? 'text-violet-100' : 'text-zinc-500 dark:text-zinc-400' }}">{{ $sLabel }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Control manual con slider --}}
                    <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 shrink-0">Ajuste fino manual:</span>
                        <input type="range" min="35" max="165" step="5" wire:model.live="pdfSettings.escala_firmas" class="w-full accent-violet-600 cursor-pointer">
                        <span class="font-mono text-xs font-bold text-zinc-800 dark:text-zinc-200 shrink-0 w-12 text-right">{{ $pdfSettings['escala_firmas'] ?? 100 }}%</span>
                    </div>

                    {{-- Muestra en Vivo de Firmas en Paralelo --}}
                    @php
                        $curScaleFactor = max(0.35, min(1.7, ((int) ($pdfSettings['escala_firmas'] ?? 100)) / 100));
                        $prevAcc = $pdfSettings['color_acento'] ?: (\App\Support\PdfReportConfig::COLOR_PRESETS[$pdfSettings['estilo_color'] ?? 'emerald']['primary'] ?? '#059669');
                    @endphp
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 space-y-2.5">
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-800">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-violet-500 animate-pulse"></span>
                                Vista interactiva de firmas en paralelo con escala {{ $pdfSettings['escala_firmas'] ?? 100 }}%:
                            </span>
                            <span class="text-[10px] text-zinc-400">Escalado reactivo proporcional</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start pt-1">
                            {{-- Muestra Firma 1 --}}
                            <div class="rounded-lg border border-zinc-200/80 bg-zinc-50/50 p-2.5 dark:border-zinc-800 dark:bg-zinc-900/40 text-left transition-all duration-150"
                                 style="font-size: {{ round(9 * $curScaleFactor, 1) }}px; line-height: 1.15;">
                                <div style="color: {{ $prevAcc }};">
                                    <span class="font-bold uppercase tracking-wider block" style="font-size: {{ round(8 * $curScaleFactor, 1) }}px; line-height: 1.05;">Firmado digitalmente por:</span>
                                    <strong class="text-zinc-950 dark:text-zinc-100 uppercase block font-black" style="font-size: {{ round(10.5 * $curScaleFactor, 1) }}px; line-height: 1.1; margin-top: 0.5px;">{{ $pdfSettings['firma_1_nombre'] ?: 'Noe Franklin Choquenaira Quispe' }}</strong>
                                </div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 1.5px; line-height: 1.15;"><strong>Cargo:</strong> {{ $pdfSettings['firma_1_cargo'] ?: 'Responsable de Fundo / Titular' }}</div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 0.5px; line-height: 1.15;"><strong>DNI / RUC:</strong> {{ $pdfSettings['firma_1_documento'] ?: 'DNI 74056499' }}</div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 0.5px; line-height: 1.15;"><strong>Motivo:</strong> Autorización y Conformidad</div>
                                <div class="font-bold" style="color: {{ $prevAcc }}; font-size: {{ round(8.5 * $curScaleFactor, 1) }}px; margin-top: 1.5px; line-height: 1.15;">Validación: AGROFUNDO ERP v2.6</div>
                            </div>

                            {{-- Muestra Firma 2 --}}
                            <div class="rounded-lg border border-zinc-200/80 bg-zinc-50/50 p-2.5 dark:border-zinc-800 dark:bg-zinc-900/40 text-left transition-all duration-150"
                                 style="font-size: {{ round(9 * $curScaleFactor, 1) }}px; line-height: 1.15;">
                                <div style="color: {{ $prevAcc }};">
                                    <span class="font-bold uppercase tracking-wider block" style="font-size: {{ round(8 * $curScaleFactor, 1) }}px; line-height: 1.05;">Firmado digitalmente por:</span>
                                    <strong class="text-zinc-950 dark:text-zinc-100 uppercase block font-black" style="font-size: {{ round(10.5 * $curScaleFactor, 1) }}px; line-height: 1.1; margin-top: 0.5px;">{{ $pdfSettings['firma_2_nombre'] ?: 'Supervisión Técnica y Sanitaria' }}</strong>
                                </div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 1.5px; line-height: 1.15;"><strong>Cargo:</strong> {{ $pdfSettings['firma_2_cargo'] ?: 'Médico Veterinario' }}</div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 0.5px; line-height: 1.15;"><strong>Colegiatura:</strong> {{ $pdfSettings['firma_2_documento'] ?: 'DNI / CMVP Colegiatura' }}</div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 0.5px; line-height: 1.15;"><strong>Motivo:</strong> Certificación Sanitaria</div>
                                <div class="font-bold" style="color: {{ $prevAcc }}; font-size: {{ round(8.5 * $curScaleFactor, 1) }}px; margin-top: 1.5px; line-height: 1.15;">Validación: Conforme</div>
                            </div>

                            {{-- Muestra Sello Externo / Banco --}}
                            <div class="rounded-lg border border-sky-200 bg-sky-50/60 p-2.5 dark:border-sky-900/60 dark:bg-sky-950/30 text-left transition-all duration-150"
                                 style="font-size: {{ round(9 * $curScaleFactor, 1) }}px; line-height: 1.15;">
                                <div class="text-sky-800 dark:text-sky-400">
                                    <span class="font-bold uppercase tracking-wider block" style="font-size: {{ round(8 * $curScaleFactor, 1) }}px; line-height: 1.05;">Evaluación Externa / Dictamen:</span>
                                    <strong class="text-zinc-950 dark:text-zinc-100 uppercase block font-black" style="font-size: {{ round(10.5 * $curScaleFactor, 1) }}px; line-height: 1.1; margin-top: 0.5px;">{{ $pdfSettings['sello_externo_entidad'] ?: 'Entidad Bancaria / Financiera' }}</strong>
                                </div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 1.5px; line-height: 1.15;"><strong>Evaluador:</strong> {{ $pdfSettings['sello_externo_nombre'] ?: 'Lic. Roberto Huamán' }}</div>
                                <div class="text-zinc-700 dark:text-zinc-300" style="margin-top: 0.5px; line-height: 1.15;"><strong>Expediente:</strong> {{ $pdfSettings['sello_externo_expediente'] ?: 'EXP-2026-CRÉD-0041' }}</div>
                                <div class="text-emerald-700 dark:text-emerald-400 font-bold" style="font-size: {{ round(8.5 * $curScaleFactor, 1) }}px; margin-top: 1.5px; line-height: 1.15;">Estado: {{ $pdfSettings['sello_externo_estado'] ?: 'CONFORME / APROBADO' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Estilo de Bordes y Esquinas de Tablas --}}
            <div class="agro-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">4. Esquinas y bordes de tablas</h3>
                            <p class="text-xs text-zinc-500">Curvatura continua o bordes rectos para todas las tablas.</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-teal-700 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/50 px-2 py-0.5 rounded-md border border-teal-200 dark:border-teal-800">
                        Radio: {{ ($pdfSettings['estilo_esquinas'] ?? 'redondeado') === 'clasico' ? '0 pt (Recto)' : (($pdfSettings['radio_esquinas'] ?? 5) . ' pt') }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <label wire:key="corner-rounded" wire:click="$set('pdfSettings.estilo_esquinas', 'redondeado')" class="cursor-pointer flex items-center gap-2.5 rounded-xl border p-2.5 transition {{ ($pdfSettings['estilo_esquinas'] ?? 'redondeado') === 'redondeado' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="pdfSettings.estilo_esquinas" value="redondeado" class="sr-only">
                            <span class="h-6 w-6 rounded-lg border-2 border-emerald-600 bg-emerald-100 flex items-center justify-center shrink-0">
                                <span class="h-2 w-2 rounded-full bg-emerald-700"></span>
                            </span>
                            <div>
                                <strong class="block text-xs font-bold">Curvadas / Modernas</strong>
                                <small class="text-[10px] text-zinc-500">Sin esquinas duras (Curvas suaves)</small>
                            </div>
                        </label>
                        <label wire:key="corner-classic" wire:click="setCornerRadius(0)" class="cursor-pointer flex items-center gap-2.5 rounded-xl border p-2.5 transition {{ ($pdfSettings['estilo_esquinas'] ?? '') === 'clasico' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                            <input type="radio" wire:model.live="pdfSettings.estilo_esquinas" value="clasico" class="sr-only">
                            <span class="h-6 w-6 border-2 border-zinc-500 bg-zinc-100 flex items-center justify-center shrink-0">
                                <span class="h-2 w-2 bg-zinc-700"></span>
                            </span>
                            <div>
                                <strong class="block text-xs font-bold">Clásicas Rectas</strong>
                                <small class="text-[10px] text-zinc-500">Bordes rectangulares (0 pt)</small>
                            </div>
                        </label>
                    </div>

                    {{-- Selector Numérico Entero de Radio de Esquina con Check --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-900/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Tamaño del radio en números enteros (Selección única):</span>
                            <span class="text-[10px] text-zinc-500">Afecta en paralelo a todas las tablas</span>
                        </div>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
                            @php
                                $radiusOptions = [
                                    0 => '0 Recto',
                                    2 => '2 Mín',
                                    3 => '3 S-Lig',
                                    4 => '4 Ligero',
                                    5 => '5 Est',
                                    6 => '6 Suave',
                                    8 => '8 Curvo',
                                    10 => '10 Red',
                                ];
                                $currentRadius = ($pdfSettings['estilo_esquinas'] ?? 'redondeado') === 'clasico' ? 0 : (int) ($pdfSettings['radio_esquinas'] ?? 5);
                            @endphp
                            @foreach($radiusOptions as $rNum => $rLabel)
                                @php $isSelected = $currentRadius === $rNum; @endphp
                                <button type="button"
                                        wire:key="radius-opt-{{ $rNum }}"
                                        wire:click="setCornerRadius({{ $rNum }})"
                                        class="relative flex flex-col items-center justify-center rounded-xl border p-2 text-center transition cursor-pointer {{ $isSelected ? 'border-emerald-600 bg-emerald-600 text-white shadow-xs ring-2 ring-emerald-600/30 dark:border-emerald-500 dark:bg-emerald-600' : 'border-zinc-200 bg-white text-zinc-800 hover:border-emerald-400 hover:bg-emerald-50/50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700' }}">
                                    @if($isSelected)
                                        <span class="absolute top-1 right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-white text-emerald-700">
                                            <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                    @endif
                                    <span class="text-sm font-black font-mono leading-none">{{ $rNum }}</span>
                                    <span class="mt-1 text-[9px] font-semibold {{ $isSelected ? 'text-emerald-100' : 'text-zinc-500 dark:text-zinc-400' }}">{{ $rLabel }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. Color Institucional y Paleta Múltiple --}}
            @php $isLogoActive = filter_var($pdfSettings['mostrar_logo'] ?? true, FILTER_VALIDATE_BOOLEAN); @endphp
            <div class="agro-card p-5 sm:p-6 space-y-4 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">5. Color institucional y rotación multicolor</h3>
                            <p class="text-xs text-zinc-500">Estilo cromático institucional para encabezados y rotación suave de lotes.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50/70 p-3.5 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Mostrar logotipo institucional en cabecera</span>
                        <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model.live="pdfSettings.mostrar_logo" class="peer sr-only">
                            <span class="h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-emerald-600 dark:bg-zinc-700"></span>
                            <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                        </label>
                    </div>

                    {{-- Color Principal Institucional (16 Presets Ricos & Suaves) --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Color principal de cabecera institucional (16 colores suaves y vivos):</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-8">
                            @foreach(\App\Support\PdfReportConfig::COLOR_PRESETS as $presetKey => $presetData)
                                <label wire:key="cp-{{ $presetKey }}" wire:click="$set('pdfSettings.estilo_color', '{{ $presetKey }}')" class="cursor-pointer flex items-center gap-2 rounded-xl border p-2 transition {{ ($pdfSettings['estilo_color'] ?? '') === $presetKey ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.estilo_color" value="{{ $presetKey }}" class="sr-only">
                                    <span class="h-4 w-4 rounded-md shadow-xs shrink-0" style="background-color: {{ $presetData['primary'] }}"></span>
                                    <span class="text-[10px] font-bold truncate">{{ $presetData['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Modo de Color para Reportes con Múltiples Tablas (Engorde, Queso, Finanzas, etc.) --}}
                    <div class="border-t border-zinc-200 pt-3.5 dark:border-zinc-800 space-y-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Coloración de múltiples tablas (Lotes y Secciones)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label wire:key="tcol-multi" wire:click="$set('pdfSettings.modo_color_tablas', 'multi')" class="cursor-pointer flex items-center gap-2.5 rounded-xl border p-2.5 transition {{ ($pdfSettings['modo_color_tablas'] ?? 'multi') === 'multi' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.modo_color_tablas" value="multi" class="sr-only">
                                    <div class="flex -space-x-1 shrink-0">
                                        <span class="h-4 w-4 rounded-full bg-emerald-600 ring-2 ring-white"></span>
                                        <span class="h-4 w-4 rounded-full bg-indigo-600 ring-2 ring-white"></span>
                                        <span class="h-4 w-4 rounded-full bg-amber-600 ring-2 ring-white"></span>
                                    </div>
                                    <div>
                                        <strong class="block text-xs font-bold">Multicolor Armónico</strong>
                                        <small class="text-[10px] text-zinc-500">Distingue cada tabla con su propio tono suave</small>
                                    </div>
                                </label>
                                <label wire:key="tcol-mono" wire:click="$set('pdfSettings.modo_color_tablas', 'monocromatico')" class="cursor-pointer flex items-center gap-2.5 rounded-xl border p-2.5 transition {{ ($pdfSettings['modo_color_tablas'] ?? '') === 'monocromatico' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.modo_color_tablas" value="monocromatico" class="sr-only">
                                    <span class="h-5 w-5 rounded-md bg-emerald-700 shrink-0"></span>
                                    <div>
                                        <strong class="block text-xs font-bold">Monocromático</strong>
                                        <small class="text-[10px] text-zinc-500">Un solo color institucional para todas las tablas</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Selección Múltiple de Colores para Rotación de Tablas (16 Colores) --}}
                        @if(($pdfSettings['modo_color_tablas'] ?? 'multi') === 'multi')
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-3.5 dark:border-zinc-800 dark:bg-zinc-900/50 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Paleta activa de colores rotativos (Selección múltiple - 16 colores disponibles):</span>
                                    <span class="text-[10px] text-zinc-500 font-semibold">Las tablas rotarán secuencialmente entre estos tonos</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-8">
                                    @foreach(\App\Support\PdfReportConfig::COLOR_PRESETS as $presetKey => $presetData)
                                        <label class="flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white p-2 text-xs font-semibold text-zinc-700 shadow-2xs transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 cursor-pointer">
                                            <input type="checkbox" wire:model.live="pdfSettings.paleta_tablas" value="{{ $presetKey }}" class="rounded text-emerald-600 focus:ring-emerald-500">
                                            <span class="h-3.5 w-3.5 rounded-full shadow-2xs shrink-0" style="background-color: {{ $presetData['primary'] }}"></span>
                                            <span class="truncate text-[10px]">{{ $presetData['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Muestra en Paralelo de 3 Tablas (Tiempo Real) --}}
                        @php
                            $activePalettes = is_array($pdfSettings['paleta_tablas'] ?? null)
                                ? $pdfSettings['paleta_tablas']
                                : explode(',', (string) ($pdfSettings['paleta_tablas'] ?? 'emerald,indigo,amber,sky'));
                            $activePalettes = array_values(array_filter($activePalettes));
                            if (empty($activePalettes)) {
                                $activePalettes = ['emerald', 'indigo', 'amber'];
                            }

                            $isMulti = ($pdfSettings['modo_color_tablas'] ?? 'multi') === 'multi';
                            $instColor = $pdfSettings['estilo_color'] ?? 'emerald';

                            $previewRadiusPx = ($pdfSettings['estilo_esquinas'] ?? 'redondeado') === 'clasico' ? 0 : (int) ($pdfSettings['radio_esquinas'] ?? 5);

                            $sampleData = [
                                0 => [
                                    'title' => 'LOTE #1 · Engorde Machos F-26',
                                    'meta' => 'Inicio: 12/01/2026 · Total: 18 animales',
                                    'themeKey' => $isMulti ? ($activePalettes[0] ?? 'emerald') : $instColor,
                                    'rows' => [
                                        ['code' => 'ENG-01', 'name' => 'Toro Bravo', 'metric' => '+42.5 kg', 'gmd' => '1.42 kg/d'],
                                        ['code' => 'ENG-02', 'name' => 'Semental Oro', 'metric' => '+38.0 kg', 'gmd' => '1.26 kg/d'],
                                    ],
                                ],
                                1 => [
                                    'title' => 'LOTE #2 · Vacunación & Sanidad',
                                    'meta' => 'Fecha: 15/02/2026 · Total: 24 dosis',
                                    'themeKey' => $isMulti ? ($activePalettes[1 % count($activePalettes)] ?? 'indigo') : $instColor,
                                    'rows' => [
                                        ['code' => 'SAN-01', 'name' => 'Fiebre Aftosa', 'metric' => 'Aplicada', 'gmd' => 'Dosis 1'],
                                        ['code' => 'SAN-02', 'name' => 'Desparasitación', 'metric' => 'Aplicada', 'gmd' => 'Dosis 2'],
                                    ],
                                ],
                                2 => [
                                    'title' => 'LOTE #3 · Control de Ordeño Mañana',
                                    'meta' => 'Turno: Mañana · Total: 340.5 L',
                                    'themeKey' => $isMulti ? ($activePalettes[2 % count($activePalettes)] ?? 'amber') : $instColor,
                                    'rows' => [
                                        ['code' => 'ORD-01', 'name' => 'Vaca Lucero', 'metric' => '18.5 L', 'gmd' => '1ra Cal.'],
                                        ['code' => 'ORD-02', 'name' => 'Vaca Princesa', 'metric' => '16.2 L', 'gmd' => '1ra Cal.'],
                                    ],
                                ],
                            ];
                        @endphp
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Vista en paralelo de 3 tablas con esquinas y colores dinámicos:</h4>
                                </div>
                                <span class="text-[10px] text-zinc-500 font-mono font-semibold">Radio actual: {{ $previewRadiusPx }}px</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @foreach($sampleData as $sIdx => $sTable)
                                    @php
                                        $pPreset = \App\Support\PdfReportConfig::COLOR_PRESETS[$sTable['themeKey']] ?? \App\Support\PdfReportConfig::COLOR_PRESETS['emerald'];
                                    @endphp
                                    <div class="overflow-hidden transition-all duration-200"
                                         style="border: 1.5px solid {{ $pPreset['border'] }}; border-radius: {{ $previewRadiusPx }}px; background-color: #ffffff;">
                                        {{-- Header de Lote --}}
                                        <div class="p-2 border-b flex items-center justify-between"
                                             style="background-color: {{ $pPreset['soft'] }}; border-color: {{ $pPreset['border'] }};">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span class="px-1.5 py-0.5 text-[9px] font-black text-white rounded shrink-0" style="background-color: {{ $pPreset['primary'] }}">#{{ $sIdx + 1 }}</span>
                                                <strong class="text-[10px] text-zinc-950 truncate font-extrabold">{{ $sTable['title'] }}</strong>
                                            </div>
                                        </div>
                                        {{-- Subcard meta --}}
                                        <div class="px-2 py-1 text-[9px] text-zinc-600 border-b" style="background-color: #ffffff; border-color: {{ $pPreset['border'] }};">
                                            {{ $sTable['meta'] }}
                                        </div>
                                        {{-- Table rows --}}
                                        <table class="w-full text-left text-[9px]">
                                            <thead>
                                                <tr style="background-color: {{ $pPreset['soft'] }}; color: {{ $pPreset['dark'] }}; border-bottom: 1px solid {{ $pPreset['border'] }};">
                                                    <th class="p-1 font-bold">Código</th>
                                                    <th class="p-1 font-bold">Detalle</th>
                                                    <th class="p-1 text-right font-bold">Resultado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y" style="border-color: {{ $pPreset['border'] }};">
                                                @foreach($sTable['rows'] as $rIdx => $row)
                                                    <tr style="background-color: {{ $rIdx % 2 === 1 ? $pPreset['row_even'] : '#ffffff' }};">
                                                        <td class="p-1 font-mono font-bold" style="color: {{ $pPreset['dark'] }}">{{ $row['code'] }}</td>
                                                        <td class="p-1 text-zinc-800">{{ $row['name'] }}</td>
                                                        <td class="p-1 text-right font-bold text-zinc-900">{{ $row['metric'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 6. Marca de Agua (Diagonal / Recta) y Pie de Página --}}
            @php $isWatermarkActive = filter_var($pdfSettings['mostrar_marca_agua'] ?? true, FILTER_VALIDATE_BOOLEAN); @endphp
            <div class="agro-card p-5 sm:p-6 space-y-4 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">6. Marca de agua & pie legal</h3>
                            <p class="text-xs text-zinc-500">Seguridad anticopia con orientación diagonal o recta y foliación.</p>
                        </div>
                    </div>

                    {{-- Toggle Switch Marca de Agua --}}
                    <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Activar / Desactivar Marca de Agua">
                        <input type="checkbox" wire:model.live="pdfSettings.mostrar_marca_agua" class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-emerald-600 dark:bg-zinc-700"></span>
                        <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                    </label>
                </div>

                <div class="space-y-4">
                    @if($isWatermarkActive)
                        <div>
                            <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Texto de la marca de agua</label>
                            <input type="text" wire:model.live.debounce.300ms="pdfSettings.texto_marca_agua" placeholder="Dejar vacío para usar el nombre oficial del fundo" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </div>

                        {{-- Orientación: Diagonal o Recto (Horizontal) --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Orientación de la marca de agua</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label wire:key="wm-opt-diag" wire:click="$set('pdfSettings.orientacion_marca_agua', 'diagonal')" class="cursor-pointer flex items-center justify-center gap-2 rounded-xl border p-2.5 text-center transition {{ ($pdfSettings['orientacion_marca_agua'] ?? 'diagonal') === 'diagonal' ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.orientacion_marca_agua" value="diagonal" class="sr-only">
                                    <svg class="h-4 w-4 transform -rotate-45 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    <div class="text-left">
                                        <strong class="block text-xs font-bold">Diagonal (-24°)</strong>
                                        <small class="text-[10px] text-zinc-500">Inclinada (Seguridad)</small>
                                    </div>
                                </label>

                                <label wire:key="wm-opt-horiz" wire:click="$set('pdfSettings.orientacion_marca_agua', 'horizontal')" class="cursor-pointer flex items-center justify-center gap-2 rounded-xl border p-2.5 text-center transition {{ in_array(($pdfSettings['orientacion_marca_agua'] ?? ''), ['horizontal', 'recto'], true) ? 'border-emerald-600 bg-emerald-50 text-emerald-950 ring-1 ring-emerald-600/50 dark:border-emerald-400 dark:bg-emerald-400/10 dark:text-emerald-200 dark:ring-emerald-400/50' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300' }}">
                                    <input type="radio" wire:model.live="pdfSettings.orientacion_marca_agua" value="horizontal" class="sr-only">
                                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                    <div class="text-left">
                                        <strong class="block text-xs font-bold">Recta (0°)</strong>
                                        <small class="text-[10px] text-zinc-500">Horizontal centrada</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Opacidad y Color --}}
                        <div class="grid grid-cols-2 gap-3 items-end">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Intensidad / Opacidad</label>
                                <x-filter-select model="pdfSettings.opacidad_marca_agua" :options="\App\Support\PdfReportConfig::OPACITY_PRESETS" tone="emerald" live />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Tonalidad / Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" wire:model.live.debounce.150ms="pdfSettings.color_marca_agua" class="h-11 w-11 cursor-pointer rounded-xl border border-zinc-300 dark:border-zinc-700 p-1 bg-white dark:bg-zinc-900 shadow-sm shrink-0">
                                    <input type="text" wire:model.live.debounce.300ms="pdfSettings.color_marca_agua" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 font-mono text-sm uppercase text-zinc-900 shadow-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Pie de página y foliación --}}
                    @php $isPieActive = filter_var($pdfSettings['mostrar_pie'] ?? true, FILTER_VALIDATE_BOOLEAN); @endphp
                    <div class="border-t border-zinc-200 pt-3.5 dark:border-zinc-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Pie de página institucional con foliación</span>
                                <span class="block text-[10px] text-zinc-500">Muestra numeración de páginas (Pag. X de Y) y texto de certificación</span>
                            </div>
                            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                <input type="checkbox" wire:model.live="pdfSettings.mostrar_pie" class="peer sr-only">
                                <span class="h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-emerald-600 dark:bg-zinc-700"></span>
                                <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                            </label>
                        </div>

                        @if($isPieActive)
                            <div>
                                <label class="mb-1 block text-xs font-bold text-zinc-700 dark:text-zinc-300">Texto legal del pie</label>
                                <input type="text" wire:model.live.debounce.300ms="pdfSettings.texto_pie" placeholder="Dejar vacío para el texto oficial predeterminado" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Botón Guardar Cambios --}}
            @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))
                <div class="lg:col-span-2 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="savePdfSettings"
                            class="agro-button w-full sm:w-auto">
                        <span wire:loading.remove wire:target="savePdfSettings">Guardar configuración de PDF</span>
                        <span wire:loading wire:target="savePdfSettings">Guardando cambios...</span>
                    </button>
                </div>
            @endif
        </form>
    @endif

    {{-- SUB-TAB 2: CERTIFICADOS DIGITALES X.509 Y CRIPTOGRAFÍA --}}
    @if($activePdfSubTab === 'certificados')
        <div class="space-y-6">
            <div class="agro-card p-5 sm:p-6 space-y-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">Certificado Digital X.509 del Fundo</h3>
                            <p class="text-xs text-zinc-500">Clave pública y privada RSA 2048-bit para firmas PAdES y protección DocMDP P=1.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($fundoCertDetails)
                            <button type="button" wire:click="downloadFundoCertificate"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-bold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Descargar Certificado (.crt)</span>
                            </button>
                        @endif

                        @if(auth()->user()->tienePermiso('ajustes', 'actualizar'))
                            <button type="button" wire:click="generateFundoCertificate"
                                    x-on:click.prevent="confirmDelete('¿Generar / Renovar Certificado?', 'Se creará un nuevo par de claves RSA 2048 y certificado X.509 para firma digital con validez de 5 años.').then((res) => { if (res.isConfirmed) $wire.generateFundoCertificate() })"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-purple-700 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-purple-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>{{ $fundoCertDetails ? 'Renovar Certificado' : 'Generar Certificado' }}</span>
                            </button>
                        @endif
                    </div>
                </div>

                @if($fundoCertDetails)
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Titular / Common Name</span>
                            <p class="mt-1 text-sm font-extrabold text-zinc-900 dark:text-zinc-100">{{ $fundoCertDetails['common_name'] }}</p>
                            <span class="mt-1 block text-xs text-zinc-500">{{ $fundoCertDetails['organization'] }} &bull; {{ $fundoCertDetails['country'] }}</span>
                        </div>

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Estado de Vigencia</span>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-extrabold text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                    Vigente
                                </span>
                                <span class="text-xs text-zinc-500">Hasta {{ $fundoCertDetails['valid_to'] }}</span>
                            </div>
                            <span class="mt-1 block text-[11px] text-zinc-500">Emitido: {{ $fundoCertDetails['valid_from'] }}</span>
                        </div>

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2 lg:col-span-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Algoritmo & Estándar</span>
                            <p class="mt-1 text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $fundoCertDetails['key_type'] }}</p>
                            <span class="mt-1 block text-[11px] text-purple-700 dark:text-purple-400 font-semibold">{{ $fundoCertDetails['standard'] }}</span>
                        </div>

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2 lg:col-span-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Huella Digital Criptográfica (SHA-256 Fingerprint)</span>
                                <span class="font-mono text-[10px] text-zinc-400">Serie: {{ $fundoCertDetails['serial_number'] }}</span>
                            </div>
                            <p class="mt-1.5 font-mono text-xs text-zinc-700 dark:text-zinc-300 break-all bg-white dark:bg-zinc-950 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-800">
                                {{ $fundoCertDetails['fingerprint_sha256'] }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                        <p class="text-sm text-zinc-500">Aún no se ha emitido un certificado digital X.509 para este fundo.</p>
                        <button type="button" wire:click="generateFundoCertificate" class="agro-button mt-4">
                            Generar Certificado Ahora
                        </button>
                    </div>
                @endif

                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20 text-xs leading-relaxed text-zinc-700 dark:text-zinc-300 space-y-1.5">
                    <strong class="text-emerald-900 dark:text-emerald-300 font-bold block">Protección Criptográfica DocMDP P=1 (Firma Perú)</strong>
                    <p>
                        Todos los reportes generados con Firma Digital incorporan un diccionario criptográfico con nivel de protección <strong>DocMDP P=1</strong>. Esto garantiza ante cualquier software visor de PDF (Adobe Acrobat, Foxit, PDF.js, etc.) que el documento está formalmente certificado y que cualquier alteración o modificación posterior invalidará automáticamente la firma.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- SUB-TAB 3: VALIDADOR DE FIRMAS DIGITALES PDF (FIRMA PERÃÅ¡) --}}
    @if($activePdfSubTab === 'validador')
        <div class="space-y-6">
            {{-- ZONA DE SUBIDA / DRAG & DROP --}}
            <div class="agro-card p-5 sm:p-6 space-y-4">
                <div>
                    <h3 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100">Validador de Firmas e Integridad PDF</h3>
                    <p class="text-xs text-zinc-500">Sube cualquier documento PDF para verificar si fue emitido por el sistema, si su firma criptográfica es auténtica y si ha sufrido modificaciones.</p>
                </div>

                <div class="relative">
                    <label for="pdf-validator-input"
                           class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-8 text-center transition hover:border-emerald-500 hover:bg-emerald-50/30 dark:border-zinc-700 dark:bg-zinc-950 dark:hover:border-emerald-500/50 cursor-pointer">
                        <svg class="h-12 w-12 text-emerald-600 dark:text-emerald-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <strong class="text-sm font-extrabold text-zinc-800 dark:text-zinc-200">Haz clic o arrastra un archivo PDF aquí</strong>
                        <span class="mt-1 text-xs text-zinc-500">Soporta archivos .pdf con firmas digitales estándar, PKCS#7 o sellos de AGROFUNDO</span>
                        <input id="pdf-validator-input" type="file" wire:model="pdfFileToVerify" accept="application/pdf" class="sr-only">
                    </label>

                    <div wire:loading wire:target="pdfFileToVerify"
                         class="absolute inset-0 flex items-center justify-center rounded-2xl bg-zinc-950/60 backdrop-blur-xs">
                        <div class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-emerald-400 shadow-2xl">
                            <svg class="h-4 w-4 animate-spin text-emerald-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span>Analizando firmas e integridad del PDF...</span>
                        </div>
                    </div>
                </div>

                @if($verificationErrorMessage)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30">
                        <strong>Error:</strong> {{ $verificationErrorMessage }}
                    </div>
                @endif

                @if($verificationResult)
                    <div class="flex justify-end">
                        <button type="button" wire:click="clearPdfVerification"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Limpiar y verificar otro archivo</span>
                        </button>
                    </div>
                @endif
            </div>

            {{-- RESULTADOS DEL ANÁLISIS DE FIRMA PERÃÅ¡ --}}
            @if($verificationResult)
                @php
                    $resTone = $verificationResult['tone'] ?? 'zinc';
                    $statusTitle = $verificationResult['status_title'] ?? 'Resultado';
                    $statusSubtitle = $verificationResult['status_subtitle'] ?? '';
                    $sig = $verificationResult['primary_signature'] ?? null;
                @endphp

                <div class="agro-card overflow-hidden p-0 border-2 {{ $resTone === 'emerald' ? 'border-emerald-500' : ($resTone === 'rose' ? 'border-rose-500' : ($resTone === 'amber' ? 'border-amber-500' : 'border-zinc-300 dark:border-zinc-700')) }}">
                    {{-- Banner Principal de Estado --}}
                    <div class="p-5 sm:p-6 {{ $resTone === 'emerald' ? 'bg-emerald-50 dark:bg-emerald-950/50' : ($resTone === 'rose' ? 'bg-rose-50 dark:bg-rose-950/50' : ($resTone === 'amber' ? 'bg-amber-50 dark:bg-amber-950/50' : 'bg-zinc-50 dark:bg-zinc-900')) }}">
                        <div class="flex items-start gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $resTone === 'emerald' ? 'bg-emerald-600 text-white' : ($resTone === 'rose' ? 'bg-rose-600 text-white' : ($resTone === 'amber' ? 'bg-amber-600 text-white' : 'bg-zinc-400 text-white')) }}">
                                @if($resTone === 'emerald')
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                @elseif($resTone === 'rose')
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                @elseif($resTone === 'amber')
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </span>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $resTone === 'emerald' ? 'bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200' : ($resTone === 'rose' ? 'bg-rose-200 text-rose-900 dark:bg-rose-900 dark:text-rose-200' : 'bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200') }}">
                                        {{ $verificationResult['is_signed'] ? ($verificationResult['is_valid'] ? 'FIRMA VÁLIDA' : ($verificationResult['is_tampered'] ? 'DOCUMENTO ALTERADO' : 'INVÁLIDO')) : 'NO FIRMADO' }}
                                    </span>
                                    @if($verificationResult['is_system_origin'])
                                        <span class="rounded-md bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                            EMITIDO POR AGROFUNDO
                                        </span>
                                    @endif
                                </div>
                                <h4 class="mt-1 text-lg font-black text-zinc-900 dark:text-zinc-100">{{ $statusTitle }}</h4>
                                <p class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ $statusSubtitle }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Desglose Estilo Firma Perú --}}
                    @if($sig)
                        <div class="p-5 sm:p-6 space-y-5">
                            {{-- Fila de Tarjetas de Diagnóstico --}}
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {{-- 1. Firmante --}}
                                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 space-y-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Firmado digitalmente por</span>
                                    <strong class="block text-sm font-extrabold text-zinc-900 dark:text-zinc-100">{{ $sig['signer_name'] }}</strong>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span><strong>Cargo:</strong> {{ $sig['signer_cargo'] }}</span>
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span><strong>DNI / Doc:</strong> {{ $sig['signer_dni'] }}</span>
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span><strong>Fundo:</strong> {{ $sig['fundo_nombre'] }}</span>
                                    </div>
                                </div>

                                {{-- 2. Sello de Tiempo y Motivo --}}
                                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 space-y-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Fecha, Hora y Motivo</span>
                                    <strong class="block text-xs font-extrabold text-zinc-900 dark:text-zinc-100">{{ $sig['signed_at'] }}</strong>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span><strong>Motivo:</strong> {{ $sig['reason'] }}</span>
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span><strong>Software:</strong> {{ $sig['software'] }}</span>
                                    </div>
                                </div>

                                {{-- 3. Integridad y DocMDP --}}
                                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 space-y-1 sm:col-span-2 lg:col-span-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Integridad & Certificación</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full {{ $sig['is_tampered'] ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                        <strong class="text-xs font-extrabold {{ $sig['is_tampered'] ? 'text-rose-600' : 'text-emerald-700 dark:text-emerald-400' }}">
                                            {{ $sig['is_tampered'] ? 'Documento Alterado' : 'Documento 100% Íntegro' }}
                                        </strong>
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span><strong>Protección:</strong> {{ $sig['protection'] }}</span>
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span><strong>Algoritmo:</strong> {{ $sig['algorithm'] }}</span>
                                    </div>
                                </div>

                                {{-- 4. Certificado X.509 Emisor y Hash --}}
                                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950 space-y-2 sm:col-span-2 lg:col-span-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-100 pb-2 dark:border-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Certificado X.509 Emisor</span>
                                            <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-mono text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $sig['cert_status'] }}</span>
                                        </div>
                                        <span class="text-[10px] text-zinc-500">Serie: {{ $sig['cert_serial'] }} &bull; Validez: {{ $sig['cert_valid_from'] }} al {{ $sig['cert_valid_to'] }}</span>
                                    </div>

                                    <div class="grid gap-2 sm:grid-cols-2 text-xs">
                                        <div>
                                            <span class="block text-[10px] font-semibold text-zinc-400">Hash SHA-256 del Documento (Firma)</span>
                                            <code class="block truncate font-mono text-[11px] text-zinc-800 dark:text-zinc-200 bg-zinc-50 dark:bg-zinc-900 p-1.5 rounded border border-zinc-200 dark:border-zinc-800" title="{{ $sig['doc_hash'] }}">
                                                {{ $sig['doc_hash'] }}
                                            </code>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] font-semibold text-zinc-400">Huella Digital del Certificado (Fingerprint)</span>
                                            <code class="block truncate font-mono text-[11px] text-zinc-800 dark:text-zinc-200 bg-zinc-50 dark:bg-zinc-900 p-1.5 rounded border border-zinc-200 dark:border-zinc-800" title="{{ $sig['cert_fingerprint'] }}">
                                                {{ $sig['cert_fingerprint'] }}
                                            </code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</section>

