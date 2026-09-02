<?php

namespace Tests\Feature;

use App\Livewire\Medicamentos\Form as MedicamentoForm;
use App\Livewire\Medicamentos\Index as MedicamentosIndex;
use App\Livewire\Medicamentos\Show as MedicamentoShow;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\MedicamentoMovimiento;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\SanidadRegistro;
use App\Models\TratamientoDosis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicamentosAplicacionesTest extends TestCase
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

    public function test_can_switch_tabs_in_medicamentos_index(): void
    {
        Livewire::test(MedicamentosIndex::class)
            ->assertSet('tab', 'inventario')
            ->assertSee('Medicamentos')
            ->call('setTab', 'insumos')
            ->assertSet('tab', 'insumos')
            ->assertSee('Insumos y Materiales')
            ->call('setTab', 'aplicaciones')
            ->assertSet('tab', 'aplicaciones')
            ->assertSee('Historial de aplicaciones de medicamentos a animales');
    }

    public function test_applications_tab_shows_animal_and_dosage_data(): void
    {
        $animal = Animal::factory()->create([
            'fundo_id' => $this->fundo->id,
            'arete' => 'BOV-101',
            'nombre' => 'Esperanza',
            'activo' => true,
        ]);

        $medicine = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Oxitetraciclina 200',
            'tipo' => 'antibiotico',
            'unidad_stock' => 'ml',
            'stock_minimo' => 50,
            'condicion_almacenamiento' => 'ambiente',
            'activo' => true,
        ]);

        $lote = MedicamentoLote::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $medicine->id,
            'numero_lote' => 'MET26-001',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 85,
            'activo' => true,
        ]);

        $caso = SanidadRegistro::factory()->create([
            'fundo_id' => $this->fundo->id,
            'animal_id' => $animal->id,
            'sintomas_diagnostico' => 'Neumonía aguda',
        ]);

        $dosis = TratamientoDosis::create([
            'fundo_id' => $this->fundo->id,
            'sanidad_registro_id' => $caso->id,
            'numero' => 1,
            'medicamento_id' => $medicine->id,
            'medicamento_nombre' => $medicine->nombre,
            'dosis' => '15 ml',
            'cantidad_inventario' => 15,
            'unidad_inventario' => 'ml',
            'via' => 'intramuscular',
            'fecha_programada' => now()->toDateString(),
            'fecha_aplicada' => now()->toDateString(),
            'aplicada' => true,
            'responsable' => 'Dr. Carlos Mendoza',
        ]);

        MedicamentoMovimiento::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $medicine->id,
            'medicamento_lote_id' => $lote->id,
            'animal_id' => $animal->id,
            'tratamiento_dosis_id' => $dosis->id,
            'user_id' => $this->user->id,
            'tipo' => 'aplicacion',
            'fecha_hora' => now(),
            'cantidad' => -15,
            'unidad' => 'ml',
            'saldo_lote' => 85,
            'detalle' => 'Dosis 1 para Neumonía aguda',
        ]);

        Livewire::test(MedicamentosIndex::class, ['tab' => 'aplicaciones'])
            ->assertSee('BOV-101')
            ->assertSee('ESPERANZA')
            ->assertSee('Oxitetraciclina 200')
            ->assertSee('MET26-001')
            ->assertSee('15 ml')
            ->assertSee('dr. carlos mendoza');
    }

    public function test_can_filter_applications_by_search_term_and_medicine(): void
    {
        $animalA = Animal::factory()->create(['fundo_id' => $this->fundo->id, 'arete' => 'CAP-001', 'activo' => true]);
        $animalB = Animal::factory()->create(['fundo_id' => $this->fundo->id, 'arete' => 'CAP-002', 'activo' => true]);

        $medA = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Ivermectina Gold',
            'tipo' => 'antiparasitario',
            'unidad_stock' => 'ml',
            'stock_minimo' => 10,
            'condicion_almacenamiento' => 'ambiente',
            'activo' => true,
        ]);

        $medB = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Complejo B Forte',
            'tipo' => 'vitamina_mineral',
            'unidad_stock' => 'ml',
            'stock_minimo' => 10,
            'condicion_almacenamiento' => 'ambiente',
            'activo' => true,
        ]);

        $loteA = MedicamentoLote::create([
            'fundo_id' => $this->fundo->id, 'medicamento_id' => $medA->id,
            'numero_lote' => 'MET26-001', 'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(), 'cantidad_inicial' => 50,
            'cantidad_disponible' => 45, 'activo' => true,
        ]);

        $loteB = MedicamentoLote::create([
            'fundo_id' => $this->fundo->id, 'medicamento_id' => $medB->id,
            'numero_lote' => 'MET26-002', 'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(), 'cantidad_inicial' => 50,
            'cantidad_disponible' => 40, 'activo' => true,
        ]);

        MedicamentoMovimiento::create([
            'fundo_id' => $this->fundo->id, 'medicamento_id' => $medA->id, 'medicamento_lote_id' => $loteA->id,
            'animal_id' => $animalA->id, 'user_id' => $this->user->id, 'tipo' => 'aplicacion',
            'fecha_hora' => now(), 'cantidad' => -5, 'unidad' => 'ml', 'saldo_lote' => 45, 'detalle' => 'Desparasitación',
        ]);

        MedicamentoMovimiento::create([
            'fundo_id' => $this->fundo->id, 'medicamento_id' => $medB->id, 'medicamento_lote_id' => $loteB->id,
            'animal_id' => $animalB->id, 'user_id' => $this->user->id, 'tipo' => 'aplicacion',
            'fecha_hora' => now(), 'cantidad' => -10, 'unidad' => 'ml', 'saldo_lote' => 40, 'detalle' => 'Vitamina',
        ]);

        Livewire::test(MedicamentosIndex::class, ['tab' => 'aplicaciones'])
            ->set('searchAplicacion', 'CAP-001')
            ->assertSee('CAP-001')
            ->assertDontSee('CAP-002')
            ->set('searchAplicacion', '')
            ->set('medicamentoAplicacionId', (string) $medB->id)
            ->assertSee('CAP-002')
            ->assertDontSee('CAP-001');
    }

    public function test_can_register_veterinary_medicine_with_extra_details(): void
    {
        Livewire::test(MedicamentoForm::class)
            ->set('nombre', 'Amoxicilina 15% L.A.')
            ->set('tipo', 'antibiotico')
            ->set('unidadStock', 'ml')
            ->set('stockMinimo', 20)
            ->set('condicionAlmacenamiento', 'refrigerado_2_8')
            ->set('viaPredeterminada', 'intramuscular')
            ->set('observaciones', 'Agitar bien antes de usar')
            ->set('agregarExistencia', true)
            ->set('tipoIngreso', 'compra')
            ->set('numeroLote', '001')
            ->set('fechaIngreso', now()->toDateString())
            ->set('fechaVencimiento', now()->addYears(2)->toDateString())
            ->set('cantidadInicial', 100)
            ->set('costoTotal', 45.50)
            ->set('proveedor', 'Distribuidora Médica Lima')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $med = Medicamento::where('nombre', 'Amoxicilina 15% L.A.')->first();
        $this->assertNotNull($med);
        $this->assertEquals('antibiotico', $med->tipo);
        $this->assertEquals('ml', $med->unidad_stock);
        $this->assertEquals('refrigerado_2_8', $med->condicion_almacenamiento);
        $this->assertEquals('intramuscular', $med->via_predeterminada);

        $lote = MedicamentoLote::where('medicamento_id', $med->id)->first();
        $this->assertNotNull($lote);
        $this->assertEquals('MET26-001', $lote->numero_lote);
        $this->assertEquals(100, $lote->cantidad_disponible);
        $this->assertEquals(45.50, $lote->costo_total);
    }

    public function test_medicamento_show_displays_applications_table(): void
    {
        $animal = Animal::factory()->create(['fundo_id' => $this->fundo->id, 'arete' => 'OVI-55', 'activo' => true]);

        $med = Medicamento::create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Tintura de Yodo 5%',
            'tipo' => 'antiseptico',
            'unidad_stock' => 'frasco',
            'stock_minimo' => 2,
            'condicion_almacenamiento' => 'ambiente',
            'activo' => true,
        ]);

        $lote = MedicamentoLote::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $med->id,
            'numero_lote' => 'MET26-001',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYears(3)->toDateString(),
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 9,
            'activo' => true,
        ]);

        MedicamentoMovimiento::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $med->id,
            'medicamento_lote_id' => $lote->id,
            'animal_id' => $animal->id,
            'user_id' => $this->user->id,
            'tipo' => 'aplicacion',
            'fecha_hora' => now(),
            'cantidad' => -1,
            'unidad' => 'frasco',
            'saldo_lote' => 9,
            'detalle' => 'Curación de herida',
        ]);

        Livewire::test(MedicamentoShow::class, ['id' => $med->id])
            ->assertSee('Historial de aplicaciones en animales')
            ->assertSee('OVI-55')
            ->assertSee('Curación de herida')
            ->assertSee('MET26-001')
            ->assertSee('1 frasco');
    }
}
