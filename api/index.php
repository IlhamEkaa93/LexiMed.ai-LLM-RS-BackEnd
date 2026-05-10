<?php
// Paksa folder cache ke RAM Vercel agar tidak Error 500
$storagePath = '/tmp/storage';
if (!is_dir($storagePath . '/framework/views')) {
    mkdir($storagePath . '/framework/views', 0777, true);
}

putenv("APP_PACKAGES_CACHE=/tmp/packages.php");
putenv("APP_SERVICES_CACHE=/tmp/services.php");
putenv("VIEW_COMPILED_PATH=$storagePath/framework/views");

// Panggil Laravel secara murni
require __DIR__ . '/../public/index.php';
