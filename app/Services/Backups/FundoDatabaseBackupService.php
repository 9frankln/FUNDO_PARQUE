<?php

namespace App\Services\Backups;

use App\Models\BackupRestore;
use App\Models\DatabaseBackup;
use App\Models\Fundo;
use App\Models\LandingBlock;
use App\Models\User;
use App\Support\SystemBranding;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Throwable;
use ZipArchive;

class FundoDatabaseBackupService
{
    private const MANIFEST_VERSION = 2;

    private const MANIFEST_ENTRY = 'manifest.json';

    private const DATABASE_SQL_ENTRY = 'database/database.sql';

    private const DATABASE_DATA_ENTRY = 'database/database.ndjson';

    private const SYSTEM_DATA_ENTRY = 'system/components.ndjson';

    private const DATABASE_TYPES = [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_COMPLETE];

    private const FILE_TYPES = [DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE];

    private const GLOBAL_TABLES = ['especies', 'razas'];

    private const MIXED_TABLES = ['categorias_financieras', 'medicamentos'];

    private const GENERATED_COLUMNS = [
        'producciones_queso' => ['fecha_activa'],
    ];

    public function create(
        Fundo $fundo,
        ?User $requestedBy = null,
        string $trigger = DatabaseBackup::TRIGGER_MANUAL,
        ?int $retentionCount = null,
        string $scope = DatabaseBackup::TYPE_DATABASE,
        bool $applyRetention = true,
        array $components = [],
    ): DatabaseBackup {
        $this->assertSupportedType($scope);
        $this->assertSupportedTrigger($trigger);
        $this->assertZipAvailable();
        $components = $this->normalizeComponents($components);

        $backup = $this->newBackupRecord($fundo, $requestedBy, $trigger, $scope, $components);
        $lock = $this->lock($fundo);

        if (! $lock->get()) {
            $exception = new RuntimeException('Ya existe una operación de backup o restauración para este fundo.');
            $this->markFailed($backup, $exception);
            throw $exception;
        }

        try {
            return $this->writeArchive($backup, $fundo, $scope, $retentionCount, $applyRetention, $components);
        } catch (Throwable $exception) {
            $this->cleanupPartialFile($backup);
            $this->markFailed($backup, $exception);
            throw $exception;
        } finally {
            $this->releaseLock($lock);
        }
    }

