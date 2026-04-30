<?php

return [
    'url' => env('BACKUP_RELAY_URL'),
    'key' => env('BACKUP_RELAY_KEY'),
    'compress' => (bool) env('BACKUP_RELAY_COMPRESS', true),
    'timeout' => (int) env('BACKUP_RELAY_TIMEOUT', 60),
    'recipients' => array_values(array_filter(array_map('trim', explode(',', (string) env('BACKUP_RELAY_RECIPIENTS', ''))))),
];
