<?php

namespace App\Livewire\Ajustes\Traits;

use App\Models\Fundo;
use App\Services\AuditLogger;
use App\Services\Pdf\PdfDigitalSignerService;
use App\Services\Pdf\PdfSignatureVerifierService;
use App\Support\PdfReportConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

trait HasPdfReportSettings
{
    public array $pdfSettings = [];

    public bool $showSamplePdfModal = false;

    // Sub-pestañas: 'diseno' | 'certificados' | 'validador'
    public string $activePdfSubTab = 'diseno';

    // Estado del Certificado Digital X.509
    public ?array $fundoCertDetails = null;

    // Estado del Validador de Firmas PDF
    public $pdfFileToVerify = null;
    public ?array $verificationResult = null;
    public bool $isVerifyingPdf = false;
    public ?string $verificationErrorMessage = null;

    public function loadPdfSettings(): void
    {
        $fundoId = $this->fundoId();
        $config = app(PdfReportConfig::class)->forFundo($fundoId);
        $settings = $config->settings();

        $this->pdfSettings = [
            'mostrar_marca_agua' => filter_var($settings['mostrar_marca_agua'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'orientacion_marca_agua' => (string) ($settings['orientacion_marca_agua'] ?? 'diagonal'),
            'texto_marca_agua' => (string) ($settings['texto_marca_agua'] ?? ''),
            'opacidad_marca_agua' => (string) ($settings['opacidad_marca_agua'] ?? '0.04'),
            'color_marca_agua' => (string) ($settings['color_marca_agua'] ?? '#064e3b'),
            'mostrar_logo' => filter_var($settings['mostrar_logo'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'estilo_color' => (string) ($settings['estilo_color'] ?? 'emerald'),
            'color_acento' => (string) ($settings['color_acento'] ?? '#047857'),
            'modo_color_tablas' => (string) ($settings['modo_color_tablas'] ?? 'multi'),
            'paleta_tablas' => is_array($settings['paleta_tablas'] ?? null)
                ? $settings['paleta_tablas']
                : explode(',', (string) ($settings['paleta_tablas'] ?? 'emerald,indigo,amber,sky,rose,slate,teal,violet')),
            'estilo_esquinas' => (string) ($settings['estilo_esquinas'] ?? 'redondeado'),
            'radio_esquinas' => (int) ($settings['radio_esquinas'] ?? 5),
            'mostrar_pie' => filter_var($settings['mostrar_pie'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'texto_pie' => (string) ($settings['texto_pie'] ?? ''),
            'mostrar_num_pagina' => filter_var($settings['mostrar_num_pagina'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_fecha_hora' => filter_var($settings['mostrar_fecha_hora'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_generado_por' => filter_var($settings['mostrar_generado_por'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_firmas' => filter_var($settings['mostrar_firmas'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_firma_1' => filter_var($settings['mostrar_firma_1'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_firma_2' => filter_var($settings['mostrar_firma_2'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'tipo_firma' => (string) ($settings['tipo_firma'] ?? 'digital'),
            'escala_firmas' => (int) ($settings['escala_firmas'] ?? 100),
            'firma_1_cargo' => (string) ($settings['firma_1_cargo'] ?? 'Responsable de Fundo / Administración'),
            'firma_1_nombre' => (string) ($settings['firma_1_nombre'] ?? 'Firma Digital Autorizada'),
            'firma_1_documento' => (string) ($settings['firma_1_documento'] ?? 'DNI / RUC Autorizado'),
            'firma_2_cargo' => (string) ($settings['firma_2_cargo'] ?? 'Control Técnico / Médico Veterinario'),
            'firma_2_nombre' => (string) ($settings['firma_2_nombre'] ?? 'Supervisión y Conformidad'),
            'firma_2_documento' => (string) ($settings['firma_2_documento'] ?? 'Reg. Profesional / Colegiatura'),
            'firma_motivo' => (string) ($settings['firma_motivo'] ?? 'Conformidad y Validación Técnica Oficial'),
            'firma_software' => (string) ($settings['firma_software'] ?? 'AGROFUNDO ERP v2.6 · Software Ganadero'),
            'firma_mostrar_hash' => filter_var($settings['firma_mostrar_hash'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_sello_autenticidad' => filter_var($settings['mostrar_sello_autenticidad'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mostrar_sello_externo' => filter_var($settings['mostrar_sello_externo'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'sello_externo_entidad' => (string) ($settings['sello_externo_entidad'] ?? 'Entidad Financiera / Bancaria'),
            'sello_externo_cargo' => (string) ($settings['sello_externo_cargo'] ?? 'Analista de Crédito / Auditor Técnico'),
            'sello_externo_nombre' => (string) ($settings['sello_externo_nombre'] ?? 'Evaluador / Analista Asignado'),
            'sello_externo_documento' => (string) ($settings['sello_externo_documento'] ?? 'Matrícula / Registro N°'),
            'sello_externo_expediente' => (string) ($settings['sello_externo_expediente'] ?? 'Exp. Evaluación / Crédito'),
            'sello_externo_estado' => (string) ($settings['sello_externo_estado'] ?? 'CONFORME / APROBADO'),
            'sello_externo_motivo' => (string) ($settings['sello_externo_motivo'] ?? 'Verificación y Aprobación Financiera / Sectorial'),
        ];

        $this->loadFundoCertificate();
    }

    public function setCornerRadius(int $radius): void
    {
        $radius = max(0, min($radius, 16));
        $this->pdfSettings['radio_esquinas'] = $radius;
        $this->pdfSettings['estilo_esquinas'] = $radius === 0 ? 'clasico' : 'redondeado';
    }

    public function setSignatureScale(int $scale): void
    {
        $this->pdfSettings['escala_firmas'] = max(35, min($scale, 170));
    }

    public function setPdfSubTab(string $subTab): void
    {
        if (in_array($subTab, ['diseno', 'certificados', 'validador'], true)) {
            $this->activePdfSubTab = $subTab;
            if ($subTab === 'certificados') {
                $this->loadFundoCertificate();
            }
        }
    }

    public function loadFundoCertificate(): void
    {
        $fundoId = $this->fundoId();
        $signerService = app(PdfDigitalSignerService::class);

        if (! $signerService->hasCertificate($fundoId)) {
            try {
                $signerService->generateCertificate($fundoId);
            } catch (Throwable) {
                // non-blocking
            }
        }

        $this->fundoCertDetails = $signerService->getCertificateDetails($fundoId);
    }

    public function generateFundoCertificate(): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $fundoId = $this->fundoId();
        $fundo = Fundo::find($fundoId);
        $signerService = app(PdfDigitalSignerService::class);

        try {
            $this->fundoCertDetails = $signerService->generateCertificate($fundoId, $fundo?->nombre);

            app(AuditLogger::class)->record(
                'generar_certificado_digital_pdf',
                'ajustes',
                "Certificado Digital X.509 de Firma Digital generado / renovado para el fundo {$fundo?->nombre}."
            );

            $this->dispatchSuccess('Certificado Generado', 'El nuevo certificado digital X.509 para firma electrónica ha sido emitido con éxito.');
        } catch (Throwable $e) {
            $this->dispatchWarning('Error al generar certificado', $e->getMessage());
        }
    }

    public function downloadFundoCertificate(): ?StreamedResponse
    {
        $fundoId = $this->fundoId();
        $signerService = app(PdfDigitalSignerService::class);
        $certPath = $signerService->getCertificatePath($fundoId);

        if (! File::exists($certPath)) {
            $this->dispatchWarning('Certificado no encontrado', 'Genera un certificado antes de descargarlo.');
            return null;
        }

        $certContent = File::get($certPath);
        $filename = "certificado_agrofundo_fundo_{$fundoId}.crt";

        return response()->streamDownload(
            fn () => print($certContent),
            $filename,
            ['Content-Type' => 'application/x-x509-ca-cert']
        );
    }

    public function updatedPdfFileToVerify(): void
    {
        $this->verifyUploadedPdf();
    }

    public function verifyUploadedPdf(): void
    {
        if (! $this->pdfFileToVerify) {
            $this->verificationResult = null;
            return;
        }

        $this->isVerifyingPdf = true;
        $this->verificationErrorMessage = null;

        try {
            $verifier = app(PdfSignatureVerifierService::class);
            $binary = '';
            if (is_object($this->pdfFileToVerify) && method_exists($this->pdfFileToVerify, 'getRealPath')) {
                $binary = File::get($this->pdfFileToVerify->getRealPath());
            } elseif (is_string($this->pdfFileToVerify) && File::exists($this->pdfFileToVerify)) {
                $binary = File::get($this->pdfFileToVerify);
            } elseif (is_string($this->pdfFileToVerify)) {
                $binary = $this->pdfFileToVerify;
            }

            $this->verificationResult = $verifier->verify($binary);
        } catch (Throwable $e) {
            $this->verificationErrorMessage = 'Error al procesar el archivo PDF: ' . $e->getMessage();
            $this->verificationResult = null;
        } finally {
            $this->isVerifyingPdf = false;
        }
    }

    public function clearPdfVerification(): void
    {
        $this->pdfFileToVerify = null;
        $this->verificationResult = null;
        $this->verificationErrorMessage = null;
    }

    public function savePdfSettings(): void
    {
        $this->authorizePermission('ajustes', 'actualizar');

        $validated = $this->validate([
            'pdfSettings.mostrar_marca_agua' => ['boolean'],
            'pdfSettings.orientacion_marca_agua' => ['required', 'string', 'in:diagonal,horizontal,recto'],
            'pdfSettings.texto_marca_agua' => ['nullable', 'string', 'max:120'],
            'pdfSettings.opacidad_marca_agua' => ['required', 'string', 'in:0.02,0.04,0.07,0.10,0.15'],
            'pdfSettings.color_marca_agua' => ['nullable', 'string', 'max:20'],
            'pdfSettings.mostrar_logo' => ['boolean'],
            'pdfSettings.estilo_color' => ['required', 'string', 'in:emerald,sky,indigo,amber,rose,slate,teal,violet,cyan,lime,orange,fuchsia,stone,purple,olive,coffee,custom'],
            'pdfSettings.color_acento' => ['nullable', 'string', 'max:20'],
            'pdfSettings.modo_color_tablas' => ['required', 'string', 'in:multi,monocromatico'],
            'pdfSettings.paleta_tablas' => ['required', 'array', 'min:1'],
            'pdfSettings.paleta_tablas.*' => ['required', 'string', 'in:emerald,sky,indigo,amber,rose,slate,teal,violet,cyan,lime,orange,fuchsia,stone,purple,olive,coffee'],
            'pdfSettings.estilo_esquinas' => ['required', 'string', 'in:redondeado,clasico'],
            'pdfSettings.radio_esquinas' => ['nullable', 'integer', 'min:0', 'max:16'],
            'pdfSettings.mostrar_pie' => ['boolean'],
            'pdfSettings.texto_pie' => ['nullable', 'string', 'max:250'],
            'pdfSettings.mostrar_num_pagina' => ['boolean'],
            'pdfSettings.mostrar_fecha_hora' => ['boolean'],
            'pdfSettings.mostrar_generado_por' => ['boolean'],
            'pdfSettings.mostrar_firmas' => ['boolean'],
            'pdfSettings.mostrar_firma_1' => ['boolean'],
            'pdfSettings.mostrar_firma_2' => ['boolean'],
            'pdfSettings.tipo_firma' => ['required', 'string', 'in:digital,clasica,ambas'],
            'pdfSettings.escala_firmas' => ['nullable', 'integer', 'min:35', 'max:170'],
            'pdfSettings.firma_1_cargo' => ['nullable', 'string', 'max:100'],
            'pdfSettings.firma_1_nombre' => ['nullable', 'string', 'max:100'],
            'pdfSettings.firma_1_documento' => ['nullable', 'string', 'max:100'],
            'pdfSettings.firma_2_cargo' => ['nullable', 'string', 'max:100'],
            'pdfSettings.firma_2_nombre' => ['nullable', 'string', 'max:100'],
            'pdfSettings.firma_2_documento' => ['nullable', 'string', 'max:100'],
            'pdfSettings.firma_motivo' => ['nullable', 'string', 'max:150'],
            'pdfSettings.firma_software' => ['nullable', 'string', 'max:120'],
            'pdfSettings.firma_mostrar_hash' => ['boolean'],
            'pdfSettings.mostrar_sello_autenticidad' => ['boolean'],
            'pdfSettings.mostrar_sello_externo' => ['boolean'],
            'pdfSettings.sello_externo_entidad' => ['nullable', 'string', 'max:120'],
            'pdfSettings.sello_externo_cargo' => ['nullable', 'string', 'max:100'],
            'pdfSettings.sello_externo_nombre' => ['nullable', 'string', 'max:100'],
            'pdfSettings.sello_externo_documento' => ['nullable', 'string', 'max:100'],
            'pdfSettings.sello_externo_expediente' => ['nullable', 'string', 'max:100'],
            'pdfSettings.sello_externo_estado' => ['nullable', 'string', 'max:100'],
            'pdfSettings.sello_externo_motivo' => ['nullable', 'string', 'max:150'],
        ])['pdfSettings'];

        $fundoId = $this->fundoId();
        PdfReportConfig::saveConfig($fundoId, $validated, app('cache.store'));

        app(AuditLogger::class)->record(
            'actualizar_configuracion_pdf',
            'ajustes',
            'Configuración global de reportes PDF y marca de agua actualizada.'
        );

        $this->dispatchSuccess('Ajustes guardados', 'La configuración de reportes PDF, marca de agua y pie de página ha sido actualizada.');
    }

    public function resetPdfSettings(): void
    {
        $this->authorizePermission('ajustes', 'actualizar');

        $fundoId = $this->fundoId();
        PdfReportConfig::resetDefaults($fundoId, app('cache.store'));
        $this->loadPdfSettings();

        app(AuditLogger::class)->record(
            'restablecer_configuracion_pdf',
            'ajustes',
            'Configuración de reportes PDF restablecida a valores por defecto.'
        );

        $this->dispatchSuccess('Valores restablecidos', 'La configuración de reportes PDF volvió a los valores estándar recomendados.');
    }

    public function downloadSamplePdf(): ?StreamedResponse
    {
        $fundoId = $this->fundoId();
        $fundo = Fundo::find($fundoId);
        if (! $fundo) {
            $this->dispatchWarning('Fundo no encontrado', 'No se pudo generar la muestra.');

            return null;
        }

        $generatedBy = auth()->user()?->name ?? 'Administrador';
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->pluck('users.name')
            ->implode(', ') ?: $generatedBy;

        $pdfConfig = app(\App\Support\PdfReportConfig::class);
        $pdfConfig->setOverrides($this->pdfSettings);

        // Create sample rows
        $animales = collect([
            (object) [
                'arete' => 'BOV26-001',
                'nombre' => 'Esperanza',
                'especie' => (object) ['nombre' => 'Bovino'],
                'raza' => (object) ['nombre' => 'Holstein'],
                'genero' => 'hembra',
                'edad_texto' => '3 años 2 meses',
                'peso' => 540.00,
                'estado_reproductivo_label' => 'Preñada',
                'tipo_alta_label' => 'Nacimiento',
                'precio_compra' => null,
                'activo' => true,
                'fecha_alta' => now()->subMonths(18),
            ],
            (object) [
                'arete' => 'BOV26-002',
                'nombre' => 'Coronel',
                'especie' => (object) ['nombre' => 'Bovino'],
                'raza' => (object) ['nombre' => 'Brown Swiss'],
                'genero' => 'macho',
                'edad_texto' => '2 años 8 meses',
                'peso' => 610.50,
                'estado_reproductivo_label' => 'Reproductor',
                'tipo_alta_label' => 'Compra',
                'precio_compra' => 4500.00,
                'activo' => true,
                'fecha_alta' => now()->subMonths(12),
            ],
            (object) [
                'arete' => 'BOV26-003',
                'nombre' => 'Luna Bella',
                'especie' => (object) ['nombre' => 'Bovino'],
                'raza' => (object) ['nombre' => 'Jersey'],
                'genero' => 'hembra',
                'edad_texto' => '4 años 1 mes',
                'peso' => 460.00,
                'estado_reproductivo_label' => 'En Lactancia',
                'tipo_alta_label' => 'Nacimiento',
                'precio_compra' => null,
                'activo' => true,
                'fecha_alta' => now()->subMonths(24),
            ],
        ]);

        $selectedColumns = ['arete', 'nombre', 'especie', 'raza', 'genero', 'edad', 'peso', 'estado_reproductivo', 'activo', 'fecha_alta'];
        $reportSummary = '3 registros de muestra. Vista preliminar de estilos PDF.';
        $filterSummary = 'Muestra de prueba con configuración actual del fundo.';

        $pdf = Pdf::loadView('pdf.animales', compact(
            'animales',
            'selectedColumns',
            'fundo',
            'generatedBy',
            'generatedAt',
            'administrators',
            'reportSummary',
            'filterSummary'
        ))->setPaper('a4', 'landscape');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isFontSubsettingEnabled' => true,
            'isPhpEnabled' => true,
            'defaultMediaType' => 'print',
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'muestra_reporte_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
