<?php
// Pastikan folder cache ada di RAM Vercel
$storagePath = '/tmp/storage';
if (!is_dir($storagePath . '/framework/views')) {
    mkdir($storagePath . '/framework/views', 0777, true);
}

// Beritahu Laravel untuk nulis manifest di /tmp, bukan di folder aplikasi
putenv("APP_PACKAGES_CACHE=/tmp/packages.php");
putenv("APP_SERVICES_CACHE=/tmp/services.php");
putenv("VIEW_COMPILED_PATH=$storagePath/framework/views");

// Jalankan Laravel secara murni (CORS akan dihandle otomatis oleh Laravel)
require __DIR__ . '/../public/index.php';
