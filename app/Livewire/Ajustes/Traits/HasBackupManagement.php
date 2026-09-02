<?php

namespace App\Livewire\Ajustes\Traits;

use App\Models\ConfiguracionSistema;
use App\Models\DatabaseBackup;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\Backups\FundoDatabaseBackupService;
use App\Services\Security\UserSessionService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\SystemBranding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;
use Throwable;

trait HasBackupManagement
{
    public const MAX_BACKUP_UPLOAD_KB = 10 * 1024 * 1024;

    public int $backupsPerPage = 10;

    public array $backupSettings = [];

    public string $backupScope = 'database';

    public bool $backupIncludeWeb = true;

    public $backupUpload;

    public bool $showBackupDetails = false;

    public bool $showRestoreModal = false;

    public ?int $restoringBackupId = null;

    public string $restoreMode = 'database';

    public string $restorePassword = '';

    public bool $createPreBackup = false;

    public array $restoreModes = [];

    public array $restoreSummary = [];

    public ?int $viewingBackupId = null;

    public function updatedBackupsPerPage($value): void
    {
        $this->backupsPerPage = $this->validPerPage($value);
        $this->resetPage('backupsPage');
    }

    public function updatedBackupSettingsIntervalUnit($value): void
    {
        if ($value === 'days' && (int) ($this->backupSettings['interval_value'] ?? 1) > 30) {
            $this->backupSettings['interval_value'] = 30;
        }
    }

    public function loadBackupSettings(): void
    {
        $configs = ConfiguracionSistema::query()
            ->where('fundo_id', $this->fundoId())
            ->whereIn('clave', [
                'backup_enabled',
                'backup_interval_value',
                'backup_interval_unit',
                'backup_retention_count',
                'backup_scope',
                'backup_include_web',
            ])
            ->pluck('valor', 'clave');

        $scope = (string) $configs->get('backup_scope', DatabaseBackup::TYPE_DATABASE);
        if (! in_array($scope, [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE], true)) {
            $scope = DatabaseBackup::TYPE_DATABASE;
        }

        $this->backupSettings = [
            'enabled' => filter_var($configs->get('backup_enabled', false), FILTER_VALIDATE_BOOL),
            'interval_value' => (int) $configs->get('backup_interval_value', 6),
            'interval_unit' => $configs->get('backup_interval_unit', 'hours'),
            'retention_count' => (int) $configs->get('backup_retention_count', 20),
            'scope' => $scope,
            'include_web' => filter_var($configs->get('backup_include_web', true), FILTER_VALIDATE_BOOL),
        ];

        $this->backupSettings['schedule'] = match (true) {
            $this->backupSettings['interval_unit'] === 'days' && $this->backupSettings['interval_value'] === 1 => 'daily',
            $this->backupSettings['interval_unit'] === 'days' && $this->backupSettings['interval_value'] === 7 => 'weekly',
            $this->backupSettings['interval_unit'] === 'days' && $this->backupSettings['interval_value'] === 30 => 'monthly',
            default => 'custom',
        };
        $this->backupIncludeWeb = $this->backupSettings['include_web'];
    }

