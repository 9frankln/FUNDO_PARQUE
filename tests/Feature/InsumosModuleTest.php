<?php

namespace Tests\Feature;

use App\Livewire\Finanzas\MovimientoForm as FinanzasMovimientoForm;
use App\Livewire\Insumos\Form as InsumoForm;
use App\Livewire\Insumos\Index as InsumoIndex;
use App\Livewire\Insumos\Show as InsumoShow;
use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Insumo;
use App\Models\InsumoLote;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\User;
use App\Support\InsumoLotCodeAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InsumosModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Fundo $fundo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->fundo = Fundo::factory()->create();
        $this->user->fundos()->attach($this->fundo->id, ['es_administrador' => true]);

        $this->actingAs($this->user);
        session(['fundo_id' => $this->fundo->id]);
    }

    public function test_can_view_insumos_index_page(): void
    {
        Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Alcohol 70 Grados',
            'tipo' => 'antiseptico_desinfectante',
            'unidad_stock' => 'frasco',
            'stock_minimo' => 2,
            'condicion_almacenamiento' => 'ambiente',
            'activo' => true,
        ]);

        Livewire::test(InsumoIndex::class)
            ->assertSee('Insumos y Materiales')
            ->assertSee('Alcohol 70 Grados')
            ->assertSee('Inventario y Stock');
    }

    public function test_can_register_new_insumo_with_initial_lot(): void
    {
        Livewire::test(InsumoForm::class)
            ->set('nombre', 'Guantes de Nitrilo M')
            ->set('tipo', 'material_descartable')
            ->set('unidadStock', 'par')
            ->set('stockMinimo', 50)
            ->set('condicionAlmacenamiento', 'ambiente')
            ->set('ubicacionPredeterminada', 'Estante Botiquín 1')
            ->set('agregarExistencia', true)
            ->set('tipoIngreso', 'compra')
            ->set('numeroLote', '001')
            ->set('fechaIngreso', now()->toDateString())
            ->set('fechaVencimiento', now()->addYears(3)->toDateString())
            ->set('cantidadInicial', 200)
            ->set('costoTotal', 85.00)
            ->set('proveedor', 'Médica del Sur')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $insumo = Insumo::where('nombre', 'Guantes de Nitrilo M')->first();
        $this->assertNotNull($insumo);
        $this->assertEquals('material_descartable', $insumo->tipo);
        $this->assertEquals('par', $insumo->unidad_stock);

        $lote = InsumoLote::where('insumo_id', $insumo->id)->first();
        $this->assertNotNull($lote);
        $this->assertEquals('INS26-001', $lote->numero_lote);
        $this->assertEquals(200, $lote->cantidad_disponible);
        $this->assertEquals(85.00, $lote->costo_total);

        // Movimiento financiero vinculado creado
        $this->assertNotNull($lote->movimiento_id);
        $mov = Movimiento::find($lote->movimiento_id);
        $this->assertNotNull($mov);
        $this->assertEquals(85.00, $mov->monto);
    }

    public function test_insumo_lot_allocator_handles_sequential_and_gap_filling(): void
    {
        $allocator = app(InsumoLotCodeAllocator::class);
        $year = 2026;

        $first = $allocator->allocate($this->fundo->id, $year);
        $this->assertEquals('INS26-001', $first);

        // Create first lot in DB
        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Gasas Estériles',
            'tipo' => 'material_curacion',
            'unidad_stock' => 'paquete',
            'stock_minimo' => 5,
            'activo' => true,
        ]);

        InsumoLote::create([
            'fundo_id' => $this->fundo->id,
            'insumo_id' => $insumo->id,
            'numero_lote' => 'INS26-001',
            'fecha_ingreso' => now()->toDateString(),
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'activo' => true,
        ]);

        $second = $allocator->allocate($this->fundo->id, $year);
        $this->assertEquals('INS26-002', $second);
    }

    public function test_deleting_insumo_lot_also_deletes_finance_egreso(): void
    {
        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Agua Oxigenada 10 Vol',
            'tipo' => 'antiseptico_desinfectante',
            'unidad_stock' => 'frasco',
            'stock_minimo' => 2,
            'activo' => true,
        ]);

        Livewire::test(InsumoShow::class, ['id' => $insumo->id])
            ->call('openLoteModal')
            ->set('lTipoIngreso', 'compra')
            ->set('lNumeroLote', '001')
            ->set('lFechaIngreso', now()->toDateString())
            ->set('lCantidad', 5)
            ->set('lCostoTotal', 25.00)
            ->set('lProveedor', 'Botica San Martín')
            ->call('saveLote')
            ->assertHasNoErrors();

        $lot = InsumoLote::where('insumo_id', $insumo->id)->first();
        $this->assertNotNull($lot);
        $movId = $lot->movimiento_id;
        $this->assertNotNull($movId);
        $this->assertDatabaseHas('movimientos', ['id' => $movId]);

        // Delete lot
        Livewire::test(InsumoShow::class, ['id' => $insumo->id])
            ->call('deleteLot', $lot->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('insumo_lotes', ['id' => $lot->id]);
        $this->assertSoftDeleted('movimientos', ['id' => $movId]);
    }

    public function test_finanzas_movimiento_form_saves_mas_detalles_for_medication(): void
    {
        $category = CategoriaFinanciera::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Medicamentos',
            'tipo' => 'egreso',
            'activo' => true,
        ]);

        $med = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Enrofloxacina 10%',
            'tipo' => 'antibiotico',
            'unidad_stock' => 'ml',
            'stock_minimo' => 10,
            'condicion_almacenamiento' => 'ambiente',
            'activo' => true,
        ]);

        Livewire::test(FinanzasMovimientoForm::class)
            ->set('tipo', 'egreso')
            ->set('categoriaId', (string) $category->id)
            ->set('monto', 90.00)
            ->set('fecha', now()->toDateString())
            ->set('descripcion', 'Compra de antibiótico')
            ->set('destinoMedicamento', 'animales')
            ->set('medicamentoId', (string) $med->id)
            ->set('cantidadMedicamento', 100)
            ->set('numeroLoteMedicamento', '001')
            ->set('fechaVencimientoMedicamento', now()->addYears(2)->toDateString())
            ->set('proveedorMedicamento', 'Agropecuaria Central')
            ->set('condicionAlmacenamientoMedicamento', 'protegido_luz')
            ->set('viaPredeterminadaMedicamento', 'subcutanea')
            ->set('stockMinimoMedicamento', 25)
            ->set('observacionesMedicamento', 'Mantener en frasco oscuro')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $med->refresh();
        $this->assertEquals('protegido_luz', $med->condicion_almacenamiento);
        $this->assertEquals('subcutanea', $med->via_predeterminada);
        $this->assertEquals(25, $med->stock_minimo);
        $this->assertEquals('Mantener en frasco oscuro', $med->observaciones);
    }

    public function test_can_delete_insumo_from_unified_botiquin_index(): void
    {
        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Jeringas 10ml Descartables',
            'tipo' => 'material_descartable',
            'unidad_stock' => 'unidad',
            'stock_minimo' => 10,
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Medicamentos\Index::class)
            ->set('tab', 'insumos')
            ->call('deleteInsumo', $insumo->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('insumos', ['id' => $insumo->id]);
    }

    public function test_can_delete_application_and_revert_stock(): void
    {
        $med = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Complejo B Fuerte',
            'tipo' => 'vitamina_mineral',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);

        $lot = MedicamentoLote::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $med->id,
            'numero_lote' => 'MET26-001',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 80,
            'activo' => true,
        ]);

        $app = \App\Models\MedicamentoMovimiento::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $med->id,
            'medicamento_lote_id' => $lot->id,
            'user_id' => $this->user->id,
            'tipo' => 'aplicacion',
            'fecha_hora' => now(),
            'cantidad' => -20,
            'unidad' => 'ml',
            'saldo_lote' => 80,
            'detalle' => 'Aplicación preventiva',
        ]);

        Livewire::test(\App\Livewire\Medicamentos\Index::class)
            ->set('tab', 'aplicaciones')
            ->call('deleteAplicacion', $app->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('medicamento_movimientos', ['id' => $app->id]);

        $lot->refresh();
        $this->assertEquals(100, $lot->cantidad_disponible);
    }

    public function test_can_upload_and_edit_insumo_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $photo = \Illuminate\Http\UploadedFile::fake()->image('insumo_alcohol.jpg', 600, 600);

        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Alcohol Yodado 2%',
            'tipo' => 'antiseptico_desinfectante',
            'unidad_stock' => 'frasco',
            'stock_minimo' => 5,
            'activo' => true,
        ]);

        Livewire::test(InsumoForm::class, ['id' => $insumo->id])
            ->set('foto', $photo)
            ->set('agregarExistencia', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('insumos.index'));

        $insumo->refresh();
        $this->assertNotNull($insumo->foto_ruta);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($insumo->foto_ruta);
    }

    public function test_insumo_loads_and_syncs_photo_from_linked_financial_movement(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        $receipt = \Illuminate\Http\UploadedFile::fake()->image('comprobante_insumo.jpg', 600, 600);
        $localPath = $receipt->store('comprobantes', 'local');

        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Guantes de Nitrilo',
            'tipo' => 'material_descartable',
            'unidad_stock' => 'caja',
            'stock_minimo' => 2,
            'foto_ruta' => null,
            'activo' => true,
        ]);

        $category = \App\Models\CategoriaFinanciera::create([
            'fundo_id' => $this->fundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Insumos Médicos',
            'activo' => true,
        ]);

        $mov = \App\Models\Movimiento::create([
            'fundo_id' => $this->fundo->id,
            'tipo' => 'egreso',
            'categoria_id' => $category->id,
            'monto' => 45,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'descripcion' => 'COMPRA DE INSUMO: GUANTES DE NITRILO',
            'comprobante_ruta' => $localPath,
        ]);

        $lot = InsumoLote::create([
            'fundo_id' => $this->fundo->id,
            'insumo_id' => $insumo->id,
            'movimiento_id' => $mov->id,
            'numero_lote' => 'INS26-001',
            'fecha_ingreso' => now()->toDateString(),
            'cantidad_inicial' => 2,
            'cantidad_disponible' => 2,
            'costo_total' => 45,
            'activo' => true,
        ]);

        // When mounting Insumos/Form on editing insumo, it should auto-sync and load photo
        $component = Livewire::test(InsumoForm::class, ['id' => $insumo->id]);
        $insumo->refresh();

        $this->assertNotNull($insumo->foto_ruta);
        $component->assertSet('fotoActual', $insumo->foto_ruta);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($insumo->foto_ruta);
    }

    public function test_can_create_and_edit_insumo_purchase_from_finanzas_module(): void
    {
        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Alcohol Yodado 1L',
            'tipo' => 'antiseptico_desinfectante',
            'unidad_stock' => 'frasco',
            'stock_minimo' => 3,
            'activo' => true,
        ]);

        $category = \App\Models\CategoriaFinanciera::create([
            'fundo_id' => $this->fundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Insumos y Materiales',
            'activo' => true,
        ]);

        // 1. Create insumo purchase from Finanzas MovimientoForm
        Livewire::test(\App\Livewire\Finanzas\MovimientoForm::class)
            ->set('tipo', 'egreso')
            ->set('categoriaId', (string) $category->id)
            ->set('insumoId', (string) $insumo->id)
            ->set('monto', 65.50)
            ->set('fecha', now()->toDateString())
            ->set('cantidadInsumo', 5)
            ->set('numeroLoteInsumo', '001')
            ->set('proveedorInsumo', 'Distribuidora FarmaVet')
            ->set('marcaLaboratorioInsumo', 'Laboratorios Quimicos')
            ->set('presentacionInsumo', 'Frasco 1000ml')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $lot = InsumoLote::where('insumo_id', $insumo->id)->first();
        $this->assertNotNull($lot);
        $this->assertEquals('INS26-001', $lot->numero_lote);
        $this->assertEquals(5, (float) $lot->cantidad_inicial);
        $this->assertEquals(65.50, (float) $lot->costo_total);
        $this->assertEquals('Distribuidora FarmaVet', $lot->proveedor);

        $mov = \App\Models\Movimiento::where('fundo_id', $this->fundo->id)->latest('id')->first();
        $this->assertEquals($mov->id, $lot->movimiento_id);
        $this->assertEquals(65.50, (float) $mov->monto);

        // 2. Edit the movement in Finanzas
        Livewire::test(\App\Livewire\Finanzas\MovimientoForm::class, ['id' => $mov->id])
            ->assertSet('insumoId', (string) $insumo->id)
            ->assertSet('numeroLoteInsumo', '001')
            ->assertSet('cantidadInsumo', 5)
            ->assertSet('isInsumoInventoryPurchase', true)
            ->assertSee('Inventario de insumos sincronizado')
            ->assertSee('INS26-')
            ->assertSee('Foto del insumo')
            ->set('monto', 70.00)
            ->set('cantidadInsumo', 6)
            ->set('proveedorInsumo', 'Distribuidora FarmaVet Actualizada')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $lot->refresh();
        $mov->refresh();
        $this->assertEquals(6, (float) $lot->cantidad_inicial);
        $this->assertEquals(6, (float) $lot->cantidad_disponible);
        $this->assertEquals(70.00, (float) $lot->costo_total);
        $this->assertEquals(70.00, (float) $mov->monto);
        $this->assertEquals('Distribuidora FarmaVet Actualizada', $lot->proveedor);
    }

    public function test_can_create_new_insumo_from_scratch_in_finanzas_movement(): void
    {
        $this->actingAs($this->user);
        session(['fundo_id' => $this->fundo->id]);

        $category = CategoriaFinanciera::create([
            'fundo_id' => null,
            'tipo' => 'egreso',
            'nombre' => 'Insumos y Materiales',
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Finanzas\MovimientoForm::class)
            ->set('tipo', 'egreso')
            ->set('categoriaId', (string) $category->id)
            ->set('modoInsumoNuevo', true)
            ->set('nombreInsumo', 'Alcohol 70 Grados')
            ->set('unidadInsumo', 'frasco')
            ->set('tipoInsumo', 'antiseptico_desinfectante')
            ->set('presentacionInsumo', 'Frasco 1 Litro')
            ->set('marcaLaboratorioInsumo', 'Laboratorios Med')
            ->set('monto', 25.00)
            ->set('fecha', now()->toDateString())
            ->set('cantidadInsumo', 4)
            ->set('numeroLoteInsumo', '001')
            ->set('proveedorInsumo', 'Botica San Martin')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $createdInsumo = Insumo::where('nombre', 'Alcohol 70 Grados')->first();
        $this->assertNotNull($createdInsumo);
        $this->assertEquals('frasco', $createdInsumo->unidad_stock);
        $this->assertEquals('antiseptico_desinfectante', $createdInsumo->tipo);

        $lot = InsumoLote::where('insumo_id', $createdInsumo->id)->first();
        $this->assertNotNull($lot);
        $this->assertEquals('INS26-001', $lot->numero_lote);
        $this->assertEquals(4, (float) $lot->cantidad_inicial);
        $this->assertEquals(25.00, (float) $lot->costo_total);
        $this->assertEquals('Botica San Martin', $lot->proveedor);
    }

    public function test_deleting_insumo_also_deletes_its_lots_and_linked_finanzas_movements(): void
    {
        $purchases = app(\App\Services\InsumoPurchaseService::class);

        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Guantes de Nitrilo',
            'tipo' => 'material_descartable',
            'unidad_stock' => 'caja',
            'stock_minimo' => 2,
            'activo' => true,
        ]);

        $lot = $purchases->createLot($insumo, [
            'fundo_id' => $this->fundo->id,
            'codigo_anio' => 2026,
            'codigo_numero' => 1,
            'fecha_ingreso' => now()->toDateString(),
            'cantidad_inicial' => 5,
            'costo_total' => 50.00,
            'proveedor' => 'Distribuidora Médica',
        ], 'compra', $this->user->id);

        $this->assertNotNull($lot->movimiento_id);
        $this->assertDatabaseHas('insumo_lotes', ['id' => $lot->id]);
        $this->assertDatabaseHas('movimientos', ['id' => $lot->movimiento_id]);

        $purchases->deleteInsumo($insumo);

        $this->assertDatabaseMissing('insumos', ['id' => $insumo->id]);
        $this->assertDatabaseMissing('insumo_lotes', ['id' => $lot->id]);
        $this->assertSoftDeleted('movimientos', ['id' => $lot->movimiento_id]);
    }

    public function test_deleting_movement_in_finanzas_also_deletes_linked_insumo_lot(): void
    {
        $purchases = app(\App\Services\InsumoPurchaseService::class);

        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Gasa Quirúrgica',
            'tipo' => 'material_descartable',
            'unidad_stock' => 'paquete',
            'activo' => true,
        ]);

        $lot = $purchases->createLot($insumo, [
            'fundo_id' => $this->fundo->id,
            'codigo_anio' => 2026,
            'codigo_numero' => 2,
            'fecha_ingreso' => now()->toDateString(),
            'cantidad_inicial' => 10,
            'costo_total' => 80.00,
            'proveedor' => 'FarmaVet',
        ], 'compra', $this->user->id);

        $movementId = $lot->movimiento_id;
        $this->assertNotNull($movementId);

        Livewire::test(\App\Livewire\Finanzas\Index::class)
            ->call('deleteMovimiento', $movementId)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('movimientos', ['id' => $movementId]);
        $this->assertDatabaseMissing('insumo_lotes', ['id' => $lot->id]);
    }

    public function test_can_delete_insumo_from_insumos_index(): void
    {
        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Algodón Plisado',
            'tipo' => 'material_descartable',
            'unidad_stock' => 'rollo',
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Insumos\Index::class)
            ->call('deleteInsumo', $insumo->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('insumos', ['id' => $insumo->id]);
    }

    public function test_can_open_medicamentos_pdf_modal_and_download_report(): void
    {
        $med = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Ivermectina 1%',
            'tipo' => 'antiparasitario',
            'unidad_stock' => 'ml',
            'stock_minimo' => 50,
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Medicamentos\Index::class)
            ->call('openMedicamentosPdfModal')
            ->assertSet('showMedicamentosPdfModal', true)
            ->set('medicamentosPdfSections', ['medicamentos', 'insumos'])
            ->set('medicamentosPdfColumns', [
                'medicamentos' => ['nombre', 'tipo', 'stock', 'estado'],
                'insumos' => ['nombre', 'tipo', 'stock', 'estado'],
            ])
            ->call('downloadMedicamentosReport')
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();
    }

    public function test_can_open_insumos_pdf_modal_and_download_report(): void
    {
        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Guantes Quirúrgicos',
            'tipo' => 'material_descartable',
            'unidad_stock' => 'par',
            'stock_minimo' => 10,
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Insumos\Index::class)
            ->call('openInsumosPdfModal')
            ->assertSet('showInsumosPdfModal', true)
            ->set('insumosPdfSections', ['insumos'])
            ->set('insumosPdfColumns', [
                'insumos' => ['nombre', 'tipo', 'stock', 'estado'],
            ])
            ->call('downloadInsumosReport')
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();
    }

    public function test_can_filter_medicamentos_by_date_and_order(): void
    {
        $med1 = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Amoxicilina 20%',
            'tipo' => 'antibiotico',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);
        $med2 = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Vitamina AD3E',
            'tipo' => 'vitamina_mineral',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);

        MedicamentoLote::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $med1->id,
            'numero_lote' => 'MET26-001',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 100,
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Medicamentos\Index::class)
            ->set('orden', 'nombre_desc')
            ->call('setPresetVencimiento', '30d')
            ->assertSet('vencimientoDesde', today()->toDateString())
            ->assertSet('vencimientoHasta', today()->addDays(30)->toDateString())
            ->assertSee('Amoxicilina 20%')
            ->call('resetFilters')
            ->assertSet('orden', 'reciente')
            ->assertSet('vencimientoDesde', '')
            ->assertSet('vencimientoHasta', '');
    }

    public function test_can_filter_insumos_by_date_and_order(): void
    {
        $insumo = Insumo::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Alcohol Yodado 1L',
            'tipo' => 'antiseptico_desinfectante',
            'unidad_stock' => 'litro',
            'activo' => true,
        ]);

        InsumoLote::create([
            'fundo_id' => $this->fundo->id,
            'insumo_id' => $insumo->id,
            'numero_lote' => 'INS26-001',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Insumos\Index::class)
            ->set('orden', 'stock_desc')
            ->call('setPresetVencimiento', '30d')
            ->assertSet('vencimientoDesde', today()->toDateString())
            ->assertSet('vencimientoHasta', today()->addDays(30)->toDateString())
            ->assertSee('Alcohol Yodado 1L')
            ->call('resetFilters')
            ->assertSet('orden', 'reciente');
    }

    public function test_can_filter_aplicaciones_by_period_presets(): void
    {
        Livewire::test(\App\Livewire\Medicamentos\Index::class)
            ->set('tab', 'aplicaciones')
            ->call('setPeriodoAplicacion', 'mes')
            ->assertSet('periodoAplicacion', 'mes')
            ->assertSet('fechaDesdeAplicacion', now()->startOfMonth()->toDateString())
            ->assertSet('fechaHastaAplicacion', now()->endOfMonth()->toDateString())
            ->call('resetAplicacionFilters')
            ->assertSet('periodoAplicacion', 'todos')
            ->assertSet('fechaDesdeAplicacion', '')
            ->assertSet('fechaHastaAplicacion', '');
    }

    public function test_insumos_form_and_finanzas_form_fields_are_unified_and_synced(): void
    {
        $catInsumos = CategoriaFinanciera::firstOrCreate(
            ['fundo_id' => $this->fundo->id, 'nombre' => 'Compra de Insumos y Materiales'],
            ['tipo' => 'egreso', 'activo' => true]
        );

        // 1. Create new insumo purchase via Finanzas MovimientoForm
        Livewire::test(FinanzasMovimientoForm::class)
            ->set('tipo', 'egreso')
            ->set('categoriaId', (string) $catInsumos->id)
            ->set('monto', 120.50)
            ->set('fecha', now()->toDateString())
            ->set('modoInsumoNuevo', true)
            ->set('nombreInsumo', 'Yodopovidona 10% Solución')
            ->set('tipoInsumo', 'antiseptico_desinfectante')
            ->set('unidadInsumo', 'frasco')
            ->set('marcaLaboratorioInsumo', 'Laboratorios Alfa')
            ->set('presentacionInsumo', 'Frasco x 1 Litro')
            ->set('cantidadInsumo', 10)
            ->set('numeroLoteInsumo', '001')
            ->set('fechaVencimientoInsumo', now()->addYears(2)->toDateString())
            ->set('proveedorInsumo', 'Distribuidora Farmavet')
            ->set('comprobanteInsumo', 'F001-9988')
            ->set('ubicacionInsumo', 'Botiquín Central - Repisa 2')
            ->set('condicionAlmacenamientoInsumo', 'protegido_luz')
            ->set('stockMinimoInsumo', 3)
            ->set('observacionesInsumo', 'Mantener en frasco opaco cerrado.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $insumo = Insumo::where('nombre', 'Yodopovidona 10% Solución')->first();
        $this->assertNotNull($insumo);
        $this->assertEquals('antiseptico_desinfectante', $insumo->tipo);
        $this->assertEquals('frasco', $insumo->unidad_stock);
        $this->assertEquals('Laboratorios Alfa', $insumo->marca_laboratorio);
        $this->assertEquals('Frasco x 1 Litro', $insumo->presentacion);
        $this->assertEquals('protegido_luz', $insumo->condicion_almacenamiento);
        $this->assertEquals(3, (float) $insumo->stock_minimo);
        $this->assertEquals('Mantener en frasco opaco cerrado.', $insumo->observaciones);

        $lote = InsumoLote::where('insumo_id', $insumo->id)->first();
        $this->assertNotNull($lote);
        $this->assertEquals('INS26-001', $lote->numero_lote);
        $this->assertEquals('Distribuidora Farmavet', $lote->proveedor);
        $this->assertEquals('F001-9988', $lote->comprobante);
        $this->assertEquals('Botiquín Central - Repisa 2', $lote->ubicacion);
        $this->assertNotNull($lote->movimiento_id);

        $mov = Movimiento::find($lote->movimiento_id);
        $this->assertNotNull($mov);
        $this->assertEquals(120.50, (float) $mov->monto);

        // 2. Open in Insumos Form (Edit mode)
        Livewire::test(InsumoForm::class, ['id' => $insumo->id])
            ->assertSet('nombre', 'Yodopovidona 10% Solución')
            ->assertSet('tipo', 'antiseptico_desinfectante')
            ->assertSet('unidadStock', 'frasco')
            ->assertSet('marcaLaboratorio', 'Laboratorios Alfa')
            ->assertSet('presentacion', 'Frasco x 1 Litro')
            ->assertSet('condicionAlmacenamiento', 'protegido_luz')
            ->assertSet('stockMinimo', 3)
            ->assertSet('observaciones', 'Mantener en frasco opaco cerrado.')
            ->assertSet('numeroLote', '001')
            ->assertSet('ubicacion', 'Botiquín Central - Repisa 2')
            ->assertSet('proveedor', 'Distribuidora Farmavet')
            ->assertSet('comprobante', 'F001-9988')
            ->set('observaciones', 'Actualizado: Uso exclusivo pecuario.')
            ->set('condicionAlmacenamiento', 'refrigerado_2_8')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $insumo->refresh();
        $this->assertEquals('Actualizado: Uso exclusivo pecuario.', $insumo->observaciones);
        $this->assertEquals('refrigerado_2_8', $insumo->condicion_almacenamiento);

        // 3. Open in Finanzas MovimientoForm (Edit mode)
        Livewire::test(FinanzasMovimientoForm::class, ['movId' => $mov->id])
            ->assertSet('nombreInsumo', 'Yodopovidona 10% Solución')
            ->assertSet('tipoInsumo', 'antiseptico_desinfectante')
            ->assertSet('unidadInsumo', 'frasco')
            ->assertSet('marcaLaboratorioInsumo', 'Laboratorios Alfa')
            ->assertSet('presentacionInsumo', 'Frasco x 1 Litro')
            ->assertSet('condicionAlmacenamientoInsumo', 'refrigerado_2_8')
            ->assertSet('stockMinimoInsumo', 3)
            ->assertSet('observacionesInsumo', 'Actualizado: Uso exclusivo pecuario.')
            ->assertSet('proveedorInsumo', 'Distribuidora Farmavet')
            ->assertSet('comprobanteInsumo', 'F001-9988')
            ->assertSet('ubicacionInsumo', 'Botiquín Central - Repisa 2');
    }
}


