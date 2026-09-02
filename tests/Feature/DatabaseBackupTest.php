<?php

namespace Tests\Feature;

use App\Models\DatabaseBackup;
use App\Models\Fundo;
use App\Models\LandingBlock;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Services\Backups\FundoDatabaseBackupService;
use App\Support\SystemBranding;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_encrypted_database_archive_and_restores_only_the_requested_fundo(): void
    {
        Storage::fake('backups');
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Norte', 'activo' => true]);
        $otherFundo = Fundo::create(['nombre' => 'Sur', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        DB::table('configuracion_sistema')->insert([
            ['fundo_id' => $fundo->id, 'clave' => 'isolation_marker', 'valor' => 'ONLY_NORTH', 'created_at' => now(), 'updated_at' => now()],
            ['fundo_id' => $otherFundo->id, 'clave' => 'isolation_marker', 'valor' => 'ONLY_SOUTH', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $service = app(FundoDatabaseBackupService::class);
        $backup = $service->create($fundo, $user);

        $this->assertSame(DatabaseBackup::STATUS_COMPLETED, $backup->status);
        $this->assertSame(DatabaseBackup::TYPE_DATABASE, $backup->type);
        $this->assertSame(DatabaseBackup::FORMAT_ZIP, $backup->format);
        $this->assertSame('mysql', $backup->database_driver);
        $this->assertNotNull($backup->completed_at);
        $this->assertSame(64, strlen($backup->checksum_sha256));
        $this->assertGreaterThan(0, $backup->record_count);
        Storage::disk('backups')->assertExists($backup->path);
        Storage::disk('backups')->assertMissing($backup->path.'.part');

        $manifest = $service->inspect($backup, $fundo);
        $this->assertSame(DatabaseBackup::TYPE_DATABASE, $manifest['type']);
        $this->assertSame([], $manifest['files']);
        $this->assertNotNull($manifest['database']);
        $this->assertSame($backup->record_count, $manifest['record_count']);

        DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'isolation_marker')->update(['valor' => 'CHANGED']);
        $restore = $service->restore($backup, $fundo, $user, DatabaseBackup::TYPE_DATABASE, 10);

        $this->assertSame(DatabaseBackup::STATUS_COMPLETED, $restore->status);
        $this->assertNotNull($restore->pre_backup_id);
        $this->assertSame('ONLY_NORTH', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'isolation_marker')->value('valor'));
        $this->assertSame('ONLY_SOUTH', DB::table('configuracion_sistema')->where('fundo_id', $otherFundo->id)->where('clave', 'isolation_marker')->value('valor'));

        $this->assertCount(2, DatabaseBackup::query()->forFundo($fundo)->get());
        $this->assertCount(0, DatabaseBackup::query()->forFundo($otherFundo)->get());
    }

    public function test_file_and_complete_backups_include_files_and_uploaded_archives_are_validated(): void
    {
        Storage::fake('backups');
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Norte', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        $path = 'fotos/ordeno/diaria.webp';
        Storage::disk('public')->put($path, 'photo-bytes');
        DB::table('ordeno_fotos_diarias')->insert([
            'fundo_id' => $fundo->id,
            'fecha' => now()->toDateString(),
            'foto_ruta' => $path,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(FundoDatabaseBackupService::class);
        $files = $service->create($fundo, $user, scope: DatabaseBackup::TYPE_FILES);
        $filesManifest = $service->inspect($files, $fundo);

        $this->assertSame(DatabaseBackup::TYPE_FILES, $files->type);
        $this->assertSame(1, $files->photo_count);
        $this->assertNull($filesManifest['database']);
        $this->assertCount(1, $filesManifest['files']);

        Storage::disk('public')->delete($path);
        $service->restore($files, $fundo, $user, DatabaseBackup::TYPE_FILES, 10);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('photo-bytes', Storage::disk('public')->get($path));

        DB::table('configuracion_sistema')->insert([
            'fundo_id' => $fundo->id,
            'clave' => 'full_restore_marker',
            'valor' => 'BEFORE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $complete = $service->create($fundo, $user, scope: DatabaseBackup::TYPE_COMPLETE);
        $completeManifest = $service->inspect($complete, $fundo);
        $this->assertNotNull($completeManifest['database']);
        $this->assertCount(1, $completeManifest['files']);

        DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'full_restore_marker')->update(['valor' => 'AFTER']);
        Storage::disk('public')->put($path, 'changed-photo');
        $service->restore($complete, $fundo, $user, DatabaseBackup::TYPE_COMPLETE, 10);
        $this->assertSame('BEFORE', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'full_restore_marker')->value('valor'));
        $this->assertSame('photo-bytes', Storage::disk('public')->get($path));

        $upload = new UploadedFile(
            Storage::disk('backups')->path($complete->path),
            'backup-completo.zip',
            'application/zip',
            null,
            true,
        );
        $imported = $service->import($upload, $fundo, $user);
        $this->assertSame(DatabaseBackup::TRIGGER_UPLOADED, $imported->trigger);
        $this->assertSame(DatabaseBackup::TYPE_COMPLETE, $imported->type);
        $this->assertNotNull($imported->integrity_verified_at);
    }

    public function test_download_requires_completed_existing_backup_from_the_active_fundo(): void
    {
        Storage::fake('backups');
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Norte', 'activo' => true]);
        $otherFundo = Fundo::create(['nombre' => 'Sur', 'activo' => true]);
        $user->fundos()->attach([
            $fundo->id => ['es_administrador' => true],
            $otherFundo->id => ['es_administrador' => true],
        ]);
        Storage::fake('local');
        Storage::fake('public');
        $backup = app(FundoDatabaseBackupService::class)->create($fundo, $user);

        $response = $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get(route('ajustes.backups.download', $backup));

        $response->assertOk();
        foreach (['no-store', 'no-cache', 'must-revalidate', 'private'] as $directive) {
            $this->assertStringContainsString($directive, (string) $response->headers->get('Cache-Control'));
        }
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Content-Type', 'application/zip');
        $response->assertDownload($backup->filename);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $otherFundo->id])
            ->get(route('ajustes.backups.download', $backup))
            ->assertNotFound();

        $viewer = User::factory()->create();
        $viewer->fundos()->attach($fundo, ['es_administrador' => false]);
        $role = Role::create(['fundo_id' => $fundo->id, 'nombre' => 'Exportador sin administración']);
        $role->permisos()->attach(Permiso::firstOrCreate(['modulo' => 'ajustes', 'accion' => 'exportar'])->id);
        $viewer->roles()->attach($role);

        $this->actingAs($viewer)
            ->withSession(['fundo_id' => $fundo->id])
            ->get(route('ajustes.backups.download', $backup))
            ->assertForbidden();
    }

    public function test_complete_backup_preserves_and_restores_web_content(): void
    {
        Storage::fake('backups');
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo integral', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        $block = LandingBlock::create([
            'section' => 'hero',
            'title' => 'Portada respaldada',
            'content' => 'Contenido original',
            'settings' => LandingBlock::defaultSettings('hero'),
            'order' => 0,
            'is_active' => true,
        ]);
        $media = $block->addMedia(UploadedFile::fake()->image('web.jpg', 1200, 900))->toMediaCollection('gallery');
        $mediaPath = $media->getPathRelativeToRoot();
        Storage::disk('public')->put('branding/logo.webp', 'brand-logo');
        app(SystemBranding::class)->save([
            'name' => 'Identidad respaldada',
            'tagline' => 'Copia integral',
            'color' => 'sage',
            'logo_path' => 'branding/logo.webp',
        ]);

        $service = app(FundoDatabaseBackupService::class);
        $backup = $service->create(
            fundo: $fundo,
            requestedBy: $user,
            scope: DatabaseBackup::TYPE_COMPLETE,
            components: ['web' => true],
        );
        $manifest = $service->inspect($backup, $fundo);

        $this->assertSame(['web' => true, 'audit' => false], $backup->components);
        $this->assertSame(['web' => true, 'audit' => false], $manifest['components']);
        $this->assertArrayHasKey('landing_blocks', $manifest['system']['tables']);
        $this->assertArrayHasKey('media', $manifest['system']['tables']);
        $this->assertArrayHasKey('branding_settings', $manifest['system']['tables']);
        $this->assertArrayNotHasKey('auditoria_logs', $manifest['system']['tables']);
        $this->assertGreaterThanOrEqual(2, $manifest['file_count']);

        $block->update(['title' => 'Portada modificada']);
        app(SystemBranding::class)->save(['name' => 'Identidad modificada']);
        $media->delete();

        $service->restore($backup, $fundo, $user, DatabaseBackup::TYPE_COMPLETE, 10);

        $this->assertSame('Portada respaldada', LandingBlock::query()->where('section', 'hero')->value('title'));
        $this->assertDatabaseHas('media', ['id' => $media->id, 'model_type' => LandingBlock::class]);
        Storage::disk('public')->assertExists($mediaPath);
        $this->assertSame('Identidad respaldada', app(SystemBranding::class)->name());
    }

    public function test_restore_rejects_a_tampered_archive_before_changing_data(): void
    {
        Storage::fake('backups');
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Seguro', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        DB::table('configuracion_sistema')->insert([
            'fundo_id' => $fundo->id,
            'clave' => 'tamper_marker',
            'valor' => 'UNCHANGED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(FundoDatabaseBackupService::class);
        $backup = $service->create($fundo, $user);
        file_put_contents(Storage::disk('backups')->path($backup->path), 'tampered', FILE_APPEND);

        try {
            $service->restore($backup, $fundo, $user, DatabaseBackup::TYPE_DATABASE, 10);
            $this->fail('Tampered archive should have been rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Checksum SHA-256', $exception->getMessage());
        }

        $this->assertSame('UNCHANGED', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'tamper_marker')->value('valor'));
        $this->assertDatabaseHas('backup_restores', [
            'database_backup_id' => $backup->id,
            'status' => DatabaseBackup::STATUS_FAILED,
        ]);
    }

    public function test_scheduled_command_runs_due_fundos_and_continues_after_one_fails(): void
    {
        $first = Fundo::create(['nombre' => 'Primero', 'activo' => true]);
        $second = Fundo::create(['nombre' => 'Segundo', 'activo' => true]);
        foreach ([$first, $second] as $fundo) {
            foreach ([
                'backup_enabled' => 'true',
                'backup_interval_value' => '1',
                'backup_interval_unit' => 'hours',
                'backup_retention_count' => '2',
                'backup_scope' => DatabaseBackup::TYPE_COMPLETE,
                'backup_include_web' => 'false',
            ] as $key => $value) {
                DB::table('configuracion_sistema')->insert([
                    'fundo_id' => $fundo->id,
                    'clave' => $key,
                    'valor' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $calls = [];
        $service = Mockery::mock(FundoDatabaseBackupService::class);
        $service->shouldReceive('create')->twice()->andReturnUsing(
            function (Fundo $fundo, ?User $requestedBy, string $trigger, ?int $retention, string $scope, bool $applyRetention, array $components) use (&$calls): DatabaseBackup {
                $calls[] = ['fundo' => $fundo->id, 'scope' => $scope, 'components' => $components];
                if (count($calls) === 1) {
                    throw new RuntimeException('simulated failure');
                }

                return new DatabaseBackup(['status' => DatabaseBackup::STATUS_COMPLETED]);
            }
        );
        $this->app->instance(FundoDatabaseBackupService::class, $service);

        $this->artisan('backups:run-scheduled')->assertSuccessful();

        $this->assertSame([
            ['fundo' => $first->id, 'scope' => DatabaseBackup::TYPE_COMPLETE, 'components' => ['web' => false]],
            ['fundo' => $second->id, 'scope' => DatabaseBackup::TYPE_COMPLETE, 'components' => ['web' => false]],
        ], $calls);
        $event = collect(app(Schedule::class)->events())->first(
            fn ($event) => str_contains($event->command ?? '', 'backups:run-scheduled')
        );
        $this->assertNotNull($event);
        $this->assertSame('*/15 * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }
}