    public function saveBackupSettings(): void
    {
        $this->authorizeFundoAdmin();
        $maxInterval = ($this->backupSettings['interval_unit'] ?? 'hours') === 'days' ? 30 : 168;
        $validated = $this->validate([
            'backupSettings.enabled' => ['boolean'],
            'backupSettings.schedule' => ['required', Rule::in(['custom', 'daily', 'weekly', 'monthly'])],
            'backupSettings.interval_value' => ['required_if:backupSettings.schedule,custom', 'integer', 'min:1', 'max:'.$maxInterval],
            'backupSettings.interval_unit' => ['required_if:backupSettings.schedule,custom', Rule::in(['hours', 'days'])],
            'backupSettings.retention_count' => ['required', 'integer', 'min:2', 'max:100'],
            'backupSettings.scope' => ['required', Rule::in([DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE])],
            'backupSettings.include_web' => ['boolean'],
        ]);

        [$intervalValue, $intervalUnit] = match ($validated['backupSettings']['schedule']) {
            'daily' => [1, 'days'],
            'weekly' => [7, 'days'],
            'monthly' => [30, 'days'],
            default => [(int) $validated['backupSettings']['interval_value'], $validated['backupSettings']['interval_unit']],
        };

        foreach ([
            'backup_enabled' => $validated['backupSettings']['enabled'] ? 'true' : 'false',
            'backup_interval_value' => (string) $intervalValue,
            'backup_interval_unit' => $intervalUnit,
            'backup_retention_count' => (string) $validated['backupSettings']['retention_count'],
            'backup_scope' => $validated['backupSettings']['scope'],
            'backup_include_web' => $validated['backupSettings']['include_web'] ? 'true' : 'false',
        ] as $key => $value) {
            $this->saveConfig($key, $value);
        }

        $this->backupSettings['interval_value'] = $intervalValue;
        $this->backupSettings['interval_unit'] = $intervalUnit;

        app(AuditLogger::class)->record('backup.programacion_actualizada', 'ajustes', 'Actualizó programación de backups.', metadata: [
            'activo' => $validated['backupSettings']['enabled'],
            'intervalo' => $intervalValue.' '.$intervalUnit,
            'retencion' => $validated['backupSettings']['retention_count'],
            'contenido' => $validated['backupSettings']['scope'],
            'gestion_web' => $validated['backupSettings']['include_web'],
        ]);

        $this->dispatchSuccess('Programación guardada', 'Frecuencia automática y retención actualizadas.');
    }

    public function generateBackup(FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $this->validate([
            'backupScope' => ['required', Rule::in([
                DatabaseBackup::TYPE_DATABASE,
                DatabaseBackup::TYPE_FILES,
                DatabaseBackup::TYPE_COMPLETE,
            ])],
            'backupIncludeWeb' => ['boolean'],
        ]);

        try {
            set_time_limit(0);
            $backup = $backups->create(
                fundo: auth()->user()->fundoActivo(),
                requestedBy: auth()->user(),
                trigger: DatabaseBackup::TRIGGER_MANUAL,
                retentionCount: (int) $this->backupSettings['retention_count'],
                scope: $this->backupScope,
                components: ['web' => $this->backupIncludeWeb],
            );
            $this->resetPage('backupsPage');
            app(AuditLogger::class)->record('backup.generado', 'ajustes', 'Generó backup '.$backup->filename.'.', metadata: [
                'backup_id' => $backup->id,
                'tipo' => $backup->type,
                'componentes' => $backup->components,
            ]);
            $this->dispatchSuccess('Backup completado', "Archivo {$backup->filename} listo para descargar.");
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Backup fallido', 'No se pudo completar. Revisa historial y logs.');
        }
    }

    public function deleteBackup(int $backupId, FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $backup = DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($backupId);
        $filename = $backup->filename;
        $backups->delete($backup, $this->fundoId());
        app(AuditLogger::class)->record('backup.eliminado', 'ajustes', 'Eliminó backup '.$filename.'.', metadata: ['backup_id' => $backupId]);
        $this->dispatchSuccess('Backup eliminado', 'Archivo privado e historial retirados.');
    }

    public function openBackupDetails(int $backupId): void
    {
        $this->authorizeFundoAdmin();
        DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($backupId);
        $this->viewingBackupId = $backupId;
        $this->showBackupDetails = true;
    }

    public function closeBackupDetails(): void
    {
        $this->showBackupDetails = false;
        $this->viewingBackupId = null;
    }

