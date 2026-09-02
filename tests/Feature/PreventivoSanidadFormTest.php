<?php

namespace Tests\Feature;

use App\Livewire\Monitoreo\SanidadForm;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Models\SanidadRegistro;
use App\Models\User;
use App\Support\ImageFrame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PreventivoSanidadFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_animals_doses_and_photos_are_saved_and_synced_on_edit(): void
    {
        Storage::fake('local');
        [$user, $fundo, $animals] = $this->context();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $doseTwo = now()->addDay()->toDateString();

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'vacunacion')
            ->assertSet('subtipo', 'rutina')
            ->assertSee('Producto y aplicación')
            ->set('animalIds', $animals->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->set('productoOpcion', 'otro')
            ->set('productoMarca', 'Vacuna Triple')
            ->set('dosisCantidad', '2 ml')
            ->set('viaAdministracion', 'subcutanea')
            ->set('numeroAplicaciones', 2)
            ->set('intervaloDias', 1)
            ->set('sintomasDiagnostico', 'Calendario anual del fundo')
            ->set('responsable', 'Dr. Frank')
            ->set('dosisPlan', [
                ['fecha_programada' => now()->toDateString(), 'aplicada' => true, 'fecha_aplicada' => now()->toDateString()],
                ['fecha_programada' => $doseTwo, 'aplicada' => false, 'fecha_aplicada' => ''],
            ])
            ->set('fotos', [
                UploadedFile::fake()->image('uno.jpg', 1200, 900),
                UploadedFile::fake()->image('dos.jpg', 1200, 900),
                UploadedFile::fake()->image('tres.jpg', 1200, 900),
            ])
            ->set('fotoEncuadres.1', ['x' => 81.0, 'y' => 23.0, 'zoom' => 1.4])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('monitoreo.index'));

        $event = SanidadRegistro::with(['dosisPlan', 'fotos'])->where('categoria_salud', 'vacunacion')->firstOrFail();
        $this->assertSame('lote', $event->alcance);
        $this->assertSame('vacuna', $event->tipo_intervencion);
        $this->assertSame('vacuna triple', $event->producto_marca);
        $this->assertSame($doseTwo, $event->proxima_dosis->toDateString());
        $this->assertCount(2, $event->dosisPlan);
        $this->assertDatabaseCount('alertas_programadas', 2);
        $this->assertDatabaseMissing('alertas_programadas', ['tratamiento_dosis_id' => null]);
        $this->assertCount(3, $event->fotos);
        $this->assertSame(['x' => 81.0, 'y' => 23.0, 'zoom' => 1.4], ImageFrame::normalize($event->fotos[1]->encuadre));

        $newDoseTwo = now()->addDays(3)->toDateString();
        $firstPhotoId = $event->fotos->first()->id;
        Livewire::test(SanidadForm::class, ['id' => $event->id])
            ->assertSet('motivoAtencion', 'vacunacion')
            ->set('animalIds', [(string) $animals->first()->id])
            ->set('dosisPlan', [
                ['fecha_programada' => now()->toDateString(), 'aplicada' => true, 'fecha_aplicada' => now()->toDateString()],
                ['fecha_programada' => $newDoseTwo, 'aplicada' => false, 'fecha_aplicada' => ''],
            ])
            ->set('existingPhotoFrames.'.$firstPhotoId, ['x' => 16.0, 'y' => 84.0, 'zoom' => 1.6])
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh()->load(['dosisPlan', 'animal']);
        $this->assertSame('individual', $event->alcance);
        $this->assertSame($newDoseTwo, $event->proxima_dosis->toDateString());
        $this->assertCount(2, $event->dosisPlan);
        $this->assertSame((int) $animals->first()->id, (int) $event->animal_id);
        $this->assertSame(['x' => 16.0, 'y' => 84.0, 'zoom' => 1.6], ImageFrame::normalize($event->fotos()->findOrFail($firstPhotoId)->encuadre));

        $staleEditor = Livewire::test(SanidadForm::class, ['id' => $event->id]);
        $event->fotos()->findOrFail($firstPhotoId)->update(['encuadre' => ['x' => 45.0, 'y' => 55.0, 'zoom' => 1.2]]);
        $staleEditor->set('responsable', 'Edición sin tocar fotos')->call('save')->assertHasNoErrors();
        $this->assertSame(
            ['x' => 45.0, 'y' => 55.0, 'zoom' => 1.2],
            ImageFrame::normalize($event->fotos()->findOrFail($firstPhotoId)->encuadre)
        );

        Livewire::test(SanidadForm::class, ['id' => $event->id])
            ->set('existingPhotos', [])
            ->set('fotos', [UploadedFile::fake()->image('cuarta.jpg', 1200, 900)])
            ->call('save')
            ->assertHasErrors('fotos');
        $this->assertCount(3, $event->fotos()->get());
        $this->assertCount(3, Storage::disk('local')->allFiles('monitoreo/sanidad'));
    }

    public function test_product_application_requires_product_and_rejects_invalid_motive(): void
    {
        [$user, $fundo, $animals] = $this->context();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'parasitos_internos')
            ->set('animalIds', [(string) $animals->first()->id])
            ->set('productoOpcion', 'otro')
            ->set('productoMarca', '')
            ->set('dosisCantidad', '5 ml')
            ->set('viaAdministracion', 'subcutanea')
            ->call('save')
            ->assertHasErrors('productoMarca');

        Livewire::test(SanidadForm::class)
            ->set('animalIds', [(string) $animals->first()->id])
            ->set('motivoAtencion', 'motivo_manipulado')
            ->call('save')
            ->assertHasErrors('motivoAtencion');
    }

    private function context(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo preventivo', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Simmental', 'activo' => true]);
        $animals = collect();
        foreach ([1, 2] as $sequence) {
            $animals->push(Animal::create([
                'fundo_id' => $fundo->id,
                'especie_id' => $species->id,
                'raza_id' => $breed->id,
                'arete' => 'BOV'.now()->format('y').'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'codigo_prefijo' => 'BOV',
                'codigo_anio' => now()->year,
                'codigo_secuencia' => $sequence,
                'nombre' => 'Animal '.$sequence,
                'genero' => 'hembra',
                'estado_productivo' => 'produccion',
                'tipo_alta' => 'compra',
                'fecha_alta' => now()->subYears(3)->toDateString(),
                'edad_estimada_meses_alta' => 24,
                'activo' => true,
            ]));
        }

        return [$user, $fundo, $animals];
    }
}
