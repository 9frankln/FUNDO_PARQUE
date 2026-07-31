<?php

namespace App\Services;

use App\Models\AuditoriaLog;
use App\Models\Fundo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function record(
        string $event,
        string $module,
        ?string $detail = null,
        ?User $targetUser = null,
        array $metadata = [],
        ?int $fundoId = null,
        string $result = 'exitoso',
        ?User $actor = null,
    ): AuditoriaLog {
        $request = $this->request();
        $sessionId = $request?->hasSession() ? $request->session()->getId() : null;
        $selectedFundoId = $fundoId ?? (session('fundo_id') ? (int) session('fundo_id') : null);
        $validFundoId = $selectedFundoId && Fundo::query()->whereKey($selectedFundoId)->exists()
            ? $selectedFundoId
            : null;

        return AuditoriaLog::create([
            'fundo_id' => $validFundoId,
            'user_id' => ($actor ?? auth()->user())?->id,
            'target_user_id' => $targetUser?->id,
            'accion' => $event,
            'event' => $event,
            'modulo' => $module,
            'detalle' => $detail,
            'ip_address' => $request?->ip(),
            'session_hash' => $sessionId ? hash('sha256', $sessionId) : null,
            'url' => $request ? '/'.$request->path() : null,
            'method' => $request?->method(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $this->sanitize($metadata),
            'result' => $result,
            'created_at' => now(),
        ]);
    }

    public function recordModel(Model $model, string $event, array $before = [], array $after = []): AuditoriaLog
    {
        $module = strtolower(class_basename($model));
        $fundoId = $model->getAttribute('fundo_id') ?? session('fundo_id');

        return $this->record(
            event: 'registro.'.$event,
            module: $module,
            detail: class_basename($model).' #'.$model->getKey(),
            metadata: [
                'modelo' => class_basename($model),
                'registro_id' => $model->getKey(),
                'antes' => $before,
                'despues' => $after,
            ],
            fundoId: $fundoId ? (int) $fundoId : null,
        );
    }

    private function request(): ?Request
    {
        return app()->bound('request') ? app(Request::class) : null;
    }

    private function sanitize(array $metadata): array
    {
        $blocked = ['password', 'password_confirmation', 'remember_token', 'session_id', 'session_hash', 'token', 'authorization'];

        return collect($metadata)->mapWithKeys(function ($value, $key) use ($blocked) {
            if (in_array(strtolower((string) $key), $blocked, true)) {
                return [];
            }

            if (is_array($value)) {
                return [$key => $this->sanitize($value)];
            }

            if ($value instanceof Model) {
                return [$key => $value->getKey()];
            }

            if (is_string($value)) {
                return [$key => str($value)->limit(2000, '')->toString()];
            }

            return [$key => $value];
        })->all();
    }
}
