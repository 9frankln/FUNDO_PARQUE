<?php

namespace Tests\Feature;

use App\Livewire\Animal\Show;
use App\Livewire\Monitoreo\Index;
use App\Livewire\Monitoreo\SanidadForm;
use App\Models\AlertaProgramada;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Medicamento;
use App\Models\Raza;
use App\Models\SanidadRegistro;
use App\Models\TratamientoDosis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonitoreoTratamientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanidad_form_saves_one_product_schedule_and_withdrawal_data(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $medicine = Medicamento::create(['fundo_id' => $fundo->id, 'nombre' => 'Oxitetraciclina LA', 'activo' => true]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'tratamiento_indicado')
            ->assertSee('Producto y aplicación')
            ->set('animalIds', [(string) $animal->id])
            ->set('sintomasDiagnostico', 'MASTITIS CLÍNICA')
            ->set('productoOpcion', 'med:'.$medicine->id)
            ->set('dosisCantidad', '10 ml')
            ->set('viaAdministracion', 'intramamaria')
            ->set('numeroAplicaciones', 3)
            ->set('intervaloDias', 1)
            ->set('retiroCarneDias', 28)
            ->set('retiroLecheHoras', 96)
            ->set('dosisPlan', [
                ['fecha_programada' => now()->toDateString(), 'aplicada' => true, 'fecha_aplicada' => now()->toDateString()],
                ['fecha_programada' => now()->addDay()->toDateString(), 'aplicada' => false, 'fecha_aplicada' => ''],
                ['fecha_programada' => now()->addDays(2)->toDateString(), 'aplicada' => false, 'fecha_aplicada' => ''],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('monitoreo.index'));

        $event = SanidadRegistro::firstOrFail();
        $this->assertDatabaseCount('tratamiento_dosis', 3);
        $d1 = TratamientoDosis::where('sanidad_registro_id', $event->id)->where('numero', 1)->firstOrFail();
        $this->assertSame($medicine->id, $d1->medicamento_id);
        $this->assertTrue($d1->aplicada);
        $this->assertSame('intramamaria', $d1->via);
        $d2 = TratamientoDosis::where('sanidad_registro_id', $event->id)->where('numero', 2)->firstOrFail();
        $this->assertSame($medicine->id, $d2->medicamento_id);
        $this->assertNull($d2->medicamento_nombre);
        $this->assertFalse($d2->aplicada);
        $this->assertSame(now()->addDay()->toDateString(), $d2->fecha_programada->toDateString());
        $this->assertSame(28, $event->retiro_carne_dias);
        $this->assertSame(96, $event->retiro_leche_horas);
        $this->assertDatabaseCount('alertas_programadas', 2);
        $this->assertDatabaseHas('animales', ['id' => $animal->id]);
    }

    public function test_editing_case_replaces_schedule_using_shared_product_fields(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $event = SanidadRegistro::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'fecha_evento' => now()->toDateString(),
            'categoria_salud' => 'enfermedad',
            'subtipo' => 'sistemica',
            'clasificacion' => 'enfermedad_infecciosa',
            'sintomas_diagnostico' => 'Fiebre alta',
            'estado_clinico' => 'en_tratamiento',
        ]);
        $event->dosisPlan()->create([
            'fundo_id' => $fundo->id,
            'numero' => 1,
            'medicamento_nombre' => 'Antiguo',
            'dosis' => '1 ml',
            'via' => 'Oral',
            'fecha_programada' => now()->toDateString(),
            'aplicada' => true,
            'fecha_aplicada' => now()->toDateString(),
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class, ['id' => $event->id])
            ->assertSet('isEdit', true)
            ->assertCount('dosisPlan', 1)
            ->set('productoOpcion', 'otro')
            ->set('productoMarca', 'Nuevo')
            ->set('dosisCantidad', '2 ml')
            ->set('viaAdministracion', 'subcutanea')
            ->set('dosisPlan', [[
                'fecha_programada' => now()->toDateString(),
                'aplicada' => true,
                'fecha_aplicada' => now()->toDateString(),
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh();
        $this->assertDatabaseCount('tratamiento_dosis', 1);
        $this->assertSame('nuevo', $event->dosisPlan->first()->medicamento_nombre);
        $this->assertSame('en_tratamiento', $event->estado_clinico);
        $this->assertSame('en_seguimiento', $event->estado_seguimiento);
    }

    public function test_administrator_can_mark_dose_applied_and_recover_case(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $event = SanidadRegistro::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'fecha_evento' => now()->toDateString(),
            'clasificacion' => 'lesion_accidente',
            'sintomas_diagnostico' => 'Herida en pata',
            'estado_clinico' => 'en_tratamiento',
        ]);
        $event->dosisPlan()->create([
            'fundo_id' => $fundo->id,
            'numero' => 1,
            'medicamento_nombre' => 'Curita',
            'dosis' => '1',
            'fecha_programada' => now()->toDateString(),
            'aplicada' => true,
            'fecha_aplicada' => now()->toDateString(),
        ]);
        $d2 = $event->dosisPlan()->create([
            'fundo_id' => $fundo->id,
            'numero' => 2,
            'medicamento_nombre' => 'Curita',
            'dosis' => '1',
            'fecha_programada' => now()->addDay()->toDateString(),
            'aplicada' => false,
        ]);
        AlertaProgramada::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo' => 'cuarentena',
            'fecha_alerta' => now()->addDays(7)->toDateString(),
            'mensaje' => 'Control de cuarentena.',
            'leida' => false,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['tab' => 'sanidad'])
            ->call('marcarDosisAplicada', $d2->id)
            ->assertDispatched('swal:toast');
        $this->assertDatabaseHas('tratamiento_dosis', ['id' => $d2->id, 'aplicada' => true]);

        Livewire::test(Index::class, ['tab' => 'sanidad'])
            ->call('openRecuperarCasoModal', $event->id)
            ->assertSet('showRecuperarCasoModal', true)
            ->assertSet('recuperarCasoData.arete', $animal->arete)
            ->set('recuperarCasoFecha', now()->toDateString())
            ->set('recuperarCasoObservaciones', 'EVOLUCIÓN FAVORABLE')
            ->call('confirmarRecuperacion')
            ->assertSet('showRecuperarCasoModal', false)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('sanidad_registros', [
            'id' => $event->id,
            'estado_seguimiento' => 'completado',
            'estado_clinico' => 'recuperada',
            'observaciones_cierre' => 'evolución favorable',
        ]);
        $this->assertNotNull($event->refresh()->fecha_cierre);
        $this->assertDatabaseHas('alertas_programadas', ['tipo' => 'cuarentena', 'leida' => true]);
    }

    public function test_form_exposes_one_professional_reason_instead_of_technical_status_fields(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->assertSee('Nueva atención de salud')
            ->assertSee('¿Qué vas a registrar?')
            ->assertSeeInOrder(['Detecté', 'Apliqué', 'Realicé', 'Revisé'])
            ->assertDontSee('id="motivo-atencion"', false)
            ->assertDontSee('Tipo específico')
            ->assertDontSee('Baja (Muerte / Descarte sanitario)')
            ->call('selectMotive', 'vacunacion')
            ->assertSet('mostrarSelectorMotivo', false)
            ->assertSee('Motivo elegido')
            ->call('showMotivePicker')
            ->assertSet('mostrarSelectorMotivo', true)
            ->assertSee('¿Qué vas a registrar?');
    }

    public function test_minimal_problem_is_classified_without_written_observations(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'respiratorio')
            ->set('animalIds', [(string) $animal->id])
            ->set('nivelAtencion', 'vigilar')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sanidad_registros', [
            'animal_id' => $animal->id,
            'categoria_salud' => 'enfermedad',
            'subtipo' => 'respiratoria',
            'severidad' => 'moderada',
            'estado_seguimiento' => 'en_seguimiento',
        ]);
    }

    public function test_animal_show_builds_unified_timeline(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $event = SanidadRegistro::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'fecha_evento' => now()->toDateString(),
            'clasificacion' => 'enfermedad_infecciosa',
            'sintomas_diagnostico' => 'Neumonía',
            'estado_clinico' => 'en_tratamiento',
        ]);
        $event->dosisPlan()->create([
            'fundo_id' => $fundo->id,
            'numero' => 1,
            'medicamento_nombre' => 'Amoxicilina',
            'dosis' => '5 ml',
            'via' => 'IM',
            'fecha_programada' => now()->toDateString(),
            'aplicada' => true,
            'fecha_aplicada' => now()->toDateString(),
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $component = Livewire::test(Show::class, ['id' => $animal->id])
            ->assertSet('estadoClinicoActual', 'tratamiento')
            ->assertSet('timeline.0.tipo', 'salud')
            ->assertSet('timeline.0.titulo', 'Otro evento')
            ->assertSee('Línea de tiempo de salud')
            ->assertSee('D1 amoxicilina · Aplicada');

        $this->assertCount(1, $component->get('timeline'));
    }

    public function test_deleting_sanidad_record_reverts_inventory_stock(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $medicine = Medicamento::create(['fundo_id' => $fundo->id, 'nombre' => 'Enrofloxacina 10%', 'unidad_stock' => 'ml', 'activo' => true]);
        $lot = \App\Models\MedicamentoLote::create([
            'fundo_id' => $fundo->id,
            'medicamento_id' => $medicine->id,
            'numero_lote' => 'MET26-001',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 50,
            'cantidad_disponible' => 50,
            'activo' => true,
        ]);

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'tratamiento_indicado')
            ->set('animalIds', [(string) $animal->id])
            ->set('sintomasDiagnostico', 'NEUMONÍA')
            ->set('productoOpcion', 'med:'.$medicine->id)
            ->set('dosisCantidad', '10 ml')
            ->set('viaAdministracion', 'intramuscular')
            ->set('numeroAplicaciones', 1)
            ->set('intervaloDias', 1)
            ->set('dosisPlan', [
                ['fecha_programada' => now()->toDateString(), 'aplicada' => true, 'fecha_aplicada' => now()->toDateString()],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $lot->refresh();
        $this->assertEquals(40, (float) $lot->cantidad_disponible);

        $record = SanidadRegistro::firstOrFail();

        Livewire::test(Index::class)
            ->call('deleteSanidad', $record->id)
            ->assertHasNoErrors();

        $lot->refresh();
        $this->assertEquals(50, (float) $lot->cantidad_disponible);
        $this->assertSoftDeleted('sanidad_registros', ['id' => $record->id]);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo tratamiento', 'activo' => true]);
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
