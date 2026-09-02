@if($pdfConfig->showSignatures())
    @php
        $sigType = $pdfConfig->signatureType();
        $accent = $pdfConfig->accentColor();
        $accentDark = $pdfConfig->accentDark();
        $accentSoft = $pdfConfig->accentSoft();
        $accentBorder = $pdfConfig->accentBorder();
        $fundoNombre = $fundo->nombre ?? 'AGROFUNDO';
        $software = $pdfConfig->signatureSoftware();
        $motivo = $pdfConfig->signatureMotivo();
        $datePeru = ($generatedAt ?? now())->copy()->timezone('America/Lima')->format('d/m/Y H:i:s');
        $showSig1 = $pdfConfig->showSignature1();
        $showSig2 = $pdfConfig->showSignature2();
        $hasExternal = $pdfConfig->showExternalStamp();
        $activeCount = ($showSig1 ? 1 : 0) + ($showSig2 ? 1 : 0) + ($hasExternal ? 1 : 0);

        $sigScale = $pdfConfig->signatureScale() / 100;
        $sigBodyFont = $pdfConfig->signatureBodyFontSizePt();
        $sigTitleFont = $pdfConfig->signatureTitleFontSizePt();
        $sigLabelFont = $pdfConfig->signatureLabelFontSizePt();
        $sigMaxWidth = $pdfConfig->signatureMaxWidthPt();

        $classicTitleFont = round(6.8 * $sigScale, 2) . 'pt';
        $classicSubtitleFont = round(5.8 * $sigScale, 2) . 'pt';
        $classicDocFont = round(5.0 * $sigScale, 2) . 'pt';
        $stampTitleFont = round(6.0 * $sigScale, 2) . 'pt';
        $stampSubFont = round(5.2 * $sigScale, 2) . 'pt';
        $stampBodyFont = round(4.6 * $sigScale, 2) . 'pt';

        $colWidth = match($activeCount) {
            1 => '48%',
            2 => '48.5%',
            default => '32%',
        };
        $gapWidth = match($activeCount) {
            1 => '0%',
            2 => '3%',
            default => '2%',
        };
    @endphp

    @if($activeCount > 0)
        @if($sigType === 'digital')
            {{-- MODELO DE FIRMA DIGITAL OFICIAL (ESTÁNDAR FORMAL COMPACTO: EXTREMOS Y CENTRO) --}}
            <table class="digital-stamp-container" style="width: 100%; margin-top: 14pt; margin-bottom: 0; page-break-inside: avoid; border-collapse: collapse; background: transparent;">
                <tr>
                    @if($activeCount === 1)
                        {{-- 1 Firma: Centrada en el documento --}}
                        <td style="width: 100%; text-align: center; vertical-align: top; padding: 0; background: transparent;">
                            @if($showSig1)
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Firmado digitalmente por:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->signature1Nombre() ?: ($branding->responsable ?? 'Noe Franklin Choquenaira Quispe') }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Cargo:</strong> {{ $pdfConfig->signature1Cargo() ?: 'Responsable de Fundo / Titular' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI / RUC:</strong> {{ $pdfConfig->signature1Documento() ?: 'DNI 74056499' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Motivo:</strong> {{ $motivo ?: 'Autorización y Conformidad del Documento' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Validación: {{ $software }}</div>
                                </div>
                            @elseif($showSig2)
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Firmado digitalmente por:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->signature2Nombre() ?: 'Supervisión Técnica y Sanitaria' }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Cargo:</strong> {{ $pdfConfig->signature2Cargo() ?: 'Médico Veterinario / Control Técnico' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI / Colegiatura:</strong> {{ $pdfConfig->signature2Documento() ?: 'DNI / CMVP Colegiatura' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Motivo:</strong> Certificación Técnica y Sanitaria Oficial</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Validación Técnica: Conforme</div>
                                </div>
                            @elseif($hasExternal)
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Evaluación Externa / Dictamen:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->externalStampEntidad() ?: 'Entidad Financiera / Bancaria' }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Evaluador:</strong> {{ $pdfConfig->externalStampNombre() ?: 'Evaluador Asignado' }} ({{ $pdfConfig->externalStampCargo() ?: 'Analista de Crédito' }})</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI:</strong> {{ $pdfConfig->externalStampDocumento() ?: 'DNI del Evaluador' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">N° Expediente:</strong> {{ $pdfConfig->externalStampExpediente() ?: 'EXP-2026-CRÉD-0041' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Dictamen: {{ $pdfConfig->externalStampEstado() ?: 'CONFORME / APROBADO' }}</div>
                                </div>
                            @endif
                        </td>

                    @elseif($activeCount === 2)
                        {{-- 2 Firmas: Extremo Izquierdo y Extremo Derecho (Compactas y proporcionadas) --}}
                        @php $firstSlot = true; @endphp
                        @if($showSig1)
                            <td style="width: 42%; text-align: left; vertical-align: top; padding: 0; background: transparent;">
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Firmado digitalmente por:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->signature1Nombre() ?: ($branding->responsable ?? 'Noe Franklin Choquenaira Quispe') }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Cargo:</strong> {{ $pdfConfig->signature1Cargo() ?: 'Responsable de Fundo / Titular' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI / RUC:</strong> {{ $pdfConfig->signature1Documento() ?: 'DNI 74056499' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Motivo:</strong> {{ $motivo ?: 'Autorización y Conformidad del Documento' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Validación: {{ $software }}</div>
                                </div>
                            </td>
                            @php $firstSlot = false; @endphp
                        @endif

                        @if($showSig2)
                            @if(!$firstSlot)
                                <td style="width: 16%;"></td>
                            @endif
                            <td style="width: 42%; text-align: {{ $firstSlot ? 'left' : 'right' }}; vertical-align: top; padding: 0; background: transparent;">
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Firmado digitalmente por:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->signature2Nombre() ?: 'Supervisión Técnica y Sanitaria' }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Cargo:</strong> {{ $pdfConfig->signature2Cargo() ?: 'Médico Veterinario / Control Técnico' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI / Colegiatura:</strong> {{ $pdfConfig->signature2Documento() ?: 'DNI / CMVP Colegiatura' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Motivo:</strong> Certificación Técnica y Sanitaria Oficial</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Validación Técnica: Conforme</div>
                                </div>
                            </td>
                            @php $firstSlot = false; @endphp
                        @endif

                        @if($hasExternal)
                            @if(!$firstSlot)
                                <td style="width: 16%;"></td>
                            @endif
                            <td style="width: 42%; text-align: right; vertical-align: top; padding: 0; background: transparent;">
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Evaluación Externa / Dictamen:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->externalStampEntidad() ?: 'Entidad Financiera / Bancaria' }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Evaluador:</strong> {{ $pdfConfig->externalStampNombre() ?: 'Evaluador Asignado' }} ({{ $pdfConfig->externalStampCargo() ?: 'Analista de Crédito' }})</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI:</strong> {{ $pdfConfig->externalStampDocumento() ?: 'DNI del Evaluador' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">N° Expediente:</strong> {{ $pdfConfig->externalStampExpediente() ?: 'EXP-2026-CRÉD-0041' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Dictamen: {{ $pdfConfig->externalStampEstado() ?: 'CONFORME / APROBADO' }}</div>
                                </div>
                            </td>
                        @endif

                    @else
                        {{-- 3 Firmas: Extremo Izquierdo, Centro Exacto y Extremo Derecho --}}
                        {{-- Firma 1 (Izquierda) --}}
                        @if($showSig1)
                            <td style="width: 30%; text-align: left; vertical-align: top; padding: 0; background: transparent;">
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Firmado digitalmente por:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->signature1Nombre() ?: ($branding->responsable ?? 'Noe Franklin Choquenaira Quispe') }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Cargo:</strong> {{ $pdfConfig->signature1Cargo() ?: 'Responsable de Fundo / Titular' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI / RUC:</strong> {{ $pdfConfig->signature1Documento() ?: 'DNI 74056499' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Motivo:</strong> {{ $motivo ?: 'Autorización y Conformidad del Documento' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Validación: {{ $software }}</div>
                                </div>
                            </td>
                        @endif

                        <td style="width: 5%;"></td>

                        {{-- Firma 2 (Centro) --}}
                        @if($showSig2)
                            <td style="width: 30%; text-align: center; vertical-align: top; padding: 0; background: transparent;">
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Firmado digitalmente por:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->signature2Nombre() ?: 'Supervisión Técnica y Sanitaria' }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Cargo:</strong> {{ $pdfConfig->signature2Cargo() ?: 'Médico Veterinario / Control Técnico' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI / Colegiatura:</strong> {{ $pdfConfig->signature2Documento() ?: 'DNI / CMVP Colegiatura' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Motivo:</strong> Certificación Técnica y Sanitaria Oficial</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Validación Técnica: Conforme</div>
                                </div>
                            </td>
                        @endif

                        <td style="width: 5%;"></td>

                        {{-- Firma 3 (Derecha) --}}
                        @if($hasExternal)
                            <td style="width: 30%; text-align: right; vertical-align: top; padding: 0; background: transparent;">
                                <div style="display: inline-block; text-align: left; font-size: {{ $sigBodyFont }}; color: #000000; line-height: 1.05; font-family: sans-serif; max-width: {{ $sigMaxWidth }};">
                                    <div style="margin-bottom: 0.2pt; line-height: 1.02;">
                                        <span style="font-size: {{ $sigLabelFont }}; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1px; color: {{ $accentDark }}; display: block; line-height: 1.02;">Evaluación Externa / Dictamen:</span>
                                        <div style="font-size: {{ $sigTitleFont }}; font-weight: bold; color: #000000; text-transform: uppercase; margin-top: 0; line-height: 1.04;">{{ $pdfConfig->externalStampEntidad() ?: 'Entidad Financiera / Bancaria' }}</div>
                                    </div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Evaluador:</strong> {{ $pdfConfig->externalStampNombre() ?: 'Evaluador Asignado' }} ({{ $pdfConfig->externalStampCargo() ?: 'Analista de Crédito' }})</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">DNI:</strong> {{ $pdfConfig->externalStampDocumento() ?: 'DNI del Evaluador' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">N° Expediente:</strong> {{ $pdfConfig->externalStampExpediente() ?: 'EXP-2026-CRÉD-0041' }}</div>
                                    <div style="color: #1e293b; margin-top: 0; line-height: 1.04;"><strong style="color: #000000;">Fecha y hora:</strong> {{ $datePeru }} (Hora oficial de Perú)</div>
                                    <div style="color: {{ $accentDark }}; font-weight: bold; margin-top: 0.2pt; line-height: 1.04;">Dictamen: {{ $pdfConfig->externalStampEstado() ?: 'CONFORME / APROBADO' }}</div>
                                </div>
                            </td>
                        @endif
                    @endif
                </tr>
            </table>

        @elseif($sigType === 'clasica')
            {{-- MODELO TRADICIONAL DE FIRMAS MANUSCRITAS --}}
            <table class="signatures-block" style="width: 100%; margin-top: 18pt; page-break-inside: avoid; border-collapse: collapse;">
                <tr>
                    @if($showSig1)
                        <td style="width: {{ $hasExternal ? '30%' : ($showSig2 ? '40%' : '50%') }}; vertical-align: top; padding: 0 6pt;">
                            <div style="height: 30pt;"></div>
                            <div style="border-top: 1.2px solid #334155; padding-top: 3pt; text-align: center;">
                                <strong style="font-size: 6.8pt; font-weight: bold; color: #0f172a; display: block;">{{ $pdfConfig->signature1Cargo() }}</strong>
                                <span style="font-size: 5.8pt; color: #64748b; display: block; margin-top: 1pt;">{{ $pdfConfig->signature1Nombre() }}</span>
                                @if($pdfConfig->signature1Documento())
                                    <span style="font-size: 5pt; color: #94a3b8; display: block;">{{ $pdfConfig->signature1Documento() }}</span>
                                @endif
                            </div>
                        </td>
                    @endif

                    @if($showSig2)
                        <td style="width: {{ $hasExternal ? '30%' : ($showSig1 ? '40%' : '50%') }}; vertical-align: top; padding: 0 6pt;">
                            <div style="height: 30pt;"></div>
                            <div style="border-top: 1.2px solid #334155; padding-top: 3pt; text-align: center;">
                                <strong style="font-size: 6.8pt; font-weight: bold; color: #0f172a; display: block;">{{ $pdfConfig->signature2Cargo() }}</strong>
                                <span style="font-size: 5.8pt; color: #64748b; display: block; margin-top: 1pt;">{{ $pdfConfig->signature2Nombre() }}</span>
                                @if($pdfConfig->signature2Documento())
                                    <span style="font-size: 5pt; color: #94a3b8; display: block;">{{ $pdfConfig->signature2Documento() }}</span>
                                @endif
                            </div>
                        </td>
                    @endif

                    @if($hasExternal)
                        <td style="width: {{ ($showSig1 && $showSig2) ? '40%' : '50%' }}; vertical-align: top; padding: 0 6pt;">
                            <div style="border: 1.2px solid #0284c7; border-radius: 3pt; background: #f0f9ff; padding: 3pt 5pt; text-align: center;">
                                <strong style="font-size: 6pt; font-weight: bold; color: #0369a1; display: block; text-transform: uppercase;">{{ $pdfConfig->externalStampEntidad() }}</strong>
                                <span style="font-size: 5.5pt; color: #0284c7; font-weight: bold; display: block;">{{ $pdfConfig->externalStampCargo() }}</span>
                                <span style="font-size: 4.8pt; color: #475569; display: block;">{{ $pdfConfig->externalStampNombre() }} ({{ $pdfConfig->externalStampDocumento() }})</span>
                                <span style="font-size: 4.8pt; color: #059669; font-weight: bold; display: block; margin-top: 1pt;">Dictamen: {{ $pdfConfig->externalStampEstado() }}</span>
                            </div>
                        </td>
                    @elseif($pdfConfig->showVerificationSeal())
                        <td style="width: 20%; text-align: center; vertical-align: middle;">
                            <div style="display: inline-block; border: 1.2px solid {{ $accent }}; border-radius: 3pt; padding: 2.5pt 5pt; background: {{ $accentSoft }}; text-align: center;">
                                <span style="font-size: 5.5pt; font-weight: bold; color: {{ $accent }}; text-transform: uppercase; display: block; letter-spacing: 0.3px;">AUTENTICIDAD</span>
                                <span style="font-size: 4.8pt; color: #475569; display: block; margin-top: 0.5pt;">{{ $fundoNombre }}</span>
                                <span style="font-size: 4.2pt; color: #94a3b8; font-family: monospace; display: block;">DOC-VERIFICADO</span>
                            </div>
                        </td>
                    @endif
                </tr>
            </table>

        @else
            {{-- MODELO DOBLE (AMBAS: LÍNEAS + SELLO DIGITAL) --}}
            <table class="signatures-block" style="width: 100%; margin-top: 14pt; page-break-inside: avoid; border-collapse: collapse;">
                <tr>
                    @if($showSig1)
                        <td style="width: {{ $showSig2 ? '36%' : '60%' }}; vertical-align: top; padding: 0 5pt;">
                            <div style="height: 26pt;"></div>
                            <div style="border-top: 1.2px solid #334155; padding-top: 2.5pt; text-align: center;">
                                <strong style="font-size: 6.5pt; color: #0f172a; display: block;">{{ $pdfConfig->signature1Cargo() }}</strong>
                                <span style="font-size: 5.5pt; color: #64748b; display: block;">{{ $pdfConfig->signature1Nombre() }}</span>
                            </div>
                        </td>
                    @endif

                    <td style="width: 28%; text-align: center; vertical-align: middle; padding: 0 3pt;">
                        <div style="border: 1px solid {{ $accent }}; border-radius: 3pt; background: #ffffff; padding: 2.5pt 3.5pt; text-align: left;">
                            <span style="font-size: 4.5pt; font-weight: bold; color: {{ $accent }}; text-transform: uppercase; display: block; text-align: center; border-bottom: 1px solid {{ $accentBorder }}; padding-bottom: 1pt; margin-bottom: 1.5pt;">
                                Sello Digital Software
                            </span>
                            <span style="font-size: 4.3pt; color: #334155; display: block;"><strong>Emisión:</strong> {{ $datePeru }}</span>
                            <span style="font-size: 4.3pt; color: #475569; display: block;"><strong>Sistema:</strong> {{ $software }}</span>
                            <span style="font-size: 4.3pt; color: #059669; font-weight: bold; display: block;">Certificado y Conforme</span>
                        </div>
                    </td>

                    @if($showSig2)
                        <td style="width: {{ $showSig1 ? '36%' : '60%' }}; vertical-align: top; padding: 0 5pt;">
                            <div style="height: 26pt;"></div>
                            <div style="border-top: 1.2px solid #334155; padding-top: 2.5pt; text-align: center;">
                                <strong style="font-size: 6.5pt; color: #0f172a; display: block;">{{ $pdfConfig->signature2Cargo() }}</strong>
                                <span style="font-size: 5.5pt; color: #64748b; display: block;">{{ $pdfConfig->signature2Nombre() }}</span>
                            </div>
                        </td>
                    @endif
                </tr>
            </table>
        @endif
    @endif
@endif
