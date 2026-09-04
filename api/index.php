<?php

/*
|--------------------------------------------------------------------------
| Entrypoint Vercel (serverless)
|--------------------------------------------------------------------------
|
| Filesystem di Vercel read-only kecuali /tmp, jadi semua path yang ditulis
| Laravel saat runtime (compiled view, log, cache) diarahkan ke /tmp dulu
| sebelum framework di-boot.
|
*/

$storagePath = '/tmp/storage';

foreach ([
    '/app/public',
    '/framework/cache/data',
    '/framework/sessions',
    '/framework/views',
    '/logs',
] as $dir) {
    if (! is_dir($storagePath.$dir)) {
        @mkdir($storagePath.$dir, 0755, true);
    }
}

$defaults = [
    'APP_STORAGE_PATH' => $storagePath,
    'VIEW_COMPILED_PATH' => $storagePath.'/framework/views',
    'LOG_CHANNEL' => 'stderr',
];

foreach ($defaults as $key => $value) {
    if (getenv($key) === false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

require __DIR__.'/../public/index.php';
