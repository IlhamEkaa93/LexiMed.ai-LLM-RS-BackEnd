<?php
// Fix Cache untuk Vercel Serverless
$storagePath = '/tmp/storage';
if (!is_dir($storagePath . '/framework/views')) {
    mkdir($storagePath . '/framework/views', 0777, true);
}
putenv("APP_SERVICES_CACHE=/tmp/services.php");
putenv("APP_PACKAGES_CACHE=/tmp/packages.php");
putenv("VIEW_COMPILED_PATH=$storagePath/framework/views");

// Jalankan Laravel murni
require __DIR__ . '/../public/index.php';