    public function import(UploadedFile $file, Fundo $fundo, User $requestedBy): DatabaseBackup
    {
        $this->assertZipAvailable();
        if (strtolower($file->getClientOriginalExtension()) !== 'zip') {
            throw new RuntimeException('El archivo debe tener extensión .zip.');
        }

        $lock = $this->lock($fundo);
        if (! $lock->get()) {
            throw new RuntimeException('Ya existe una operación de backup o restauración para este fundo.');
        }

        $disk = Storage::disk((string) config('backups.disk', 'backups'));
        $uuid = (string) Str::uuid();
        $filename = sprintf('fundo-%d_importado_%s_%s.zip', $fundo->getKey(), now()->format('Ymd_His'), $uuid);
        $path = sprintf('fundos/%d/%s', $fundo->getKey(), $filename);
        $partPath = $path.'.part';

        try {
            $disk->makeDirectory(dirname($partPath));
            $input = fopen($file->getRealPath(), 'rb');
            if (! is_resource($input)) {
                throw new RuntimeException('No se pudo leer el backup subido.');
            }
            try {
                if (! $disk->writeStream($partPath, $input)) {
                    throw new RuntimeException('No se pudo guardar el backup subido.');
                }
            } finally {
                fclose($input);
            }

            $manifest = $this->readManifest($disk->path($partPath), $fundo->getKey());
            $disk->move($partPath, $path);
            $checksum = $this->hashFile($disk->path($path));
            $size = (int) $disk->size($path);

            return DatabaseBackup::create([
                'fundo_id' => $fundo->getKey(),
                'requested_by' => $requestedBy->getKey(),
                'trigger' => DatabaseBackup::TRIGGER_UPLOADED,
                'type' => $manifest['type'],
                'components' => $this->normalizeComponents($manifest['components'] ?? []),
                'format' => DatabaseBackup::FORMAT_ZIP,
                'status' => DatabaseBackup::STATUS_COMPLETED,
                'disk' => (string) config('backups.disk', 'backups'),
                'path' => $path,
                'filename' => $filename,
                'database_driver' => $manifest['database_driver'] ?? null,
                'size_bytes' => $size,
                'checksum_sha256' => $checksum,
                'record_count' => (int) ($manifest['record_count'] ?? 0),
                'photo_count' => (int) ($manifest['file_count'] ?? 0),
                'manifest_version' => (int) $manifest['version'],
                'started_at' => now(),
                'completed_at' => now(),
                'integrity_verified_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $disk->delete([$partPath, $path]);
            throw $exception;
        } finally {
            $this->releaseLock($lock);
        }
    }

    public function inspect(DatabaseBackup $backup, Fundo|int $fundo): array
    {
        $fundoId = $fundo instanceof Fundo ? $fundo->getKey() : $fundo;
        $backup = DatabaseBackup::query()->forFundo($fundoId)->findOrFail($backup->getKey());
        $this->assertRestorableRecord($backup);
        $this->verifyRecordChecksum($backup);

        return $this->readManifest(Storage::disk($backup->disk)->path($backup->path), $fundoId);
    }

    public function restore(
        DatabaseBackup $backup,
        Fundo $fundo,
        User $requestedBy,
        string $mode,
        ?int $retentionCount = null,
    ): BackupRestore {
        $backup = DatabaseBackup::query()->forFundo($fundo)->findOrFail($backup->getKey());
        $restore = BackupRestore::create([
            'fundo_id' => $fundo->getKey(),
            'database_backup_id' => $backup->getKey(),
            'requested_by' => $requestedBy->getKey(),
            'mode' => $mode,
            'status' => DatabaseBackup::STATUS_PENDING,
        ]);
        $lock = $this->lock($fundo);

        if (! $lock->get()) {
            $exception = new RuntimeException('Ya existe una operación de backup o restauración para este fundo.');
            $this->markRestoreFailed($restore, $exception);
            throw $exception;
        }

        try {
            $this->assertRestorableRecord($backup);
            $this->verifyRecordChecksum($backup);
            $archivePath = Storage::disk($backup->disk)->path($backup->path);
            $manifest = $this->readManifest($archivePath, $fundo->getKey());
            $components = $this->normalizeComponents($manifest['components'] ?? []);
            $this->assertRestoreMode($manifest['type'], $mode);
            if ($components['web'] && in_array($mode, self::DATABASE_TYPES, true)) {
                $canRestoreWeb = $requestedBy->fundos()
                    ->whereKey($fundo->getKey())
                    ->wherePivot('es_administrador', true)
                    ->exists();
                if (! $canRestoreWeb) {
                    throw new RuntimeException('Solo un administrador del fundo puede restaurar Gestión web.');
                }
            }
            $this->assertTenantIntegrity($fundo->getKey());

            $restore->update(['status' => DatabaseBackup::STATUS_RUNNING, 'started_at' => now()]);
            $preBackupType = match ($mode) {
                DatabaseBackup::TYPE_DATABASE => DatabaseBackup::TYPE_DATABASE,
                DatabaseBackup::TYPE_FILES => DatabaseBackup::TYPE_FILES,
                default => DatabaseBackup::TYPE_COMPLETE,
            };
            $preBackup = $this->newBackupRecord($fundo, $requestedBy, DatabaseBackup::TRIGGER_PRE_RESTORE, $preBackupType, $components);
            try {
                $preBackup = $this->writeArchive($preBackup, $fundo, $preBackupType, $retentionCount, false, $components);
            } catch (Throwable $exception) {
                $this->markFailed($preBackup, $exception);
                throw $exception;
            }
            $restore->update(['pre_backup_id' => $preBackup->getKey()]);

            $zip = $this->openValidatedArchive($archivePath, $fundo->getKey(), $manifest);
            try {
                $this->verifyArchiveEntries($zip, $manifest);

                if ($mode === DatabaseBackup::TYPE_FILES) {
                    $currentFiles = $this->fileInventory($fundo->getKey(), false, $components['web']);
                    if (! hash_equals((string) $manifest['reference_fingerprint'], $currentFiles['fingerprint'])) {
                        throw new RuntimeException('Las referencias de archivos cambiaron. Usa restauración completa para evitar archivos desalineados.');
                    }
                }

                if ($mode === DatabaseBackup::TYPE_DATABASE) {
                    $this->assertReferencedFilesExist($manifest);
                }

                if (in_array($mode, self::FILE_TYPES, true)) {
                    $this->restoreFiles($zip, $manifest, $fundo->getKey());
                }
                if (in_array($mode, self::DATABASE_TYPES, true)) {
                    $this->restoreDatabase($zip, $manifest, $fundo);
                }
            } finally {
                $zip->close();
            }

            $this->assertTenantIntegrity($fundo->getKey());
            $restore->update([
                'status' => DatabaseBackup::STATUS_COMPLETED,
                'completed_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ]);

            return $restore->fresh(['preBackup']);
        } catch (Throwable $exception) {
            $this->markRestoreFailed($restore, $exception);
            throw $exception;
        } finally {
            $this->releaseLock($lock);
        }
    }

    public function delete(DatabaseBackup $backup, Fundo|int $fundo): void
    {
        $fundoId = $fundo instanceof Fundo ? $fundo->getKey() : $fundo;
        $backup = DatabaseBackup::query()->forFundo($fundoId)->findOrFail($backup->getKey());
        if ($backup->restores()->where('status', DatabaseBackup::STATUS_RUNNING)->exists()) {
            throw new RuntimeException('No se puede eliminar un backup durante una restauración.');
        }

        if ($backup->path) {
            Storage::disk($backup->disk)->delete([$backup->path, $backup->path.'.part']);
        }
        $backup->delete();
    }

    private function newBackupRecord(Fundo $fundo, ?User $requestedBy, string $trigger, string $type, array $components = []): DatabaseBackup
    {
        return DatabaseBackup::create([
            'fundo_id' => $fundo->getKey(),
            'requested_by' => $requestedBy?->getKey(),
            'trigger' => $trigger,
            'type' => $type,
            'components' => $this->normalizeComponents($components),
            'format' => DatabaseBackup::FORMAT_ZIP,
            'status' => DatabaseBackup::STATUS_PENDING,
            'disk' => (string) config('backups.disk', 'backups'),
            'database_driver' => $this->databaseDriver(DB::connection()),
            'manifest_version' => self::MANIFEST_VERSION,
        ]);
    }

    private function writeArchive(
        DatabaseBackup $backup,
        Fundo $fundo,
        string $type,
        ?int $retentionCount,
        bool $applyRetention,
        array $components = [],
    ): DatabaseBackup {
        $components = $this->normalizeComponents($components);
        $this->assertTenantIntegrity($fundo->getKey());
        $disk = Storage::disk($backup->disk);
        $timestamp = now()->format('Ymd_His');
        $filename = sprintf('fundo-%d_%s_%s_%s.zip', $fundo->getKey(), $type, $timestamp, $backup->uuid);
        $path = sprintf('fundos/%d/%s', $fundo->getKey(), $filename);
        $partPath = $path.'.part';
        $workDirectory = sprintf('.work/%s', $backup->uuid);
        $sqlPath = $workDirectory.'/database.sql';
        $dataPath = $workDirectory.'/database.ndjson';
        $systemPath = $workDirectory.'/components.ndjson';

        $backup->update([
            'status' => DatabaseBackup::STATUS_RUNNING,
            'path' => $path,
            'filename' => $filename,
            'started_at' => now(),
            'error_message' => null,
        ]);
        $disk->makeDirectory(dirname($partPath));
        $disk->makeDirectory($workDirectory);

        try {
            $inventory = $this->fileInventory(
                $fundo->getKey(),
                in_array($type, self::FILE_TYPES, true),
                $components['web'],
            );
            if (in_array($type, self::FILE_TYPES, true)
                && $inventory['missing'] !== []
                && $backup->trigger !== DatabaseBackup::TRIGGER_PRE_RESTORE) {
                throw new RuntimeException('Existen archivos referenciados que no están disponibles: '.count($inventory['missing']).'.');
            }

            $database = null;
            $recordCount = 0;
            if (in_array($type, self::DATABASE_TYPES, true)) {
                $database = $this->writeDatabaseFiles($disk->path($sqlPath), $disk->path($dataPath), $fundo);
                $recordCount = $database['record_count'];
            }

            $system = null;
            if ($components['web'] || $components['audit']) {
                $system = $this->writeSystemDataFile($disk->path($systemPath), $fundo, $components);
                $recordCount += $system['record_count'];
            }

            $manifest = $this->manifest($backup, $fundo, $type, $components, $database, $system, $inventory, $recordCount);
            $zip = new ZipArchive;
            $opened = $zip->open($disk->path($partPath), ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($opened !== true) {
                throw new RuntimeException('No se pudo crear archivo ZIP de respaldo.');
            }

            try {
                $password = $this->archivePassword($fundo->getKey());
                $zip->setPassword($password);
                $this->addEncryptedString($zip, self::MANIFEST_ENTRY, $this->encodeJson($manifest), $password);
                if ($database) {
                    $this->addEncryptedFile($zip, $disk->path($sqlPath), self::DATABASE_SQL_ENTRY, $password);
                    $this->addEncryptedFile($zip, $disk->path($dataPath), self::DATABASE_DATA_ENTRY, $password);
                }
                if ($system) {
                    $this->addEncryptedFile($zip, $disk->path($systemPath), self::SYSTEM_DATA_ENTRY, $password);
                }
                foreach ($inventory['files'] as $file) {
                    $source = Storage::disk($file['disk'])->path($file['path']);
                    $this->addEncryptedFile($zip, $source, $file['entry'], $password);
                }
            } finally {
                if (! $zip->close()) {
                    throw new RuntimeException('No se pudo finalizar archivo ZIP de respaldo.');
                }
            }

            $disk->move($partPath, $path);
            $size = (int) $disk->size($path);
            $checksum = $this->hashFile($disk->path($path));
            $backup->update([
                'status' => DatabaseBackup::STATUS_COMPLETED,
                'size_bytes' => $size,
                'checksum_sha256' => $checksum,
                'record_count' => $recordCount,
                'photo_count' => count($inventory['files']),
                'completed_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ]);

            if ($applyRetention) {
                $this->prune($fundo, $type, $retentionCount ?? (int) config('backups.retention_count', 10));
            }

            return $backup->fresh();
        } finally {
            $disk->deleteDirectory($workDirectory);
        }
    }

    private function writeDatabaseFiles(string $sqlPath, string $dataPath, Fundo $fundo): array
    {
        $sql = fopen($sqlPath, 'wb');
        $data = fopen($dataPath, 'wb');
        if (! is_resource($sql) || ! is_resource($data)) {
            throw new RuntimeException('No se pudieron crear archivos temporales de base de datos.');
        }

        $driver = $this->databaseDriver(DB::connection());
        $recordCount = 0;
        try {
            DB::connection()->transaction(function () use ($sql, $data, $fundo, $driver, &$recordCount): void {
                $this->writeStream($sql, $this->header($fundo, $driver));
                foreach ($this->tableQueries($fundo->getKey()) as $table => $query) {
                    $columns = array_values(array_diff(
                        DB::getSchemaBuilder()->getColumnListing($table),
                        self::GENERATED_COLUMNS[$table] ?? [],
                    ));
                    $this->writeStream($sql, "\n-- Table: {$table}\n");
                    $this->writeStream($sql, $this->createTableSql($table, $driver).";\n");
                    $quotedColumns = implode(', ', array_map(fn (string $column) => $this->quoteIdentifier($column, $driver), $columns));
                    $query->orderBy($columns[0]);

                    foreach ($query->cursor() as $row) {
                        $payload = [];
                        foreach ($columns as $column) {
                            $payload[$column] = $row->{$column};
                        }
                        $values = array_map(fn (string $column) => $this->sqlValue($row->{$column}), $columns);
                        $this->writeStream($sql, sprintf(
                            'INSERT INTO %s (%s) VALUES (%s);'."\n",
                            $this->quoteIdentifier($table, $driver),
                            $quotedColumns,
                            implode(', ', $values),
                        ));
                        $this->writeStream($data, $this->encodeJson(['table' => $table, 'row' => $payload])."\n");
                        $recordCount++;
                    }
                }
                $this->writeStream($sql, $driver === 'sqlite' ? "PRAGMA foreign_keys=ON;\n" : "SET FOREIGN_KEY_CHECKS=1;\n");
            });
        } finally {
            fclose($sql);
            fclose($data);
        }

        return [
            'record_count' => $recordCount,
            'sql' => ['entry' => self::DATABASE_SQL_ENTRY, 'size' => filesize($sqlPath), 'sha256' => $this->hashFile($sqlPath)],
            'data' => ['entry' => self::DATABASE_DATA_ENTRY, 'size' => filesize($dataPath), 'sha256' => $this->hashFile($dataPath)],
        ];
    }

    private function writeSystemDataFile(string $path, Fundo $fundo, array $components): array
    {
        $stream = fopen($path, 'wb');
        if (! is_resource($stream)) {
            throw new RuntimeException('No se pudo crear archivo temporal de componentes adicionales.');
        }

        $recordCount = 0;
        $tables = [];
        try {
            DB::connection()->transaction(function () use ($stream, $fundo, $components, &$recordCount, &$tables): void {
                foreach ($this->systemTableQueries($fundo->getKey(), $components) as $table => $query) {
                    $columns = DB::getSchemaBuilder()->getColumnListing($table);
                    $tableCount = 0;
                    $query->orderBy($columns[0]);

                    foreach ($query->cursor() as $row) {
                        $payload = [];
                        foreach ($columns as $column) {
                            $payload[$column] = $row->{$column};
                        }
                        $this->writeStream($stream, $this->encodeJson(['table' => $table, 'row' => $payload])."\n");
                        $recordCount++;
                        $tableCount++;
                    }
                    $tables[$table] = $tableCount;
                }
            });
        } finally {
            fclose($stream);
        }

        return [
            'entry' => self::SYSTEM_DATA_ENTRY,
            'size' => filesize($path),
            'sha256' => $this->hashFile($path),
            'record_count' => $recordCount,
            'tables' => $tables,
        ];
    }

    private function manifest(
        DatabaseBackup $backup,
        Fundo $fundo,
        string $type,
        array $components,
        ?array $database,
        ?array $system,
        array $inventory,
        int $recordCount,
    ): array {
        $manifest = [
            'version' => self::MANIFEST_VERSION,
            'backup_uuid' => $backup->uuid,
            'created_at' => now()->toIso8601String(),
            'installation_id' => $this->installationId(),
            'migrations_fingerprint' => $this->migrationsFingerprint(),
            'fundo' => ['id' => $fundo->getKey(), 'name' => $fundo->nombre],
            'type' => $type,
            'components' => $components,
            'format' => DatabaseBackup::FORMAT_ZIP,
            'database_driver' => $backup->database_driver,
            'record_count' => $recordCount,
            'file_count' => count($inventory['files']),
            'reference_fingerprint' => $inventory['fingerprint'],
            'references' => $inventory['references'],
            'missing_files' => $inventory['missing'],
            'database' => $database,
            'system' => $system,
            'files' => $inventory['files'],
            'encryption' => 'AES-256',
        ];
        $manifest['signature'] = $this->signManifest($manifest);

        return $manifest;
    }

    private function fileInventory(int $fundoId, bool $includeContents, bool $includeWeb = false): array
    {
        $files = [];
        $missing = [];
        $add = function (string $disk, ?string $path, string $table, int $id, string $column) use (&$files, &$missing, $includeContents): void {
            if (! is_string($path) || trim($path) === '') {
                return;
            }
            $path = $this->safeRelativePath($path);
            $key = $disk.'|'.$path;
            $owner = ['table' => $table, 'id' => $id, 'column' => $column];

            if (! Storage::disk($disk)->exists($path)) {
                $missing[$key] ??= ['disk' => $disk, 'path' => $path, 'owners' => []];
                $missing[$key]['owners'][] = $owner;

                return;
            }

            $files[$key] ??= [
                'disk' => $disk,
                'path' => $path,
                'entry' => 'files/'.$disk.'/'.$path,
                'size' => $includeContents ? (int) Storage::disk($disk)->size($path) : null,
                'sha256' => $includeContents ? $this->storageHash($disk, $path) : null,
                'owners' => [],
            ];
            $files[$key]['owners'][] = $owner;
        };

        $this->collectFileColumn($add, 'animales', 'foto_ruta', 'public', $fundoId);
        $this->collectFileColumn($add, 'lotes_engorde', 'foto_ruta', 'public', $fundoId);
        $this->collectFileColumn($add, 'ordeno_fotos_diarias', 'foto_ruta', 'public', $fundoId);
        $this->collectFileColumn($add, 'producciones_queso', 'foto_ruta', 'public', $fundoId);
        $this->collectFileColumn($add, 'movimientos', 'comprobante_ruta', 'local', $fundoId);
        $this->collectFileColumn($add, 'asignaciones_familiares', 'foto_ruta', 'local', $fundoId);
        $this->collectFileColumn($add, 'registro_fotos', 'ruta', 'local', $fundoId);

        $fundoLogo = DB::table('fundos')->where('id', $fundoId)->value('logo_ruta');
        if (is_string($fundoLogo) && $fundoLogo !== '') {
            $logoPath = $this->safeRelativePath($fundoLogo);
            $logoDisk = Storage::disk('public')->exists($logoPath) ? 'public' : 'local';
            $add($logoDisk, $logoPath, 'fundos', $fundoId, 'logo_ruta');
        }

        DB::table('sanidad_registros')->where('fundo_id', $fundoId)->whereNotNull('evidencia_ruta')->orderBy('id')->get(['id', 'evidencia_ruta'])->each(
            function ($row) use ($add): void {
                $path = $this->safeRelativePath($row->evidencia_ruta);
                $local = Storage::disk('local')->exists($path);
                $public = Storage::disk('public')->exists($path);
                if ($local && $public && ! hash_equals($this->storageHash('local', $path), $this->storageHash('public', $path))) {
                    throw new RuntimeException("Ruta ambigua con contenido distinto en discos public/local: {$path}");
                }
                $add($local ? 'local' : ($public ? 'public' : 'local'), $path, 'sanidad_registros', (int) $row->id, 'evidencia_ruta');
            }
        );

        if ($includeWeb) {
            $landingIds = DB::table('landing_blocks')->pluck('id');
            $prefix = trim((string) config('media-library.prefix', ''), '/');
            DB::table('media')
                ->where('model_type', LandingBlock::class)
                ->whereIn('model_id', $landingIds)
                ->orderBy('id')
                ->get(['id', 'file_name', 'disk', 'conversions_disk'])
                ->each(function ($media) use ($add, $prefix): void {
                    $directory = ($prefix !== '' ? $prefix.'/' : '').$media->id;
                    $disk = (string) $media->disk;
                    $conversionsDisk = (string) ($media->conversions_disk ?: $disk);
                    foreach (array_unique([$disk, $conversionsDisk]) as $mediaDisk) {
                        if (! in_array($mediaDisk, ['local', 'public'], true)) {
                            throw new RuntimeException("Disco de Gestión web no permitido en backup: {$mediaDisk}.");
                        }
                    }

                    $add($disk, $directory.'/'.$media->file_name, 'media', (int) $media->id, 'file_name');
                    foreach (array_unique([$disk, $conversionsDisk]) as $mediaDisk) {
                        foreach (Storage::disk($mediaDisk)->allFiles($directory) as $path) {
                            $add($mediaDisk, $path, 'media', (int) $media->id, 'files');
                        }
                    }
                });

            $brandLogo = DB::table('branding_settings')->where('id', 1)->value('logo_path');
            if (is_string($brandLogo) && trim($brandLogo) !== '') {
                $add('public', $brandLogo, 'branding_settings', 1, 'logo_path');
            }
        }

        ksort($files);
        ksort($missing);
        foreach ($files as &$file) {
            usort($file['owners'], fn (array $a, array $b) => [$a['table'], $a['id'], $a['column']] <=> [$b['table'], $b['id'], $b['column']]);
        }
        unset($file);
        foreach ($missing as &$file) {
            usort($file['owners'], fn (array $a, array $b) => [$a['table'], $a['id'], $a['column']] <=> [$b['table'], $b['id'], $b['column']]);
        }
        unset($file);

        $allReferences = $files + $missing;
        ksort($allReferences);
        $references = array_map(
            fn (array $file) => ['disk' => $file['disk'], 'path' => $file['path'], 'owners' => $file['owners']],
            array_values($allReferences),
        );

        return [
            'files' => $includeContents ? array_values($files) : [],
            'references' => $references,
            'missing' => array_values($missing),
            'fingerprint' => hash('sha256', $this->encodeJson($references)),
        ];
    }

    private function collectFileColumn(callable $add, string $table, string $column, string $disk, int $fundoId): void
    {
        DB::table($table)->where('fundo_id', $fundoId)->whereNotNull($column)->orderBy('id')->get(['id', $column])->each(
            fn ($row) => $add($disk, $row->{$column}, $table, (int) $row->id, $column)
        );
    }

    private function restoreDatabase(ZipArchive $zip, array $manifest, Fundo $fundo): void
    {
        if (($manifest['migrations_fingerprint'] ?? null) !== $this->migrationsFingerprint()) {
            throw new RuntimeException('Esquema incompatible. Actualiza aplicación antes de restaurar este backup.');
        }

        $stream = $zip->getStream(self::DATABASE_DATA_ENTRY);
        if (! is_resource($stream)) {
            throw new RuntimeException('No se pudo leer contenido estructurado de base de datos.');
        }

        $components = $this->normalizeComponents($manifest['components'] ?? []);
        $systemStream = null;
        if ($components['web']) {
            if (! is_array($manifest['system'] ?? null)) {
                fclose($stream);
                throw new RuntimeException('Backup no contiene datos declarados de Gestión web.');
            }
            $systemStream = $zip->getStream(self::SYSTEM_DATA_ENTRY);
            if (! is_resource($systemStream)) {
                fclose($stream);
                throw new RuntimeException('No se pudo leer contenido de Gestión web.');
            }
        }

        try {
            DB::transaction(function () use ($stream, $systemStream, $components, $fundo): void {
                $this->restoreFundoRows($stream, $fundo);
                if (is_resource($systemStream)) {
                    $this->restoreSystemWebRows($systemStream, $components);
                }
            }, 1);
        } finally {
            fclose($stream);
            if (is_resource($systemStream)) {
                fclose($systemStream);
            }
        }

        Cache::forget('queso.dashboard.v1.'.$fundo->getKey());
        if ($components['web']) {
            app(SystemBranding::class)->invalidate();
        }
    }

    private function restoreFundoRows($stream, Fundo $fundo): void
    {
        $this->deleteFundoData($fundo->getKey());
        $order = array_flip(array_keys($this->tableQueries($fundo->getKey())));
        $lastOrder = -1;
        $currentTable = null;
        $rows = [];

        $flush = function () use (&$currentTable, &$rows, $fundo): void {
            if ($currentTable !== null && $rows !== []) {
                $this->insertRestoredRows($currentTable, $rows, $fundo);
            }
            $rows = [];
        };

        while (($line = fgets($stream)) !== false) {
            $entry = $this->decodeJson(trim($line));
            $table = $entry['table'] ?? null;
            $row = $entry['row'] ?? null;
            if (! is_string($table) || ! is_array($row) || ! isset($order[$table])) {
                throw new RuntimeException('Backup contiene tabla o registro no permitido.');
            }
            if ($order[$table] < $lastOrder) {
                throw new RuntimeException('Orden de tablas inválido en backup.');
            }
            if ($currentTable !== $table || count($rows) >= 500) {
                $flush();
                $currentTable = $table;
            }
            $lastOrder = $order[$table];
            $rows[] = $row;
        }
        $flush();
    }

    private function restoreSystemWebRows($stream, array $components): void
    {
        $allowed = [];
        if ($components['web']) {
            $allowed += ['landing_blocks' => 0, 'media' => 1, 'branding_settings' => 2];
            DB::table('media')->where('model_type', LandingBlock::class)->delete();
            DB::table('landing_blocks')->delete();
        }
        if ($components['audit']) {
            $allowed['auditoria_logs'] = 3;
        }

        $lastOrder = -1;
        $currentTable = null;
        $rows = [];
        $flush = function () use (&$currentTable, &$rows): void {
            if ($currentTable !== null && $currentTable !== 'auditoria_logs' && $rows !== []) {
                $this->insertRestoredSystemRows($currentTable, $rows);
            }
            $rows = [];
        };

        while (($line = fgets($stream)) !== false) {
            $entry = $this->decodeJson(trim($line));
            $table = $entry['table'] ?? null;
            $row = $entry['row'] ?? null;
            if (! is_string($table) || ! is_array($row) || ! isset($allowed[$table])) {
                throw new RuntimeException('Componente adicional contiene tabla o registro no permitido.');
            }
            if ($allowed[$table] < $lastOrder) {
                throw new RuntimeException('Orden de componentes adicionales inválido.');
            }
            if ($currentTable !== $table || count($rows) >= 500) {
                $flush();
                $currentTable = $table;
            }
            $lastOrder = $allowed[$table];
            $rows[] = $row;
        }
        $flush();
    }

    private function insertRestoredSystemRows(string $table, array $rows): void
    {
        $columns = array_flip(Schema::getColumnListing($table));
        foreach ($rows as $row) {
            if (array_diff_key($row, $columns) !== []) {
                throw new RuntimeException("Componente contiene columnas desconocidas en {$table}.");
            }
        }

        if ($table === 'branding_settings') {
            $row = $rows[0] ?? [];
            $row['id'] = 1;
            DB::table($table)->updateOrInsert(['id' => 1], $row);

            return;
        }

        if ($table === 'media') {
            foreach ($rows as $row) {
                if (($row['model_type'] ?? null) !== LandingBlock::class
                    || ! DB::table('landing_blocks')->where('id', $row['model_id'] ?? null)->exists()) {
                    throw new RuntimeException('Medio web no pertenece a una sección válida.');
                }
            }
        }

        if ($rows !== []) {
            DB::table($table)->insert($rows);
        }
    }

    private function deleteFundoData(int $fundoId): void
    {
        $queries = array_reverse($this->tableQueries($fundoId), true);
        foreach ($queries as $table => $query) {
            if (in_array($table, ['fundos', ...self::GLOBAL_TABLES], true)) {
                continue;
            }
            if (in_array($table, self::MIXED_TABLES, true)) {
                DB::table($table)->where('fundo_id', $fundoId)->delete();

                continue;
            }
            if ($table === 'configuracion_sistema') {
                DB::table($table)->where('fundo_id', $fundoId)->where('clave', 'not like', 'backup_%')->delete();

                continue;
            }
            $query->delete();
        }
    }

    private function insertRestoredRows(string $table, array $rows, Fundo $fundo): void
    {
        if ($table === 'fundos') {
            $row = $rows[0] ?? [];
            $allowed = array_intersect_key($row, array_flip([
                'nombre', 'ruc', 'direccion', 'departamento', 'provincia', 'distrito', 'logo_ruta', 'updated_at',
            ]));
            DB::table('fundos')->where('id', $fundo->getKey())->update($allowed);

            return;
        }

        if (in_array($table, self::GLOBAL_TABLES, true)) {
            DB::table($table)->insertOrIgnore($rows);

            return;
        }

        if (in_array($table, self::MIXED_TABLES, true)) {
            $global = array_values(array_filter($rows, fn (array $row) => $row['fundo_id'] === null));
            $local = array_values(array_filter($rows, fn (array $row) => (int) $row['fundo_id'] === $fundo->getKey()));
            if ($global !== []) {
                DB::table($table)->insertOrIgnore($global);
            }
            if ($local !== []) {
                DB::table($table)->insert($local);
            }

            return;
        }

        if ($table === 'configuracion_sistema') {
            $rows = array_values(array_filter($rows, fn (array $row) => ! str_starts_with((string) $row['clave'], 'backup_')));
        }

        foreach ($rows as $row) {
            if (array_key_exists('fundo_id', $row) && (int) $row['fundo_id'] !== $fundo->getKey()) {
                throw new RuntimeException("Registro de otro fundo detectado en {$table}.");
            }
        }
        if ($rows !== []) {
            DB::table($table)->insert($rows);
        }
    }

    private function restoreFiles(ZipArchive $zip, array $manifest, int $fundoId): void
    {
        foreach ($manifest['files'] as $file) {
            $disk = (string) $file['disk'];
            $path = $this->safeRelativePath((string) $file['path']);
            if (! in_array($disk, ['local', 'public'], true)) {
                throw new RuntimeException('Disco de archivo no permitido en backup.');
            }

            if (Storage::disk($disk)->exists($path)) {
                $currentHash = $this->storageHash($disk, $path);
                if (hash_equals((string) $file['sha256'], $currentHash)) {
                    continue;
                }
                if ($this->pathReferencedByOtherFundo($disk, $path, $fundoId)) {
                    throw new RuntimeException("Archivo compartido por otro fundo no puede sobrescribirse: {$path}");
                }
            }

            $stream = $zip->getStream((string) $file['entry']);
            if (! is_resource($stream)) {
                throw new RuntimeException("No se pudo extraer archivo {$path}.");
            }
            try {
                if (! Storage::disk($disk)->writeStream($path, $stream)) {
                    throw new RuntimeException("No se pudo restaurar archivo {$path}.");
                }
            } finally {
                fclose($stream);
            }
            if (! hash_equals((string) $file['sha256'], $this->storageHash($disk, $path))) {
                throw new RuntimeException("Integridad inválida después de restaurar archivo {$path}.");
            }
        }
    }

    private function pathReferencedByOtherFundo(string $disk, string $path, int $fundoId): bool
    {
        $sources = $disk === 'public'
            ? [['animales', 'foto_ruta'], ['lotes_engorde', 'foto_ruta'], ['ordeno_fotos_diarias', 'foto_ruta'], ['producciones_queso', 'foto_ruta']]
            : [['movimientos', 'comprobante_ruta'], ['asignaciones_familiares', 'foto_ruta'], ['registro_fotos', 'ruta']];

        foreach ($sources as [$table, $column]) {
            if (DB::table($table)->where('fundo_id', '!=', $fundoId)->where($column, $path)->exists()) {
                return true;
            }
        }

        if (DB::table('fundos')->where('id', '!=', $fundoId)->where('logo_ruta', $path)->exists()) {
            return true;
        }

        return DB::table('sanidad_registros')->where('fundo_id', '!=', $fundoId)->where('evidencia_ruta', $path)->exists();
    }

    private function assertReferencedFilesExist(array $manifest): void
    {
        if (($manifest['missing_files'] ?? []) !== []) {
            throw new RuntimeException('Backup de base de datos registra archivos faltantes. Usa un backup completo.');
        }
        foreach ($manifest['references'] as $file) {
            if (! Storage::disk($file['disk'])->exists($file['path'])) {
                throw new RuntimeException('Falta archivo referenciado. Usa restauración completa: '.$file['path']);
            }
        }
    }

    private function verifyArchiveEntries(ZipArchive $zip, array $manifest): void
    {
        $entries = [];
        if (is_array($manifest['database'] ?? null)) {
            $entries[] = $manifest['database']['sql'];
            $entries[] = $manifest['database']['data'];
        }
        if (is_array($manifest['system'] ?? null)) {
            $entries[] = $manifest['system'];
        }
        foreach ($manifest['files'] as $file) {
            $entries[] = $file;
        }

        foreach ($entries as $entry) {
            $stream = $zip->getStream((string) $entry['entry']);
            if (! is_resource($stream)) {
                throw new RuntimeException('No se pudo leer entrada cifrada: '.$entry['entry']);
            }
            try {
                $hash = hash_init('sha256');
                $size = hash_update_stream($hash, $stream);
                $checksum = hash_final($hash);
            } finally {
                fclose($stream);
            }
            if ($size !== (int) $entry['size'] || ! hash_equals((string) $entry['sha256'], $checksum)) {
                throw new RuntimeException('Integridad interna inválida: '.$entry['entry']);
            }
        }
    }

    private function readManifest(string $archivePath, int $fundoId): array
    {
        $zip = $this->openValidatedArchive($archivePath, $fundoId);
        try {
            $raw = $zip->getFromName(self::MANIFEST_ENTRY);
            if (! is_string($raw) || strlen($raw) > 2 * 1024 * 1024) {
                throw new RuntimeException('Manifest cifrado ausente o inválido.');
            }
            $manifest = $this->decodeJson($raw);
            $this->validateManifest($manifest, $fundoId);
            $this->assertDeclaredEntries($zip, $manifest);

            return $manifest;
        } finally {
            $zip->close();
        }
    }

    private function openValidatedArchive(string $archivePath, int $fundoId, ?array $knownManifest = null): ZipArchive
    {
        $zip = new ZipArchive;
        $opened = $zip->open($archivePath, ZipArchive::RDONLY);
        if ($opened !== true) {
            throw new RuntimeException('ZIP corrupto o no compatible.');
        }
        $zip->setPassword($this->archivePassword($fundoId));

        try {
            $this->validateZipStructure($zip);
            if ($knownManifest !== null) {
                $this->validateManifest($knownManifest, $fundoId);
                $this->assertDeclaredEntries($zip, $knownManifest);
            }
        } catch (Throwable $exception) {
            $zip->close();
            throw $exception;
        }

        return $zip;
    }

    private function validateZipStructure(ZipArchive $zip): void
    {
        if ($zip->numFiles < 1 || $zip->numFiles > (int) config('backups.max_entries', 50000)) {
            throw new RuntimeException('Cantidad de entradas ZIP fuera de límite.');
        }
        $seen = [];
        $total = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            if (! is_array($stat) || ! isset($stat['name'])) {
                throw new RuntimeException('Entrada ZIP inválida.');
            }
            $name = $this->safeArchiveEntry((string) $stat['name']);
            $folded = mb_strtolower($name);
            if (isset($seen[$folded])) {
                throw new RuntimeException('ZIP contiene nombres duplicados.');
            }
            $seen[$folded] = true;
            $size = (int) ($stat['size'] ?? 0);
            $compressed = max(1, (int) ($stat['comp_size'] ?? 0));
            $total += $size;
            if ($size / $compressed > (float) config('backups.max_compression_ratio', 1000)) {
                throw new RuntimeException('ZIP rechazado por ratio de compresión inseguro.');
            }
        }
        if ($total > (int) config('backups.max_uncompressed_bytes', 20 * 1024 * 1024 * 1024)) {
            throw new RuntimeException('Contenido ZIP excede tamaño permitido.');
        }
    }

    private function validateManifest(array $manifest, int $fundoId): void
    {
        $version = (int) ($manifest['version'] ?? 0);
        if (! in_array($version, [1, self::MANIFEST_VERSION], true)) {
            throw new RuntimeException('Versión de backup no compatible.');
        }
        if (! hash_equals($this->installationId(), (string) ($manifest['installation_id'] ?? ''))) {
            throw new RuntimeException('Backup pertenece a otra instalación. Importación cruzada requiere remapeo y no está permitida.');
        }
        if ((int) ($manifest['fundo']['id'] ?? 0) !== $fundoId) {
            throw new RuntimeException('Backup pertenece a otro fundo.');
        }
        $this->assertSupportedType((string) ($manifest['type'] ?? ''));
        $components = $this->normalizeComponents($manifest['components'] ?? []);
        if ($version >= 2 && ($components['web'] || $components['audit']) !== is_array($manifest['system'] ?? null)) {
            throw new RuntimeException('Declaración de componentes adicionales inválida.');
        }
        if (($manifest['format'] ?? null) !== DatabaseBackup::FORMAT_ZIP || ($manifest['encryption'] ?? null) !== 'AES-256') {
            throw new RuntimeException('Formato o cifrado de backup no válido.');
        }
        $signature = (string) ($manifest['signature'] ?? '');
        unset($manifest['signature']);
        if (! hash_equals($this->signManifest($manifest), $signature)) {
            throw new RuntimeException('Firma HMAC del backup no coincide.');
        }
    }

    private function assertDeclaredEntries(ZipArchive $zip, array $manifest): void
    {
        $expected = [self::MANIFEST_ENTRY];
        if (is_array($manifest['database'] ?? null)) {
            $expected[] = (string) $manifest['database']['sql']['entry'];
            $expected[] = (string) $manifest['database']['data']['entry'];
        }
        if (is_array($manifest['system'] ?? null)) {
            $expected[] = (string) $manifest['system']['entry'];
        }
        foreach ($manifest['files'] ?? [] as $file) {
            $expected[] = (string) $file['entry'];
        }
        $actual = [];
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $actual[] = (string) $zip->getNameIndex($index);
        }
        sort($expected);
        sort($actual);
        if ($expected !== $actual) {
            throw new RuntimeException('ZIP contiene entradas no declaradas o incompletas.');
        }
    }

    private function assertRestoreMode(string $archiveType, string $mode): void
    {
        $allowed = match ($archiveType) {
            DatabaseBackup::TYPE_DATABASE => [DatabaseBackup::TYPE_DATABASE],
            DatabaseBackup::TYPE_FILES => [DatabaseBackup::TYPE_FILES],
            DatabaseBackup::TYPE_COMPLETE => [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE],
            default => [],
        };
        if (! in_array($mode, $allowed, true)) {
            throw new RuntimeException('Modo de restauración no disponible para este backup.');
        }
    }

    private function assertRestorableRecord(DatabaseBackup $backup): void
    {
        if ($backup->status !== DatabaseBackup::STATUS_COMPLETED || $backup->format !== DatabaseBackup::FORMAT_ZIP || ! $backup->path) {
            throw new RuntimeException('Solo backups ZIP completados pueden restaurarse.');
        }
        if (! Storage::disk($backup->disk)->exists($backup->path)) {
            throw new RuntimeException('Archivo de backup no está disponible.');
        }
    }

    private function verifyRecordChecksum(DatabaseBackup $backup): void
    {
        $checksum = $this->hashFile(Storage::disk($backup->disk)->path($backup->path));
        if (! $backup->checksum_sha256 || ! hash_equals($backup->checksum_sha256, $checksum)) {
            $backup->update(['integrity_verified_at' => null]);
            throw new RuntimeException('Checksum SHA-256 del archivo no coincide.');
        }
        $backup->update(['integrity_verified_at' => now()]);
    }

    private function addEncryptedFile(ZipArchive $zip, string $source, string $entry, string $password): void
    {
        $this->safeArchiveEntry($entry);
        if (! $zip->addFile($source, $entry) || ! $zip->setEncryptionName($entry, ZipArchive::EM_AES_256, $password)) {
            throw new RuntimeException("No se pudo cifrar entrada ZIP {$entry}.");
        }
    }

    private function addEncryptedString(ZipArchive $zip, string $entry, string $contents, string $password): void
    {
        if (! $zip->addFromString($entry, $contents) || ! $zip->setEncryptionName($entry, ZipArchive::EM_AES_256, $password)) {
            throw new RuntimeException("No se pudo cifrar entrada ZIP {$entry}.");
        }
    }

    private function archivePassword(int $fundoId): string
    {
        return hash_hmac('sha256', 'fundo:'.$fundoId, $this->archiveSecret());
    }

    private function archiveSecret(): string
    {
        $secret = (string) config('backups.archive_key');
        if ($secret === '') {
            throw new RuntimeException('BACKUP_ARCHIVE_KEY no está configurada.');
        }
        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);
            if ($decoded !== false) {
                $secret = $decoded;
            }
        }

        return $secret;
    }

    private function installationId(): string
    {
        return hash_hmac('sha256', 'installation', $this->archiveSecret());
    }

    private function signManifest(array $manifest): string
    {
        return hash_hmac('sha256', $this->encodeJson($manifest), $this->archiveSecret());
    }

    private function migrationsFingerprint(): string
    {
        return hash('sha256', $this->encodeJson(DB::table('migrations')->orderBy('migration')->pluck('migration')->all()));
    }

    private function storageHash(string $disk, string $path): string
    {
        $stream = Storage::disk($disk)->readStream($path);
        if (! is_resource($stream)) {
            throw new RuntimeException("No se pudo leer archivo {$path}.");
        }
        try {
            $hash = hash_init('sha256');
            hash_update_stream($hash, $stream);

            return hash_final($hash);
        } finally {
            fclose($stream);
        }
    }

    private function safeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '' || str_contains($path, "\0") || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path)) {
            throw new RuntimeException('Ruta de archivo no permitida.');
        }
        $parts = explode('/', $path);
        if (in_array('..', $parts, true) || in_array('', $parts, true)) {
            throw new RuntimeException('Ruta de archivo no permitida.');
        }

        return implode('/', $parts);
    }

