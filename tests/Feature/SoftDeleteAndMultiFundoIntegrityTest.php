<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\EngordeAnimal;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\Ordeno;
use App\Models\OrdenoDetalle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteAndMultiFundoIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_ordeno_unique_constraint_allows_new_record_after_soft_delete(): void
    {
        $fundo = Fundo::create(['nombre' => 'Fundo Test SoftDelete', 'activo' => true]);

        $ordeno1 = Ordeno::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-08-02',
            'turno' => 'manana',
            'tipo_registro' => 'individual',
            'litros_total' => 100,
        ]);

        $this->assertDatabaseHas('ordenos', ['id' => $ordeno1->id]);

        $ordeno1->delete();

        $ordeno2 = Ordeno::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-08-02',
            'turno' => 'manana',
            'tipo_registro' => 'individual',
            'litros_total' => 150,
        ]);

        $this->assertDatabaseHas('ordenos', ['id' => $ordeno2->id]);
    }

    public function test_cross_fundo_assignment_throws_invalid_argument_exception(): void
    {
        $fundoA = Fundo::create(['nombre' => 'Fundo Alfa Integrity', 'activo' => true]);
        $fundoB = Fundo::create(['nombre' => 'Fundo Beta Integrity', 'activo' => true]);

        $animalA = Animal::factory()->create([
            'fundo_id' => $fundoA->id,
        ]);

        $ordenoB = Ordeno::create([
            'fundo_id' => $fundoB->id,
            'fecha' => '2026-08-02',
            'turno' => 'manana',
            'litros_total' => 50,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        OrdenoDetalle::create([
            'ordeno_id' => $ordenoB->id,
            'animal_id' => $animalA->id,
            'litros' => 10,
        ]);
    }

    public function test_cross_fundo_engorde_throws_exception(): void
    {
        $fundoA = Fundo::create(['nombre' => 'Fundo A Engorde', 'activo' => true]);
        $fundoB = Fundo::create(['nombre' => 'Fundo B Engorde', 'activo' => true]);

        $loteA = LoteEngorde::create([
            'fundo_id' => $fundoA->id,
            'codigo' => 'LOTE-2026-001',
            'nombre' => 'Lote Alfa',
            'fecha_inicio' => '2026-08-01',
            'estado' => 'activo',
        ]);

        $animalB = Animal::factory()->create([
            'fundo_id' => $fundoB->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        EngordeAnimal::create([
            'lote_id' => $loteA->id,
            'animal_id' => $animalB->id,
            'peso_inicial' => 200,
            'peso_actual' => 210,
            'fecha_ingreso' => '2026-08-01',
        ]);
    }
}
