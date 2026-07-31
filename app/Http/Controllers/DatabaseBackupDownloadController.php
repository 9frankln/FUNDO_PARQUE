<?php

namespace App\Http\Controllers;

use App\Models\DatabaseBackup;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DatabaseBackupDownloadController extends Controller
{
    public function __invoke(Request $request, string $backup): Response
    {
        $fundoId = (int) $request->session()->get('fundo_id');
        $request->user()->loadMissing('fundos');
        $membership = $request->user()->fundos->firstWhere('id', $fundoId);
        abort_unless((bool) $membership?->pivot?->es_administrador, 403, 'Solo administradores pueden descargar backups.');

        $record = DatabaseBackup::query()
            ->forFundo($fundoId)
            ->where('uuid', $backup)
            ->where('status', DatabaseBackup::STATUS_COMPLETED)
            ->firstOrFail();

        abort_unless($record->path && Storage::disk($record->disk)->exists($record->path), 404);

        app(AuditLogger::class)->record(
            'backup.descargado',
            'ajustes',
            'Descargó backup '.$record->filename.'.',
            metadata: ['backup_id' => $record->id, 'tipo' => $record->type],
        );

        return Storage::disk($record->disk)->download($record->path, $record->filename, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Type' => $record->format === DatabaseBackup::FORMAT_ZIP ? 'application/zip' : 'application/sql',
        ]);
    }
}
