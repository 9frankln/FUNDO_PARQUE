<?php

namespace Tests\Feature;

use App\Livewire\Animal\Form as AnimalForm;
use App\Livewire\Animal\Index as AnimalIndex;
use App\Livewire\Animal\Show as AnimalShow;
use App\Livewire\Auditoria\Index as AuditoriaIndex;
use App\Livewire\Buscador;
use App\Livewire\Dashboard;
use App\Livewire\Engorde\Index as EngordeIndex;
use App\Livewire\Engorde\LoteForm;
use App\Livewire\Engorde\Show as EngordeShow;
use App\Livewire\Finanzas\Index as FinanzasIndex;
use App\Livewire\Finanzas\MovimientoForm;
use App\Livewire\Finanzas\MovimientoShow;
use App\Livewire\Insumos\Form as InsumosForm;
use App\Livewire\Insumos\Index as InsumosIndex;
use App\Livewire\Insumos\Show as InsumosShow;
use App\Livewire\Leche\Form as LecheForm;
use App\Livewire\Leche\Index as LecheIndex;
use App\Livewire\Leche\Show as LecheShow;
use App\Livewire\Medicamentos\Form as MedicamentosForm;
use App\Livewire\Medicamentos\Index as MedicamentosIndex;
use App\Livewire\Medicamentos\Show as MedicamentosShow;
use App\Livewire\Monitoreo\Index as MonitoreoIndex;
use App\Livewire\Monitoreo\PartoForm;
use App\Livewire\Monitoreo\SanidadForm;
use App\Livewire\Queso\Form as QuesoForm;
use App\Livewire\Queso\Index as QuesoIndex;
use App\Livewire\Queso\Show as QuesoShow;
use App\Models\Animal;
use App\Models\CategoriaFinanciera;
use App\Models\EngordeAnimal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Insumo;
use App\Models\InsumoLote;
use App\Models\LoteEngorde;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\Movimiento;
use App\Models\Ordeno;
use App\Models\Parto;
use App\Models\ProduccionQueso;
use App\Models\Raza;
use App\Models\SanidadRegistro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ComprehensiveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Fundo $fundo;

    protected Especie $especie;

    protected Raza $raza;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'ADMIN TEST', 'username' => 'admintest']);
        $this->fundo = Fundo::create(['nombre' => 'FUNDO TEST COMPREHENSIVE', 'activo' => true]);
        $this->user->fundos()->attach($this->fundo->id, ['es_administrador' => true]);

        $this->especie = Especie::create([
            'nombre' => 'Bovino',
            'codigo_animal' => 'BOV',
            'prefijo_codigo' => 'BOV',
            'activo' => true,
        ]);
        $this->raza = Raza::create([
            'especie_id' => $this->especie->id,
            'nombre' => 'Holstein',
            'activo' => true,
        ]);

        CategoriaFinanciera::create(['fundo_id' => $this->fundo->id, 'nombre' => 'Ventas de Ganado', 'tipo' => 'ingreso']);
        CategoriaFinanciera::create(['fundo_id' => $this->fundo->id, 'nombre' => 'Medicamentos y Veterinaria', 'tipo' => 'egreso']);
        CategoriaFinanciera::create(['fundo_id' => $this->fundo->id, 'nombre' => 'Insumos y Materiales', 'tipo' => 'egreso']);

        $this->actingAs($this->user);
        session(['fundo_id' => $this->fundo->id]);
    }

    public function test_full_animal_lifecycle_and_actions(): void
    {
        // 1. Crear Vaca
        Livewire::test(AnimalForm::class)
            ->set('especieId', $this->especie->id)
            ->set('razaId', $this->raza->id)
            ->set('codigoNumero', '001')
            ->set('genero', 'hembra')
            ->set('nombre', 'CORONA')
            ->set('tipoAlta', 'parto')
            ->set('fechaNacimiento', '2024-05-10')
            ->set('peso', 480.00)
            ->set('aptaOrdeno', true)
            ->call('save')
            ->assertHasNoErrors();

        $animal = Animal::where('fundo_id', $this->fundo->id)->where('nombre', 'CORONA')->firstOrFail();
        $this->assertEquals('hembra', $animal->genero);
        $this->assertEquals(480.00, (float) $animal->peso);

        // 2. Index de Animales (filtros y exportación)
        Livewire::test(AnimalIndex::class)
            ->set('search', 'CORONA')
            ->set('genero', 'hembra')
            ->set('activo', '1')
            ->assertSee('CORONA')
            ->set('exportFormat', 'pdf')
            ->call('exportar')
            ->assertOk();

        // 3. Show de Animal
        Livewire::test(AnimalShow::class, ['id' => $animal->id])
            ->assertOk()
            ->assertSee('CORONA');

        // 4. Edición de Animal
        Livewire::test(AnimalForm::class, ['id' => $animal->id])
            ->set('nombre', 'CORONA REINA')
            ->set('peso', 495.00)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('CORONA REINA', $animal->fresh()->nombre);
    }

    public function test_full_engorde_lifecycle_batch_weighing_and_liquidation(): void
    {
        // Crear animales para el lote
        $toro1 = Animal::create([
            'fundo_id' => $this->fundo->id,
            'especie_id' => $this->especie->id,
            'raza_id' => $this->raza->id,
            'genero' => 'macho',
            'arete' => 'BOV-26-001',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => 2026,
            'codigo_secuencia' => 1,
            'nombre' => 'TORO ALFA',
            'fecha_alta' => '2026-08-15',
            'fecha_nacimiento' => '2025-01-01',
            'peso' => 320.00,
            'activo' => true,
        ]);
        $toro2 = Animal::create([
            'fundo_id' => $this->fundo->id,
            'especie_id' => $this->especie->id,
            'raza_id' => $this->raza->id,
            'genero' => 'macho',
            'arete' => 'BOV-26-002',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => 2026,
            'codigo_secuencia' => 2,
            'nombre' => 'TORO BETA',
            'fecha_alta' => '2026-08-15',
            'fecha_nacimiento' => '2025-01-01',
            'peso' => 340.00,
            'activo' => true,
        ]);

        // 1. Crear Lote de Engorde
        Livewire::test(LoteForm::class)
            ->set('nombre', 'LOTE ENGORDE AGOSTO')
            ->set('fechaInicio', '2026-08-10')
            ->call('save')
            ->assertHasNoErrors();

        $lote = LoteEngorde::where('fundo_id', $this->fundo->id)->firstOrFail();

        // 2. Agregar animales al lote con fecha histórica 2026-08-12 (antes de fecha_alta de los animales)
        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->set('selectedAnimals', [$toro1->id => true, $toro2->id => true])
            ->set('fechasIngreso', [$toro1->id => '2026-08-12', $toro2->id => '2026-08-12'])
            ->set('pesosIniciales', [$toro1->id => 320.00, $toro2->id => 340.00])
            ->call('agregarAnimales')
            ->assertHasNoErrors();

        $this->assertEquals(2, $lote->animales()->count());
        $this->assertEquals('2026-08-12', $toro1->fresh()->fecha_alta->format('Y-m-d'));

        // 3. Registrar pesajes de control
        $engorde1 = EngordeAnimal::where('lote_id', $lote->id)->where('animal_id', $toro1->id)->firstOrFail();
        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->call('openLogWeightModal', $engorde1->id)
            ->set('nuevoPeso', 380.00)
            ->set('fechaPesaje', '2026-08-20')
            ->call('registrarPesaje')
            ->assertHasNoErrors();

        // 4. Liquidar / Vender lote
        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->set('animalesAVender', [$toro1->id => true, $toro2->id => true])
            ->set('preciosAnimales', [$toro1->id => 3200.00, $toro2->id => 3200.00])
            ->set('compradorVenta', 'FRIGORIFICO CENTRAL')
            ->set('fechaVenta', '2026-08-21')
            ->set('montoVenta', '6400.00')
            ->call('liquidarVentaLote')
            ->assertHasNoErrors();

        $lote->refresh();
        $this->assertEquals('cerrado', $lote->estado);

        // Verificar sincronización con Finanzas
        $movimiento = Movimiento::where('fundo_id', $this->fundo->id)->where('tipo', 'ingreso')->firstOrFail();
        $this->assertEquals(6400.00, (float) $movimiento->monto);
    }

    public function test_full_sanidad_multidose_and_partos(): void
    {
        $vaca = Animal::create([
            'fundo_id' => $this->fundo->id,
            'especie_id' => $this->especie->id,
            'raza_id' => $this->raza->id,
            'genero' => 'hembra',
            'arete' => 'BOV-26-010',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => 2026,
            'codigo_secuencia' => 10,
            'nombre' => 'MARGARITA',
            'fecha_alta' => '2026-08-01',
            'fecha_nacimiento' => '2023-01-01',
            'peso' => 510.00,
            'apta_ordeno' => true,
            'activo' => true,
        ]);

        // 1. Crear Registro Sanitario
        Livewire::test(SanidadForm::class)
            ->set('animalIds', [$vaca->id])
            ->set('fechaEvento', '2026-08-18')
            ->set('motivoAtencion', 'respiratorio')
            ->set('sintomasDiagnostico', 'Tos y dificultad respiratoria leve')
            ->call('save')
            ->assertHasNoErrors();

        $sanidad = SanidadRegistro::where('fundo_id', $this->fundo->id)->where('animal_id', $vaca->id)->firstOrFail();
        $this->assertEquals('enfermedad', $sanidad->categoria_salud);

        // 2. Registrar Parto
        Livewire::test(PartoForm::class)
            ->set('animalMadreId', $vaca->id)
            ->set('criaRazaId', $this->raza->id)
            ->set('fechaParto', '2026-08-20')
            ->set('tipoParto', 'normal')
            ->set('criaSexo', 'hembra')
            ->set('criaPesoNacer', 38.50)
            ->call('save')
            ->assertHasNoErrors();

        $parto = Parto::where('fundo_id', $this->fundo->id)->where('animal_madre_id', $vaca->id)->firstOrFail();
        $this->assertEquals('normal', $parto->tipo_parto);
    }

    public function test_botiquin_medicamento_and_insumos_stock_flow(): void
    {
        // 1. Crear Medicamento
        Livewire::test(MedicamentosForm::class)
            ->set('nombre', 'IVERMECTINA 1%')
            ->set('tipo', 'antiparasitario')
            ->set('principioActivo', 'Ivermectina')
            ->set('unidadStock', 'frasco')
            ->set('agregarExistencia', false)
            ->call('save')
            ->assertHasNoErrors();

        $med = Medicamento::where('fundo_id', $this->fundo->id)->firstOrFail();

        // 2. Registrar lote de medicamento
        MedicamentoLote::create([
            'fundo_id' => $this->fundo->id,
            'medicamento_id' => $med->id,
            'codigo_lote' => 'MET26-001',
            'numero_lote' => '001',
            'fecha_ingreso' => '2026-08-10',
            'fecha_vencimiento' => '2028-08-10',
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'costo_total' => 200.00,
            'proveedor' => 'VET PERU',
            'estado' => 'disponible',
        ]);

        // 3. Crear Insumo
        Livewire::test(InsumosForm::class)
            ->set('nombre', 'JERINGA DESCARTABLE 20ML')
            ->set('tipo', 'material_descartable')
            ->set('unidadStock', 'unidad')
            ->set('agregarExistencia', false)
            ->call('save')
            ->assertHasNoErrors();

        $insumo = Insumo::where('fundo_id', $this->fundo->id)->firstOrFail();

        // 4. Registrar lote de insumo
        InsumoLote::create([
            'fundo_id' => $this->fundo->id,
            'insumo_id' => $insumo->id,
            'codigo_lote' => 'INS26-001',
            'numero_lote' => '001',
            'fecha_ingreso' => '2026-08-15',
            'fecha_vencimiento' => '2029-08-15',
            'cantidad_inicial' => 50,
            'cantidad_disponible' => 50,
            'costo_total' => 75.00,
            'proveedor' => 'MEDISUPPLY',
            'estado' => 'disponible',
        ]);

        $this->assertEquals(10, $med->lotes()->sum('cantidad_disponible'));
        $this->assertEquals(50, $insumo->lotes()->sum('cantidad_disponible'));
    }

    public function test_leche_queso_and_finanzas_flow(): void
    {
        $vaca = Animal::create([
            'fundo_id' => $this->fundo->id,
            'especie_id' => $this->especie->id,
            'raza_id' => $this->raza->id,
            'genero' => 'hembra',
            'arete' => 'BOV-26-020',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => 2026,
            'codigo_secuencia' => 20,
            'nombre' => 'PRINCESA',
            'fecha_alta' => '2026-08-01',
            'fecha_nacimiento' => '2023-01-01',
            'peso' => 500.00,
            'apta_ordeno' => true,
            'activo' => true,
        ]);

        // 1. Control Lechero
        Livewire::test(LecheForm::class)
            ->set('fecha', '2026-08-21')
            ->set('tipoRegistro', 'lote')
            ->set('cantidadVacas', 1)
            ->set('litrosTotal', '25.0')
            ->call('save')
            ->assertHasNoErrors();

        $ordeno = Ordeno::where('fundo_id', $this->fundo->id)->firstOrFail();
        $this->assertEquals(25.0, (float) $ordeno->litros_total);

        // 2. Producción de Queso
        Livewire::test(QuesoForm::class)
            ->set('fecha', '2026-08-21')
            ->set('litrosLeche', 25.0)
            ->set('presentaciones', [
                ['peso_gramos' => '1000', 'cantidad' => 2],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $queso = ProduccionQueso::where('fundo_id', $this->fundo->id)->firstOrFail();
        $this->assertEquals(25.0, (float) $queso->litros_leche_usados);

        // 3. Movimiento Financiero
        $cat = CategoriaFinanciera::where('fundo_id', $this->fundo->id)->where('tipo', 'ingreso')->firstOrFail();
        Livewire::test(MovimientoForm::class)
            ->set('tipo', 'ingreso')
            ->set('categoriaId', $cat->id)
            ->set('monto', 44.00)
            ->set('fecha', '2026-08-21')
            ->set('dineroProviene', 'VENTA DIRECTA QUESOS')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Movimiento::where('fundo_id', $this->fundo->id)->where('monto', 44.00)->exists());
    }
}
