<?php

namespace Tests\Feature;

use App\Livewire\Buscador;
use App\Livewire\Finanzas\MovimientoForm;
use App\Models\Animal;
use App\Models\CategoriaFinanciera;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Movimiento;
use App\Models\Permiso;
use App\Models\Raza;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SearchAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_hides_unauthorized_modules(): void
    {
        [$user, $fundo] = $this->userWithFundo();
        $role = Role::create(['nombre' => 'Buscador animal', 'fundo_id' => $fundo->id]);
        $role->permisos()->attach([
            Permiso::firstOrCreate(['modulo' => 'buscador', 'accion' => 'leer'])->id,
            Permiso::firstOrCreate(['modulo' => 'animal', 'accion' => 'leer'])->id,
        ]);
        $user->roles()->attach($role);
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Criollo', 'activo' => true]);
        Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-001',
            'nombre' => 'Objetivo animal',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'fecha_alta' => now()->toDateString(),
            'activo' => true,
        ]);
        $category = CategoriaFinanciera::create([
            'tipo' => 'egreso',
            'nombre' => 'Prueba',
            'activo' => true,
        ]);
        DB::table('movimientos')->insert([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'categoria_id' => $category->id,
            'monto' => 10,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'descripcion' => 'Objetivo financiero',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('auditoria_logs')->insert([
            'fundo_id' => $fundo->id,
            'user_id' => $user->id,
            'accion' => 'Objetivo auditoría',
            'modulo' => 'prueba',
            'detalle' => 'Objetivo restringido',
            'created_at' => now(),
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Buscador::class)
            ->set('search', 'Objetivo')
            ->assertSet('resultados', fn ($results) => collect($results)->pluck('tipo')->all() === ['animal']);
    }

    public function test_shared_financial_categories_are_visible_but_other_fundo_categories_are_not(): void
    {
        $global = CategoriaFinanciera::create([
            'fundo_id' => null,
            'tipo' => 'egreso',
            'nombre' => 'Global',
            'activo' => true,
        ]);
        [$user, $fundo] = $this->userWithFundo(true);
        $otherFundo = Fundo::create(['nombre' => 'Otro fundo', 'activo' => true]);
        $current = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Local',
            'activo' => true,
        ]);
        $other = CategoriaFinanciera::create([
            'fundo_id' => $otherFundo->id,
            'tipo' => 'egreso',
            'nombre' => 'Ajena',
            'activo' => true,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        // El scope global expone categorías globales (fundo_id null) + las del fundo actual.
        // Existe la categoría global "Asignación Familiar" (seed de migración), además de
        // $global y $current; la categoría de otro fundo ($other) queda oculta.
        $visibleIds = CategoriaFinanciera::pluck('id')->all();
        $this->assertContains($global->id, $visibleIds);
        $this->assertContains($current->id, $visibleIds);
        $this->assertNotContains($other->id, $visibleIds);
        $this->assertContains(
            CategoriaFinanciera::where('fundo_id', null)->where('nombre', 'Asignación Familiar')->value('id'),
            $visibleIds
        );

        $movement = Movimiento::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'categoria_id' => $global->id,
            'monto' => 25,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
        ]);
        $this->assertTrue($movement->categoria->is($global));

        Livewire::test(MovimientoForm::class)
            ->assertSet('categorias', fn ($categories) => collect($categories)->pluck('id')->contains($global->id));
    }

    public function test_global_search_uses_current_animal_cheese_and_finance_schema(): void
    {
        [$user, $fundo] = $this->userWithFundo(true);
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Holstein', 'activo' => true]);
        Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-010',
            'nombre' => 'Objetivo animal',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'fecha_alta' => now()->toDateString(),
            'activo' => true,
        ]);
        DB::table('producciones_queso')->insert([
            'fundo_id' => $fundo->id,
            'fecha' => now()->toDateString(),
            'unidades' => 12,
            'peso_total_kg' => 9.5,
            'observaciones' => 'Objetivo quesero',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $category = CategoriaFinanciera::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'ingreso',
            'nombre' => 'Venta objetivo',
            'activo' => true,
        ]);
        Movimiento::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'ingreso',
            'categoria_id' => $category->id,
            'monto' => 150,
            'fecha' => now()->toDateString(),
            'descripcion' => 'Objetivo financiero',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Buscador::class)
            ->set('search', 'Objetivo')
            ->assertSet('resultados', function ($results) {
                $types = collect($results)->pluck('tipo')->unique()->all();

                return in_array('animal', $types, true)
                    && in_array('queso', $types, true)
                    && in_array('finanzas', $types, true);
            })
            ->call('setCategoria', 'queso')
            ->assertSet('resultados', fn ($results) => collect($results)->pluck('tipo')->unique()->all() === ['queso'])
            ->set('search', now()->format('d/m/Y'))
            ->assertSet('resultados', fn ($results) => collect($results)->pluck('tipo')->unique()->all() === ['queso'])
            ->call('clearSearch')
            ->assertSet('search', '')
            ->assertSet('resultados', []);
    }

    private function userWithFundo(bool $administrator = false): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => $administrator]);

        return [$user, $fundo];
    }
}
