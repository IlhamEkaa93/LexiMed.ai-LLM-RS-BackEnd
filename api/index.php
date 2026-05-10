<?php
// --- VERCEL SERVERLESS ULTIMATE FIX ---

// 1. Paksa buat folder cache di RAM Vercel (/tmp)
$storageFolders = [
    '/tmp/laravel/bootstrap/cache',
    '/tmp/laravel/storage/framework/sessions',
    '/tmp/laravel/storage/framework/views',
    '/tmp/laravel/storage/framework/cache'
];

foreach ($storageFolders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
}

// 2. Beritahu Laravel alamat rumah barunya untuk SEMUA hal yang berbau cache
putenv("APP_SERVICES_CACHE=/tmp/laravel/bootstrap/cache/services.php");
putenv("APP_PACKAGES_CACHE=/tmp/laravel/bootstrap/cache/packages.php");
putenv("APP_CONFIG_CACHE=/tmp/laravel/bootstrap/cache/config.php");
putenv("APP_ROUTES_CACHE=/tmp/laravel/bootstrap/cache/routes.php");
putenv("VIEW_COMPILED_PATH=/tmp/laravel/storage/framework/views");

// 3. Jalankan Laravel
require __DIR__ . '/../public/index.php';