    private function safeArchiveEntry(string $entry): string
    {
        return $this->safeRelativePath($entry);
    }

    private function encodeJson(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('No se pudo serializar contenido del backup.', previous: $exception);
        }
    }

    private function decodeJson(string $value): array
    {
        try {
            $decoded = json_decode($value, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('JSON interno del backup no es válido.', previous: $exception);
        }
        if (! is_array($decoded)) {
            throw new RuntimeException('JSON interno del backup no es un objeto válido.');
        }

        return $decoded;
    }

    private function hashFile(string $path): string
    {
        $hash = hash_file('sha256', $path);
        if (! is_string($hash)) {
            throw new RuntimeException('No se pudo calcular checksum SHA-256.');
        }

        return $hash;
    }

    private function writeStream($stream, string $contents): void
    {
        if (fwrite($stream, $contents) !== strlen($contents)) {
            throw new RuntimeException('No se pudo escribir contenido completo del backup.');
        }
    }

    private function assertSupportedType(string $type): void
    {
        if (! in_array($type, [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE], true)) {
            throw new RuntimeException('Tipo de backup no soportado.');
        }
    }

    /** @return array{web: bool, audit: bool} */
    private function normalizeComponents(array $components): array
    {
        return [
            'web' => filter_var($components['web'] ?? false, FILTER_VALIDATE_BOOL),
            'audit' => filter_var($components['audit'] ?? false, FILTER_VALIDATE_BOOL),
        ];
    }

    private function assertSupportedTrigger(string $trigger): void
    {
        if (! in_array($trigger, [DatabaseBackup::TRIGGER_MANUAL, DatabaseBackup::TRIGGER_SCHEDULED, DatabaseBackup::TRIGGER_PRE_RESTORE], true)) {
            throw new RuntimeException('Origen de backup no soportado.');
        }
    }

    private function assertZipAvailable(): void
    {
        if (! class_exists(ZipArchive::class) || ! defined('ZipArchive::EM_AES_256')) {
            throw new RuntimeException('Extensión PHP zip con AES-256 no está disponible.');
        }
    }

    private function lock(Fundo|int $fundo): Lock
    {
        $id = $fundo instanceof Fundo ? $fundo->getKey() : $fundo;

        return Cache::lock('backup-operation:fundo:'.$id, (int) config('backups.lock_seconds', 3600));
    }

    private function assertTenantIntegrity(int $fundoId): void
    {
        $checks = [
            DB::table('engorde_animales as e')->join('lotes_engorde as l', 'l.id', '=', 'e.lote_id')->join('animales as a', 'a.id', '=', 'e.animal_id')->where('l.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('engorde_animales as e')->join('lotes_engorde as l', 'l.id', '=', 'e.lote_id')->join('animales as a', 'a.id', '=', 'e.animal_id')->where('a.fundo_id', $fundoId)->where('l.fundo_id', '!=', $fundoId),
            DB::table('ordeno_detalles as d')->join('ordenos as o', 'o.id', '=', 'd.ordeno_id')->join('animales as a', 'a.id', '=', 'd.animal_id')->where('o.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('ordeno_detalles as d')->join('ordenos as o', 'o.id', '=', 'd.ordeno_id')->join('animales as a', 'a.id', '=', 'd.animal_id')->where('a.fundo_id', $fundoId)->where('o.fundo_id', '!=', $fundoId),
            DB::table('sanidad_registros as s')->join('animales as a', 'a.id', '=', 's.animal_id')->where('s.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('sanidad_registros as s')->join('animales as a', 'a.id', '=', 's.animal_id')->where('a.fundo_id', $fundoId)->where('s.fundo_id', '!=', $fundoId),
            DB::table('profilaxis_animales as p')->join('profilaxis_registros as r', 'r.id', '=', 'p.profilaxis_id')->join('animales as a', 'a.id', '=', 'p.animal_id')->where('r.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('profilaxis_animales as p')->join('profilaxis_registros as r', 'r.id', '=', 'p.profilaxis_id')->join('animales as a', 'a.id', '=', 'p.animal_id')->where('a.fundo_id', $fundoId)->where('r.fundo_id', '!=', $fundoId),
            DB::table('animal_identifiers as i')->join('animales as a', 'a.id', '=', 'i.animal_id')->where('i.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('animal_identifiers as i')->join('animales as a', 'a.id', '=', 'i.animal_id')->where('a.fundo_id', $fundoId)->where('i.fundo_id', '!=', $fundoId),
            DB::table('partos as p')->join('animales as a', 'a.id', '=', 'p.animal_madre_id')->where('p.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('partos as p')->join('animales as a', 'a.id', '=', 'p.animal_madre_id')->where('a.fundo_id', $fundoId)->where('p.fundo_id', '!=', $fundoId),
            DB::table('partos as p')->join('animales as a', 'a.id', '=', 'p.cria_animal_id')->where('p.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('alertas_programadas as x')->join('animales as a', 'a.id', '=', 'x.animal_id')->where('x.fundo_id', $fundoId)->where('a.fundo_id', '!=', $fundoId),
            DB::table('movimientos as m')->join('categorias_financieras as c', 'c.id', '=', 'm.categoria_id')->where('m.fundo_id', $fundoId)->whereNotNull('c.fundo_id')->where('c.fundo_id', '!=', $fundoId),
            DB::table('sanidad_registros as s')->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')->where('s.fundo_id', $fundoId)->whereNotNull('m.fundo_id')->where('m.fundo_id', '!=', $fundoId),
        ];
        foreach ($checks as $check) {
            if ($check->exists()) {
                throw new RuntimeException('Se detectaron relaciones cruzadas entre fundos. Backup cancelado por seguridad.');
            }
        }
    }

    /** @return array<string, Builder> */
    private function systemTableQueries(int $fundoId, array $components): array
    {
        $queries = [];
        if ($components['web']) {
            $landingIds = fn (Builder $query) => $query->select('id')->from('landing_blocks');
            $queries['landing_blocks'] = DB::table('landing_blocks');
            $queries['media'] = DB::table('media')
                ->where('model_type', LandingBlock::class)
                ->whereIn('model_id', $landingIds);
            $queries['branding_settings'] = DB::table('branding_settings')->where('id', 1);
        }
        if ($components['audit']) {
            $userIds = fn (Builder $query) => $query->select('user_id')->from('fundo_user')->where('fundo_id', $fundoId);
            $queries['auditoria_logs'] = DB::table('auditoria_logs')->where(fn (Builder $query) => $query
                ->where('fundo_id', $fundoId)
                ->orWhere(fn (Builder $global) => $global->whereNull('fundo_id')->whereIn('user_id', $userIds)));
        }

        return $queries;
    }

    /** @return array<string, Builder> */
    private function tableQueries(int $fundoId): array
    {
        $animalIds = fn (Builder $query) => $query->select('id')->from('animales')->where('fundo_id', $fundoId);
        $loteIds = fn (Builder $query) => $query->select('id')->from('lotes_engorde')->where('fundo_id', $fundoId);
        $engordeIds = fn (Builder $query) => $query->select('id')->from('engorde_animales')->whereIn('lote_id', $loteIds);
        $ordenoIds = fn (Builder $query) => $query->select('id')->from('ordenos')->where('fundo_id', $fundoId);
        $quesoIds = fn (Builder $query) => $query->select('id')->from('producciones_queso')->where('fundo_id', $fundoId);
        $profilaxisIds = fn (Builder $query) => $query->select('id')->from('profilaxis_registros')->where('fundo_id', $fundoId);

        return [
            'fundos' => DB::table('fundos')->where('id', $fundoId),
            'especies' => DB::table('especies')->where(fn ($query) => $query
                ->whereIn('id', fn ($subquery) => $subquery->select('especie_id')->from('animales')->where('fundo_id', $fundoId))
                ->orWhereIn('id', fn ($subquery) => $subquery->select('especie_id')->from('animal_code_sequences')->where('fundo_id', $fundoId))),
            'razas' => DB::table('razas')->whereIn('id', fn ($query) => $query->select('raza_id')->from('animales')->where('fundo_id', $fundoId)),
            'categorias_financieras' => DB::table('categorias_financieras')->where(fn ($query) => $query->whereNull('fundo_id')->orWhere('fundo_id', $fundoId))->where(fn ($query) => $query->whereIn('id', fn ($subquery) => $subquery->select('categoria_id')->from('movimientos')->where('fundo_id', $fundoId))->orWhere('fundo_id', $fundoId)),
            'medicamentos' => DB::table('medicamentos')->where(fn ($query) => $query->whereNull('fundo_id')->orWhere('fundo_id', $fundoId))->where(fn ($query) => $query->whereIn('id', fn ($subquery) => $subquery->select('medicamento_id')->from('sanidad_registros')->where('fundo_id', $fundoId)->whereNotNull('medicamento_id'))->orWhere('fundo_id', $fundoId)),
            'animales' => DB::table('animales')->where('fundo_id', $fundoId),
            'animal_code_sequences' => DB::table('animal_code_sequences')->where('fundo_id', $fundoId),
            'animal_identifiers' => DB::table('animal_identifiers')->where('fundo_id', $fundoId),
            'lotes_engorde' => DB::table('lotes_engorde')->where('fundo_id', $fundoId),
            'lote_code_sequences' => DB::table('lote_code_sequences')->where('fundo_id', $fundoId),
            'engorde_animales' => DB::table('engorde_animales')->whereIn('lote_id', $loteIds),
            'pesajes_engorde' => DB::table('pesajes_engorde')->whereIn('engorde_animal_id', $engordeIds),
            'ordenos' => DB::table('ordenos')->where('fundo_id', $fundoId),
            'ordeno_detalles' => DB::table('ordeno_detalles')->whereIn('ordeno_id', $ordenoIds),
            'ordeno_fotos_diarias' => DB::table('ordeno_fotos_diarias')->where('fundo_id', $fundoId),
            'producciones_queso' => DB::table('producciones_queso')->where('fundo_id', $fundoId),
            'produccion_queso_presentaciones' => DB::table('produccion_queso_presentaciones')->whereIn('produccion_queso_id', $quesoIds),
            'movimientos' => DB::table('movimientos')->where('fundo_id', $fundoId),
            'asignaciones_familiares' => DB::table('asignaciones_familiares')->where('fundo_id', $fundoId),
            'sanidad_registros' => DB::table('sanidad_registros')->where('fundo_id', $fundoId),
            'profilaxis_registros' => DB::table('profilaxis_registros')->where('fundo_id', $fundoId),
            'profilaxis_animales' => DB::table('profilaxis_animales')->whereIn('profilaxis_id', $profilaxisIds)->whereIn('animal_id', $animalIds),
            'profilaxis_dosis_programadas' => DB::table('profilaxis_dosis_programadas')->whereIn('profilaxis_id', $profilaxisIds),
            'partos' => DB::table('partos')->where('fundo_id', $fundoId),
            'alertas_programadas' => DB::table('alertas_programadas')->where('fundo_id', $fundoId),
            'registro_fotos' => DB::table('registro_fotos')->where('fundo_id', $fundoId),
            'configuracion_sistema' => DB::table('configuracion_sistema')->where('fundo_id', $fundoId)->where('clave', 'not like', 'backup_%'),
        ];
    }

    private function createTableSql(string $table, string $driver): string
    {
        if ($driver === 'sqlite') {
            $sql = DB::table('sqlite_master')->where('type', 'table')->where('name', $table)->value('sql');
        } else {
            $row = (array) DB::selectOne('SHOW CREATE TABLE '.$this->quoteIdentifier($table, $driver));
            $sql = array_values($row)[1] ?? null;
        }
        if (! is_string($sql) || $sql === '') {
            throw new RuntimeException("No se pudo leer esquema de tabla {$table}.");
        }

        return rtrim($sql, ';');
    }

    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        return DB::connection()->getPdo()->quote((string) $value);
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        $quote = $driver === 'sqlite' ? '"' : '`';

        return $quote.str_replace($quote, $quote.$quote, $identifier).$quote;
    }

    private function databaseDriver(Connection $connection): string
    {
        $driver = $connection->getDriverName();
        if ($driver === 'sqlite') {
            return 'sqlite';
        }
        if ($driver === 'mariadb') {
            return 'mariadb';
        }
        if ($driver === 'mysql') {
            $version = (string) $connection->selectOne('SELECT VERSION() AS version')->version;

            return stripos($version, 'mariadb') !== false ? 'mariadb' : 'mysql';
        }
        throw new RuntimeException("Motor de base de datos [{$driver}] no soportado.");
    }

    private function header(Fundo $fundo, string $driver): string
    {
        $foreignKeysOff = $driver === 'sqlite' ? 'PRAGMA foreign_keys=OFF;' : 'SET FOREIGN_KEY_CHECKS=0;';

        return "-- Backup SQL portátil\n-- Fundo: {$fundo->getKey()}\n-- Driver: {$driver}\n-- Restauración interna usa database.ndjson parametrizado.\n{$foreignKeysOff}\n";
    }

    private function prune(Fundo $fundo, string $type, int $retentionCount): void
    {
        $expired = DatabaseBackup::query()
            ->forFundo($fundo)
            ->where('type', $type)
            ->where('status', DatabaseBackup::STATUS_COMPLETED)
            ->latest('completed_at')
            ->latest('id')
            ->get()
            ->slice(max(1, $retentionCount));

        foreach ($expired as $backup) {
            if ($backup->restores()->where('status', DatabaseBackup::STATUS_RUNNING)->exists()) {
                continue;
            }
            $backup->update(['expires_at' => now()]);
            $this->delete($backup, $fundo);
        }
    }

    private function cleanupPartialFile(DatabaseBackup $backup): void
    {
        if ($backup->path) {
            Storage::disk($backup->disk)->delete([$backup->path, $backup->path.'.part']);
        }
        Storage::disk($backup->disk)->deleteDirectory('.work/'.$backup->uuid);
    }

    private function markFailed(DatabaseBackup $backup, Throwable $exception): void
    {
        $backup->update([
            'status' => DatabaseBackup::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => mb_substr($exception->getMessage(), 0, 65535),
        ]);
    }

    private function markRestoreFailed(BackupRestore $restore, Throwable $exception): void
    {
        $restore->update([
            'status' => DatabaseBackup::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => mb_substr($exception->getMessage(), 0, 65535),
        ]);
    }

    private function releaseLock(Lock $lock): void
    {
        $lock->release();
    }
}
