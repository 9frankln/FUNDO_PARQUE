<?php

namespace Tests\Feature;

use App\Exports\AnimalesTemplateExport;
use App\Livewire\Animal\Index;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\User;
use App\Services\AnimalImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AnimalImportTest extends TestCase
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

        $especie = Especie::create([
            'nombre' => 'Bovino',
            'codigo_animal' => 'BOV',
            'activo' => true,
        ]);

        $raza = Raza::create([
            'especie_id' => $especie->id,
            'nombre' => 'Holstein',
            'activo' => true,
        ]);

        return [$user, $fundo, $especie, $raza];
    }

    public function test_template_export_can_be_generated(): void
    {
        [$user, $fundo] = $this->setupAdminAndFundo();

        $export = new AnimalesTemplateExport($fundo->id);
        $sheets = $export->sheets();

        $this->assertCount(2, $sheets);
        $this->assertSame('Animales a Registrar', $sheets[0]->title());
        $this->assertSame('Guía y Códigos', $sheets[1]->title());
    }

    public function test_animal_import_service_imports_valid_records(): void
    {
        [$user, $fundo, $especie, $raza] = $this->setupAdminAndFundo();

        // Create a temporary CSV file with valid animal data
        $csvContent = implode("\n", [
            'Código / Arete,Nombre,Género,Especie,Raza,Fecha Nacimiento,Edad Meses,Peso,Estado Reproductivo,Procedencia,Precio Compra,Fecha Ingreso,Apta Ordeño,Observaciones',
            'IMP-001,Margarita,Hembra,Bovino,Holstein,2023-05-10,,420.5,Lactante,Compra,3200.00,2024-01-10,SI,Primera vaca importada',
            'IMP-002,Bandido,Macho,Bovino,Holstein,,24,550.0,,Parto,0.00,2024-02-01,NO,Toro reproductor',
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tmpFile, $csvContent);

        $service = new AnimalImportService;
        $result = $service->import($tmpFile, $fundo->id, false);

        @unlink($tmpFile);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['total']);
        $this->assertSame(2, $result['imported']);
        $this->assertSame(0, $result['invalid']);

        $this->assertDatabaseHas('animales', [
            'fundo_id' => $fundo->id,
            'arete' => 'IMP-001',
            'nombre' => 'MARGARITA',
            'genero' => 'hembra',
            'estado_reproductivo' => 'lactante',
            'apta_ordeno' => true,
        ]);

        $this->assertDatabaseHas('animales', [
            'fundo_id' => $fundo->id,
            'arete' => 'IMP-002',
            'nombre' => 'BANDIDO',
            'genero' => 'macho',
            'apta_ordeno' => false,
        ]);
    }

    public function test_animal_import_service_detects_duplicates_and_invalid_data(): void
    {
        [$user, $fundo, $especie, $raza] = $this->setupAdminAndFundo();

        // Create an existing animal
        Animal::create([
            'fundo_id' => $fundo->id,
            'arete' => 'EXIST-01',
            'nombre' => 'Previa',
            'genero' => 'hembra',
            'especie_id' => $especie->id,
            'raza_id' => $raza->id,
            'tipo_alta' => 'compra',
            'activo' => true,
            'fecha_alta' => now(),
        ]);

        $csvContent = implode("\n", [
            'Código / Arete,Nombre,Género,Especie,Raza,Fecha Nacimiento,Edad Meses,Peso,Estado Reproductivo,Procedencia,Precio Compra,Fecha Ingreso,Apta Ordeño,Observaciones',
            'EXIST-01,Duplicada BD,Hembra,Bovino,Holstein,,,,,,,,,',
            'DUP-02,Primera,Hembra,Bovino,Holstein,,,,,,,,,',
            'DUP-02,Repetida Archivo,Hembra,Bovino,Holstein,,,,,,,,,',
            ',Sin Arete,Hembra,Bovino,Holstein,,,,,,,,,',
            'BAD-03,Genero Malo,Invalido,Bovino,Holstein,,,,,,,,,',
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tmpFile, $csvContent);

        $service = new AnimalImportService;
        $result = $service->import($tmpFile, $fundo->id, true); // dry run

        @unlink($tmpFile);

        $this->assertFalse($result['success']);
        $this->assertSame(5, $result['total']);
        $this->assertSame(2, $result['valid']); // DUP-02 first instance and Sin Arete (auto-allocated) are valid
        $this->assertSame(3, $result['invalid']); // EXIST-01, DUP-02 duplicate, and BAD-03 invalid gender
        $this->assertNotEmpty($result['errors']);
    }

    public function test_livewire_animal_index_can_download_template_and_open_import_modal(): void
    {
        [$user, $fundo] = $this->setupAdminAndFundo();

        Excel::fake();

        session(['fundo_id' => $fundo->id]);

        \Carbon\Carbon::setTestNow(now());
        $expectedName = 'plantilla_importacion_animales_'.now()->format('Ymd_His').'.xlsx';

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
