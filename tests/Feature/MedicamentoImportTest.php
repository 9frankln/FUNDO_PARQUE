<?php

namespace Tests\Feature;

use App\Exports\MedicamentosTemplateExport;
use App\Livewire\Medicamentos\Index;
use App\Models\Fundo;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\User;
use App\Services\MedicamentoImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MedicamentoImportTest extends TestCase
{
    use RefreshDatabase;

    private function setupAdminAndFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create([
            'nombre' => 'Fundo Los Andes',
            'activo' => true,
        ]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    public function test_medicamentos_template_export_can_be_generated(): void
    {
        [$user, $fundo] = $this->setupAdminAndFundo();

        $export = new MedicamentosTemplateExport($fundo->id);
        $sheets = $export->sheets();

        $this->assertCount(2, $sheets);
        $this->assertSame('Medicamentos a Registrar', $sheets[0]->title());
        $this->assertSame('Guía y Ejemplos', $sheets[1]->title());
    }

    public function test_medicamento_import_service_imports_valid_records(): void
    {
        [$user, $fundo] = $this->setupAdminAndFundo();

        $csvContent = implode("\n", [
            'Nombre Comercial,Categoría,Principio Activo,Concentración,Presentación,Unidad de Dosificación,Alerta Stock,N° Lote,Fecha Ingreso,Fecha Vencimiento,Cantidad en Stock,Costo Total,Proveedor,Laboratorio,Ubicación,Observaciones',
            'OXITETRACICLINA 200,Antibiótico,Oxitetraciclina L.A.,200 mg/ml,Frasco 250ml,ml,50,L-84920,2026-01-10,2027-01-10,250,65.00,Agroveterinaria El Campo,Agrovet Market,Estante Antibióticos,Uso intramuscular',
            'IVERMECTINA 1%,Antiparasitario,Ivermectina,10 mg/ml,Frasco 500ml,ml,100,IV-2025,2026-02-01,2028-02-01,500,85.00,Veterinaria San Martín,Montana,Estante Parasitarios,Para parásitos internos',
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_med_');
        file_put_contents($tmpFile, $csvContent);

        $service = new MedicamentoImportService;
        $result = $service->import($tmpFile, $fundo->id, false);

        @unlink($tmpFile);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['total']);
        $this->assertSame(2, $result['imported']);
        $this->assertSame(0, $result['invalid']);

        $this->assertDatabaseHas('medicamentos', [
            'fundo_id' => $fundo->id,
            'nombre' => 'OXITETRACICLINA 200',
            'tipo' => 'antibiotico',
            'unidad_stock' => 'ml',
        ]);

        $this->assertDatabaseHas('medicamento_lotes', [
            'fundo_id' => $fundo->id,
            'numero_lote' => 'L-84920',
            'cantidad_inicial' => 250,
            'cantidad_disponible' => 250,
        ]);
    }

    public function test_livewire_medicamentos_index_can_download_template_and_open_import_modal(): void
    {
        [$user, $fundo] = $this->setupAdminAndFundo();

        Excel::fake();

        session(['fundo_id' => $fundo->id]);

        \Carbon\Carbon::setTestNow(now());
        $expectedName = 'plantilla_importacion_medicamentos_'.now()->format('Ymd_His').'.xlsx';

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSet('showImportModal', false)
            ->call('openImportModal')
            ->assertSet('showImportModal', true)
            ->call('downloadImportTemplate');

        Excel::assertDownloaded($expectedName);
        \Carbon\Carbon::setTestNow();
    }
}
