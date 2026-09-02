<?php

namespace Tests\Feature;

use App\Livewire\Finanzas\Index as FinanzasIndex;
use App\Livewire\Finanzas\MovimientoForm;
use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Movimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecentRecordModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_and_updated_assignment_opens_its_tab_and_is_temporarily_pinned(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $asignacionCategory = CategoriaFinanciera::where('fundo_id', null)
            ->where('tipo', 'egreso')
            ->where('nombre', 'Asignación Familiar')
            ->firstOrFail();

        $existing = Movimiento::create([
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'categoria_id' => $asignacionCategory->id,
            'monto' => 100,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'beneficiario' => 'Registro anterior',
            'proposito' => 'estudio',
        ]);

        Livewire::test(MovimientoForm::class)
            ->set('tipo', 'egreso')
            ->set('categoriaId', (string) $asignacionCategory->id)
            ->set('beneficiario', 'Registro reciente')
            ->set('monto', 250)
            ->set('fecha', now()->subDay()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $recent = Movimiento::where('beneficiario', 'REGISTRO RECIENTE')->firstOrFail();
        Livewire::test(FinanzasIndex::class)
            ->assertSet('recentRecord.id', $recent->id)
            ->assertSet('recentRecord.action', 'created')
            ->assertViewHas('movimientos', fn ($rows) => $rows->first()->is($recent))
            ->call('clearRecentRecord')
            ->assertViewHas('movimientos', fn ($rows) => $rows->first()->is($existing));

        Livewire::test(MovimientoForm::class, ['id' => $recent->id])
            ->set('monto', 300)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(FinanzasIndex::class)
            ->assertSet('recentRecord.action', 'updated')
            ->assertViewHas('movimientos', fn ($rows) => $rows->first()->is($recent));
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
