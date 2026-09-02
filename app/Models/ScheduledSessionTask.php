<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledSessionTask extends Model
{
    use BelongsToFundo;

    public const TIPO_RESET = 'reset';

    public const TIPO_PURGE = 'purge';

    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'fundo_id', 'user_id', 'tipo', 'execute_at', 'status', 'result', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'execute_at' => 'datetime',
        ];
    }

    public function fundo(): BelongsTo
    {
        return $this->belongsTo(Fundo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
