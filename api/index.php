<?php
// --- 1. MATIKAN ERROR HTML AGAR TIDAK MERUSAK JSON ---
ini_set('display_errors', '0');

// --- 2. VERCEL CORS & PREFLIGHT FIX ---
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

// --- 3. VERCEL AUTHORIZATION HEADER FIX ---
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (isset($_SERVER['Authorization'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['Authorization'];
    } elseif (function_exists('getallheaders')) {
        $requestHeaders = getallheaders();
        $requestHeaders = array_change_key_case($requestHeaders, CASE_CAPITAL);
        if (isset($requestHeaders['AUTHORIZATION'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $requestHeaders['AUTHORIZATION'];
        }
    }
}

// --- 4. FIX LARAVEL ROUTE STRIPPING (Trik agar Laravel tidak membuang /api) ---
$_SERVER['SCRIPT_NAME'] = '/index.php';

// --- 5. VERCEL SERVERLESS CACHE FIX ---
$tmpDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache'
];
foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$_ENV['APP_SERVICES_CACHE'] = '/tmp/bootstrap/cache/services.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/bootstrap/cache/packages.php';
$_ENV['APP_CONFIG_CACHE'] = '/tmp/bootstrap/cache/config.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/bootstrap/cache/routes.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/bootstrap/cache/events.php';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_ENV['SESSION_DRIVER'] = 'array'; // Cegah nulis file session
$_ENV['LOG_CHANNEL'] = 'stderr';   // Arahkan log ke Vercel

// --- 6. JALANKAN LARAVEL ---
require __DIR__ . '/../public/index.php';
