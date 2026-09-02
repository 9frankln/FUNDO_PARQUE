<?php

namespace App\Http\Middleware;

use App\Models\ScheduledSessionTask;
use App\Services\Security\ScheduledSessionTaskService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcessScheduledSessionTasks
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('testing')) {
            // Respaldo sin cron: si hay tareas vencidas, ejecutarlas (consulta indexada y barata).
            $due = ScheduledSessionTask::query()
                ->withoutGlobalScopes()
                ->where('status', ScheduledSessionTask::STATUS_PENDING)
                ->where('execute_at', '<=', now())
                ->exists();

            if ($due) {
                app(ScheduledSessionTaskService::class)->processDueTasks();
            }
        }

        return $next($request);
    }
}
