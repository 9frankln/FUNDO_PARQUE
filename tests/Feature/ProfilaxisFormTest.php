<?php

namespace Tests\Feature;

use App\Livewire\Monitoreo\ProfilaxisForm;
use App\Models\AlertaProgramada;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\ProfilaxisRegistro;
use App\Models\Raza;
use App\Models\User;
use App\Support\ImageFrame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilaxisFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_animals_doses_and_photos_are_saved_and_synced_on_edit(): void
    {
        Storage::fake('local');
        [$user, $fundo, $animals] = $this->context();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $doseTwo = now()->addDay()->toDateString();
        $doseThree = now()->addDays(2)->toDateString();

        Livewire::test(ProfilaxisForm::class)
            ->assertSee('$wire.entangle(\'selectedAnimals\')', false)
            ->assertDontSee('Selección de Animales')
            ->assertSee('Calendario de dosis')
            ->set('selectedAnimals', $animals->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->set('productoMarca', 'Vacuna Triple')
            ->set('dosis', '2 ml')
            ->set('dosisProgramadas', [
                ['fecha' => $doseTwo],
                ['fecha' => $doseThree],
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

        $profilaxis = ProfilaxisRegistro::with(['dosisProgramadas', 'animales', 'fotos'])->firstOrFail();
        $this->assertSame('lote', $profilaxis->alcance);
        $this->assertSame($doseTwo, $profilaxis->proxima_dosis->toDateString());
        $this->assertCount(2, $profilaxis->dosisProgramadas);
        $this->assertCount(2, $profilaxis->animales);
        $this->assertCount(3, $profilaxis->fotos);
        $this->assertSame(['x' => 81.0, 'y' => 23.0, 'zoom' => 1.4], ImageFrame::normalize($profilaxis->fotos[1]->encuadre));
        $this->assertSame(4, AlertaProgramada::whereNotNull('profilaxis_dosis_id')->count());

        $newDoseTwo = now()->addDays(3)->toDateString();
        $firstPhotoId = $profilaxis->fotos->first()->id;
        Livewire::test(ProfilaxisForm::class, ['id' => $profilaxis->id])
            ->assertSet('selectedAnimals', $animals->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->set('selectedAnimals', [(string) $animals->first()->id])
            ->set('dosisProgramadas', [['fecha' => $newDoseTwo]])
            ->set('existingPhotoFrames.'.$firstPhotoId, ['x' => 16.0, 'y' => 84.0, 'zoom' => 1.6])
            ->call('save')
            ->assertHasNoErrors();

        $profilaxis->refresh()->load(['dosisProgramadas', 'animales']);
        $this->assertSame('individual', $profilaxis->alcance);
        $this->assertSame($newDoseTwo, $profilaxis->proxima_dosis->toDateString());
        $this->assertCount(1, $profilaxis->dosisProgramadas);
        $this->assertCount(1, $profilaxis->animales);
        $this->assertSame(['x' => 16.0, 'y' => 84.0, 'zoom' => 1.6], ImageFrame::normalize($profilaxis->fotos()->findOrFail($firstPhotoId)->encuadre));
        $this->assertSame(1, AlertaProgramada::whereNotNull('profilaxis_dosis_id')->count());

        $staleEditor = Livewire::test(ProfilaxisForm::class, ['id' => $profilaxis->id]);
        $profilaxis->fotos()->findOrFail($firstPhotoId)->update([
            'encuadre' => ['x' => 45.0, 'y' => 55.0, 'zoom' => 1.2],
        ]);
        $staleEditor
            ->set('responsable', 'Edición sin tocar fotos')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame(
            ['x' => 45.0, 'y' => 55.0, 'zoom' => 1.2],
            ImageFrame::normalize($profilaxis->fotos()->findOrFail($firstPhotoId)->encuadre)
        );

        Livewire::test(ProfilaxisForm::class, ['id' => $profilaxis->id])
            ->set('existingPhotos', [])
            ->set('fotos', [UploadedFile::fake()->image('cuarta.jpg', 1200, 900)])
            ->call('save')
            ->assertHasErrors('fotos');
        $this->assertCount(3, $profilaxis->fotos()->get());
        $this->assertCount(3, Storage::disk('local')->allFiles('monitoreo/profilaxis'));
    }

    public function test_scheduled_doses_must_be_in_chronological_order(): void
    {
        [$user, $fundo, $animals] = $this->context();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(ProfilaxisForm::class)
            ->set('selectedAnimals', [(string) $animals->first()->id])
            ->set('productoMarca', 'Ivermectina')
            ->set('dosisProgramadas', [
                ['fecha' => now()->addDays(3)->toDateString()],
                ['fecha' => now()->addDays(2)->toDateString()],
            ])
            ->call('save')
            ->assertHasErrors('dosisProgramadas.1.fecha');
    }

    private function context(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo profilaxis', 'activo' => true]);
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
