<?php

namespace Tests\Feature;

use App\Livewire\Finanzas\AsignacionForm;
use App\Livewire\Finanzas\Index as FinanzasIndex;
use App\Models\AsignacionFamiliar;
use App\Models\Fundo;
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
        $existing = AsignacionFamiliar::create([
            'fundo_id' => $fundo->id,
            'beneficiario' => 'Registro anterior',
            'monto' => 100,
            'moneda' => 'PEN',
            'fecha' => now()->toDateString(),
            'proposito' => 'estudio',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AsignacionForm::class)
            ->set('beneficiario', 'Registro reciente')
            ->set('monto', 250)
            ->set('fecha', now()->subDay()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $recent = AsignacionFamiliar::where('beneficiario', 'REGISTRO RECIENTE')->firstOrFail();
        Livewire::test(FinanzasIndex::class)
            ->assertSet('tab', 'asignaciones')
            ->assertSet('recentRecord.id', $recent->id)
            ->assertSet('recentRecord.action', 'created')
            ->assertViewHas('asignaciones', fn ($rows) => $rows->first()->is($recent))
            ->call('clearRecentRecord')
            ->assertViewHas('asignaciones', fn ($rows) => $rows->first()->is($existing));

        Livewire::test(AsignacionForm::class, ['id' => $recent->id])
            ->set('monto', 300)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(FinanzasIndex::class)
            ->assertSet('tab', 'asignaciones')
            ->assertSet('recentRecord.action', 'updated')
            ->assertViewHas('asignaciones', fn ($rows) => $rows->first()->is($recent));
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
