<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DatabaseBackup extends Model
{
    public const TRIGGER_MANUAL = 'manual';

    public const TRIGGER_SCHEDULED = 'scheduled';

    public const TRIGGER_UPLOADED = 'uploaded';

    public const TRIGGER_PRE_RESTORE = 'pre_restore';

    public const TYPE_DATABASE = 'database';

    public const TYPE_FILES = 'files';

    public const TYPE_COMPLETE = 'complete';

    public const FORMAT_SQL = 'sql';

    public const FORMAT_ZIP = 'zip';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid', 'fundo_id', 'requested_by', 'trigger', 'type', 'components', 'format', 'status', 'disk', 'path', 'filename',
        'database_driver', 'size_bytes', 'checksum_sha256', 'record_count', 'photo_count', 'manifest_version', 'started_at', 'completed_at',
        'failed_at', 'expires_at', 'integrity_verified_at', 'error_message',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $backup): void {
            $backup->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'components' => 'array',
            'record_count' => 'integer',
            'photo_count' => 'integer',
            'manifest_version' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'expires_at' => 'datetime',
            'integrity_verified_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function fundo(): BelongsTo
    {
        return $this->belongsTo(Fundo::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function restores()
    {
        return $this->hasMany(BackupRestore::class);
    }

    public function scopeForFundo(Builder $query, Fundo|int $fundo): Builder
    {
        $fundoId = $fundo instanceof Fundo ? $fundo->getKey() : $fundo;

        return $query->where($query->qualifyColumn('fundo_id'), $fundoId);
    }
}
