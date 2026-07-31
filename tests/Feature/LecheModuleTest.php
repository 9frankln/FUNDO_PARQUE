<?php

namespace Tests\Feature;

use App\Exports\OrdenosExport;
use App\Livewire\Leche\Form;
use App\Livewire\Leche\Index;
use App\Models\Fundo;
use App\Models\Ordeno;
use App\Models\OrdenoFotoDiaria;
use App\Models\User;
use App\Support\ImageFrame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Tests\TestCase;

class LecheModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_open_milk_index_create_edit_and_show_pages(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $ordeno = $this->createOrdeno($fundo);

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $this->get('/leche')->assertOk();
        $this->get('/leche/nuevo')->assertOk();
        $this->get('/leche/editar/'.$ordeno->id)->assertOk();
        $this->get('/leche/'.$ordeno->id)->assertOk();
    }

    public function test_all_shifts_share_only_one_optional_photo_per_day(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $date = now()->subDay()->toDateString();

        Livewire::test(Form::class)
            ->set('fecha', $date)
            ->set('turno', 'manana')
            ->set('tipoRegistro', 'lote')
            ->set('litrosTotal', 120.5)
            ->set('cantidadVacas', 10)
            ->set('foto', UploadedFile::fake()->image('manana.jpg'))
            ->assertNotDispatched('swal:confirm')
            ->assertSet('photoConfirmed', true)
            ->set('fotoEncuadre', ['x' => 30.0, 'y' => 65.0, 'zoom' => 1.2])
            ->call('save')
            ->assertRedirect(route('leche.index'));

        $firstPath = OrdenoFotoDiaria::whereDate('fecha', $date)->value('foto_ruta');
        $this->assertStringEndsWith('.webp', $firstPath);
        $this->assertLessThanOrEqual(1536 * 1024, Storage::disk('public')->size($firstPath));
        Storage::disk('public')->assertExists($firstPath);

        Livewire::test(Form::class)
            ->set('fecha', $date)
            ->set('turno', 'tarde')
            ->set('tipoRegistro', 'lote')
            ->set('litrosTotal', 98.25)
            ->set('cantidadVacas', 8)
            ->set('foto', UploadedFile::fake()->image('tarde.jpg'))
            ->assertNotDispatched('swal:confirm')
            ->assertSet('photoConfirmed', true)
            ->set('fotoEncuadre', ['x' => 72.0, 'y' => 44.0, 'zoom' => 1.35])
            ->call('save')
            ->assertRedirect(route('leche.index'));

        $dailyPhoto = OrdenoFotoDiaria::whereDate('fecha', $date)->firstOrFail();

        $this->assertSame(1, OrdenoFotoDiaria::whereDate('fecha', $date)->count());
        $this->assertNotSame($firstPath, $dailyPhoto->foto_ruta);
        $this->assertSame(['x' => 72.0, 'y' => 44.0, 'zoom' => 1.35], ImageFrame::normalize($dailyPhoto->foto_encuadre));
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($dailyPhoto->foto_ruta);

        Livewire::test(Index::class)
            ->assertSee('src="/storage/'.$dailyPhoto->foto_ruta.'"', false);
    }

    public function test_discarding_prepared_photo_clears_temporary_image_without_confirmation(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('tipoRegistro', 'lote')
            ->set('foto', UploadedFile::fake()->image('descartar.jpg'))
            ->assertNotDispatched('swal:confirm')
            ->assertSet('photoConfirmed', true)
            ->call('cancelPhotoChange')
            ->assertSet('foto', null)
            ->assertSet('photoConfirmed', false);
    }

    public function test_daily_photo_is_deleted_only_after_confirmation_and_save(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $ordeno = $this->createOrdeno($fundo);
        $photoPath = 'fotos/ordeno/existente.jpg';
        Storage::disk('public')->put($photoPath, 'foto');
        OrdenoFotoDiaria::create([
            'fundo_id' => $fundo->id,
            'fecha' => $ordeno->fecha,
            'foto_ruta' => $photoPath,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class, ['id' => $ordeno->id])
            ->assertSet('existingFoto', $photoPath)
            ->assertSee('src="/storage/'.$photoPath.'"', false)
            ->call('requestPhotoRemoval')
            ->assertDispatched('swal:confirm')
            ->assertSet('removeFoto', false)
            ->dispatch('confirmarEliminacionFoto')
            ->assertSet('removeFoto', true)
            ->call('save')
            ->assertRedirect(route('leche.index'));

        $this->assertDatabaseMissing('ordeno_fotos_diarias', [
            'fundo_id' => $fundo->id,
            'fecha' => $ordeno->fecha->toDateString(),
        ]);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_moving_the_last_shift_to_another_date_cleans_the_orphan_daily_photo(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        $oldDate = now()->subDays(2)->toDateString();
        $newDate = now()->subDay()->toDateString();
        $first = $this->createOrdeno($fundo, ['fecha' => $oldDate, 'turno' => 'manana']);
        $second = $this->createOrdeno($fundo, ['fecha' => $oldDate, 'turno' => 'tarde']);
        $photoPath = 'fotos/ordeno/fecha-anterior.webp';
        Storage::disk('public')->put($photoPath, 'foto');
        OrdenoFotoDiaria::create([
            'fundo_id' => $fundo->id,
            'fecha' => $oldDate,
            'foto_ruta' => $photoPath,
            'foto_encuadre' => ['x' => 25, 'y' => 75, 'zoom' => 1.3],
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class, ['id' => $first->id])
            ->set('fecha', $newDate)
            ->call('save')
            ->assertHasNoErrors();
        $this->assertTrue(OrdenoFotoDiaria::where('fundo_id', $fundo->id)->whereDate('fecha', $oldDate)->exists());
        Storage::disk('public')->assertExists($photoPath);

        Livewire::test(Form::class, ['id' => $second->id])
            ->set('fecha', $newDate)
            ->set('turno', 'tarde')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertFalse(OrdenoFotoDiaria::where('fundo_id', $fundo->id)->whereDate('fecha', $oldDate)->exists());
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_delete_archives_order_and_dispatches_sweetalert_toast(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $ordeno = $this->createOrdeno($fundo);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->dispatch('confirmarEliminacion', id: $ordeno->id)
            ->assertDispatched('swal:toast');

        $this->assertSoftDeleted('ordenos', ['id' => $ordeno->id]);
    }

    public function test_administrator_can_update_an_existing_milk_record(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $ordeno = $this->createOrdeno($fundo);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class, ['id' => $ordeno->id])
            ->set('litrosTotal', 145.75)
            ->set('cantidadVacas', 12)
            ->set('observaciones', 'Registro actualizado')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('leche.index'));

        $this->assertDatabaseHas('ordenos', [
            'id' => $ordeno->id,
            'litros_total' => 145.75,
            'cantidad_vacas' => 12,
            'observaciones' => 'REGISTRO ACTUALIZADO',
        ]);
        $this->assertSame('leche.ordenos', session('ui_recent_record.scope'));
        $this->assertSame($ordeno->id, session('ui_recent_record.id'));
        $this->assertSame('updated', session('ui_recent_record.action'));
    }

    public function test_new_milk_record_is_published_pinned_and_highlighted(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('fecha', now()->subDays(10)->toDateString())
            ->set('turno', 'tarde')
            ->set('tipoRegistro', 'lote')
            ->set('litrosTotal', 75)
            ->set('cantidadVacas', 6)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('leche.index'));

        $recent = Ordeno::latest('id')->firstOrFail();
        $this->createOrdeno($fundo, ['fecha' => now()->subDay()->toDateString()]);

        $this->assertSame('leche.ordenos', session('ui_recent_record.scope'));
        $this->assertSame($recent->id, session('ui_recent_record.id'));
        $this->assertSame('created', session('ui_recent_record.action'));

        Livewire::test(Index::class)
            ->assertViewHas('ordenos', fn ($ordenos) => $ordenos->first()->is($recent))
            ->assertSee('Nuevo');
    }

    public function test_excel_export_uses_selected_columns_and_filters(): void
    {
        [, $fundo] = $this->administratorWithFundo();
        $this->withSession(['fundo_id' => $fundo->id]);
        $this->createOrdeno($fundo, ['turno' => 'manana']);
        $this->createOrdeno($fundo, ['turno' => 'tarde']);

        $export = new OrdenosExport(
            $fundo->id,
            ['fecha', 'turno', 'litros_total'],
            ['turno' => 'tarde', 'sortBy' => 'fecha', 'sortDir' => 'desc'],
            'Administrador'
        );

        $contents = ExcelFacade::raw($export, Excel::XLSX);

        $this->assertNotEmpty($contents);
        $this->assertSame(8, $export->collection()->count());
    }

    public function test_history_can_be_filtered_by_year_and_month(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $marchRecord = $this->createOrdeno($fundo, [
            'fecha' => '2024-03-15',
            'turno' => 'manana',
        ]);
        $this->createOrdeno($fundo, [
            'fecha' => '2024-04-15',
            'turno' => 'tarde',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('anio', '2024')
            ->set('mes', '3')
            ->assertViewHas('ordenos', fn ($ordenos) => $ordenos->total() === 1
                && $ordenos->first()->is($marchRecord));
    }

    public function test_history_can_be_searched_by_observation(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $matchingRecord = $this->createOrdeno($fundo, [
            'turno' => 'manana',
            'observaciones' => 'Control preventivo de mastitis',
        ]);
        $this->createOrdeno($fundo, [
            'turno' => 'tarde',
            'observaciones' => 'Producción normal',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('observacion', 'mastitis')
            ->assertViewHas('ordenos', fn ($ordenos) => $ordenos->total() === 1
                && $ordenos->first()->is($matchingRecord));
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo lechero', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    private function createOrdeno(Fundo $fundo, array $attributes = []): Ordeno
    {
        return Ordeno::create(array_merge([
            'fundo_id' => $fundo->id,
            'fecha' => now()->subDay()->toDateString(),
            'turno' => 'manana',
            'tipo_registro' => 'lote',
            'litros_total' => 100,
            'cantidad_vacas' => 10,
        ], $attributes));
    }
}
