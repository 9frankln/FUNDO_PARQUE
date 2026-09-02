<?php

namespace Tests\Feature;

use App\Models\Fundo;
use App\Models\User;
use App\Support\PdfReportConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PdfDynamicPaginationAndHarmonizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Fundo $fundo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->fundo = Fundo::create([
            'nombre' => 'Fundo Los Sauces',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lurin',
            'activo' => true,
        ]);

        $this->admin->fundos()->attach($this->fundo->id, ['es_administrador' => true]);
        session(['fundo_id' => $this->fundo->id]);
    }

    public function test_pdf_footer_generates_distinct_dynamic_page_numbers_per_page(): void
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            @page { size: A4 landscape; margin: 10mm; }
            .page-break { page-break-after: always; }
        </style></head><body>
            <div class="page-break"><h1>Contenido Cara 1</h1></div>
            <div class="page-break"><h1>Contenido Cara 2</h1></div>
            <div class="page-break"><h1>Contenido Cara 3</h1></div>
            <div><h1>Contenido Cara 4</h1></div>
            ' . view('pdf.partials.footer', [
                'fundo' => $this->fundo,
                'branding' => (object)['name' => 'AgroFundo'],
                'generatedAt' => now(),
                'generatedBy' => 'Admin Test',
                'pdfConfig' => app(PdfReportConfig::class),
            ])->render() . '
        </body></html>';

        $dompdf = Pdf::loadHTML($html);
        $dompdf->setOptions(['isPhpEnabled' => true]);
        $output = $dompdf->output();

        $this->assertNotEmpty($output);

        // Extract and decompress stream objects from generated PDF
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $output, $matches);
        $decompressedStreams = [];
        foreach ($matches[1] as $stream) {
            $decomp = @gzuncompress($stream);
            if ($decomp) {
                $decompressedStreams[] = $decomp;
            }
        }

        $allStreamsCombined = implode("\n", $decompressedStreams);

        // Assert that distinct page numbers are rendered on consecutive pages
        $this->assertStringContainsString('1 de 4', $allStreamsCombined);
        $this->assertStringContainsString('2 de 4', $allStreamsCombined);
        $this->assertStringContainsString('3 de 4', $allStreamsCombined);
        $this->assertStringContainsString('4 de 4', $allStreamsCombined);
    }

    public function test_animal_export_generates_pdf_preview_with_pagination(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Animal\Index::class)
            ->set('exportFormat', 'pdf')
            ->set('pdfScale', '50')
            ->set('pdfIncludeSignatures', true)
            ->call('exportar')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null)
            ->call('setPdfScale', '50')
            ->assertSet('pdfScale', '50')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_leche_export_generates_pdf_preview_with_scale_and_signatures(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Leche\Index::class)
            ->set('exportFormat', 'pdf')
            ->set('pdfScale', '75')
            ->set('pdfIncludeSignatures', true)
            ->call('exportar')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_engorde_export_generates_pdf_preview_with_scale_and_signatures(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Engorde\Index::class)
            ->set('exportFormat', 'pdf')
            ->set('pdfScale', '85')
            ->set('pdfIncludeSignatures', true)
            ->call('exportar')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_engorde_summary_report_generates_pdf_preview(): void
    {
        $lote = \App\Models\LoteEngorde::create([
            'fundo_id' => $this->fundo->id,
            'codigo' => 'LOT-2026-SUM',
            'nombre' => 'Lote Resumen',
            'fecha_inicio' => now()->subMonth(),
            'estado' => 'activo',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Engorde\Index::class)
            ->call('openExportModal')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'options')
            ->call('exportar')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null)
            ->call('setPdfScale', '40')
            ->assertSet('pdfScale', '40')
            ->assertNotSet('pdfPreviewToken', null)
            ->call('togglePdfSignatures')
            ->assertSet('pdfIncludeSignatures', false)
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_engorde_detailed_report_generates_pdf_preview(): void
    {
        $lote1 = \App\Models\LoteEngorde::create([
            'fundo_id' => $this->fundo->id,
            'codigo' => 'LOT-2026-01',
            'nombre' => 'Lote Alfa',
            'fecha_inicio' => now()->subMonths(2),
            'estado' => 'activo',
        ]);

        $lote2 = \App\Models\LoteEngorde::create([
            'fundo_id' => $this->fundo->id,
            'codigo' => 'LOT-2026-02',
            'nombre' => 'Lote Beta',
            'fecha_inicio' => now()->subMonth(),
            'estado' => 'activo',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Engorde\Index::class)
            ->call('openDetailedReportModal')
            ->assertSet('showDetailedReportModal', true)
            ->call('exportDetailedReport', 'filtered', [], \App\Support\EngordeReport::DEFAULT_COLUMNS)
            ->assertSet('showExportModal', true)
            ->assertSet('showDetailedReportModal', false)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null)
            ->call('setPdfScale', '40')
            ->assertSet('pdfScale', '40')
            ->assertNotSet('pdfPreviewToken', null)
            ->call('togglePdfSignatures')
            ->assertSet('pdfIncludeSignatures', false)
            ->assertNotSet('pdfPreviewToken', null)
            ->call('backToExportOptions')
            ->assertSet('showDetailedReportModal', true)
            ->assertSet('showExportModal', false);
    }

    public function test_animal_show_generates_pdf_preview(): void
    {
        $especie = \App\Models\Especie::create(['nombre' => 'Bovino', 'activo' => true]);
        $raza = \App\Models\Raza::create(['especie_id' => $especie->id, 'nombre' => 'Holstein', 'activo' => true]);

        $animal = \App\Models\Animal::create([
            'fundo_id' => $this->fundo->id,
            'especie_id' => $especie->id,
            'raza_id' => $raza->id,
            'arete' => 'PE-1001',
            'nombre' => 'Estrella',
            'sexo' => 'hembra',
            'fecha_nacimiento' => now()->subYears(3),
            'fecha_alta' => now()->subYears(2),
            'estado' => 'activo',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Animal\Show::class, ['id' => $animal->id])
            ->set('pdfScale', '85')
            ->set('pdfIncludeSignatures', true)
            ->call('downloadAnimalReport')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_finanzas_export_generates_pdf_preview(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Finanzas\Index::class)
            ->set('pdfScale', '85')
            ->set('pdfIncludeSignatures', true)
            ->call('downloadReport')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_medicamentos_export_generates_pdf_preview(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Medicamentos\Index::class)
            ->call('openMedicamentosPdfModal')
            ->set('pdfScale', '85')
            ->set('pdfIncludeSignatures', true)
            ->call('downloadMedicamentosReport')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_insumos_export_generates_pdf_preview(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Insumos\Index::class)
            ->call('openInsumosPdfModal')
            ->set('pdfScale', '85')
            ->set('pdfIncludeSignatures', true)
            ->call('downloadInsumosReport')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_monitoreo_export_generates_pdf_preview(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Monitoreo\Index::class)
            ->call('openMonitoreoPdfModal')
            ->set('pdfScale', '85')
            ->set('pdfIncludeSignatures', true)
            ->call('downloadMonitoreoReport')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_queso_export_generates_pdf_preview(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Queso\Index::class)
            ->call('openQuesoReportModal')
            ->set('pdfScale', '85')
            ->set('pdfIncludeSignatures', true)
            ->call('downloadQuesoReport')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->assertNotSet('pdfPreviewToken', null);
    }

    public function test_pdf_preview_modal_supports_dynamic_table_radius_and_scale_overrides(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Animal\Index::class)
            ->set('exportFormat', 'pdf')
            ->call('exportar')
            ->assertSet('showExportModal', true)
            ->assertSet('exportStep', 'preview')
            ->call('setPdfTableRadius', '4')
            ->assertSet('pdfTableRadius', '4')
            ->call('setPdfTableRadius', '0')
            ->assertSet('pdfTableRadius', '0')
            ->call('setPdfScale', '45')
            ->assertSet('pdfScale', '45')
            ->call('setPdfScale', '100')
            ->assertSet('pdfScale', '100')
            ->call('setPdfTableColorMode', 'mono')
            ->assertSet('pdfTableColorMode', 'mono')
            ->call('setPdfTablePreset', 'indigo')
            ->assertSet('pdfTablePreset', 'indigo');
    }
}
