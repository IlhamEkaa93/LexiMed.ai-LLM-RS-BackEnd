<?php
// --- 1. GERBANG CORS (Paling Atas!) ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- 2. VERCEL STORAGE REDIRECT ---
$storagePath = '/tmp/storage';
$cachePath = '/tmp/bootstrap/cache';

$folders = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $cachePath
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
}

// --- 3. PAKSA LARAVEL MENULIS KE /TMP ---
putenv("APP_SERVICES_CACHE={$cachePath}/services.php");
putenv("APP_PACKAGES_CACHE={$cachePath}/packages.php");
putenv("APP_CONFIG_CACHE={$cachePath}/config.php");
putenv("APP_ROUTES_CACHE={$cachePath}/routes.php");
putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");

// 4. Jalankan Laravel
require __DIR__ . '/../public/index.php';
