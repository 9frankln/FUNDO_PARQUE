<?php

return [
    'disk' => 'backups',
    'retention_count' => 10,
    'lock_seconds' => 3600,
    'archive_key' => env('BACKUP_ARCHIVE_KEY', env('APP_KEY')),
    'max_entries' => 50000,
    'max_uncompressed_bytes' => 20 * 1024 * 1024 * 1024,
    'max_compression_ratio' => 1000,
];