    public function uploadBackup(FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $this->validate([
            'backupUpload' => ['required', 'file', 'extensions:zip', 'max:'.self::MAX_BACKUP_UPLOAD_KB],
        ], [
            'backupUpload.required' => 'Selecciona un backup ZIP.',
            'backupUpload.extensions' => 'Solo se permiten backups .zip.',
            'backupUpload.max' => 'El backup supera el máximo permitido de 10 GB.',
        ]);

        try {
            set_time_limit(0);
            $backup = $backups->import($this->backupUpload, auth()->user()->fundoActivo(), auth()->user());
            $this->reset('backupUpload');
            $this->resetPage('backupsPage');
            app(AuditLogger::class)->record('backup.importado', 'ajustes', 'Importó backup '.$backup->filename.'.', metadata: ['backup_id' => $backup->id, 'tipo' => $backup->type]);
            $this->dispatchSuccess('Backup importado', "{$backup->filename} validado, cifrado y listo para restaurar.");
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Importación rechazada', mb_substr($exception->getMessage(), 0, 240));
        }
    }

    public function openRestoreModal(int $backupId, FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $backup = DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($backupId);

        try {
            $manifest = $backups->inspect($backup, $this->fundoId());
            $this->restoreModes = match ($manifest['type']) {
                DatabaseBackup::TYPE_DATABASE => [DatabaseBackup::TYPE_DATABASE],
                DatabaseBackup::TYPE_FILES => [DatabaseBackup::TYPE_FILES],
                default => [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE],
            };
            $this->restoreMode = in_array(DatabaseBackup::TYPE_COMPLETE, $this->restoreModes, true)
                ? DatabaseBackup::TYPE_COMPLETE
                : $this->restoreModes[0];
            $this->restoreSummary = [
                'filename' => $backup->filename,
                'created_at' => $backup->created_at->format('d/m/Y H:i:s'),
                'type' => $manifest['type'],
                'records' => (int) ($manifest['record_count'] ?? 0),
                'files' => (int) ($manifest['file_count'] ?? 0),
                'checksum' => $backup->checksum_sha256,
                'components' => $manifest['components'] ?? ['web' => false, 'audit' => false],
            ];
            $this->restoringBackupId = $backup->id;
            $this->restorePassword = '';
            $this->createPreBackup = false;
            $this->showBackupDetails = false;
            $this->viewingBackupId = null;
            $this->showRestoreModal = true;
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Backup no restaurable', mb_substr($exception->getMessage(), 0, 240));
        }
    }

    public function closeRestoreModal(): void
    {
        $this->showRestoreModal = false;
        $this->restoringBackupId = null;
        $this->restoreMode = DatabaseBackup::TYPE_DATABASE;
        $this->restorePassword = '';
        $this->createPreBackup = true;
        $this->restoreModes = [];
        $this->restoreSummary = [];
        $this->resetValidation(['restoreMode', 'restorePassword']);
    }

    public function restoreBackup(FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $this->validate([
            'restoringBackupId' => ['required', 'integer'],
            'restoreMode' => ['required', Rule::in($this->restoreModes)],
            'restorePassword' => ['required', 'string'],
        ], [
            'restorePassword.required' => 'Ingresa tu contraseña para confirmar.',
        ]);

        $user = auth()->user();
        if (! $user || ! Hash::check($this->restorePassword, $user->password)) {
            $this->addError('restorePassword', 'La contraseña no es correcta.');

            return;
        }

        $backup = DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($this->restoringBackupId);
        try {
            set_time_limit(0);
            $restoredMode = $this->restoreMode;
            $restore = $backups->restore(
                $backup,
                $user->fundoActivo(),
                $user,
                $this->restoreMode,
                (int) $this->backupSettings['retention_count'],
                $this->createPreBackup,
            );
            $preBackup = $restore->preBackup?->filename ?? 'no disponible';
            $this->closeRestoreModal();
            $user->unsetRelation('fundos');
            $this->loadSettings();
            $this->resetPage('backupsPage');
            app(AuditLogger::class)->record('backup.restaurado', 'ajustes', 'Restauró backup '.$backup->filename.'.', metadata: ['backup_id' => $backup->id, 'modo' => $restoredMode, 'pre_backup' => (bool) $this->createPreBackup]);
            $this->dispatchSuccess('Restauración completada', $this->createPreBackup ? "Datos restaurados. Copia de seguridad previa: {$preBackup}." : 'Datos restaurados correctamente.');
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Restauración cancelada', mb_substr($exception->getMessage(), 0, 240));
        }
    }

