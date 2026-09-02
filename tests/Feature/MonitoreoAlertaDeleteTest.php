<?php

namespace Tests\Feature;

use App\Livewire\Monitoreo\Index;
use App\Models\AlertaProgramada;
use App\Models\Animal;
use App\Models\AuditoriaLog;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonitoreoAlertaDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_delete_a_programmed_alert(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $alerta = AlertaProgramada::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo' => 'cuarentena',
            'fecha_alerta' => now()->toDateString(),
            'mensaje' => 'Aislar animal por sospecha de enfermedad.',
            'leida' => false,
        ]);

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->assertSet('puedeBorrarAlertas', true)
            ->call('openDeleteAlertaModal', $alerta->id)
            ->assertSet('showDeleteAlertaModal', true)
            ->assertSet('deleteAlertaId', $alerta->id)
            ->assertSet('deleteAlertaData.fecha', now()->format('d/m/Y'))
            ->assertSet('deleteAlertaData.animal', $animal->arete)
            ->assertSet('deleteAlertaData.tipo', 'cuarentena')
            ->call('deleteAlerta')
            ->assertSet('showDeleteAlertaModal', false)
            ->assertSet('deleteAlertaId', null);

        $this->assertDatabaseMissing('alertas_programadas', ['id' => $alerta->id]);
        // El animal NO se borra (la alerta era independiente)
        $this->assertDatabaseHas('animales', ['id' => $animal->id]);
        // Se registró auditoría
        $this->assertDatabaseHas('auditoria_logs', [
            'accion' => 'alerta.eliminada',
            'modulo' => 'monitoreo',
        ]);
    }

    public function test_non_administrator_cannot_open_or_run_delete(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $alerta = AlertaProgramada::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo' => 'proxima_dosis',
            'fecha_alerta' => now()->toDateString(),
            'mensaje' => 'Próxima dosis programada.',
            'leida' => false,
        ]);

        // Usuario estándar (no admin) del mismo fundo
        $standard = User::factory()->create();
        $standard->fundos()->attach($fundo, ['es_administrador' => false]);

        $this->actingAs($standard)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->assertSet('puedeBorrarAlertas', false);

        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->call('openDeleteAlertaModal', $alerta->id)
            ->assertForbidden();

        // La alerta sigue existiendo y no se borró
        $this->assertDatabaseHas('alertas_programadas', ['id' => $alerta->id]);
    }

    public function test_delete_cannot_touch_alert_from_another_fundo(): void
    {
        [$admin, $fundo] = $this->administratorWithFundo();
        $otherFundo = Fundo::create(['nombre' => 'Otro fundo', 'activo' => true]);
        $otherAnimal = $this->animal($otherFundo);
        $otherAlert = AlertaProgramada::create([
            'fundo_id' => $otherFundo->id,
            'animal_id' => $otherAnimal->id,
            'tipo' => 'parto',
            'fecha_alerta' => now()->toDateString(),
            'mensaje' => 'Alerta de otro fundo.',
            'leida' => false,
        ]);

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        // Como admin solo ve el modal con su propia alerta; intentar borrar una
        // alerta ajena no encuentra nada (scoped por fundo) y no la borra.
        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->set('deleteAlertaId', $otherAlert->id)
            ->call('deleteAlerta')
            ->assertSet('showDeleteAlertaModal', false);

        $this->assertDatabaseHas('alertas_programadas', ['id' => $otherAlert->id]);
    }

    public function test_administrator_can_select_and_bulk_delete_alertas(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $ids = collect();
        foreach ([1, 2, 3] as $i) {
            $ids->push(AlertaProgramada::create([
                'fundo_id' => $fundo->id,
                'animal_id' => $animal->id,
                'tipo' => 'proxima_dosis',
                'fecha_alerta' => now()->addDays($i)->toDateString(),
                'mensaje' => "Dosis programada {$i}.",
                'leida' => false,
            ])->id);
        }
        // Una alerta que NO se seleccionará
        $keepId = AlertaProgramada::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo' => 'cuarentena',
            'fecha_alerta' => now()->addDays(10)->toDateString(),
            'mensaje' => 'Cuarentena de control.',
            'leida' => false,
        ])->id;

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->assertSet('puedeBorrarAlertas', true)
            ->call('toggleAlertaSeleccion', $ids[0])
            ->call('toggleAlertaSeleccion', $ids[1])
            ->call('toggleAlertaSeleccion', $ids[2])
            ->assertSet('selectedAlertas', $ids->all())
            // Al togglear de nuevo se deselecciona
            ->call('toggleAlertaSeleccion', $ids[1])
            ->assertSet('selectedAlertas', [$ids[0], $ids[2]])
            // Re-seleccionar el tercero (se agrega al final)
            ->call('toggleAlertaSeleccion', $ids[1])
            ->assertSet('selectedAlertas', [$ids[0], $ids[2], $ids[1]])
            ->call('openDeleteAlertasMasivoModal', 'seleccion')
            ->assertSet('showDeleteAlertasMasivoModal', true)
            ->assertSet('deleteAlertasMasivoCount', 3)
            ->assertSet('deleteAlertasMasivoMode', 'seleccion')
            ->call('deleteAlertasMasivo')
            ->assertSet('showDeleteAlertasMasivoModal', false)
            ->assertSet('selectedAlertas', []);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('alertas_programadas', ['id' => $id]);
        }
        // La no seleccionada sigue existiendo
        $this->assertDatabaseHas('alertas_programadas', ['id' => $keepId]);
        // El animal intacto
        $this->assertDatabaseHas('animales', ['id' => $animal->id]);
        // Auditoría masiva
        $this->assertDatabaseHas('auditoria_logs', [
            'accion' => 'alertas.eliminadas_masivo',
            'modulo' => 'monitoreo',
        ]);
    }

    public function test_administrator_can_bulk_delete_filtered_alertas(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $leidas = collect();
        foreach ([1, 2, 3] as $i) {
            $leidas->push(AlertaProgramada::create([
                'fundo_id' => $fundo->id,
                'animal_id' => $animal->id,
                'tipo' => 'proxima_dosis',
                'fecha_alerta' => now()->addDays($i)->toDateString(),
                'mensaje' => "Dosis programada {$i}.",
                'leida' => true,
            ])->id);
        }
        $pendiente = AlertaProgramada::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo' => 'cuarentena',
            'fecha_alerta' => now()->addDays(10)->toDateString(),
            'mensaje' => 'Cuarentena pendiente.',
            'leida' => false,
        ])->id;

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        // Filtro: solo leídas → borrar todas las filtradas
        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->set('alertaFiltroLeida', '1')
            ->call('openDeleteAlertasMasivoModal', 'filtradas')
            ->assertSet('showDeleteAlertasMasivoModal', true)
            ->assertSet('deleteAlertasMasivoCount', 3)
            ->assertSet('deleteAlertasMasivoMode', 'filtradas')
            ->call('deleteAlertasMasivo')
            ->assertSet('showDeleteAlertasMasivoModal', false);

        foreach ($leidas as $id) {
            $this->assertDatabaseMissing('alertas_programadas', ['id' => $id]);
        }
        // La pendiente (fuera del filtro) permanece
        $this->assertDatabaseHas('alertas_programadas', ['id' => $pendiente]);
    }

    public function test_bulk_delete_with_no_selection_shows_warning_and_keeps_data(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $alerta = AlertaProgramada::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo' => 'sanidad',
            'fecha_alerta' => now()->toDateString(),
            'mensaje' => 'Alerta de sanidad.',
            'leida' => false,
        ]);

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        // Sin selección: no abre modal y no borra nada
        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->call('openDeleteAlertasMasivoModal', 'seleccion')
            ->assertSet('showDeleteAlertasMasivoModal', false)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('alertas_programadas', ['id' => $alerta->id]);
    }

    public function test_non_administrator_cannot_bulk_delete(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $alerta = AlertaProgramada::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo' => 'parto',
            'fecha_alerta' => now()->toDateString(),
            'mensaje' => 'Alerta de parto.',
            'leida' => false,
        ]);

        $standard = User::factory()->create();
        $standard->fundos()->attach($fundo, ['es_administrador' => false]);
        $this->actingAs($standard)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['tab' => 'alertas'])
            ->call('toggleAlertaSeleccion', $alerta->id)
            ->assertSet('selectedAlertas', [])
            ->call('openDeleteAlertasMasivoModal', 'seleccion')
            ->assertForbidden();

        $this->assertDatabaseHas('alertas_programadas', ['id' => $alerta->id]);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo alertas', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    private function animal(Fundo $fundo): Animal
    {
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Holstein', 'activo' => true]);

        return Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-001',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'fecha_alta' => now()->subYears(2)->toDateString(),
            'edad_estimada_meses_alta' => 24,
            'activo' => true,
        ]);
    }
}
