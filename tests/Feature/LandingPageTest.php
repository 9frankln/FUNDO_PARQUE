<?php

namespace Tests\Feature;

use App\Livewire\Admin\LandingManager;
use App\Models\Fundo;
use App\Models\LandingBlock;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Support\ImageFrame;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_uses_the_selected_fundo_and_tracks_database_changes(): void
    {
        $otherFundo = Fundo::create(['nombre' => 'Fundo reserva', 'activo' => true]);
        $publicFundo = Fundo::create([
            'nombre' => 'Fundo publicado',
            'ruc' => '20123456789',
            'direccion' => 'Camino privado 99',
            'distrito' => 'Pucara',
            'provincia' => 'Lampa',
            'departamento' => 'Puno',
            'activo' => true,
        ]);
        $this->createBlock('hero', [
            'title' => 'Portada publica',
            'settings' => array_replace(LandingBlock::defaultSettings('hero'), [
                'public_fundo_id' => $publicFundo->id,
                'show_location' => true,
                'custom_location' => 'Cusco - Canas - Kunturkanki',
                'show_address' => false,
            ]),
        ]);
        $this->createBlock('ganaderia', ['title' => 'Area ganadera publicada']);
        $this->createBlock('equinos', ['title' => 'Area equina privada', 'is_active' => false]);

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSeeText('FUNDO PUBLICADO')
            ->assertSeeText('Cusco - Canas - Kunturkanki')
            ->assertSeeText('Area ganadera publicada')
            ->assertDontSeeText('FUNDO RESERVA')
            ->assertDontSeeText('Area equina privada')
            ->assertDontSeeText('Pucara, Lampa, Puno')
            ->assertDontSeeText('20123456789')
            ->assertDontSeeText('Camino privado 99')
            ->assertViewHas('publicFundo', fn (Fundo $fundo) => ! array_key_exists('ruc', $fundo->getAttributes()) && ! array_key_exists('distrito', $fundo->getAttributes()));

        $publicFundo->update(['nombre' => 'Fundo actualizado']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('FUNDO ACTUALIZADO');
    }

    public function test_hidden_hero_and_gallery_are_not_published(): void
    {
        $this->createBlock('hero', ['title' => 'Portada privada', 'is_active' => false]);
        $content = $this->createBlock('ganaderia', ['title' => 'Ganaderia visible']);
        $this->attachMedia($content, 'ganado.jpg');
        $this->createBlock('galeria', ['title' => 'Galeria privada', 'is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('heroVisible', false)
            ->assertViewHas('galleryVisible', false)
            ->assertViewHas('heroItems', fn ($items) => $items->isEmpty())
            ->assertViewHas('galleryItems', fn ($items) => $items->isEmpty())
            ->assertDontSeeText('Portada privada')
            ->assertDontSeeText('Galeria privada');
    }

    public function test_single_hero_mode_only_publishes_the_cover_image(): void
    {
        $hero = $this->createBlock('hero', [
            'settings' => array_replace(LandingBlock::defaultSettings('hero'), ['hero_mode' => 'single']),
        ]);
        $cover = $this->attachMedia($hero, 'portada.jpg', 1);
        $this->attachMedia($hero, 'alternativa.jpg', 2);

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('heroItems', fn ($items) => $items->count() === 1 && $items->first()['id'] === (string) $cover->id);
    }

    public function test_setting_a_cover_reorders_the_editor_and_every_public_use(): void
    {
        [$admin, $fundo] = $this->administratorWithFundo();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);
        $block = $this->createBlock('ganaderia');
        $previousCover = $this->attachMedia($block, 'anterior.jpg', 1);
        $newCover = $this->attachMedia($block, 'nueva-portada.jpg', 2);
        $lastImage = $this->attachMedia($block, 'ultima.jpg', 3);

        Livewire::test(LandingManager::class)
            ->set('blocks.ganaderia.title', 'Cambio pendiente')
            ->call('setAsCover', 'ganaderia', $newCover->id)
            ->assertSet('blocks.ganaderia.media.0.id', $newCover->id)
            ->assertSet('blocks.ganaderia.title', 'Cambio pendiente')
            ->assertDispatched('swal:toast');

        $this->assertSame(1, $newCover->fresh()->order_column);
        $this->assertSame(2, $previousCover->fresh()->order_column);
        $this->assertSame(3, $lastImage->fresh()->order_column);
        $this->assertDatabaseHas('auditoria_logs', ['accion' => 'landing.portada_actualizada']);

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('contentBlocks', fn ($blocks) => $blocks->get('ganaderia')->media->first()->is($newCover))
            ->assertViewHas('heroItems', fn ($items) => $items->first()['id'] === (string) $newCover->id)
            ->assertViewHas('galleryItems', fn ($items) => $items->first()['id'] === (string) $newCover->id);
    }

    public function test_manager_saves_a_non_destructive_frame_for_every_public_format(): void
    {
        [$admin, $fundo] = $this->administratorWithFundo();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);
        $hero = $this->createBlock('hero');
        $media = $this->attachMedia($hero, 'animal-completo.jpg');

        Livewire::test(LandingManager::class)
            ->call('openFrameEditor', 'hero', $media->id)
            ->assertSet('showFrameEditor', true)
            ->assertSet('frameX', 50.0)
            ->assertSet('frameY', 50.0)
            ->assertSet('frameZoom', 1.0)
            ->assertSee('Ajustar encuadre')
            ->set('frameX', 18.5)
            ->set('frameY', 37.0)
            ->set('frameZoom', 1.35)
            ->call('saveFrame')
            ->assertHasNoErrors()
            ->assertSet('showFrameEditor', false)
            ->assertSet('blocks.hero.media.0.focus_x', 18.5)
            ->assertSet('blocks.hero.media.0.focus_y', 37.0)
            ->assertSet('blocks.hero.media.0.zoom', 1.35)
            ->assertDispatched('swal:toast');

        $media->refresh();
        $this->assertSame(18.5, (float) $media->getCustomProperty('focus_x'));
        $this->assertSame(37.0, (float) $media->getCustomProperty('focus_y'));
        $this->assertSame(1.35, (float) $media->getCustomProperty('zoom'));
        $this->assertDatabaseHas('auditoria_logs', ['accion' => 'landing.encuadre_actualizado']);

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('heroItems', fn ($items) => $items->first()['focus_x'] === 18.5
                && $items->first()['focus_y'] === 37.0
                && $items->first()['zoom'] === 1.35)
            ->assertSee('landing-story is-reversed', false);
    }

    public function test_manager_saves_public_identity_content_and_optimized_media(): void
    {
        Storage::fake('public');
        [$admin, $fundo] = $this->administratorWithFundo();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(LandingManager::class)
            ->set('blocks.hero.title', 'Una portada renovada')
            ->set('blocks.hero.content', 'Contenido institucional para visitantes.')
            ->set('blocks.hero.settings.public_fundo_id', $fundo->id)
            ->set('blocks.hero.settings.hero_mode', 'single')
            ->set('blocks.hero.settings.show_whatsapp', true)
            ->set('blocks.hero.settings.whatsapp_number', '+51 987 654 321')
            ->set('blocks.hero.settings.whatsapp_message', 'Hola desde el fundo')
            ->set('uploads.hero', [UploadedFile::fake()->image('portada.jpg', 1200, 900)])
            ->set('uploadFrames.hero.0', ['x' => 22.0, 'y' => 61.0, 'zoom' => 1.3])
            ->call('saveBlock', 'hero')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $hero = LandingBlock::query()->where('section', 'hero')->firstOrFail();
        $media = $hero->getFirstMedia('gallery');

        $this->assertSame('Una portada renovada', $hero->title);
        $this->assertSame($fundo->id, (int) $hero->settings['public_fundo_id']);
        $this->assertSame('51987654321', $hero->settings['whatsapp_number']);
        $this->assertNotNull($media);
        $this->assertTrue($media->hasGeneratedConversion('thumb'));
        $this->assertTrue($media->hasGeneratedConversion('optimized'));
        $this->assertSame(22.0, (float) $media->getCustomProperty('focus_x'));
        $this->assertSame(61.0, (float) $media->getCustomProperty('focus_y'));
        $this->assertSame(1.3, (float) $media->getCustomProperty('zoom'));
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot('thumb'));
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot('optimized'));
        $this->assertDatabaseHas('auditoria_logs', ['accion' => 'landing.seccion_actualizada']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Una portada renovada')
            ->assertSeeText($fundo->nombre)
            ->assertSee('https://wa.me/51987654321?text=Hola%20desde%20el%20fundo', false)
            ->assertSeeText('WhatsApp');
    }

    public function test_selecting_a_new_landing_batch_resets_previous_pending_frames(): void
    {
        Storage::fake('public');
        [$admin, $fundo] = $this->administratorWithFundo();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(LandingManager::class)
            ->set('uploadFrames.hero', [['x' => 5.0, 'y' => 95.0, 'zoom' => 2.0]])
            ->set('uploads.hero', [UploadedFile::fake()->image('reemplazo.jpg', 1200, 900)])
            ->assertSet('uploadFrames.hero.0', ImageFrame::DEFAULT);
    }

    public function test_manager_requires_manual_text_for_a_visible_public_location(): void
    {
        [$admin, $fundo] = $this->administratorWithFundo();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(LandingManager::class)
            ->set('blocks.hero.settings.show_location', true)
            ->set('blocks.hero.settings.custom_location', '')
            ->call('saveBlock', 'hero')
            ->assertHasErrors(['blocks.hero.settings.custom_location' => 'required'])
            ->set('blocks.hero.settings.custom_location', 'Cusco - Canas - Kunturkanki')
            ->set('blocks.hero.settings.show_whatsapp', true)
            ->set('blocks.hero.settings.whatsapp_number', '123')
            ->call('saveBlock', 'hero')
            ->assertHasErrors(['blocks.hero.settings.whatsapp_number'])
            ->set('blocks.hero.settings.whatsapp_number', '51987654321')
            ->call('saveBlock', 'hero')
            ->assertHasNoErrors();
    }

    public function test_manager_rejects_unauthorized_access_and_media_from_another_section(): void
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo restringido', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => false]);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get(route('ajustes.web'))
            ->assertForbidden();

        $user->fundos()->updateExistingPivot($fundo->id, ['es_administrador' => true]);
        $user->unsetRelation('fundos');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $component = Livewire::test(LandingManager::class);
        $foreignMedia = $this->attachMedia(
            LandingBlock::query()->where('section', 'ganaderia')->firstOrFail(),
            'otra-seccion.jpg',
        );

        try {
            $component->call('deleteMedia', 'hero', $foreignMedia->id);
            $this->fail('A media item from another section was accepted.');
        } catch (ModelNotFoundException) {
            $this->assertDatabaseHas('media', ['id' => $foreignMedia->id]);
        }
    }

    public function test_web_management_has_its_own_permission_without_granting_general_settings(): void
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo web', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => false]);
        $role = Role::create(['fundo_id' => $fundo->id, 'nombre' => 'Editor web']);
        $role->permisos()->attach(
            Permiso::query()->where('modulo', 'gestion_web')->where('accion', 'actualizar')->firstOrFail(),
        );
        $user->roles()->attach($role);

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $this->get(route('ajustes.web'))
            ->assertOk()
            ->assertSeeText('Gestión Web Pública');
        Livewire::test(LandingManager::class)
            ->assertSee('Gestión Web Pública')
            ->assertHasNoErrors();
        $this->assertFalse($user->tienePermiso('ajustes', 'actualizar'));
    }

    private function createBlock(string $section, array $overrides = []): LandingBlock
    {
        $content = LandingBlock::defaultContent($section);
        $definition = LandingBlock::sectionDefinitions()[$section];

        return LandingBlock::create(array_replace([
            'section' => $section,
            'title' => $content['title'],
            'content' => $content['content'],
            'settings' => LandingBlock::defaultSettings($section),
            'order' => $definition['order'],
            'is_active' => true,
        ], $overrides));
    }

    private function attachMedia(LandingBlock $block, string $fileName, int $order = 1): Media
    {
        return $block->media()->create([
            'collection_name' => 'gallery',
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 1024,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'order_column' => $order,
        ]);
    }

    private function administratorWithFundo(): array
    {
        $admin = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo principal', 'activo' => true]);
        $admin->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$admin, $fundo];
    }
}
