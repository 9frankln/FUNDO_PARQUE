<?php

namespace Tests\Feature;

use App\Livewire\Finanzas\AsignacionForm;
use App\Livewire\Finanzas\Index;
use App\Livewire\Finanzas\MovimientoForm;
use App\Models\AsignacionFamiliar;
use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Movimiento;
use App\Models\User;
use App\Support\ImageFrame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FinanzasModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_movement_categories_follow_type_and_large_receipt_is_optimized(): void
    {
        Storage::fake('local');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $egreso = CategoriaFinanciera::create(['fundo_id' => $fundo->id, 'tipo' => 'egreso', 'nombre' => 'Alimentos', 'activo' => true]);
        $ingreso = CategoriaFinanciera::create(['fundo_id' => $fundo->id, 'tipo' => 'ingreso', 'nombre' => 'Venta de leche', 'activo' => true]);

        Livewire::test(MovimientoForm::class)
            ->assertSet('tipo', 'egreso')
            ->assertSet('categorias.0.id', $egreso->id)
            ->set('tipo', 'ingreso')
            ->assertSet('categoriaId', '')
            ->assertSet('categorias.0.id', $ingreso->id)
            ->set('categoriaId', (string) $ingreso->id)
            ->set('monto', '850.50')
            ->set('cantidadLitros', 500)
            ->set('descripcion', 'Venta semanal')
            ->set('comprobante', UploadedFile::fake()->image('comprobante-grande.jpg', 3200, 2400)->size(5000))
            ->set('comprobanteEncuadre', ['x' => 18.0, 'y' => 76.0, 'zoom' => 1.5])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('finanzas.index', ['tab' => 'movimientos']));

        $movement = Movimiento::firstOrFail();
        Storage::disk('local')->assertExists($movement->comprobante_ruta);
        $this->assertStringEndsWith('.webp', $movement->comprobante_ruta);
        $this->assertSame(['x' => 18.0, 'y' => 76.0, 'zoom' => 1.5], ImageFrame::normalize($movement->comprobante_encuadre));
        $this->assertLessThanOrEqual(900 * 1024, Storage::disk('local')->size($movement->comprobante_ruta));
        [$width, $height] = getimagesize(Storage::disk('local')->path($movement->comprobante_ruta));
        $this->assertLessThanOrEqual(1400, max($width, $height));

        Livewire::test(Index::class, ['tab' => 'movimientos'])
            ->assertSee('Ver movimiento')
            ->assertSee('Exportar PDF')
            ->assertSee('Pulso económico del fundo')
            ->assertDontSee('Ver imagen')
            ->assertSee(route('finanzas.movimiento.show', $movement), false)
            ->assertSee(route('movimiento.comprobante', $movement), false)
            ->assertSee('?v='.sha1($movement->comprobante_ruta), false);

        $this->get(route('finanzas.movimiento.show', $movement))
            ->assertOk()
            ->assertSee('VENTA SEMANAL')
            ->assertDontSee('Generar ficha PDF')
            ->assertSee(route('finanzas.index', ['tab' => 'movimientos']), false);

        Livewire::test(Index::class, ['tab' => 'movimientos'])
            ->call('openReportModal')
            ->assertSet('showReportModal', true)
            ->assertSet('reportType', 'movimientos')
            ->set('selectedReportSections', ['summary', 'records', 'categories'])
            ->call('downloadReport')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }

    public function test_assignment_photo_is_optimized_and_assignment_has_view_flow(): void
    {
        Storage::fake('local');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AsignacionForm::class)
            ->set('beneficiario', 'María Delgado')
            ->set('proposito', 'estudio')
            ->set('monto', '300')
            ->set('descripcion', 'Mensualidad universitaria')
            ->set('foto', UploadedFile::fake()->image('respaldo-grande.png', 3000, 2200)->size(6000))
            ->set('fotoEncuadre', ['x' => 67.0, 'y' => 42.0, 'zoom' => 1.3])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('finanzas.index', ['tab' => 'asignaciones']));

        $assignment = AsignacionFamiliar::firstOrFail();
        Storage::disk('local')->assertExists($assignment->foto_ruta);
        $this->assertStringEndsWith('.webp', $assignment->foto_ruta);
        $this->assertSame(['x' => 67.0, 'y' => 42.0, 'zoom' => 1.3], ImageFrame::normalize($assignment->foto_encuadre));
        $this->assertLessThanOrEqual(600 * 1024, Storage::disk('local')->size($assignment->foto_ruta));
        [$width, $height] = getimagesize(Storage::disk('local')->path($assignment->foto_ruta));
        $this->assertLessThanOrEqual(1080, max($width, $height));

        Livewire::test(Index::class, ['tab' => 'asignaciones'])
            ->assertSee('MARÍA DELGADO')
            ->assertSee('Foto')
            ->assertSee('Ver asignación')
            ->assertSee('Exportar PDF')
            ->assertDontSee('<th class="p-4 whitespace-nowrap">Descripción</th>', false);

        $this->get(route('finanzas.asignacion.show', $assignment))
            ->assertOk()
            ->assertSee('MARÍA DELGADO')
            ->assertSee('MENSUALIDAD UNIVERSITARIA')
            ->assertDontSee('Generar ficha PDF')
            ->assertSee(route('finanzas.index', ['tab' => 'asignaciones']), false);

        Livewire::test(Index::class, ['tab' => 'asignaciones'])
            ->call('openReportModal')
            ->assertSet('showReportModal', true)
            ->assertSet('reportType', 'asignaciones')
            ->set('selectedReportSections', ['summary', 'records', 'purposes'])
            ->call('downloadReport')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }

    public function test_movement_update_replaces_receipt_and_returns_to_movement_table(): void
    {
        Storage::fake('local');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $category = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'ingreso',
            'nombre' => 'Venta de Animales',
            'activo' => true,
        ]);
        $movement = Movimiento::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'ingreso',
            'categoria_id' => $category->id,
            'monto' => 320,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'descripcion' => 'Compra inicial',
            'comprobante_ruta' => 'comprobantes/anterior.jpg',
        ]);
        Storage::disk('local')->put($movement->comprobante_ruta, 'comprobante anterior');

        Livewire::test(MovimientoForm::class, ['id' => $movement->id])
            ->set('comprobante', UploadedFile::fake()->image('comprobante-nuevo.jpg', 1800, 1200))
            ->set('comprobanteEncuadre', ['x' => 25, 'y' => 75, 'zoom' => 1.4])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('finanzas.index', ['tab' => 'movimientos']));

        $movement->refresh();
        Storage::disk('local')->assertMissing('comprobantes/anterior.jpg');
        Storage::disk('local')->assertExists($movement->comprobante_ruta);
        $this->assertStringEndsWith('.webp', $movement->comprobante_ruta);
        $this->assertSame(['x' => 25.0, 'y' => 75.0, 'zoom' => 1.4], ImageFrame::normalize($movement->comprobante_encuadre));
        $this->assertSame('Movimiento actualizado', session('swal.title'));
        $this->assertSame($movement->id, session('ui_recent_record.id'));
        $this->assertSame('updated', session('ui_recent_record.action'));
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
