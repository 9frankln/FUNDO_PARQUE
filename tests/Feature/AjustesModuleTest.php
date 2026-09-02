<?php

namespace Tests\Feature;

use App\Livewire\Ajustes\Index;
use App\Models\Animal;
use App\Models\BackupRestore;
use App\Models\DatabaseBackup;
use App\Models\Fundo;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Support\SystemBranding;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AjustesModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_is_first_and_both_management_tables_are_searchable_and_paginated(): void
    {
        [$admin, $fundo] = $this->context();
        foreach (range(1, 12) as $number) {
            $user = User::factory()->create([
                'name' => "Integrante {$number}",
                'username' => "integrante{$number}",
                'dni' => str_pad((string) $number, 8, '0', STR_PAD_LEFT),
            ]);
            $user->fundos()->attach($fundo, ['es_administrador' => false]);
        }
        Role::create(['fundo_id' => $fundo->id, 'nombre' => 'Operador especial', 'descripcion' => 'Trabajo diario']);

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->assertSet('activeTab', 'colaboradores')
            ->assertSee('Equipo del fundo')
            ->assertViewHas('usuariosFundo', fn ($users) => $users->total() === 13 && $users->count() === 10)
            ->set('userSearch', 'integrante12')
            ->assertViewHas('usuariosFundo', fn ($users) => $users->total() === 1)
            ->set('activeTab', 'roles')
            ->set('roleSearch', 'especial')
            ->assertViewHas('rolesFundo', fn ($roles) => $roles->total() === 1)
            ->set('rolesPerPage', 100)
            ->assertSet('rolesPerPage', 100)
            ->set('rolesPerPage', 5000)
            ->assertSet('rolesPerPage', 10);
    }

    public function test_team_crud_preserves_other_fundos_and_rejects_cross_fundo_edits(): void
    {
        [$admin, $fundo] = $this->context();
        $otherFundo = Fundo::create(['nombre' => 'Otro fundo', 'activo' => true]);
        $foreignUser = User::factory()->create();
        $foreignUser->fundos()->attach($otherFundo, ['es_administrador' => false]);
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('userName', 'Nuevo Integrante')
            ->set('userUsername', 'nuevo.integrante')
            ->set('userEmail', 'nuevo@example.test')
            ->set('userDni', '74125896')
            ->set('userPassword', 'Password-123')
            ->set('userPasswordConfirmation', 'Password-123')
            ->call('saveUser')
            ->assertHasNoErrors()
            ->assertSet('showUserAccessModal', true);

        $created = User::where('username', 'nuevo.integrante')->firstOrFail();
        $this->assertTrue($created->fundos()->whereKey($fundo->id)->exists());
        $this->assertNotNull($created->email_verified_at);

        Livewire::test(Index::class)
            ->call('removeUserFromFundo', $created->id)
            ->assertHasNoErrors();
        $this->assertFalse($created->fundos()->whereKey($fundo->id)->exists());

        $this->expectException(ModelNotFoundException::class);
        Livewire::test(Index::class)->call('openUserFormModal', $foreignUser->id);
    }

    public function test_role_selector_lists_fundo_users_and_assigns_them_to_role(): void
    {
        [$admin, $fundo] = $this->context();
        $user = User::factory()->create([
            'name' => 'Jesus Operador',
            'username' => 'jesus.operador',
        ]);
        $user->fundos()->attach($fundo, ['es_administrador' => false]);
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('activeTab', 'roles')
            ->call('openRoleModal')
            ->set('roleNombre', 'Operador de Campo')
            ->call('saveRole')
            ->assertHasNoErrors();

        $role = Role::where('nombre', 'Operador de Campo')->firstOrFail();
        $this->assertSame($fundo->id, $role->fundo_id);
    }

    public function test_role_matrix_exposes_auditing_and_independent_web_management_permissions(): void
    {
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('activeTab', 'roles')
            ->call('openRoleModal')
            ->assertSee('Auditoría')
            ->assertSee('Gestión web');

        $webPermission = Permiso::query()
            ->where('modulo', 'gestion_web')
            ->where('accion', 'actualizar')
            ->firstOrFail();
        $generalAdministrator = Role::query()
            ->whereNull('fundo_id')
            ->where('nombre', 'Administrador General')
            ->firstOrFail();

        $this->assertTrue($generalAdministrator->permisos()->whereKey($webPermission->id)->exists());
        $this->assertSame(2, Permiso::query()->where('modulo', 'auditoria')->count());
    }

    public function test_preferences_update_global_branding_and_backup_schedule(): void
    {
        Storage::fake('public');
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('brandName', 'Campo Central')
            ->set('brandTagline', 'Producción segura')
            ->set('brandColor', 'blue')
            ->set('brandLogo', UploadedFile::fake()->image('logo.png', 800, 600))
            ->set('brandLogoFrame', ['x' => 33.0, 'y' => 64.0, 'zoom' => 1.25])
            ->call('saveBranding')
            ->assertHasNoErrors()
            ->assertDispatched('branding-updated')
            ->set('backupSettings.enabled', true)
            ->set('backupSettings.interval_value', 3)
            ->set('backupSettings.interval_unit', 'hours')
            ->set('backupSettings.retention_count', 25)
            ->call('saveBackupSettings')
            ->assertHasNoErrors();

        $branding = app(SystemBranding::class);
        $this->assertSame('Campo Central', $branding->name());
        $this->assertSame('blue', $branding->color());
        $this->assertSame(['x' => 33.0, 'y' => 64.0, 'zoom' => 1.25], $branding->logoFrame());
        Storage::disk('public')->assertExists($branding->logoPath());
        $this->assertSame('true', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'backup_enabled')->value('valor'));
        $this->assertSame('3', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'backup_interval_value')->value('valor'));
    }

    public function test_backup_schedule_presets_and_integrity_verification_work(): void
    {
        Storage::fake('backups');
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        $component = Livewire::test(Index::class)
            ->set('backupSettings.enabled', true)
            ->set('backupSettings.schedule', 'weekly')
            ->set('backupSettings.retention_count', 12)
            ->call('saveBackupSettings')
            ->assertHasNoErrors();

        $this->assertSame('7', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'backup_interval_value')->value('valor'));
        $this->assertSame('days', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'backup_interval_unit')->value('valor'));

        $component
            ->set('backupSettings.schedule', 'custom')
            ->set('backupSettings.interval_value', 5)
            ->set('backupSettings.interval_unit', 'days')
            ->set('backupSettings.scope', DatabaseBackup::TYPE_COMPLETE)
            ->set('backupSettings.include_web', true)
            ->call('saveBackupSettings')
            ->assertHasNoErrors();
        $this->assertSame('5', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'backup_interval_value')->value('valor'));
        $this->assertSame(DatabaseBackup::TYPE_COMPLETE, DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'backup_scope')->value('valor'));
        $this->assertSame('true', DB::table('configuracion_sistema')->where('fundo_id', $fundo->id)->where('clave', 'backup_include_web')->value('valor'));

        $contents = '-- verified backup';
        $backup = DatabaseBackup::create([
            'fundo_id' => $fundo->id,
            'requested_by' => $admin->id,
            'trigger' => DatabaseBackup::TRIGGER_MANUAL,
            'status' => DatabaseBackup::STATUS_COMPLETED,
            'disk' => 'backups',
            'path' => 'fundos/'.$fundo->id.'/verified.sql',
            'filename' => 'verified.sql',
            'database_driver' => 'mysql',
            'size_bytes' => strlen($contents),
            'checksum_sha256' => hash('sha256', $contents),
            'started_at' => now()->subSecond(),
            'completed_at' => now(),
        ]);
        Storage::disk('backups')->put($backup->path, $contents);

        Livewire::test(Index::class)
            ->set('activeTab', 'backup')
            ->call('verifyBackup', $backup->id)
            ->assertHasNoErrors();

        $this->assertNotNull($backup->fresh()->integrity_verified_at);
    }

    public function test_backup_import_accepts_archives_up_to_ten_gigabytes_and_shows_upload_progress(): void
    {
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        $this->assertContains('max:10485760', config('livewire.temporary_file_upload.rules'));
        $this->assertSame(1440, config('livewire.temporary_file_upload.max_upload_time'));

        Livewire::test(Index::class)
            ->set('activeTab', 'backup')
            ->assertSee('Máximo 10 GB')
            ->assertSee('Subiendo al servidor...')
            ->assertSee('Sin límite de velocidad');
    }

    public function test_backup_ui_generates_selected_scope_and_restores_with_confirmation(): void
    {
        Storage::fake('backups');
        Storage::fake('local');
        Storage::fake('public');
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        $component = Livewire::test(Index::class)
            ->set('activeTab', 'backup')
            ->set('backupScope', DatabaseBackup::TYPE_COMPLETE)
            ->call('generateBackup')
            ->assertHasNoErrors();

        $backup = DatabaseBackup::query()->where('trigger', DatabaseBackup::TRIGGER_MANUAL)->latest('id')->firstOrFail();
        $this->assertSame(DatabaseBackup::TYPE_COMPLETE, $backup->type);
        $this->assertSame(DatabaseBackup::FORMAT_ZIP, $backup->format);

        $component
            ->call('openRestoreModal', $backup->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('restoreMode', DatabaseBackup::TYPE_COMPLETE)
            ->set('restorePassword', 'password')
            ->set('createPreBackup', true)
            ->call('restoreBackup')
            ->assertHasNoErrors()
            ->assertSet('showRestoreModal', false);

        $this->assertDatabaseHas('backup_restores', [
            'database_backup_id' => $backup->id,
            'status' => DatabaseBackup::STATUS_COMPLETED,
        ]);
        $this->assertSame(1, BackupRestore::query()->count());
    }

    public function test_settings_tabs_are_hidden_and_rejected_without_specific_permissions(): void
    {
        $fundo = Fundo::create(['nombre' => 'Fundo restringido', 'activo' => true]);
        $user = User::factory()->create();
        $user->fundos()->attach($fundo, ['es_administrador' => false]);
        $role = Role::create(['fundo_id' => $fundo->id, 'nombre' => 'Consulta de equipo']);
        $role->permisos()->sync([
            Permiso::firstOrCreate(['modulo' => 'ajustes', 'accion' => 'leer'])->id,
        ]);
        $user->roles()->attach($role);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['tab' => 'roles'])
            ->assertSet('activeTab', 'colaboradores')
            ->assertViewHas('settingsTabAccess', fn (array $access) => $access === [
                'colaboradores' => true,
                'roles' => false,
                'general' => false,
                'pdf' => false,
                'backup' => false,
                'peligro' => false,
            ])
            ->set('activeTab', 'roles')
            ->assertSet('activeTab', 'colaboradores');
    }

    public function test_fundo_administrator_can_assign_a_new_password(): void
    {
        [$admin, $fundo] = $this->context();
        $target = User::factory()->create();
        $target->fundos()->attach($fundo, ['es_administrador' => false]);
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->call('openPasswordResetModal', $target->id)
            ->assertSet('showPasswordResetModal', true)
            ->set('newPassword', 'Nueva-Clave-987')
            ->set('newPasswordConfirmation', 'Nueva-Clave-987')
            ->call('resetUserPassword')
            ->assertHasNoErrors()
            ->assertSet('showPasswordResetModal', false);

        $this->assertTrue(Hash::check('Nueva-Clave-987', $target->fresh()->password));
    }

    public function test_non_admin_cannot_view_backups_even_with_backup_permissions(): void
    {
        Storage::fake('backups');
        $fundo = Fundo::create(['nombre' => 'Fundo privado', 'activo' => true]);
        $user = User::factory()->create();
        $user->fundos()->attach($fundo, ['es_administrador' => false]);
        $role = Role::create(['fundo_id' => $fundo->id, 'nombre' => 'Exportador']);
        $role->permisos()->sync(collect(['leer', 'exportar', 'restaurar'])->map(
            fn (string $action) => Permiso::firstOrCreate(['modulo' => 'ajustes', 'accion' => $action])->id
        ));
        $user->roles()->attach($role);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['tab' => 'backup'])
            ->assertSet('activeTab', 'colaboradores')
            ->assertViewHas('settingsTabAccess', fn (array $access) => $access['backup'] === false)
            ->call('generateBackup')
            ->assertForbidden();
    }

    public function test_non_admin_manager_cannot_grant_fundo_administrator_access(): void
    {
        $fundo = Fundo::create(['nombre' => 'Fundo seguro', 'activo' => true]);
        $manager = User::factory()->create();
        $target = User::factory()->create();
        $manager->fundos()->attach($fundo, ['es_administrador' => false]);
        $target->fundos()->attach($fundo, ['es_administrador' => false]);
        $role = Role::create(['fundo_id' => $fundo->id, 'nombre' => 'Gestor limitado']);
        $role->permisos()->sync([
            Permiso::firstOrCreate(['modulo' => 'ajustes', 'accion' => 'leer'])->id,
            Permiso::firstOrCreate(['modulo' => 'ajustes', 'accion' => 'actualizar'])->id,
        ]);
        $manager->roles()->attach($role);
        $this->actingAs($manager)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->call('openUserAccessModal', $target->id)
            ->set('userEsAdmin', true)
            ->call('saveUserAccess')
            ->assertHasNoErrors();

        $this->assertFalse((bool) $target->fundos()->whereKey($fundo->id)->firstOrFail()->pivot->es_administrador);

        Livewire::test(Index::class)
            ->call('openPasswordResetModal', $target->id)
            ->assertForbidden();
    }

    public function test_danger_zone_requires_admin_password_and_wipes_operational_data(): void
    {
        Storage::fake('backups');
        Storage::fake('local');
        Storage::fake('public');
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        $animal = Animal::factory()->create(['fundo_id' => $fundo->id]);
        $block = \App\Models\LandingBlock::create([
            'section' => 'hero',
            'title' => 'Titulo landing',
            'order' => 0,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('animales', ['id' => $animal->id]);
        $this->assertDatabaseHas('landing_blocks', ['id' => $block->id]);

        // Contraseña incorrecta → error y NO borra.
        Livewire::test(Index::class)
            ->call('openDangerDeleteModal')
            ->assertSet('showDangerDeleteModal', true)
            ->set('dangerPassword', 'incorrecta')
            ->call('confirmDangerDelete')
            ->assertHasErrors('dangerPassword')
            ->assertSet('showDangerDeleteModal', true);

        $this->assertDatabaseHas('animales', ['id' => $animal->id]);
        $this->assertDatabaseHas('landing_blocks', ['id' => $block->id]);

        // Contraseña correcta → borra todo (incluyendo gestión web).
        Livewire::test(Index::class)
            ->call('openDangerDeleteModal')
            ->set('dangerPassword', 'password')
            ->call('confirmDangerDelete')
            ->assertHasNoErrors()
            ->assertSet('showDangerDeleteModal', false);

        $this->assertDatabaseMissing('animales', ['id' => $animal->id]);
        $this->assertDatabaseMissing('landing_blocks', ['id' => $block->id]);
        // Conserva usuarios del fundo y deja el rastro de auditoría de la propia acción.
        $this->assertDatabaseHas('fundo_user', ['fundo_id' => $fundo->id]);
        $this->assertDatabaseHas('auditoria_logs', ['fundo_id' => $fundo->id]);
    }

    public function test_pdf_report_settings_can_be_saved_with_individual_signatures_and_watermark_orientation(): void
    {
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class, ['activeTab' => 'pdf'])
            ->assertSet('activeTab', 'pdf')
            ->assertSet('pdfSettings.orientacion_marca_agua', 'diagonal')
            ->assertSet('pdfSettings.mostrar_firma_1', true)
            ->assertSet('pdfSettings.mostrar_firma_2', true)
            ->set('pdfSettings.orientacion_marca_agua', 'horizontal')
            ->set('pdfSettings.texto_marca_agua', 'CONFIDENCIAL')
            ->set('pdfSettings.mostrar_firma_1', true)
            ->set('pdfSettings.mostrar_firma_2', false)
            ->set('pdfSettings.firma_1_cargo', 'Gerente General')
            ->set('pdfSettings.firma_1_nombre', 'Ing. Juan Pérez')
            ->call('savePdfSettings')
            ->assertHasNoErrors();

        $config = app(\App\Support\PdfReportConfig::class)->forFundo($fundo->id);
        $this->assertSame('horizontal', $config->watermarkOrientation());
        $this->assertSame('0deg', $config->watermarkRotation());
        $this->assertSame('CONFIDENCIAL', $config->watermarkText());
        $this->assertTrue($config->showSignature1());
        $this->assertFalse($config->showSignature2());

        // Reset
        Livewire::test(Index::class, ['activeTab' => 'pdf'])
            ->call('resetPdfSettings')
            ->assertHasNoErrors()
            ->assertSet('pdfSettings.orientacion_marca_agua', 'diagonal')
            ->assertSet('pdfSettings.mostrar_firma_1', true)
            ->assertSet('pdfSettings.mostrar_firma_2', true);
    }

    private function context(): array
    {
        $admin = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo principal', 'activo' => true]);
        $admin->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$admin, $fundo];
    }
}
