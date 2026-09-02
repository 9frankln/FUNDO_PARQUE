<?php

namespace App\Console\Commands;

use App\Models\ConfiguracionSistema;
use App\Models\DatabaseBackup;
use App\Models\Fundo;
use App\Services\Backups\FundoDatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class RunScheduledBackups extends Command
{
    protected $signature = 'backups:run-scheduled';

    protected $description = 'Create due encrypted backups with configured content for active fundos';

    public function handle(FundoDatabaseBackupService $backups): int
    {
        Fundo::query()->where('activo', true)->orderBy('id')->each(function (Fundo $fundo) use ($backups): void {
            try {
                $settings = ConfiguracionSistema::query()
                    ->withoutGlobalScopes()
                    ->where('fundo_id', $fundo->getKey())
                    ->whereIn('clave', [
                        'backup_enabled',
                        'backup_interval_value',
                        'backup_interval_unit',
                        'backup_retention_count',
                        'backup_scope',
                        'backup_include_web',
                    ])
                    ->pluck('valor', 'clave');

                if (! filter_var($settings->get('backup_enabled', false), FILTER_VALIDATE_BOOL)) {
                    return;
                }

                $value = filter_var($settings->get('backup_interval_value'), FILTER_VALIDATE_INT);
                $unit = strtolower((string) $settings->get('backup_interval_unit'));
                if (! $this->validInterval($value, $unit)) {
                    $this->warn("Fundo {$fundo->getKey()}: invalid backup interval configuration.");

                    return;
                }

                $scope = strtolower((string) $settings->get('backup_scope', DatabaseBackup::TYPE_DATABASE));
                if (! in_array($scope, [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE], true)) {
                    $this->warn("Fundo {$fundo->getKey()}: invalid backup content configuration.");

                    return;
                }

                $lastScheduled = DatabaseBackup::query()
                    ->forFundo($fundo)
                    ->where('trigger', DatabaseBackup::TRIGGER_SCHEDULED)
                    ->where('type', $scope)
                    ->where('status', DatabaseBackup::STATUS_COMPLETED)
                    ->latest('created_at')
                    ->first();
                $dueAt = $lastScheduled?->created_at?->add($unit, $value);
                if ($dueAt && $dueAt->isFuture()) {
                    return;
                }

                $retention = filter_var($settings->get('backup_retention_count'), FILTER_VALIDATE_INT);
                $backups->create(
                    $fundo,
                    trigger: DatabaseBackup::TRIGGER_SCHEDULED,
                    retentionCount: $retention && $retention > 0 ? $retention : null,
                    scope: $scope,
                    components: [
                        'web' => filter_var($settings->get('backup_include_web', true), FILTER_VALIDATE_BOOL),
                    ],
                );
                $this->info("Fundo {$fundo->getKey()}: backup completed.");
            } catch (Throwable $exception) {
                report($exception);
                $this->error("Fundo {$fundo->getKey()}: {$exception->getMessage()}");
            }
        });

        return self::SUCCESS;
    }

    private function validInterval(int|false $value, string $unit): bool
    {
        return match ($unit) {
            'hour', 'hours' => $value !== false && $value >= 1 && $value <= 168,
            'day', 'days' => $value !== false && $value >= 1 && $value <= 30,
            default => false,
        };
    }
}
