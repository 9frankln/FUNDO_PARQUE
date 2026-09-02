<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\EngordeAnimal;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\Movimiento;
use App\Models\Ordeno;
use App\Models\OrdenoDetalle;
use App\Models\Parto;
use App\Models\PesajeEngorde;
use App\Models\ProduccionQueso;
use App\Models\SanidadRegistro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_factories_create_consistent_tenant_records(): void
    {
        $fundo = Fundo::factory()->create();
        $animal = Animal::factory()->create(['fundo_id' => $fundo->id]);
        $ordeno = Ordeno::factory()->create(['fundo_id' => $fundo->id]);
        $detail = OrdenoDetalle::factory()->create([
            'ordeno_id' => $ordeno->id,
            'animal_id' => $animal->id,
        ]);
        $movement = Movimiento::factory()->create(['fundo_id' => $fundo->id]);
        $cheese = ProduccionQueso::factory()->create(['fundo_id' => $fundo->id]);
        $health = SanidadRegistro::factory()->create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
        ]);
        $birth = Parto::factory()->create([
            'fundo_id' => $fundo->id,
            'animal_madre_id' => Animal::factory()->female()->create(['fundo_id' => $fundo->id])->id,
        ]);
        $lot = LoteEngorde::factory()->create(['fundo_id' => $fundo->id]);
        $fattening = EngordeAnimal::factory()->create([
            'lote_id' => $lot->id,
            'animal_id' => $animal->id,
        ]);
        $weight = PesajeEngorde::factory()->create(['engorde_animal_id' => $fattening->id]);

        $this->assertSame($fundo->id, $animal->fundo_id);
        $this->assertSame($ordeno->id, $detail->ordeno_id);
        $this->assertSame($fundo->id, $movement->fundo_id);
        $this->assertSame($fundo->id, $cheese->fundo_id);
        $this->assertSame($animal->id, $health->animal_id);
        $this->assertSame($fundo->id, $birth->fundo_id);
        $this->assertSame($lot->id, $fattening->lote_id);
        $this->assertSame($fattening->id, $weight->engorde_animal_id);
    }
}
