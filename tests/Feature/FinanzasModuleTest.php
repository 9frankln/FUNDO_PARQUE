<?php

namespace Tests\Feature;

use App\Livewire\Finanzas\Index;
use App\Livewire\Finanzas\MovimientoForm;
use App\Livewire\Medicamentos\Form as MedicamentoForm;
use App\Models\Animal;
use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\Movimiento;
use App\Models\User;
use App\Support\ImageFrame;
use App\Support\MedicamentoLotCodeAllocator;
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
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();
    }

    public function test_animal_medicine_destination_keeps_full_inline_form_and_other_categories_work(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $medicine = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Medicamentos',
            'activo' => true,
        ]);
        $otherExpense = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Otros Egresos',
            'activo' => true,
        ]);
        $otherIncome = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'ingreso',
            'nombre' => 'Otros Ingresos',
            'activo' => true,
        ]);

        Livewire::test(MovimientoForm::class)
            ->set('categoriaId', (string) $medicine->id)
            ->set('destinoMedicamento', 'animales')
            ->assertSet('destinoMedicamento', 'animales')
            ->assertSee('Inventario veterinario sincronizado')
            ->assertSee('Cantidad comprada')
            ->assertSee('Monto')
            ->assertSee('type="submit"', false)
            ->set('categoriaId', (string) $otherExpense->id)
            ->assertSet('destinoMedicamento', 'personas')
            ->assertSee('Monto')
            ->assertSee('type="submit"', false)
            ->set('categoriaId', (string) $medicine->id)
            ->set('destinoMedicamento', 'animales')
            ->set('tipo', 'ingreso')
            ->assertSet('destinoMedicamento', 'personas')
            ->set('categoriaId', (string) $otherIncome->id)
            ->assertSee('Monto')
            ->assertSee('type="submit"', false);
    }

    public function test_veterinary_purchase_is_one_record_editable_from_finance_and_medicines_with_shared_photo(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $category = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Medicamentos',
            'activo' => true,
        ]);
        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Desalor prueba',
            'tipo' => 'antibiotico',
            'presentacion' => 'Frasco 20 ml',
            'unidad_stock' => 'ml',
            'foto_ruta' => 'medicamentos/original.webp',
            'activo' => true,
        ]);

        Livewire::test(MovimientoForm::class)
            ->set('categoriaId', (string) $category->id)
            ->set('destinoMedicamento', 'animales')
            ->set('medicamentoId', (string) $medicine->id)
            ->set('numeroLoteMedicamento', '7')
            ->set('fechaVencimientoMedicamento', now()->addYear()->toDateString())
            ->set('cantidadMedicamento', 20)
            ->set('proveedorMedicamento', 'Proveedor veterinario')
            ->set('comprobanteMedicamento', 'F001-20')
            ->set('ubicacionMedicamento', 'Estante A')
            ->set('monto', 121)
            ->set('descripcion', 'Compra veterinaria sincronizada')
            ->call('save')
            ->assertHasNoErrors();

        $lotCode = MedicamentoLotCodeAllocator::format(now()->year, 7);
        $lot = MedicamentoLote::where('numero_lote', $lotCode)->firstOrFail();
        $movement = $lot->movimientoFinanciero()->firstOrFail();
        $this->assertSame($movement->id, $lot->movimiento_id);
        $this->assertSame(121.0, (float) $movement->monto);

        Livewire::test(Index::class)
            ->assertSee('Desalor prueba')
            ->assertSee(asset('storage/medicamentos/original.webp'), false);

        $financePhoto = UploadedFile::fake()->image('finance-photo.jpg', 900, 700);
        Livewire::test(MovimientoForm::class, ['id' => $movement->id])
            ->assertSet('medicamentoLoteId', $lot->id)
            ->assertSet('numeroLoteMedicamento', '007')
            ->assertSee($lotCode)
            ->assertSet('cantidadMedicamento', '20.000')
            ->assertSet('monto', '121.00')
            ->assertSet('medicamentoFotoActual', 'medicamentos/original.webp')
            ->set('cantidadMedicamento', 25)
            ->set('monto', 145)
            ->set('proveedorMedicamento', 'Proveedor actualizado')
            ->set('comprobante', $financePhoto)
            ->call('save')
            ->assertHasNoErrors();

        $lot->refresh();
        $movement->refresh();
        $medicine->refresh();
        $this->assertSame(25.0, (float) $lot->cantidad_inicial);
        $this->assertSame(145.0, (float) $movement->monto);
        $this->assertNotSame('medicamentos/original.webp', $medicine->foto_ruta);
        Storage::disk('public')->assertExists($medicine->foto_ruta);

        $medicinePhotoFromFinance = $medicine->foto_ruta;
        Livewire::test(MedicamentoForm::class, ['id' => $medicine->id])
            ->assertSet('loteId', (string) $lot->id)
            ->assertSet('costoTotal', '145.00')
            ->assertSet('fotoActual', $medicinePhotoFromFinance)
            ->set('costoTotal', 160)
            ->set('foto', UploadedFile::fake()->image('medicine-photo.jpg', 800, 800))
            ->call('save')
            ->assertHasNoErrors();

        $movement->refresh();
        $medicine->refresh();
        $this->assertSame(160.0, (float) $movement->monto);
        $this->assertNotSame($medicinePhotoFromFinance, $medicine->foto_ruta);

        Livewire::test(MovimientoForm::class, ['id' => $movement->id])
            ->assertSet('monto', '160.00')
            ->assertSet('medicamentoFotoActual', $medicine->foto_ruta)
            ->assertSee('Foto compartida del medicamento');
    }

    public function test_assignment_photo_is_optimized_and_assignment_has_view_flow(): void
    {
        Storage::fake('local');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $asignacionCategory = CategoriaFinanciera::where('fundo_id', null)
            ->where('tipo', 'egreso')
            ->where('nombre', 'Asignación Familiar')
            ->firstOrFail();

        Livewire::test(MovimientoForm::class)
            ->set('tipo', 'egreso')
            ->set('categoriaId', (string) $asignacionCategory->id)
            ->set('beneficiario', 'María Delgado')
            ->set('proposito', 'estudio')
            ->set('monto', '300')
            ->set('fecha', now()->toDateString())
            ->set('descripcion', 'Mensualidad universitaria')
            ->set('comprobante', UploadedFile::fake()->image('respaldo-grande.png', 3000, 2200)->size(6000))
            ->set('comprobanteEncuadre', ['x' => 67.0, 'y' => 42.0, 'zoom' => 1.3])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('finanzas.index', ['tab' => 'movimientos']));

        $assignment = Movimiento::firstOrFail();
        $this->assertSame('MARÍA DELGADO', $assignment->beneficiario);
        $this->assertSame('estudio', $assignment->proposito);
        Storage::disk('local')->assertExists($assignment->comprobante_ruta);
        $this->assertStringEndsWith('.webp', $assignment->comprobante_ruta);
        $this->assertSame(['x' => 67.0, 'y' => 42.0, 'zoom' => 1.3], ImageFrame::normalize($assignment->comprobante_encuadre));
        $this->assertLessThanOrEqual(900 * 1024, Storage::disk('local')->size($assignment->comprobante_ruta));
        [$width, $height] = getimagesize(Storage::disk('local')->path($assignment->comprobante_ruta));
        $this->assertLessThanOrEqual(1400, max($width, $height));

        Livewire::test(Index::class, ['tab' => 'movimientos'])
            ->assertSee('MARÍA DELGADO')
            ->assertSee('Ver movimiento')
            ->assertSee('Exportar PDF')
            ->assertSee('Para: MARÍA DELGADO', false);

        $this->get(route('finanzas.movimiento.show', $assignment))
            ->assertOk()
            ->assertSee('MARÍA DELGADO')
            ->assertSee('MENSUALIDAD UNIVERSITARIA')
            ->assertSee('Beneficiario')
            ->assertDontSee('Generar ficha PDF')
            ->assertSee(route('finanzas.index', ['tab' => 'movimientos']), false);

        Livewire::test(Index::class, ['tab' => 'movimientos'])
            ->call('openReportModal')
            ->assertSet('showReportModal', true)
            ->assertSet('reportType', 'movimientos')
            ->set('selectedReportSections', ['summary', 'records', 'categories'])
            ->call('downloadReport')
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
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
        $this->assertSame('¡Actualizado!', session('swal.title'));
        $this->assertSame($movement->id, session('ui_recent_record.id'));
        $this->assertSame('updated', session('ui_recent_record.action'));
    }

    public function test_sold_animal_code_links_to_its_record_from_finance_table(): void
    {
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
            'monto' => 5000,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'descripcion' => '[Venta Animales: BOV26-007] [A: Acopiador] - Se va para Espinar',
        ]);
        $animal = Animal::factory()->create([
            'fundo_id' => $fundo->id,
            'arete' => 'BOV26-007',
            'activo' => false,
            'motivo_baja' => 'venta',
            'movimiento_venta_id' => $movement->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee('BOV26-007')
            ->assertSee('Comprador: ACOPIADOR')
            ->assertSee('SE VA PARA ESPINAR')
            ->assertDontSee('[VENTA ANIMALES:')
            ->assertSee(route('animal.show', $animal), false);

        $this->get(route('animal.show', $animal))->assertOk();
    }

    public function test_insumo_purchase_renders_clickable_badge_and_link(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $insumo = \App\Models\Insumo::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Guantes Quirúrgicos',
            'tipo' => 'material_descartable',
            'unidad_stock' => 'caja',
            'stock_minimo' => 2,
            'activo' => true,
        ]);

        $category = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Insumos y Materiales',
            'activo' => true,
        ]);

        $mov = Movimiento::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'categoria_id' => $category->id,
            'monto' => 50,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'descripcion' => 'Compra de insumo para botiquín',
        ]);

        $lot = \App\Models\InsumoLote::create([
            'fundo_id' => $fundo->id,
            'insumo_id' => $insumo->id,
            'movimiento_id' => $mov->id,
            'numero_lote' => 'INS26-001',
            'fecha_ingreso' => now()->toDateString(),
            'cantidad_inicial' => 1,
            'cantidad_disponible' => 1,
            'costo_total' => 50,
            'proveedor' => 'FARMACIA CENTRAL',
            'activo' => true,
        ]);

        Livewire::test(Index::class)
            ->assertSee('Guantes Quirúrgicos · INS26-001')
            ->assertSee('(1 caja)')
            ->assertSee('Prov: FARMACIA CENTRAL')
            ->assertSee(route('insumos.show', $insumo->id), false);

        Livewire::test(\App\Livewire\Finanzas\MovimientoShow::class, ['id' => $mov->id])
            ->assertSee('Compra de insumo / material')
            ->assertSee('Guantes Quirúrgicos')
            ->assertSee('Lote INS26-001')
            ->assertSee('No perecible');
    }

    public function test_can_create_new_medication_from_scratch_in_finanzas_movement(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user);
        session(['fundo_id' => $fundo->id]);

        $category = CategoriaFinanciera::create([
            'fundo_id' => null,
            'tipo' => 'egreso',
            'nombre' => 'Medicamentos',
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Finanzas\MovimientoForm::class)
            ->set('tipo', 'egreso')
            ->set('categoriaId', (string) $category->id)
            ->set('destinoMedicamento', 'animales')
            ->set('modoMedicamentoNuevo', true)
            ->set('nombreMedicamento', 'Oxitetraciclina L.A.')
            ->set('unidadMedicamento', 'ml')
            ->set('tipoMedicamento', 'antibiotico')
            ->set('monto', 85.00)
            ->set('fecha', now()->toDateString())
            ->set('fechaVencimientoMedicamento', now()->addYear()->toDateString())
            ->set('cantidadMedicamento', 100)
            ->set('numeroLoteMedicamento', '001')
            ->set('proveedorMedicamento', 'FarmaVet Central')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $createdMed = \App\Models\Medicamento::where('nombre', 'Oxitetraciclina L.A.')->first();
        $this->assertNotNull($createdMed);
        $this->assertEquals('ml', $createdMed->unidad_stock);
        $this->assertEquals('antibiotico', $createdMed->tipo);

        $lot = \App\Models\MedicamentoLote::where('medicamento_id', $createdMed->id)->first();
        $this->assertNotNull($lot);
        $this->assertEquals('MET26-001', $lot->numero_lote);
        $this->assertEquals(100, (float) $lot->cantidad_inicial);
        $this->assertEquals(85.00, (float) $lot->costo_total);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
