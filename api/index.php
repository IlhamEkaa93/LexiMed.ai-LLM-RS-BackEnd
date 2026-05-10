<?php
// --- VERCEL SERVERLESS FIX ---
// Vercel itu Read-Only. Kita paksa Laravel nulis cache di RAM (/tmp)
$tmpCache = '/tmp/laravel/cache';
$tmpStorage = '/tmp/laravel/storage';

// Buat foldernya kalau belum ada di memori Vercel
if (!is_dir($tmpCache)) {
    mkdir($tmpCache, 0777, true);
}
if (!is_dir($tmpStorage . '/framework/views')) {
    mkdir($tmpStorage . '/framework/views', 0777, true);
}

// Beritahu Laravel alamat rumah barunya
putenv("APP_SERVICES_CACHE={$tmpCache}/services.php");
putenv("APP_PACKAGES_CACHE={$tmpCache}/packages.php");
putenv("APP_CONFIG_CACHE={$tmpCache}/config.php");
putenv("APP_ROUTES_CACHE={$tmpCache}/routes.php");
putenv("APP_EVENTS_CACHE={$tmpCache}/events.php");
putenv("VIEW_COMPILED_PATH={$tmpStorage}/framework/views");

try {
    // Jalankan Mesin Laravel
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode([
        'STATUS' => 'CRASH_SETELAH_TMP',
        'PESAN_ERROR_ASLI' => $e->getMessage()
    ]);
    exit;
}
