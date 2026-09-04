<?php

/*
|--------------------------------------------------------------------------
| Entrypoint Vercel (serverless)
|--------------------------------------------------------------------------
|
| Filesystem di Vercel read-only kecuali /tmp, jadi semua path yang ditulis
| Laravel saat runtime diarahkan ke /tmp dulu sebelum framework di-boot:
|
|   - storage/  : log, compiled view, cache file
|   - bootstrap/cache/ : packages.php & services.php. Runtime vercel-php
|     memasang dependency dengan "composer install --no-scripts", jadi
|     "artisan package:discover" tidak pernah jalan saat build dan Laravel
|     membuat kedua file itu saat boot pertama. Tanpa pengalihan ini, boot
|     gagal dengan "The bootstrap/cache directory must be present and
|     writable." dan request balik 500 tanpa body.
|
*/

$storagePath = '/tmp/storage';
$bootstrapCache = '/tmp/bootstrap/cache';

foreach ([
    $storagePath.'/app/public',
    $storagePath.'/framework/cache/data',
    $storagePath.'/framework/sessions',
    $storagePath.'/framework/views',
    $storagePath.'/logs',
    $bootstrapCache,
] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

$defaults = [
    'APP_STORAGE_PATH' => $storagePath,
    'VIEW_COMPILED_PATH' => $storagePath.'/framework/views',
    'LOG_CHANNEL' => 'stderr',
    'APP_PACKAGES_CACHE' => $bootstrapCache.'/packages.php',
    'APP_SERVICES_CACHE' => $bootstrapCache.'/services.php',
    'APP_CONFIG_CACHE' => $bootstrapCache.'/config.php',
    'APP_ROUTES_CACHE' => $bootstrapCache.'/routes-v7.php',
    'APP_EVENTS_CACHE' => $bootstrapCache.'/events.php',
];

foreach ($defaults as $key => $value) {
    if (getenv($key) === false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

/*
| Entrypoint-nya ada di /api, jadi SCRIPT_NAME = "/api/index.php" dan Symfony
| menghitung baseUrl = "/api" lalu memotong prefix itu dari path. Akibatnya
| route "/api/login" dicari sebagai "/login" dan balik 404. Disamakan dengan
| layout Laravel biasa (public/index.php di root) supaya path utuh.
*/
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/../public/index.php';
