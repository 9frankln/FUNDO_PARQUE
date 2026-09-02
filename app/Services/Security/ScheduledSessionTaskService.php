<?php

namespace App\Services\Security;

use App\Models\ScheduledSessionTask;
use App\Models\User;
use App\Models\UserSession;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class ScheduledSessionTaskService
{
    public function create(int $fundoId, int $userId, string $tipo, DateTimeInterface $executeAt, ?int $createdBy = null): ScheduledSessionTask
    {
        return ScheduledSessionTask::create([
            'fundo_id' => $fundoId,
            'user_id' => $userId,
            'tipo' => $tipo,
            'execute_at' => $executeAt,
            'status' => ScheduledSessionTask::STATUS_PENDING,
            'created_by' => $createdBy,
        ]);
    }

    public function cancel(ScheduledSessionTask $task): void
    {
        if ($task->status === ScheduledSessionTask::STATUS_PENDING) {
            $task->update(['status' => ScheduledSessionTask::STATUS_CANCELLED]);
        }
    }

    /**
     * Ejecuta todas las tareas vencidas (sin importar el fundo). Devuelve cuántas se procesaron.
     */
    public function processDueTasks(): int
    {
        $sessions = app(UserSessionService::class);
        $processed = 0;

        ScheduledSessionTask::query()
            ->withoutGlobalScopes()
            ->where('status', ScheduledSessionTask::STATUS_PENDING)
            ->where('execute_at', '<=', now())
            ->orderBy('execute_at')
            ->each(function (ScheduledSessionTask $task) use ($sessions, &$processed): void {
                $processed++;

                try {
                    $result = DB::transaction(function () use ($task, $sessions): string {
                        $user = User::query()->withTrashed()->find($task->user_id);
                        if (! $user) {
                            $task->update(['status' => ScheduledSessionTask::STATUS_CANCELLED, 'result' => 'Usuario ya no existe.']);

                            return 'cancelado: usuario no existe';
                        }

                        if ($task->tipo === ScheduledSessionTask::TIPO_RESET) {
                            $count = $sessions->revokeAll($user, null, 'scheduled_reset');
                            $task->update(['status' => ScheduledSessionTask::STATUS_DONE, 'result' => "{$count} sesión(es) revocada(s)."]);

                            return "ok:{$count}";
                        }

                        // purge: elimina el historial revocado/expirado del usuario.
                        $deleted = UserSession::query()
                            ->where('user_id', $user->id)
                            ->whereNotNull('revoked_at')
                            ->delete();
                        $task->update(['status' => ScheduledSessionTask::STATUS_DONE, 'result' => "{$deleted} registro(s) eliminado(s)."]);

                        return "ok:{$deleted}";
                    });

                    logger()->info('Tarea programada de sesiones procesada', ['task' => $task->id, 'result' => $result]);
                } catch (Throwable $exception) {
                    report($exception);
                    $task->update(['status' => ScheduledSessionTask::STATUS_FAILED, 'result' => $exception->getMessage()]);
                }
            });

        return $processed;
    }
}
