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
}
