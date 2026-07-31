<?php

namespace App\Traits;

use App\Services\AuditLogger;
use Illuminate\Support\Arr;

trait Auditable
{
    protected array $auditBefore = [];

    protected array $auditAfter = [];

    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::registrarAuditoria($model, 'creado', [], $model->getAttributes());
        });

        static::updating(function ($model) {
            $changes = Arr::except($model->getDirty(), ['updated_at']);
            $model->auditBefore = Arr::only($model->getOriginal(), array_keys($changes));
            $model->auditAfter = $changes;
        });

        static::updated(function ($model) {
            if ($model->auditAfter !== []) {
                self::registrarAuditoria($model, 'actualizado', $model->auditBefore, $model->auditAfter);
            }
        });

        static::deleted(function ($model) {
            self::registrarAuditoria($model, 'eliminado', $model->getAttributes(), []);
        });
    }

    protected static function registrarAuditoria($model, string $accion, array $before, array $after): void
    {
        if (! auth()->check()) {
            return;
        }

        app(AuditLogger::class)->recordModel($model, $accion, $before, $after);
    }
}
