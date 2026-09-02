<?php

namespace Tests\Feature;

use App\Livewire\Ajustes\Index as AjustesIndex;
use App\Models\Fundo;
use App\Models\User;
use App\Services\Pdf\PdfDigitalSignerService;
use App\Services\Pdf\PdfSignatureVerifierService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PdfDigitalSignatureAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        $admin = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
        $fundo = Fundo::create(['nombre' => 'Fundo Modelo', 'activo' => true]);
        $admin->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$admin, $fundo];
    }

    public function test_can_generate_and_retrieve_fundo_x509_certificate(): void
    {
        [$admin, $fundo] = $this->context();
        $signerService = app(PdfDigitalSignerService::class);

        $certDetails = $signerService->generateCertificate($fundo->id, $fundo->nombre);

        $this->assertNotEmpty($certDetails);
        $this->assertTrue($signerService->hasCertificate($fundo->id));
        $this->assertEquals('PE', $certDetails['country']);
        $this->assertStringContainsString($fundo->nombre, $certDetails['common_name']);
        $this->assertNotEmpty($certDetails['fingerprint_sha256']);
        $this->assertTrue($certDetails['is_valid']);
    }

    public function test_can_sign_pdf_cryptographically_and_verify_its_authenticity(): void
    {
        [$admin, $fundo] = $this->context();

        $signerService = app(PdfDigitalSignerService::class);
        $verifierService = app(PdfSignatureVerifierService::class);

        // Generate sample PDF binary
        $pdf = Pdf::loadHTML('<html><body><h1>Reporte Ganadero Oficial</h1><p>Animales: 25</p></body></html>');
        $pdf->setOptions(['isPhpEnabled' => true]);
        $rawBinary = $pdf->output();

        // 1. Sign PDF
        $signedBinary = $signerService->signPdf($rawBinary, $fundo->id, $admin, 'password123', [
            'reason' => 'Aprobación y Certificación de Ganado',
        ]);

        $this->assertNotEmpty($signedBinary);
        $this->assertStringContainsString('/AgroFundo', $signedBinary);
        $this->assertStringContainsString('/Type /Sig', $signedBinary);
        $this->assertStringContainsString('/DocMDP', $signedBinary);

        // 2. Verify authentic signed PDF
        $verification = $verifierService->verify($signedBinary);

        $this->assertTrue($verification['is_signed']);
        $this->assertTrue($verification['is_valid']);
        $this->assertFalse($verification['is_tampered']);
        $this->assertTrue($verification['is_system_origin']);
        $this->assertEquals('valid', $verification['status']);
        $this->assertEquals($admin->name, $verification['primary_signature']['signer_name']);

        // 3. Test tamper detection (Modify 1 byte in the PDF body)
        $tamperedBinary = substr_replace($signedBinary, 'X', 50, 1);
        $tamperedVerification = $verifierService->verify($tamperedBinary);

        $this->assertTrue($tamperedVerification['is_signed']);
        $this->assertTrue($tamperedVerification['is_tampered']);
        $this->assertFalse($tamperedVerification['is_valid']);
        $this->assertEquals('tampered', $tamperedVerification['status']);
    }

    public function test_ajustes_pdf_subtabs_and_validator_livewire(): void
    {
        [$admin, $fundo] = $this->context();

        // Generate sample signed PDF
        $signerService = app(PdfDigitalSignerService::class);
        $pdf = Pdf::loadHTML('<html><body><h1>Prueba Livewire</h1></body></html>');
        $signedPdf = $signerService->signPdf($pdf->output(), $fundo->id, $admin, 'password123');

        $uploadedPdf = UploadedFile::fake()->createWithContent('documento_firmado.pdf', $signedPdf);

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class, ['activeTab' => 'pdf'])
            ->assertSet('activeTab', 'pdf')
            ->call('setPdfSubTab', 'certificados')
            ->assertSet('activePdfSubTab', 'certificados')
            ->assertSee('Certificado Digital X.509 del Fundo')
            ->call('setPdfSubTab', 'validador')
            ->assertSet('activePdfSubTab', 'validador')
            ->assertSee('Validador de Firmas e Integridad PDF')
            ->set('pdfFileToVerify', $uploadedPdf)
            ->assertSet('verificationResult.is_signed', true)
            ->assertSet('verificationResult.is_valid', true)
            ->assertSee('FIRMA VÁLIDA')
            ->assertSee('EMITIDO POR AGROFUNDO')
            ->call('clearPdfVerification')
            ->assertSet('pdfFileToVerify', null)
            ->assertSet('verificationResult', null);
    }

    public function test_ajustes_pdf_table_color_and_corner_settings(): void
    {
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class, ['activeTab' => 'pdf'])
            ->set('pdfSettings.modo_color_tablas', 'multi')
            ->set('pdfSettings.paleta_tablas', ['indigo', 'emerald', 'amber', 'rose'])
            ->call('setCornerRadius', 8)
            ->assertSet('pdfSettings.radio_esquinas', 8)
            ->assertSet('pdfSettings.estilo_esquinas', 'redondeado')
            ->assertSee('Vista en paralelo de 3 tablas con esquinas y colores dinámicos')
            ->call('savePdfSettings')
            ->assertHasNoErrors();

        $config = app(\App\Support\PdfReportConfig::class)->forFundo($fundo->id);
        $this->assertEquals('multi', $config->modoColorTablas());
        $this->assertTrue($config->isMultiColorTablas());
        $this->assertEquals(['indigo', 'emerald', 'amber', 'rose'], $config->paletaTablas());
        $this->assertTrue($config->isRoundedTables());
        $this->assertEquals('8pt', $config->tableBorderRadius());
        $this->assertEquals(8, $config->tableBorderRadiusPx());
        $this->assertEquals('#4f46e5', $config->tableThemeForIndex(0)['primary']);
        $this->assertEquals('#059669', $config->tableThemeForIndex(1)['primary']);

        // Test classic (0 pt)
        Livewire::test(AjustesIndex::class, ['activeTab' => 'pdf'])
            ->call('setCornerRadius', 0)
            ->assertSet('pdfSettings.radio_esquinas', 0)
            ->assertSet('pdfSettings.estilo_esquinas', 'clasico')
            ->call('savePdfSettings')
            ->assertHasNoErrors();

        $configFresh = app(\App\Support\PdfReportConfig::class)->forFundo($fundo->id);
        $this->assertFalse($configFresh->isRoundedTables());
        $this->assertEquals('0', $configFresh->tableBorderRadius());
        $this->assertEquals(0, $configFresh->tableBorderRadiusPx());
    }

    public function test_ajustes_pdf_signature_scale_and_expanded_color_palette(): void
    {
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        // 1. Verify 16 color presets exist
        $this->assertCount(16, \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('cyan', \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('lime', \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('orange', \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('fuchsia', \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('stone', \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('purple', \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('olive', \App\Support\PdfReportConfig::COLOR_PRESETS);
        $this->assertArrayHasKey('coffee', \App\Support\PdfReportConfig::COLOR_PRESETS);

        // 2. Test signature scale setting via Livewire
        Livewire::test(AjustesIndex::class, ['activeTab' => 'pdf'])
            ->call('setSignatureScale', 120)
            ->assertSet('pdfSettings.escala_firmas', 120)
            ->set('pdfSettings.estilo_color', 'cyan')
            ->call('savePdfSettings')
            ->assertHasNoErrors();

        $config = app(\App\Support\PdfReportConfig::class)->forFundo($fundo->id);
        $this->assertEquals(120, $config->signatureScale());
        $this->assertEquals('4.56pt', $config->signatureBodyFontSizePt());
        $this->assertEquals('5.76pt', $config->signatureTitleFontSizePt());
        $this->assertEquals('4.56pt', $config->signatureLabelFontSizePt());
        $this->assertEquals('204pt', $config->signatureMaxWidthPt());
        $this->assertEquals('#0891b2', $config->accentColor());
    }
}
