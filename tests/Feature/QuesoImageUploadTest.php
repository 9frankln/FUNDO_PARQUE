<?php

namespace Tests\Feature;

use App\Livewire\Queso\Form;
use App\Models\Fundo;
use App\Models\ProduccionQueso;
use App\Models\User;
use App\Support\ImageFrame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QuesoImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_large_cheese_photo_is_resized_and_stored_as_webp(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('fecha', '2026-07-14')
            ->set('presentaciones', [['peso_gramos' => '1000', 'cantidad' => 15]])
            ->set('foto', UploadedFile::fake()->image('lote.jpg', 2400, 1800))
            ->set('fotoEncuadre', ['x' => 74.0, 'y' => 32.0, 'zoom' => 1.45])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('queso.index'));

        $production = ProduccionQueso::firstOrFail();
        $photoPath = $production->foto_ruta;
        $this->assertSame(['x' => 74.0, 'y' => 32.0, 'zoom' => 1.45], ImageFrame::normalize($production->foto_encuadre));
        Storage::disk('public')->assertExists($photoPath);
        $this->assertStringEndsWith('.webp', $photoPath);

        [$width, $height] = getimagesize(Storage::disk('public')->path($photoPath));
        $this->assertLessThanOrEqual(1600, max($width, $height));
    }

    public function test_cheese_record_can_be_saved_without_a_photo(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('fecha', '2026-07-14')
            ->set('presentaciones', [['peso_gramos' => '1000', 'cantidad' => 15]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(ProduccionQueso::firstOrFail()->foto_ruta);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
