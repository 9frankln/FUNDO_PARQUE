<?php

namespace Tests\Feature\Security;

use App\Livewire\Finanzas\MovimientoForm;
use App\Models\Animal;
use App\Models\CategoriaFinanciera;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Movimiento;
use App\Models\Raza;
use App\Models\SanidadRegistro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PrivateFileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_files_require_permission_and_active_fundo(): void
    {
        Storage::fake('local');
        $category = CategoriaFinanciera::create([
            'tipo' => 'egreso',
            'nombre' => 'Prueba',
            'activo' => true,
        ]);
        [$user, $fundo] = $this->administratorWithFundo();
        [$otherUser, $otherFundo] = $this->administratorWithFundo();
        $movement = Movimiento::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'categoria_id' => $category->id,
            'monto' => 20,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'comprobante_ruta' => 'comprobantes/privado.pdf',
        ]);
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Simmental', 'activo' => true]);
        $animal = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-099',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => now()->year,
            'codigo_secuencia' => 99,
            'nombre' => 'Animal prueba',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'fecha_alta' => now()->subYears(2)->toDateString(),
            'edad_estimada_meses_alta' => 24,
            'activo' => true,
        ]);
        $profilaxis = SanidadRegistro::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo_evento' => 'preventivo',
            'fecha_evento' => now()->toDateString(),
            'alcance' => 'individual',
            'tipo_intervencion' => 'vacuna',
            'producto_marca' => 'Prueba',
            'clasificacion' => 'enfermedad_infecciosa',
            'estado_clinico' => 'en_tratamiento',
        ]);
        $photo = $profilaxis->fotos()->create([
            'fundo_id' => $fundo->id,
            'ruta' => 'monitoreo/sanidad/privada.webp',
            'orden' => 0,
        ]);
        Storage::disk('local')->put($movement->comprobante_ruta, 'comprobante privado');
        Storage::disk('local')->put($photo->ruta, 'foto privada');

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $this->get(route('movimiento.comprobante', $movement))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-cache, private');
        $this->get(route('record-photo.show', $photo))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=3600, private');

        $profilaxis->delete();
        $this->get(route('record-photo.show', $photo))->assertNotFound();
        $profilaxis->restore();

        $this->actingAs($otherUser)->withSession(['fundo_id' => $otherFundo->id]);
        $this->get(route('movimiento.comprobante', $movement))->assertNotFound();
        $this->get(route('record-photo.show', $photo))->assertNotFound();
    }

    public function test_uploaded_receipt_is_stored_privately(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $category = CategoriaFinanciera::create([
            'tipo' => 'egreso',
            'nombre' => 'Prueba',
            'activo' => true,
        ]);
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(MovimientoForm::class)
            ->set('categoriaId', (string) $category->id)
            ->set('monto', '25.50')
            ->set('descripcion', 'Compra de prueba')
            ->set('comprobante', UploadedFile::fake()->create('recibo.pdf', 10, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $path = Movimiento::firstOrFail()->comprobante_ruta;
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => fake()->unique()->company(), 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
