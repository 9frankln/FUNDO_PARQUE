<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BackupRestore extends Model
{
    protected $fillable = [
        'uuid', 'fundo_id', 'database_backup_id', 'pre_backup_id', 'requested_by', 'mode',
        'status', 'started_at', 'completed_at', 'failed_at', 'error_message',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $restore): void {
            $restore->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(DatabaseBackup::class, 'database_backup_id');
    }

    public function preBackup(): BelongsTo
    {
        return $this->belongsTo(DatabaseBackup::class, 'pre_backup_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
