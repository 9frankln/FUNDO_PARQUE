<?php

namespace Tests\Feature;

use App\Livewire\Engorde\LoteForm;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\User;
use App\Support\ImageFrame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EngordeLotePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_lot_can_be_created_without_a_photo(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(LoteForm::class)
            ->assertSet('codigo', 'LOT'.now()->format('y').'-001')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('engorde.index'));

        $lot = LoteEngorde::firstOrFail();
        $this->assertSame('LOT'.now()->format('y').'-001', $lot->codigo);
        $this->assertSame(now()->year, $lot->codigo_anio);
        $this->assertSame(1, $lot->codigo_secuencia);
        $this->assertNull($lot->foto_ruta);
    }

    public function test_confirmed_large_photo_is_optimized_and_saved_with_the_lot(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(LoteForm::class)
            ->set('foto', UploadedFile::fake()->image('lote.jpg', 2400, 1800))
            ->assertNotDispatched('swal:confirm')
            ->assertSet('photoConfirmed', true)
            ->set('fotoEncuadre', ['x' => 70.0, 'y' => 35.5, 'zoom' => 1.25])
            ->call('save')
            ->assertHasNoErrors();

        $lot = LoteEngorde::firstOrFail();
        $this->assertStringStartsWith('fotos/engorde/lotes/', $lot->foto_ruta);
        $this->assertStringEndsWith('.webp', $lot->foto_ruta);
        $this->assertSame(['x' => 70.0, 'y' => 35.5, 'zoom' => 1.25], ImageFrame::normalize($lot->foto_encuadre));
        Storage::disk('public')->assertExists($lot->foto_ruta);

        [$width, $height] = getimagesize(Storage::disk('public')->path($lot->foto_ruta));
        $this->assertSame(1600, $width);
        $this->assertSame(1200, $height);
    }

    public function test_lot_codes_increment_automatically_per_year(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $prefix = 'LOT'.now()->format('y').'-';

        Livewire::test(LoteForm::class)
            ->assertSet('codigo', $prefix.'001')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(LoteForm::class)
            ->assertSet('codigo', $prefix.'002')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lotes_engorde', ['codigo' => $prefix.'001', 'codigo_secuencia' => 1]);
        $this->assertDatabaseHas('lotes_engorde', ['codigo' => $prefix.'002', 'codigo_secuencia' => 2]);
    }

    public function test_existing_photo_is_deleted_only_after_confirming_removal_and_saving(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $photoPath = 'fotos/engorde/lotes/existente.webp';
        Storage::disk('public')->put($photoPath, 'foto');
        $lot = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOTE-B',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 'activo',
            'foto_ruta' => $photoPath,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(LoteForm::class, ['id' => $lot->id])
            ->call('requestPhotoRemoval')
            ->assertDispatched('swal:confirm')
            ->assertSet('removeFoto', false)
            ->dispatch('confirmarEliminacionFotoLote')
            ->assertSet('removeFoto', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($lot->refresh()->foto_ruta);
        Storage::disk('public')->assertMissing($photoPath);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
