<?php

namespace App\Livewire\Ajustes\Traits;

use App\Models\AuditoriaLog;
use App\Models\DatabaseBackup;
use App\Models\LandingBlock;
use App\Services\AuditLogger;
use App\Services\Backups\FundoDatabaseBackupService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

trait HasDangerZoneManagement
{
    public bool $showDangerDeleteModal = false;

    public string $dangerPassword = '';

    public bool $dangerCreateBackup = false;

    public function openDangerDeleteModal(): void
    {
        $this->authorizeFundoAdmin();
        $this->resetDangerFields();
        $this->showDangerDeleteModal = true;
    }

    public function closeDangerDeleteModal(): void
    {
        $this->showDangerDeleteModal = false;
        $this->resetDangerFields();
    }

    public function resetDangerFields(): void
    {
        $this->dangerPassword = '';
        $this->dangerCreateBackup = false;
        $this->resetValidation();
    }

    /**
     * Conteo en vivo y desglosado de los datos operativos que se eliminarían del fundo actual.
     * Cada grupo muestra un subtotal y las partidas que lo componen, para que cada número
     * sea verificable contra lo que se ve en cada módulo.
     *
     * @return array{total:int, records:int, files:int, groups:array<int, array{label:string, total:int, items:array<string,int>}>}
     */
    public function dangerZoneCounts(): array
    {
        $fundoId = $this->fundoId();
        $count = fn (string $table) => (int) DB::table($table)->where('fundo_id', $fundoId)->count();

        // Subqueries (en lugar de pluck+whereIn) para ser escalable con miles de registros.
        $engordeSub = fn ($q) => $q->select('id')->from('lotes_engorde')->where('fundo_id', $fundoId);
        $pesajeSub = fn ($q) => $q->select('id')->from('engorde_animales')->whereIn('lote_id', $engordeSub);
        $ordenoSub = fn ($q) => $q->select('id')->from('ordenos')->where('fundo_id', $fundoId);
        $quesoSub = fn ($q) => $q->select('id')->from('producciones_queso')->where('fundo_id', $fundoId);

        $groups = [
            [
                'label' => 'Animales y engorde',
                'items' => [
                    'Animales' => $count('animales'),
                    'Lotes de engorde' => (int) DB::table('lotes_engorde')->where('fundo_id', $fundoId)->count(),
                    'Animales en lotes' => (int) DB::table('engorde_animales')->whereIn('lote_id', $engordeSub)->count(),
                    'Pesajes registrados' => (int) DB::table('pesajes_engorde')->whereIn('engorde_animal_id', $pesajeSub)->count(),
                ],
            ],
            [
                'label' => 'Ordeños y leche',
                'items' => [
                    'Ordeños' => $count('ordenos'),
                    'Detalles de ordeño' => (int) DB::table('ordeno_detalles')->whereIn('ordeno_id', $ordenoSub)->count(),
                ],
            ],
            [
                'label' => 'Producción de queso',
                'items' => [
                    'Producciones' => $count('producciones_queso'),
                    'Presentaciones' => (int) DB::table('produccion_queso_presentaciones')->whereIn('produccion_queso_id', $quesoSub)->count(),
                ],
            ],
            [
                'label' => 'Finanzas y asignaciones',
                'items' => [
                    'Movimientos' => $count('movimientos'),
                    'Asignaciones familiares' => $count('asignaciones_familiares'),
                ],
            ],
            [
                'label' => 'Monitoreo',
                'items' => [
                    'Registros de sanidad' => $count('sanidad_registros'),
                    'Dosis de tratamiento' => $count('tratamiento_dosis'),
                    'Partos' => $count('partos'),
                    'Alertas programadas' => $count('alertas_programadas'),
                ],
            ],
            [
                'label' => 'Botiquín (medicamentos e insumos)',
                'items' => [
                    'Lotes de medicamentos' => $count('medicamento_lotes'),
                    'Movimientos de medicamentos' => $count('medicamento_movimientos'),
                    'Lotes de insumos' => $count('insumo_lotes'),
                    'Movimientos de insumos' => $count('insumo_movimientos'),
                ],
            ],
            [
                'label' => 'Gestión web y contenido público',
                'items' => [
                    'Bloques de contenido web' => (int) DB::table('landing_blocks')->count(),
                    'Fotos y medios web' => (int) DB::table('media')->where('model_type', LandingBlock::class)->count(),
                ],
            ],
        ];

        $groups = array_map(function (array $group): array {
            $group['items'] = array_filter($group['items'], fn (int $v): bool => $v > 0);
            $group['total'] = array_sum($group['items']);

            return $group;
        }, $groups);

        $records = array_sum(array_column($groups, 'total'));
        $files = $this->operationalFilesCount();

        return [
            'total' => $records + $files,
            'records' => $records,
            'files' => $files,
            'groups' => $groups,
        ];
    }

