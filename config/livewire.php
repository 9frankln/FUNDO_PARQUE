<?php

return [
    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:'.(int) env('LIVEWIRE_MAX_UPLOAD_KB', 10 * 1024 * 1024)],
        'max_upload_time' => (int) env('LIVEWIRE_MAX_UPLOAD_MINUTES', 1440),
    ],
];
