<?php

namespace App\Console\Commands;

use App\Services\Security\ScheduledSessionTaskService;
use Illuminate\Console\Command;

class ProcessScheduledSessionTasks extends Command
{
    protected $signature = 'sessions:process-scheduled';

    protected $description = 'Ejecuta las tareas programadas de sesiones vencidas (restablecer / limpiar historial).';

    public function handle(ScheduledSessionTaskService $service): int
    {
        $processed = $service->processDueTasks();
        $this->info("Tareas programadas procesadas: {$processed}");

        return self::SUCCESS;
    }
}
