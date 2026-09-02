<?php

namespace App\Traits;

use App\Services\AuditLogger;
use App\Services\Pdf\PdfDigitalSignerService;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasPdfPreviewModal
{
    public bool $showExportModal = false;
    public string $exportStep = 'options'; // 'options' | 'preview'
    /** @deprecated Use pdfPreviewToken. Kept null always now — token-based architecture. */
    public ?string $pdfPreviewData = null;
    public ?string $pdfPreviewToken = null;
    public string $pdfPreviewFilename = '';
    public string $pdfPreviewTitle = '';
    public ?int $pdfPreviewRowCount = null;
    public ?int $pdfPreviewPageCount = null;
    public string $pdfScale = '85';
    public bool $pdfIncludeSignatures = true;
    public string $pdfSignatureScale = '100';
    public ?string $pdfTableColorMode = null; // 'multi' | 'mono'
    public ?string $pdfTableRadius = null;    // '0', '3', '5', '8'
    public ?string $pdfTablePreset = null;    // 'emerald', 'indigo', 'amber', 'sky', 'rose', 'slate', 'teal', 'violet'

    // Estado para modal de firma digital criptográfica con contraseña
    public bool $showSignModal = false;
    public string $signPassword = '';
    public ?string $signPasswordError = null;
    public bool $signWithProtection = true;

    public function loadPdfConfigDefaults(): void
    {
        try {
            $pdfConfig = app(\App\Support\PdfReportConfig::class);
            $this->pdfIncludeSignatures = (bool) $pdfConfig->mostrarFirmas();
            $this->pdfSignatureScale = (string) ($pdfConfig->escalaFirmas() ?: 100);
            $this->pdfTableColorMode = $pdfConfig->modoColorTablas(); // 'multi' or 'mono'
            $this->pdfTablePreset = $pdfConfig->colorPreset();
            $this->pdfTableRadius = (string) $pdfConfig->tableBorderRadiusPx();
        } catch (\Throwable) {
            // Keep defaults
        }
    }

    public function openExportModal(): void
    {
        $this->loadPdfConfigDefaults();
        $this->applyPdfConfigOverrides();
        $this->exportStep = 'options';
        if (property_exists($this, 'exportFormat')) {
            $this->exportFormat = 'pdf';
        }
        $this->pdfPreviewData = null;
        $this->pdfPreviewToken = null;
        $this->pdfPreviewPageCount = null;
        $this->showSignModal = false;
        $this->signPassword = '';
        $this->signPasswordError = null;
        $this->showExportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->showExportModal = false;
        $this->exportStep = 'options';
        $this->_clearPdfCache();
        $this->pdfPreviewData = null;
        $this->pdfPreviewToken = null;
        $this->pdfPreviewPageCount = null;
        $this->showSignModal = false;
        $this->signPassword = '';
        $this->signPasswordError = null;
        $this->resetErrorBag();
    }

    public function togglePdfSignatures(): void
    {
        $this->pdfIncludeSignatures = ! $this->pdfIncludeSignatures;
        $this->applyPdfConfigOverrides();
        if ($this->exportStep === 'preview') {
            $this->regeneratePdfPreview();
        }
    }

    public function setPdfScale(string $scale): void
    {
        if (in_array($scale, ['40', '45', '50', '55', '65', '75', '85', '100'], true)) {
            $this->pdfScale = $scale;
            if ($this->exportStep === 'preview') {
                $this->regeneratePdfPreview();
            }
        }
    }

    public function setPdfSignatureScale(string $scale): void
    {
        if (in_array($scale, ['40', '45', '60', '65', '80', '100', '120', '140', '155'], true) || (is_numeric($scale) && (int) $scale >= 35 && (int) $scale <= 170)) {
            $this->pdfSignatureScale = (string) $scale;
            $this->applyPdfConfigOverrides();
            if ($this->exportStep === 'preview') {
                $this->regeneratePdfPreview();
            }
        }
    }

    public function setPdfTableColorMode(string $mode): void
    {
        $normalized = in_array($mode, ['mono', 'monocromatico'], true) ? 'mono' : 'multi';
        $this->pdfTableColorMode = $normalized;
        $this->applyPdfConfigOverrides();
        if ($this->exportStep === 'preview') {
            $this->regeneratePdfPreview();
        }
    }

    public function setPdfTableRadius(string $radius): void
    {
        if (in_array($radius, ['0', '2', '3', '4', '5', '6', '8', '10'], true)) {
            $this->pdfTableRadius = $radius;
            $this->applyPdfConfigOverrides();
            if ($this->exportStep === 'preview') {
                $this->regeneratePdfPreview();
            }
        }
    }

    public function setPdfTablePreset(string $preset): void
    {
        if (array_key_exists($preset, \App\Support\PdfReportConfig::COLOR_PRESETS)) {
            $this->pdfTableColorMode = 'mono';
            $this->pdfTablePreset = $preset;
            $this->applyPdfConfigOverrides();
            if ($this->exportStep === 'preview') {
                $this->regeneratePdfPreview();
            }
        }
    }

    public function applyPdfConfigOverrides(): void
    {
        try {
            $pdfConfig = app(\App\Support\PdfReportConfig::class);
            $overrides = [
                'mostrar_firmas' => $this->pdfIncludeSignatures ? 'true' : 'false',
                'escala_firmas' => (int) ($this->pdfSignatureScale ?: 100),
            ];
            if ($this->pdfTableColorMode !== null) {
                $overrides['modo_color_tablas'] = $this->pdfTableColorMode;
            }
            if ($this->pdfTableRadius !== null) {
                $overrides['radio_esquinas'] = (int) $this->pdfTableRadius;
                $overrides['estilo_esquinas'] = ((int) $this->pdfTableRadius === 0) ? 'recto' : 'redondeado';
            }
            if ($this->pdfTablePreset !== null) {
                $overrides['estilo_color'] = $this->pdfTablePreset;
                if (isset(\App\Support\PdfReportConfig::COLOR_PRESETS[$this->pdfTablePreset])) {
                    $overrides['color_acento'] = \App\Support\PdfReportConfig::COLOR_PRESETS[$this->pdfTablePreset]['primary'];
                }
            }
            $pdfConfig->setOverrides($overrides);
        } catch (\Throwable) {
            // Ignore if config not resolvable
        }
    }

    public function regeneratePdfPreview(): void
    {
        $this->applyPdfConfigOverrides();

        if (method_exists($this, 'exportDetailedReport') && property_exists($this, 'activeExportType') && $this->activeExportType === 'detailed') {
            $this->exportDetailedReport();
        } elseif (method_exists($this, 'downloadAnimalReport')) {
            $this->downloadAnimalReport();
        } elseif (method_exists($this, 'exportarDetallado') && property_exists($this, 'activeExportType') && $this->activeExportType === 'detailed') {
            $this->exportarDetallado();
        } elseif (method_exists($this, 'downloadMedicamentosReport')) {
            $this->downloadMedicamentosReport();
        } elseif (method_exists($this, 'downloadInsumosReport')) {
            $this->downloadInsumosReport();
        } elseif (method_exists($this, 'downloadMonitoreoReport')) {
            $this->downloadMonitoreoReport();
        } elseif (method_exists($this, 'downloadQuesoReport')) {
            $this->downloadQuesoReport();
        } elseif (method_exists($this, 'downloadReport')) {
            $this->downloadReport();
        } elseif (method_exists($this, 'exportar')) {
            $this->exportar();
        }
    }

    public function backToExportOptions(): void
    {
        $this->exportStep = 'options';
        $this->_clearPdfCache();
        $this->pdfPreviewToken = null;
        $this->pdfPreviewData = null;
        $this->showSignModal = false;
        if (property_exists($this, 'activeExportType') && $this->activeExportType === 'detailed') {
            if (property_exists($this, 'showDetailedReportModal')) {
                $this->showDetailedReportModal = true;
                $this->showExportModal = false;
            }
        }
    }

    /**
     * Store the PDF binary in cache and emit a JS event so Alpine loads the iframe URL.
     * This replaces the old base64-in-Livewire-property approach.
     */
    public function setPdfPreview(DomPdfWrapper $pdf, string $filename, string $title = 'Reporte PDF', ?int $rowCount = null): void
    {
        try {
            $this->applyPdfConfigOverrides();

            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isFontSubsettingEnabled' => false,
                'isPhpEnabled' => true,
                'defaultMediaType' => 'print',
            ]);

            $binary = $pdf->output();

            try {
                $this->pdfPreviewPageCount = $pdf->getDomPDF()->getCanvas()->get_page_count();
            } catch (\Throwable) {
                $this->pdfPreviewPageCount = null;
            }

            // Store binary in cache with 10-minute TTL (old tokens expire naturally without race conditions)
            $token = Str::uuid()->toString();
            $cacheKey = 'pdf_preview_' . $token;
            Cache::put($cacheKey, [
                'binary'   => $binary,
                'filename' => $filename,
            ], now()->addMinutes(10));

            $this->pdfPreviewToken = $token;
            $this->pdfPreviewData = null; // always null now
            $this->pdfPreviewFilename = $filename;
            $this->pdfPreviewTitle = $title;
            $this->pdfPreviewRowCount = $rowCount;
            $this->exportStep = 'preview';
            $this->showSignModal = false;
            $this->signPassword = '';
            $this->signPasswordError = null;
            $this->showExportModal = true;

            // Emit JS event so Alpine updates the iframe src immediately
            $this->dispatch('pdf-preview-ready', url: route('pdf.preview', $token));

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error generando vista previa PDF: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->addError('exportFormat', 'Ocurrió un error al generar la vista previa: ' . $e->getMessage());
            $this->exportStep = 'options';
            $this->showExportModal = true;
        }
    }

    public function downloadCurrentPdf()
    {
        if (! $this->pdfPreviewToken) {
            return null;
        }

        $entry = Cache::get('pdf_preview_' . $this->pdfPreviewToken);
        if (! $entry || ! isset($entry['binary'])) {
            $this->addError('exportFormat', 'La vista previa ha expirado. Genera el PDF nuevamente.');
            return null;
        }

        $binary = $entry['binary'];
        $filename = $this->pdfPreviewFilename ?: ($entry['filename'] ?? 'reporte_' . now()->format('Ymd_His') . '.pdf');

        $this->closeExportModal();

        return response()->streamDownload(
            fn () => print($binary),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function openSignModal(): void
    {
        $this->signPassword = '';
        $this->signPasswordError = null;
        $this->showSignModal = true;
    }

    public function closeSignModal(): void
    {
        $this->showSignModal = false;
        $this->signPassword = '';
        $this->signPasswordError = null;
    }

    public function signAndDownloadCurrentPdf(): ?StreamedResponse
    {
        if (! $this->pdfPreviewToken) {
            return null;
        }

        $user = auth()->user();
        if (! $user) {
            $this->signPasswordError = 'Usuario no autenticado.';
            return null;
        }

        if (empty($this->signPassword)) {
            $this->signPasswordError = 'Debes ingresar tu contraseña para autorizar la firma digital.';
            return null;
        }

        if (! Hash::check($this->signPassword, $user->password)) {
            $this->signPasswordError = 'Contraseña incorrecta. Se denegó la firma por seguridad.';
            return null;
        }

        $entry = Cache::get('pdf_preview_' . $this->pdfPreviewToken);
        if (! $entry || ! isset($entry['binary'])) {
            $this->signPasswordError = 'La vista previa ha expirado. Genera el PDF nuevamente.';
            return null;
        }

        $fundoId = (int) (session('fundo_id') ?: ($user->fundo_id ?: 1));
        $originalBinary = $entry['binary'];

        try {
            $signerService = app(PdfDigitalSignerService::class);
            $signedBinary = $signerService->signPdf(
                $originalBinary,
                $fundoId,
                $user,
                $this->signPassword,
                [
                    'reason' => 'Autorización y Certificación Oficial Ganadera (Firma Perú)',
                    'software' => 'AGROFUNDO ERP v2.6 · Motor Criptográfico',
                ]
            );

            // Audit record
            try {
                app(AuditLogger::class)->record(
                    'firmar_digitalmente_pdf',
                    'pdf',
                    "PDF {$this->pdfPreviewFilename} firmado digitalmente con certificado criptográfico X.509 y DocMDP P=1 por {$user->name}."
                );
            } catch (\Throwable) {
                // non-blocking
            }

            $rawName = pathinfo($this->pdfPreviewFilename, PATHINFO_FILENAME);
            $signedFilename = ($rawName ?: 'reporte') . '_FIRMADO_DIGITAL_' . now()->format('Ymd_His') . '.pdf';

            $this->closeExportModal();

            return response()->streamDownload(
                fn () => print($signedBinary),
                $signedFilename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "attachment; filename=\"{$signedFilename}\"",
                ]
            );
        } catch (\Throwable $e) {
            $this->signPasswordError = 'Error durante la firma criptográfica: ' . $e->getMessage();
            return null;
        }
    }

    /** Remove PDF binary from cache when no longer needed. */
    private function _clearPdfCache(): void
    {
        if ($this->pdfPreviewToken) {
            Cache::forget('pdf_preview_' . $this->pdfPreviewToken);
        }
    }
}