    /** Número real de archivos adjuntos (fotos, evidencias, comprobantes) que se borrarían. */
    public function operationalFilesCount(): int
    {
        $files = $this->collectOperationalFiles($this->fundoId());

        return count($files['public']) + count($files['local']);
    }

    /**
     * Conteo en vivo de lo que se CONSERVA tras el borrado (usuarios, roles, config, backups).
     *
     * @return array<string, int>
     */
    public function dangerPreservedCounts(): array
    {
        $fundoId = $this->fundoId();

        return [
            'Usuarios con acceso' => (int) DB::table('fundo_user')->where('fundo_id', $fundoId)->count(),
            'Roles y permisos' => (int) DB::table('roles')
                ->where(fn ($query) => $query->whereNull('fundo_id')->orWhere('fundo_id', $fundoId))
                ->count(),
            'Configuración del sistema' => (int) DB::table('configuracion_sistema')->where('fundo_id', $fundoId)->count(),
            'Identidad visual (branding)' => (int) DB::table('branding_settings')->count(),
            'Backups existentes' => (int) DB::table('database_backups')->where('fundo_id', $fundoId)->count(),
        ];
    }

    public function confirmDangerDelete(FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();

        $this->validate([
            'dangerPassword' => ['required', 'string'],
        ], [
            'dangerPassword.required' => 'Ingresa tu contraseña para autorizar.',
        ]);

        if (! Hash::check($this->dangerPassword, auth()->user()->password)) {
            $this->addError('dangerPassword', 'La contraseña es incorrecta. No se realizó ningún cambio.');

            return;
        }

        $fundoId = $this->fundoId();
        $fundo = auth()->user()->fundoActivo();

        // 1) Recolectar rutas de archivos ANTES de borrar las filas.
        $files = $this->collectOperationalFiles($fundoId);

        // 2) Backup de seguridad opcional (antes del borrado con web incluido).
        $backup = null;
        if ($this->dangerCreateBackup) {
            try {
                set_time_limit(0);
                $backup = $backups->create(
                    fundo: $fundo,
                    requestedBy: auth()->user(),
                    trigger: DatabaseBackup::TRIGGER_MANUAL,
                    retentionCount: (int) ($this->backupSettings['retention_count'] ?? config('backups.retention_count', 10)),
                    scope: DatabaseBackup::TYPE_COMPLETE,
                    components: ['web' => true],
                );
            } catch (Throwable $exception) {
                report($exception);
                $this->dispatchWarning('Backup fallido', 'No se pudo crear la copia de seguridad. Se canceló el borrado.');

                return;
            }
        }

        // 3) Borrado total de datos operativos del fundo y gestión web.
        try {
            DB::transaction(function () use ($fundoId, $backups, $files): void {
                $backups->deleteFundoData($fundoId);
                $this->deleteAuditLogs($fundoId);
                $this->deleteStoredFiles($files);
                $this->deleteWebData();
            });

            Cache::forget('dashboard.stats.v2');

            app(AuditLogger::class)->record(
                'datos.borrados',
                'ajustes',
                'Ejecutó el borrado total de datos operativos del fundo y gestión web.',
                metadata: [
                    'backup_previo' => $this->dangerCreateBackup,
                    'backup_id' => $backup?->id,
                ],
            );

            $this->closeDangerDeleteModal();
            $this->dispatchSuccess('Borrado total completado', 'Todos los datos operativos, fotos y contenido web fueron eliminados.');
            $this->dispatch('$refresh');
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Borrado fallido', 'Ocurrió un error durante el proceso. Revisa los logs.');
        }
    }

