<?php
// ALAT RONTGEN (DIAGNOSTIK VERCEL)
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/cek-sistem') !== false) {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode([
        '1_STATUS' => 'Pengecekan Environment Variables Vercel',
        '2_APP_KEY' => getenv('APP_KEY') ? '? AMAN (Terisi)' : '? KOSONG! (Ini penyebab utama Error 500)',
        '3_DB_HOST' => getenv('DB_HOST') ? '? AMAN (Terisi)' : '? KOSONG! (Database mati)',
        '4_DB_PASSWORD' => getenv('DB_PASSWORD') ? '? AMAN (Terisi)' : '? KOSONG!',
        '5_SOLUSI' => 'Jika ada yang silang merah (?), tambahkan di Vercel -> Settings -> Environment Variables, lalu wajib REDEPLOY!'
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    // Jalankan Mesin Laravel Normal
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    echo json_encode([
        'PESAN_ERROR_ASLI' => $e->getMessage(),
        'FILE' => $e->getFile() . ' (Baris ' . $e->getLine() . ')'
    ]);
    exit;
}
