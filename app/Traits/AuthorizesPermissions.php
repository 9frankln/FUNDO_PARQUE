<?php

namespace App\Traits;

use App\Services\AuditLogger;

trait AuthorizesPermissions
{
    protected function authorizePermission(string $module, string $action): void
    {
        abort_unless(
            auth()->user()?->tienePermiso($module, $action),
            403,
            'No tiene permiso para realizar esta acción.'
        );

        if ($action === 'exportar') {
            app(AuditLogger::class)->record(
                'datos.exportados',
                $module,
                'Solicitó exportación de '.$module.'.',
            );
        }
    }
}
