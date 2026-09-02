<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_species_distribution_from_the_species_relation(): void
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
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

        Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $especie->id,
            'raza_id' => $raza->id,
            'arete' => 'BOV26-001',
            'nombre' => 'Luna',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'fecha_alta' => today(),
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Centro de operaciones')
            ->assertSee('Indicadores esenciales')
            ->assertSee('Áreas disponibles')
            ->assertSee('Bovino');
    }

    public function test_dashboard_renders_all_modules_and_metrics_successfully(): void
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo Completo', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        $especie = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $raza = Raza::create(['especie_id' => $especie->id, 'nombre' => 'Brown Swiss', 'activo' => true]);

        $madre = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $especie->id,
            'raza_id' => $raza->id,
            'arete' => 'BOV26-002',
            'nombre' => 'Estrella',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'fecha_alta' => today(),
            'activo' => true,
        ]);

        $cria = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $especie->id,
            'raza_id' => $raza->id,
            'arete' => 'BOV26-003',
            'nombre' => 'Cría Uno',
            'genero' => 'macho',
            'estado_productivo' => 'cria',
            'tipo_alta' => 'parto',
            'fecha_alta' => today(),
            'activo' => true,
        ]);

        \App\Models\Parto::create([
            'fundo_id' => $fundo->id,
            'animal_madre_id' => $madre->id,
            'cria_animal_id' => $cria->id,
            'fecha_parto' => today(),
            'tipo_parto' => 'normal',
            'cria_sexo' => 'macho',
            'cria_peso_nacer' => 38.5,
            'cria_estado' => 'vivo_vigoroso',
            'condicion_madre' => 'optima',
        ]);

        \App\Models\SanidadRegistro::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $madre->id,
            'categoria_salud' => 'control',
            'subtipo' => 'rutina',
            'severidad' => 'leve',
            'estado_seguimiento' => 'en_seguimiento',
            'fecha_evento' => today(),
            'tipo_evento' => 'curativo',
            'clasificacion' => 'enfermedad_infecciosa',
            'sintomas_diagnostico' => 'chequeo general',
        ]);

        $med = \App\Models\Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Oxitetraciclina 200',
            'tipo' => 'antibiotico',
            'presentacion' => 'Frasco 100ml',
            'unidad_stock' => 'ml',
            'stock_minimo' => 50,
            'activo' => true,
        ]);

        \App\Models\MedicamentoLote::create([
            'fundo_id' => $fundo->id,
            'medicamento_id' => $med->id,
            'numero_lote' => 'LOT-2026-01',
            'fecha_ingreso' => today(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 30,
            'fecha_vencimiento' => today()->addDays(15),
            'activo' => true,
        ]);

        $ins = \App\Models\Insumo::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Jeringas 20ml',
            'categoria' => 'material_medico',
            'unidad_medida' => 'unidad',
            'stock_minimo' => 20,
            'activo' => true,
        ]);

        \App\Models\InsumoLote::create([
            'fundo_id' => $fundo->id,
            'insumo_id' => $ins->id,
            'numero_lote' => 'INS-LOT-01',
            'fecha_ingreso' => today(),
            'cantidad_inicial' => 50,
            'cantidad_disponible' => 10,
            'fecha_vencimiento' => today()->addDays(20),
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Centro de operaciones')
            ->assertSee('Áreas disponibles')
            ->assertSee('Insumos y Materiales');
    }
}