    private function collectOperationalFiles(int $fundoId): array
    {
        $files = ['public' => [], 'local' => []];

        $publicTables = [
            'animales' => 'foto_ruta',
            'lotes_engorde' => 'foto_ruta',
            'ordeno_fotos_diarias' => 'foto_ruta',
            'producciones_queso' => 'foto_ruta',
            'medicamentos' => 'foto_ruta',
            'insumos' => 'foto_ruta',
        ];
        $localTables = [
            'movimientos' => 'comprobante_ruta',
            'asignaciones_familiares' => 'foto_ruta',
            'sanidad_registros' => 'evidencia_ruta',
            'registro_fotos' => 'ruta',
        ];

        foreach ($publicTables as $table => $column) {
            $files['public'] = array_merge($files['public'], DB::table($table)->where('fundo_id', $fundoId)->pluck($column)->all());
        }
        foreach ($localTables as $table => $column) {
            $files['local'] = array_merge($files['local'], DB::table($table)->where('fundo_id', $fundoId)->pluck($column)->all());
        }

        // Archivos multimedia de Gestión Web (Landing pública)
        $prefix = trim((string) config('media-library.prefix', ''), '/');
        $mediaRecords = DB::table('media')->where('model_type', LandingBlock::class)->get(['id', 'disk']);
        foreach ($mediaRecords as $media) {
            $directory = ($prefix !== '' ? $prefix.'/' : '').$media->id;
            $mediaDisk = (string) ($media->disk ?: 'public');
            if (in_array($mediaDisk, ['public', 'local'], true)) {
                $files[$mediaDisk] = array_merge($files[$mediaDisk], Storage::disk($mediaDisk)->allFiles($directory));
            }
        }

        return [
            'public' => array_values(array_unique(array_filter($files['public']))),
            'local' => array_values(array_unique(array_filter($files['local']))),
        ];
    }

    private function deleteStoredFiles(array $files): void
    {
        foreach ($files['public'] as $path) {
            Storage::disk('public')->delete($path);
        }
        foreach ($files['local'] as $path) {
            Storage::disk('local')->delete($path);
        }

        // Limpiar carpetas operativas completas (fotos, comprobantes, temporales)
        $publicDirs = [
            'fotos/animales',
            'fotos/engorde',
            'fotos/ordeno',
            'fotos/queso',
            'fotos/medicamentos',
            'fotos/insumos',
            'comprobantes',
            'livewire-tmp',
            '1',
            '2',
            '3',
        ];
        foreach ($publicDirs as $dir) {
            if (Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->deleteDirectory($dir);
            }
        }

        $localDirs = [
            'comprobantes',
            'finanzas',
            'asignaciones',
            'evidencias',
            'monitoreo',
            'sanidad',
            'partos',
            'livewire-tmp',
        ];
        foreach ($localDirs as $dir) {
            if (Storage::disk('local')->exists($dir)) {
                Storage::disk('local')->deleteDirectory($dir);
            }
        }
    }

    private function deleteWebData(): void
    {
        $prefix = trim((string) config('media-library.prefix', ''), '/');
        $mediaRecords = DB::table('media')->where('model_type', LandingBlock::class)->get(['id', 'disk']);
        foreach ($mediaRecords as $media) {
            $directory = ($prefix !== '' ? $prefix.'/' : '').$media->id;
            $disk = (string) ($media->disk ?: 'public');
            if (in_array($disk, ['public', 'local'], true)) {
                Storage::disk($disk)->deleteDirectory($directory);
            }
        }

        DB::table('media')->where('model_type', LandingBlock::class)->delete();
        DB::table('landing_blocks')->delete();
    }

    private function deleteAuditLogs(int $fundoId): void
    {
        AuditoriaLog::query()
            ->where(function ($query) use ($fundoId): void {
                $query->where('fundo_id', $fundoId)
                    ->orWhere(function ($global) use ($fundoId): void {
                        $global->whereNull('fundo_id')
                            ->where(function ($users) use ($fundoId): void {
                                $users->whereHas('usuario.fundos', fn ($fundos) => $fundos->where('fundos.id', $fundoId))
                                    ->orWhereHas('usuarioObjetivo.fundos', fn ($fundos) => $fundos->where('fundos.id', $fundoId));
                            });
                    });
            })
            ->delete();
    }
}
