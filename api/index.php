<?php
// --- 1. FORCE CORS PRIVILEGES ---
header('Access-Control-Allow-Origin: https://leximedai-olivia2026-web-technology.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Date, X-Api-Version');

// Tangani permintaan preflight (OPTIONS) dari browser
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- 2. SERVERLESS CACHE FIX ---
$tmp = ['/tmp/storage/framework/views', '/tmp/storage/framework/cache', '/tmp/storage/framework/sessions', '/tmp/bootstrap/cache'];
foreach ($tmp as $dir) { if (!is_dir($dir)) { mkdir($dir, 0777, true); } }

putenv("APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php");
putenv("APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php");
putenv("VIEW_COMPILED_PATH=/tmp/storage/framework/views");

// --- 3. RUN LARAVEL ---
require __DIR__ . '/../public/index.php';
