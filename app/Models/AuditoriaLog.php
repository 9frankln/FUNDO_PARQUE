<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class AuditoriaLog extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected $table = 'auditoria_logs';

    protected $fillable = [
        'fundo_id', 'user_id', 'target_user_id', 'accion', 'event', 'modulo',
        'detalle', 'ip_address', 'session_hash', 'url', 'method', 'user_agent',
        'metadata', 'result', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fundo()
    {
        return $this->belongsTo(Fundo::class);
    }

    public function usuarioObjetivo()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Retención de auditoría: los registros con más de 180 días se eliminan
     * automáticamente (pruning diario vía `php artisan model:prune`).
     * Evita que la tabla crezca sin límite.
     */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(180));
    }
}