    public function verifyBackup(int $backupId): void
    {
        $this->authorizeFundoAdmin();
        $backup = DatabaseBackup::query()
            ->forFundo($this->fundoId())
            ->where('status', DatabaseBackup::STATUS_COMPLETED)
            ->findOrFail($backupId);

        if (! $backup->path || ! Storage::disk($backup->disk)->exists($backup->path)) {
            $backup->update(['integrity_verified_at' => null]);
            $this->dispatchWarning('Archivo no encontrado', 'Registro existe, pero archivo de respaldo no está disponible.');

            return;
        }

        $stream = Storage::disk($backup->disk)->readStream($backup->path);
        if (! is_resource($stream)) {
            $this->dispatchWarning('Verificación fallida', 'No se pudo leer archivo de respaldo.');

            return;
        }

        try {
            $hash = hash_init('sha256');
            hash_update_stream($hash, $stream);
            $matches = hash_equals((string) $backup->checksum_sha256, hash_final($hash));
        } finally {
            fclose($stream);
        }

        $backup->update(['integrity_verified_at' => $matches ? now() : null]);
        app(AuditLogger::class)->record(
            'backup.integridad_verificada',
            'ajustes',
            $matches ? 'Verificó integridad de backup.' : 'Detectó integridad comprometida en backup.',
            metadata: ['backup_id' => $backup->id, 'integridad' => $matches],
            result: $matches ? 'exitoso' : 'rechazado',
        );
        $matches
            ? $this->dispatchSuccess('Integridad verificada', 'Checksum SHA-256 coincide con archivo almacenado.')
            : $this->dispatchWarning('Integridad comprometida', 'Checksum SHA-256 no coincide. No uses este backup para restaurar.');
    }

    public function forceReleaseBackupLock(): void
    {
        $this->authorizeFundoAdmin();
        \Illuminate\Support\Facades\Cache::forget('backup-operation:fundo:'.$this->fundoId());
        app(AuditLogger::class)->record('backup.lock_liberado', 'ajustes', 'Liberó bloqueo de emergencia de backup.');
        $this->dispatchSuccess('Bloqueo liberado', 'Se ha restablecido la posibilidad de generar o restaurar backups.');
    }

    public function maxUploadLimitLabel(): string
    {
        return $this->formatBytes(self::MAX_BACKUP_UPLOAD_KB * 1024);
    }

    public function serverUploadLimitLabel(): string
    {
        return $this->formatBytes($this->effectiveServerUploadLimit());
    }

    public function serverUploadLimitIsBelowDeclared(): bool
    {
        return $this->effectiveServerUploadLimit() < self::MAX_BACKUP_UPLOAD_KB * 1024;
    }

    private function effectiveServerUploadLimit(): int
    {
        $upload = $this->iniSizeToBytes((string) ini_get('upload_max_filesize'));
        $post = $this->iniSizeToBytes((string) ini_get('post_max_size'));
        $limits = array_values(array_filter([$upload, $post]));

        return $limits !== [] ? min($limits) : self::MAX_BACKUP_UPLOAD_KB * 1024;
    }

    private function iniSizeToBytes(string $value): ?int
    {
        if (! preg_match('/^\s*(\d+)\s*([KMG])?\s*$/i', $value, $m)) {
            return null;
        }
        $multiplier = ['K' => 1024, 'M' => 1024 ** 2, 'G' => 1024 ** 3, '' => 1][strtoupper($m[2] ?? '')];

        return (int) $m[1] * $multiplier;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 ** 3) {
            return rtrim(rtrim(number_format($bytes / (1024 ** 3), 2, '.', ''), '0'), '.').' GB';
        }

        return number_format($bytes / (1024 ** 2), 0).' MB';
    }
}
